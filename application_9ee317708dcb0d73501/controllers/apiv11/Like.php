<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

class Like extends REST_Controller {


	public function update_post()
	{

		if (CHECK_REQUEST) {
			$this->check_key->check();
		}
		
		$this->load->model('like_model');

		$result = $this->like_model->like(
					$this->utils->get_user_id(),
					$this->obfuscate_id->id_decode($this->post('id_item'))
				);

		if ($result == 1)  
			$this->response(array('message' => $this->lang->line("like_like")), 200);
		else
			$this->response(array('message' => $this->lang->line("like_dislike")), 200);

	}





}
