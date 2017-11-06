<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

class Follow extends REST_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('follow_model');
    }

    public function follow_post()
    {
    	$id_follower = $this->utils->get_user_id();
    	$id_followed = $this->obfuscate_id->id_decode($this->post('id_followed'));

    	if ($id_follower == $id_followed)
    		$this->response(NULL, 403);

    	$response = $this->follow_model->add_new_follower($id_follower, $id_followed);

    	if ($response) 
    	{
    		$this->response(array('message' => $this->lang->line("follower_added")), 200);
    	}
    	else
    	{
    		$this->response(array('message' => $this->lang->line("follower_already_added")), 200);
    	}

    }

    public function un_follow_post()
    {
    	$id_followed = $this->obfuscate_id->id_decode($this->post('id_followed'));
    	$id_follower = $this->utils->get_user_id();

    	if ($id_follower == $id_followed)
    		$this->response(NULL, 403);

    	$this->follow_model->delete_follower($id_follower, $id_followed);

    	$this->response(array('message' => $this->lang->line("follower_removed")), 200);
    }


	
}