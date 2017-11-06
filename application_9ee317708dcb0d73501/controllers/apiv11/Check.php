<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

class Check extends REST_Controller {


	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_model5');
	}

	public function is_email_availaible_get()
	{

		if (!$this->get('email'))
			$this->response(NULL, 403);

		$result = $this->user_model5->is_email_availaible($this->get('email'));

		if ($result > 0)  
			$this->response(array('error' => $this->lang->line("check_email_already_used")), 200);
		else 
			$this->response(array('message' => $this->lang->line("check_email_not_used")), 200);
	}

	public function is_username_availaible_get()
	{
		if (!$this->get('pseudo'))
			$this->response(NULL, 403);

		$result = $this->user_model5->is_username_available($this->get('pseudo'));

		if ($result > 0) 
			$this->response(array('error' => $this->lang->line("check_pseudo_already_used")), 200);
		else
			$this->response(array('message' => $this->lang->line("check_pseudo_not_used")), 200);
	}



}