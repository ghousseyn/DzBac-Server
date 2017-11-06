<?php defined('BASEPATH') OR exit('No direct script access allowed');


require_once APPPATH.'/libraries/REST_Controller.php';

class Signalement extends REST_Controller {

	function __construct()
	{
		 parent::__construct();  
		 $this->load->model('signalisation_model');
	}


	public function list_get()
	{
		$offset = ($this->get('page') * 25 ) - 25;
		$result = $this->signalisation_model->get_posts_signaled($offset);
		$this->response($result, 200);
	}

	public function test_user_get() 
	{
		echo $this->obfuscate_id->id_encode(10);
	}


	public function index_put()
	{
		$this->signalisation_model->signale(
				$this->utils->get_user_id(),
				$this->obfuscate_id->id_decode($this->put('id_content'))
			);


		$this->response(array('message' => 'Merci pour ton aide ;D'), 200);
	}


	public function delete_signal_post()
	{
		$id_content = $this->obfuscate_id->id_decode($this->post('id_content'));

		$this->signalisation_model->delete_signalement($id_content);
		$this->response(array('message' => 'Signalement supprimé !'), 200);
	}



	
}