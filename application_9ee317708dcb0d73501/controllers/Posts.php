<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Posts extends CI_Controller {


	public function index($id)
	{
	//echo 'id : ' . $id;
		/*$this->load->model("content_model_web");

		$id_content = $this->obfuscate_id->id_decode($id);

		$data = $this->content_model_web->get_content($id_content);
		
		if (count($data['contents']) == 0)
			echo "<h1>Oops ce post n'existe pas ! :(</h1>";
		else
		{
			//print_r($data['contents'][0]);
			if ($data['contents'][0]['filename'] == null || empty($data['contents'][0]['filename']))
				$data['contents'][0]['hide'] = true;
			else
				$data['contents'][0]['hide'] = false;

			if ($data['contents'][0]['audio'] == null || empty($data['contents'][0]['audio']))
				$data['contents'][0]['hide_audio'] = true;
			else
				$data['contents'][0]['hide_audio'] = false;
				
			$this->load->view('posts', $data['contents'][0]);	
		}
		*/
		redirect('https://play.google.com/store/apps/details?id=com.squalala.dzbac&hl=fr');
		header("https://play.google.com/store/apps/details?id=com.squalala.dzbac&hl=fr");
		//die();
	}


}
