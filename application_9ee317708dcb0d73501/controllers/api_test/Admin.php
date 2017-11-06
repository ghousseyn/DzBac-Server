<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

class Admin extends REST_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('admin_model');
    }

    public function update_level_post()
    {
        $this->db->set('level', 1, FALSE);
        $this->db->update('membres');
    }


    public function graduation_post()
    {
    	if ($this->post('password') != 'c7a0b41248bcb6d53cacf92509eafa70abd48fe6')
    		$this->response(NULL, 403);

    	$id_membre = $this->post('id_membre');
    	$message = $this->post('message');
    	$title = $this->post('title');
    	$level = $this->post('level');

    		// on met level à zero
	 	$this->db->where('id', $id_membre);
		$this->db->set('level', $level, FALSE);
		$this->db->update('membres');

		$this->load->library('GCMPushMessage');

		// On récupère l'id gmc du membre 
        $this->db->select("gcm_id");
        $this->db->from('membres');
        $this->db->where("id", $id_membre);
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            # code...
            $gcm_id = $row->gcm_id;
        }

        $this->gcmpushmessage->setDevices($gcm_id);

        $this->gcmpushmessage->send(null,
            array(   
              'message' => $message,
              'level' => $level,
              'type_notification' => 'level_user'
            ), null, 2419200); 
	
		$this->response(array('message' => 'Grade modifié !'), 200);
    }


    /**
      * Pour bannir un utilisateur de l'application
      */
    public function bann_put()
    {

    	$id_banned = $this->obfuscate_id->id_decode($this->put('id_banned'));

    	$result = $this->admin_model->bann_user(
    		$id_banned,
			$this->utils->get_user_id()
    	);

    	if ($result)
    	{
    		$this->load->library('GCMPushMessage');

    		// On récupère l'id gmc du membre banni
	        $this->db->select("gcm_id");
	        $this->db->from('membres');
	        $this->db->where("id", $id_banned);
	        $query = $this->db->get();

	        foreach ($query->result() as $row) {
	            # code...
	            $gcm_id = $row->gcm_id;
	        }

	        $this->gcmpushmessage->setDevices($gcm_id);

	        $this->gcmpushmessage->send(null,
	            array(   
	              'message' => $this->lang->line("admin_you_have_been_banned"),
	              'type_notification' => 'user_banned'
	            ), null, 2419200); 
    	
    		$this->response(array('message' => $this->lang->line("admin_user_banned")), 200);
    	}
    	else
    	{
    		$this->response(array('message' => $this->lang->line("admin_user_already_banned")), 200);
    	}
	}

	
}