<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

class User extends REST_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_model3');

	}

	public function ranking_get()
	{
		$response = $this->user_model3->get_ranking();

		$this->response($response, 200);
	}

	public function state_get()
	{
		$this->load->model('messagerie_model2');
		$this->load->model("notification_model");
		$this->load->model('admin_model');

		$id_membre = $this->utils->get_user_id();

        // On update la date de l'activité de l'utilisateur
        $data = array(
            'date_last_activity' => date("Y-m-d H:i:s")
        );
        
        $this->db->where('id', $id_membre);
        $this->db->update('membres', $data);

		$response = $this->notification_model->get_number_notification_unread(
			$id_membre
		);

		$response['nombre_message_non_lu'] = $this->messagerie_model2->get_nombre_msg_non_lu(
			$this->utils->get_user_id()
		);

		$banned = $this->admin_model->is_user_already_banned($id_membre);

		if ($banned)
			$response['state_bann'] = 1;
		else
			$response['state_bann'] = 0;

		$this->response($response, 200);
	}


	public function login_get()
	{
		if (!$this->get('email') && !$this->get('password'))
			$this->response(NULL, 403);

		$result = $this->user_model3->login(
				$this->get('email'),
				$this->get('password'),
				$this->get('gcm_id')
			);

		if ($result != 0)
			$this->response($result , 200);
		else   
			$this->response(array('error' => $this->lang->line("user_error_login")) , 404);
	}

	public function login_facebook_get()
	{
		if (!$this->get('id_facebook'))
			$this->response(NULL, 403);

		$result = $this->user_model3->login_facebook(
				$this->get('id_facebook'),
				$this->get('gcm_id')
			);

		if ($result != 0)
			$this->response($result , 200);
		else   
			$this->response(array('error' => $this->lang->line("user_error_inscrire_compte")) , 404);
	}

	public function login_google_get()
	{
		if (!$this->get('id_google'))
			$this->response(NULL, 403);

		$result = $this->user_model3->login_google(
				$this->get('id_google'),
				$this->get('gcm_id')
		);

		if ($result != 0)
			$this->response($result, 200);
		else
			$this->response(array('error' => $this->lang->line("user_error_inscrire_compte")) , 404);
	}

	public function register_put()
	{
		if (!$this->put('email') && !$this->put('password'))
			$this->response(NULL, 403);

		$result = $this->user_model3->register(
				$this->put('username'),
				$this->put('password'),
				$this->put('email'),
				$this->put('gcm_id')
			);

		if ( (array_key_exists('success', $result)))
			$this->response($result, 403);
		else
		{
			$this->response($result, 200);
		}
	}

	public function register_facebook_put()
	{
		if (!$this->put('email') && !$this->put('password'))
			$this->response(NULL, 403);

		$result = $this->user_model3->register_facebook(
				$this->put('username'),
				$this->put('id_facebook'),
				$this->put('id_facebook'),
				$this->put('email'),
				$this->put('url_avatar'),
				$this->put('gcm_id')
			);

		if ( (array_key_exists('success', $result)))
			$this->response($result, 403);
		else
			$this->response($result, 200);
	}

	public function register_google_put()
	{
		if (!$this->put('email') && !$this->put('id_google'))
			$this->response(NULL, 403);

		$result = $this->user_model3->register_google(
				$this->put('username'),
				$this->put('id_google'),
				$this->put('email'),
				$this->put('url_avatar'),
				$this->put('gcm_id')
			);

		if ( (array_key_exists('success', $result)))
			$this->response($result, 403);
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

		$result = $this->user_model3->get_informations(
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
		
		$result = $this->user_model3->update_informations(
				$this->utils->get_user_id(),
				json_decode($this->post('params'), true)
			);
		
		$this->response(array('message' => $this->lang->line("user_update_data")), 200);
	}


}