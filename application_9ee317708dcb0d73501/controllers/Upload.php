<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

class Upload extends REST_Controller {


	public function __construct()
	{
		parent::__construct();
		$this->load->model('upload_model');

		if (CHECK_REQUEST) {
			$this->check_key->check();
		}
	}

	public function file_post()
	{
	//	$this->load->helper(array('form', 'url'));

//		$new_file_name =  sha1(microtime()) . '_' .$_FILES['file']['name'];
		$new_file_name = $_FILES['file']['name'];

		$config['file_name'] = $new_file_name;
		$config['upload_path'] = realpath(APPPATH . '../uploads');
		$config['allowed_types'] = 'pdf|doc|docx|txt|rar|zip';
		$config['max_size']	= 10240; // 10 Mo

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload('file'))
		{
		//$error = array('error' => $this->upload->display_errors());

            $this->response(array('message' =>  'Fichier non uploadé'), 200);
		}
		else
		{
			$data = $this->upload->data();

			$this->response(array('filename' =>  $data['file_name']), 200);
		}

	}

	public function audio_post()
	{
		//new String[]{".m4a" , ".3gp", ".ts", ".ogg", ".mp3", ".mp4", ".aac", ".wav"};
	//	$this->load->helper(array('form', 'url'));

//		$new_file_name =  sha1(microtime()) . '_' .$_FILES['file']['name'];
		$new_file_name = sha1(microtime()); //. '_' . $_FILES['file']['name'];

		//var_dump($_FILES['file']);

		//die();

		$config['file_name'] = $new_file_name;
		$config['upload_path'] = realpath(APPPATH . '../audio');

		if ((strpos($_FILES['file']['name'], 'm4a') !== false) 
			||
			(strpos($_FILES['file']['name'], '3gpp') !== false)
			||
			(strpos($_FILES['file']['name'], '3gpp') !== false)
			||
			(strpos($_FILES['file']['name'], '3gp') !== false)
			||
			(strpos($_FILES['file']['name'], 'mp3') !== false)
			||
			(strpos($_FILES['file']['name'], 'aac') !== false)
			||
			(strpos($_FILES['file']['name'], 'wav') !== false)
			||
			(strpos($_FILES['file']['name'], 'ts') !== false)

			) {
		  	$config['allowed_types'] = '*'; //|3gp|ts|ogg|mp3|mp4|aac|wav';
		}
		else {
			$config['allowed_types'] = '3gp|ts|ogg|mp3|mp4|aac|wav';
		}

		$config['max_size']	= 15360 * 2; // 15 Mo

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload('file'))
		{
		//$error = array('error' => $this->upload->display_errors());
			 $error = array('error' => $this->upload->display_errors());
			// print_r($error);
            $this->response(array('message' =>  'Fichier non uploadé'), 200);
		}
		else
		{
			$data = $this->upload->data();

			$this->response(array('filename' =>  $data['file_name']), 200);
		}

	}

	public function video_post()
	{
		$new_file_name = sha1(time()) . '_' . $_FILES['file']['name'];

		$config['file_name'] = $new_file_name;
		$config['upload_path'] = realpath(APPPATH . '../videos');
		$config['allowed_types'] = 'mp4|3gp';
		$config['max_size']	= 10240 * 10; // 100 Mo

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload('file'))
		{
		    $error = array('error' => $this->upload->display_errors());
            $this->response(array('message' =>  'Fichier non uploadé', $error), 200);
		}
		else
		{
			$data = $this->upload->data();

			$this->response(array('filename' =>  'http://www.squalala.xyz/dz_bac/videos/' .$data['file_name']), 200);
		}

	}

	public function delete_file_post()
	{
		if ($this->post('filename') == 'index.html')
			$this->response(array('error' => "Don't try to hack me please !"), 403);

		$path_file = realpath(APPPATH . '../uploads') . '/' . $this->post('filename');

		if (file_exists($path_file) && !is_dir($path_file)) {
				if (unlink($path_file)) {

					$this->load->model('content_model');

					$id = $this->obfuscate_id->id_decode($this->post('id_content'));

					$data = array('filename' => '');

					$this->content_model->update($id, $data);


					$this->response(array('message' => "Fichier supprimé"), 200);
				}
				else
					$this->response(array('error' => "Fichier non supprimé"), 200);
		}
		else {
			$this->response(array('error' => "Ce fichier n'existe pas."), 200);
		}
	}

	public function delete_audio_post()
	{
		if ($this->post('filename') == 'index.html')
			$this->response(array('error' => "Don't try to hack me please !"), 403);

		$path_file = realpath(APPPATH . '../audio') . '/' . $this->post('filename');

		if (file_exists($path_file) && !is_dir($path_file)) {
				if (unlink($path_file)) {

					$this->load->model('content_model');

					$id = $this->obfuscate_id->id_decode($this->post('id_content'));

					$data = array('audio' => '');

					$this->content_model->update($id, $data);


					$this->response(array('message' => "Fichier supprimé"), 200);
				}
				else
					$this->response(array('error' => "Fichier non supprimé"), 200);
		}
		else {
			$this->response(array('error' => "Ce fichier n'existe pas."), 200);
		}
	}

	public function images_item_post()
	{
		$path_images = '/images/posts/' . $this->post('id') . '/';
		$this->load->library('Uploadhandler');
		$this->uploadhandler->init_uploadhandler($path_images);
	}


	public function images_info_post()
	{
		$path_images = 'images/posts/'. $this->post('id') . '/';
		$path = $path_images . 'thumbnail';
		$url_image_presentation = $this->post('url_image_presentation');
		$files = $this->images->get_all_images($path);

		if (count($files) > 0)
		{
			if ( $url_image_presentation == NULL || $url_image_presentation === 0  ||  
			!in_array($url_image_presentation, $files) || $url_image_presentation == 'none')
				$url_image_presentation = $this->images->get_first_image($path);
		}
		else
			$url_image_presentation = "djihti_no_image.png";

		$result = $this->upload_model->update_info_images(
			$url_image_presentation,
			$this->obfuscate_id->id_decode($this->post('id')) ,
			$this->images->get_path_images($path)
		);

		if ($result)
			$this->response(array('message' => $this->lang->line("upload_photo_modified")), 200);
		else
			$this->response(array('message' =>  $this->lang->line("upload_photo_not_modified")), 400);
		
	}

	public function delete_post() 
	{
		$path =  $this->post('id') . '/';

		unlink('images/posts/' . $path . $this->post('filename'));
	//	unlink('images'. $path . 'facebook/' . $filename);
	    unlink('images/posts/'. $path . 'medium/' . $this->post('filename'));
		unlink('images/posts/'. $path . 'thumbnail/' . $this->post('filename'));	

		$this->response(array("message" => $this->lang->line("upload_photo_deleted"), 200));
	}


	public function images_comment_post()
	{
		$path_images = '/images/commentaires/' . $this->post('id') . '/';
		$this->load->library('Uploadhandler');
		$this->uploadhandler->init_uploadhandler($path_images);
	}


	public function images_info_comment_post()
	{

		$path_images = 'images/commentaires/'. $this->post('id') . '/';
		$path = $path_images . 'thumbnail';
		$url_image_presentation = $this->post('url_image_presentation');
		$files = $this->images->get_all_images($path);

		if ($result)
			$this->response(array('message' => $this->lang->line("upload_photo_modified")), 200);
		else
			$this->response(array('message' => $this->lang->line("upload_photo_not_modified")), 400);
	}


	public function delete_image_comment_post() 
	{
		$path =  $this->post('id') . '/';

		unlink('images/commentaires/' . $path . $this->post('filename'));
	//	unlink('images'. $path . 'facebook/' . $filename);
	    unlink('images/commentaires/'. $path . 'medium/' . $this->post('filename'));
		unlink('images/commentaires/'. $path . 'thumbnail/' . $this->post('filename'));	

		$this->response(array("message" => $this->lang->line("upload_photo_deleted"), 200));
	}


	public function avatar_post()
	{
		$path = '/avatar/'. $this->utils->get_user_id_encoded() . '/';
		
		$this->images->delete_current_avatar($path);
		error_reporting(E_ALL | E_STRICT);
		$this->load->library('Uploadhandler_avatar');
		$this->uploadhandler_avatar->init_uploadhandler('/images' .  $path);

		$path_avatar =  'images' . $path . 'thumbnail/';	

		$this->upload_model->update_url_avatar($path_avatar, 
			$this->utils->get_user_id());
	}

	public function background_post()
	{
		$path =  '/background/' . $this->utils->get_user_id_encoded() . '/';
		$this->images->delete_current_background($path);
		error_reporting(E_ALL | E_STRICT);
		$this->load->library('Uploadhandler_background');
		$this->uploadhandler_background->init_uploadhandler('/images' . $path);

		$path_background =  'images' . $path;	
		$this->upload_model->update_url_background($path_background, 
			$this->utils->get_user_id());
	}



}