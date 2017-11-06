<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Likes {

	private $CI;
	
	function __construct()
	{
		$this->CI = & get_instance();
	}
	
	function like($type_item, $message_item, $id_item, $id_membre)
	{
		if ($this->CI->my_statesession->state_user)
		{
			$this->CI->load->model('like_model');
		
			if ($this->CI->like_model->like($type_item, $id_membre, $id_item))
			{
				$response['success'] = 1;
				$response['message'] = "Vous aimez "  . $message_item .  " !";
			}
			else
			{
				$response['success'] = 1;
				$response['message'] = "Vous n'aimez plus " . $message_item . " !";
			}
		}
		else
		{
			$response['success'] = 0;
			$response['message'] = "Vous devez être connecter pour aimer des items";
		}
		
		echo json_encode($response);	
	}
	
	

	
}