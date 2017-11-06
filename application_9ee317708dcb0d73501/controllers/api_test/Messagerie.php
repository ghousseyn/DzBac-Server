<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

class Messagerie extends REST_Controller {


	public function __construct()
	{
		parent::__construct();
		$this->load->model('messagerie_model2');

		if (CHECK_REQUEST) {
			$this->check_key->check();
		}
		
	}


	public function message_non_lu_new_get()
	{
		$result = $this->messagerie_model2->get_message_non_lu_new(
			$this->utils->get_user_id()
		);

		$this->response(array("nombre_message_non_lu"=> $result), 200);
	}

	public function message_non_lu_get()
	{
		$result = $this->messagerie_model2->get_nombre_msg_non_lu(
			$this->utils->get_user_id()
		);

		$this->response(array("nombre_message_non_lu"=> $result), 200);
	}

	public function block_post()
	{
		$id_blocked = $this->obfuscate_id->id_decode($this->post('id_blocked'));

		$result = $this->messagerie_model2->block_user(
			$this->utils->get_user_id(),
			$id_blocked
		);

		if ($result)
			$this->response(array('message' => $this->lang->line("messagerie_user_blocked")), 200);
		else
			$this->response(array('message' => $this->lang->line("messagerie_user_already_blocked")), 200);
	}
	

	public function conversation_put()
	{

		if ($this->utils->get_user_id() == $this->put('id_receveur'))
			$this->response(array('message' => $this->lang->line("messagerie_error_same_person")), 200);

		$result = $this->messagerie_model2->start_conversation(
				$this->put('pseudo_expediteur'),
				$this->utils->get_user_id() ,
				$this->obfuscate_id->id_decode($this->put('id_receveur')),
				$this->put('message'), 
				$this->put('sujet')
			);	

		if ($result)
			$this->response(array('message' => $this->lang->line("messagerie_sended")), 200);
		else  
			$this->response(array('error' => $this->lang->line("messagerie_error_not_sended")), 404);

	}


	public function list_conversations_get()
	{
		$offset = ($this->get('page') * 25) - 25;

		$result = $this->messagerie_model2->get_list_conversations(
				$this->utils->get_user_id(),
				$offset
			);

		$this->response($result, 200);
	}

	public function conversation_delete()
	{
		$result = $this->messagerie_model2->delete_conversation(
			$this->utils->get_user_id(),
			$this->obfuscate_id->id_decode_new($this->delete('id_header'), KEY_CONVERSATION)
		);
		
		if ($result) 
			$this->response(array('message' => $this->lang->line("messagerie_converstion_deleted")), 200);
		else
			$this->response(array('message' => $this->lang->line("messagerie_converstion_not_deleted")), 400);
	}


	public function conversation_get()
	{
		$offset = ($this->get('page') * 25) - 25;

		$result = $this->messagerie_model2->get_conversation(
				$this->obfuscate_id->id_decode_new($this->get('id_header'), KEY_CONVERSATION),
				$this->utils->get_user_id(),
				$offset
			);

		$this->response($result, 200);
	}


	public function send_put()
	{
		$result = $this->messagerie_model2->send_message(
				$this->put('message'),
				$this->utils->get_user_id(),
				$this->obfuscate_id->id_decode($this->put('id_receveur')),
				$this->obfuscate_id->id_decode_new($this->put('id_header'), KEY_CONVERSATION)
			);

		if ($result) 
			$this->response(array('message' => $this->lang->line("messagerie_sended_2")), 200);
		else
			$this->response(array('message' => $this->lang->line("messagerie_error_not_sended_2")), 400);
	}



}