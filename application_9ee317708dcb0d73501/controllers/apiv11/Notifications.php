<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

class Notifications extends REST_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model("notification_model2");
    }

    public function notifications_news_get()
    {
		$response = $this->notification_model2->get_new_notifications(
			$this->utils->get_user_id()
		);

		$this->response($response, 200);
    }

    /*
     * Pour quitter un groupe
     */
    public function remove_from_group_post()
    {
    	$this->notification_model2->remove_in_group(
    		$this->utils->get_user_id(),
    		$this->obfuscate_id->id_decode($this->post('id_item')),
    	    'contents' // $this->post('type_item')
    	);

    	$this->response(array("state_group" => false), 200);
    }

    /*
     * Rejoindre un groupe
	 */
    public function join_group_post()
    {
   	    $this->notification_model2->add_to_group(
    		$this->utils->get_user_id(),
    		$this->obfuscate_id->id_decode($this->post('id_item')),
    	    'contents'
    	);

    	$this->response(array("state_group" => true), 200);
    }

    /*
     * Mettre toutes nos notifications en lus
     *
     */
    public function all_notification_read_post()
    {
		$response = $this->notification_model2->set_all_notifications_read(
			$this->utils->get_user_id()
		);

		$this->response(array("message" => 'OK'), 200);
    }

    public function get_nomber_notification_get()
    {
        $id_membre = $this->utils->get_user_id();

        // On update la date de l'activité de l'utilisateur
        $data = array(
            'date_last_activity' => date("Y-m-d H:i:s")
        );
        
        $this->db->where('id', $id_membre);
        $this->db->update('membres', $data);


		$response = $this->notification_model2->get_number_notification_unread(
			$id_membre
		);

		$this->response($response, 200);
    }

    public function list_get()
    {
		$offset = ($this->get('page') * 25) - 25;

		$response = $this->notification_model2->get_notifications(
			$this->utils->get_user_id(),
			$offset
		);

		$this->response($response, 200);
    }




}