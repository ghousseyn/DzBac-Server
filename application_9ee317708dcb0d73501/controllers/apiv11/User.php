<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

class User extends REST_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_model6');

	}

	public function ranking_get()
	{
		$response = $this->user_model6->get_ranking();

		$this->response($response, 200);
	}

	public function friends_get()
	{
		$digits_ids = json_decode($this->get('data'), true);

		$response = $this->user_model6->get_friends($digits_ids);

		$this->response($response, 200);
	}

	public function search_post()
	{
		$data = json_decode($this->post('data'), true);
		$offset = ($this->post('page') * 25 ) - 25;
		$response = $this->user_model6->get_user($offset, $data);
		$this->response($response, 200);
	}

	public function state_get()
	{
		$this->load->model('messagerie_model4');
		$this->load->model("notification_model2");
		$this->load->model('admin_model');
		$this->load->model('payement_model'); 
		$this->load->model('user_model6');

		$id_membre = $this->utils->get_user_id();

        // On update la date de l'activité de l'utilisateur
        $data = array(
        	'version_code_app' => $this->get('version_code_app'),
            'date_last_activity' => date("Y-m-d H:i:s"),
            'ip_adress' => $_SERVER['REMOTE_ADDR']
        );
        
        $this->db->where('id', $id_membre);
        $this->db->update('membres', $data);

		$response = $this->notification_model2->get_number_notification_unread(
			$id_membre
		);

		$response['nombre_message_non_lu'] = $this->messagerie_model4->get_nombre_msg_non_lu(
			$this->utils->get_user_id()
		);

		$banned = $this->admin_model->is_user_already_banned($id_membre);

		if ($banned)
			$response['state_bann'] = 1;
		else
			$response['state_bann'] = 0;

		if ($id_membre == 0)
			$response['state_bann'] = 1;

		$response['min_app_version'] = 72;

		$response['level_contribution'] = $this->user_model6->get_level_contribution($id_membre);

		$response['prenium'] = $this->payement_model->add_user($id_membre, $this->get('android_id'));

		$this->response($response, 200);
	}


	public function login_get()
	{
		if (!$this->get('email') && !$this->get('password'))
			$this->response(NULL, 403);

		$result = $this->user_model6->login(
				$this->get('email'),
				$this->get('password'),
				$this->get('gcm_id')
			);

		if ($result != 0)
			$this->response($result , 200);
		else   
			$this->response(array('error' => $this->lang->line("user_error_login")) , 200);
	}

	public function login_facebook_get()
	{
		if (!$this->get('id_facebook'))
			$this->response(NULL, 403);

		$result = $this->user_model6->login_facebook(
				$this->get('id_facebook'),
				$this->get('gcm_id')
			);

		if ($result != 0)
			$this->response($result , 200);
		else   
			$this->response(array('error' => $this->lang->line("user_error_inscrire_compte")) , 200);
	}

	public function login_google_get()
	{
		if (!$this->get('id_google'))
			$this->response(NULL, 403);

		$result = $this->user_model6->login_google(
				$this->get('id_google'),
				$this->get('gcm_id')
		);

		if ($result != 0)
			$this->response($result, 200);
		else
			$this->response(array('error' => $this->lang->line("user_error_inscrire_compte")) , 200);
	}

	public function register_put()
	{
		if (!$this->put('email') && !$this->put('password'))
			$this->response(NULL, 403);

		$result = $this->user_model6->register(
				$this->put('username'),
				$this->put('password'),
				$this->put('email'),
				$this->put('gcm_id')
			);

		if ( (array_key_exists('success', $result)))
			$this->response($result, 200);
		else
		{
			$this->response($result, 200);
		}
	}

	public function register_facebook_put()
	{
		if (!$this->put('email') && !$this->put('password'))
			$this->response(NULL, 403);

		$result = $this->user_model6->register_facebook(
				$this->put('username'),
				$this->put('id_facebook'),
				$this->put('id_facebook'),
				$this->put('email'),
				$this->put('url_avatar'),
				$this->put('gcm_id')
			);

		if ( (array_key_exists('success', $result)))
			$this->response($result, 200);
		else
			$this->response($result, 200);
	}

	public function register_google_put()
	{
		if (!$this->put('email') && !$this->put('id_google'))
			$this->response(NULL, 403);

		$result = $this->user_model6->register_google(
				$this->put('username'),
				$this->put('id_google'),
				$this->put('email'),
				$this->put('url_avatar'),
				$this->put('gcm_id')
			);

		if ( (array_key_exists('success', $result)))
			$this->response($result, 200);
		else
			$this->response($result, 200);
	}

	/*
	 * Voir le profil d'un utilisateur
	 *
	 */
	public function show_get()
	{
		if (CHECK_REQUEST) {
			$this->check_key->check();
		}

		$result = $this->user_model6->get_informations(
			$this->obfuscate_id->id_decode($this->get('id_membre'))
		);

		$this->response($result, 200);	
	}

    /*
     * Obtenir les posts d'un utilisateur particulier
     *
     */
	public function posts() 
	{

	}

	public function update_post()
	{
		if (CHECK_REQUEST) {
			$this->check_key->check();
		}

		$data = json_decode($this->post('params'), true);

		// Si c'est un update/insert de phone on vérifie qu'il n'a pas été banni
		if (array_key_exists('phone', $data)) {
			$this->load->model('admin_model');

			$phone = $this->db->escape($data['phone']);
			$banned = $this->admin_model->is_phone_banned($phone);
			$isPhoneAlreadyUsed = $this->admin_model->is_phone_already_used($phone, $this->utils->get_user_id());

			if ($banned) {

				$this->admin_model->bann_user($this->utils->get_user_id(), 10);

				$this->response(array('message' => $this->lang->line("user_banned"),
									  'state_bann' => 1), 200);
			}
			else if ($isPhoneAlreadyUsed) {
				$this->response(array('message' => $this->lang->line("phone_already_used"),
									  'error' => 1), 200);
			}
		}
		
		$result = $this->user_model6->update_informations(
				$this->utils->get_user_id(),
				$data
			);
		
		$this->response(array('message' => $this->lang->line("user_update_data")), 200);
	}


}