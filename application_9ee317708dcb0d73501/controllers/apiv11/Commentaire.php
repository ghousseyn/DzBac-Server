<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

class Commentaire extends REST_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('commentaire_model4');
        $this->load->model('notification_model2');



       /* if ($_SERVER['REMOTE_ADDR'] == '105.105.80.29'|| 
     	 	$_SERVER['REMOTE_ADDR'] == '105.104.46.41') {
        	$this->response(NULL, 500);
        }*/
    }

   public function test_content_get()
   {
   		$this->load->model('content_model7');
   		$postDeleted = $this->content_model7->is_post_deleted($this->get('id'));

		if ($postDeleted)
			$message = 'est supprimé';
		else
			$message = 'est pas supprimé';

		$this->response(array('message' => $message), 200);
   }

    public function init_put()
    {
    	// Check si le user n'est pas banni
		$this->load->model('admin_model');
		$this->load->model('content_model7');

		$id_content = $this->obfuscate_id->id_decode($this->put('id_content'));

		$banned = $this->admin_model->is_user_already_banned($this->utils->get_user_id());

		if ($banned)
			$this->response(NULL, 200);

		$postDeleted = $this->content_model7->is_post_deleted($id_content);

		if ($postDeleted)
			$this->response(NULL, 200); 

    	$id_encoded = $this->commentaire_model4->initialise(
			 $this->utils->get_user_id(),
			 $id_content
    	);

    	$this->response(array('id' => $id_encoded), 200);
    }


    /**
     * Insérer un commentaire audio
     */
    public function insert_audio_post()
    { //add_commentaire_with_audio
		$data = $this->commentaire_model4->add_commentaire_with_audio(
				$this->utils->get_user_id(),
				$this->obfuscate_id->id_decode($this->post('id_content')),
				$this->post('filename')
		);

		$this->notification_model2->add_notification(
			$this->utils->get_user_id(),
			$this->obfuscate_id->id_decode($this->post('id_content')),
			'contents',
			"commented",
			'MESSAGE VOCAL',
			$data
			);

		$this->response(array('message' => $this->lang->line("commentaire_added")), 200);
    }


    /**
     *  Insérer un commentaire avec des images
     */
    public function insert_images_post()
	{
		$data = $this->commentaire_model4->add_commentaire_with_images(
				$this->obfuscate_id->id_decode($this->post('id_comment')),
				$this->obfuscate_id->id_decode($this->post('id_content')),
				$this->post('message')
		);

		$this->notification_model2->add_notification(
			$this->utils->get_user_id(),
			$this->obfuscate_id->id_decode($this->post('id_content')),
			'contents',
			"commented",
			$this->post('message'),
			$data
			);

		$this->response(array('message' => $this->lang->line("commentaire_added")), 200);
	}


	/**
     *  Insérer un commentaire simple
     */
	public function insert_put()
	{
		// Check si le user n'est pas banni
		$this->load->model('admin_model');
		$this->load->model('content_model7');

		$id_content = $this->obfuscate_id->id_decode($this->put('id_content'));

		$banned = $this->admin_model->is_user_already_banned($this->utils->get_user_id());

		if ($banned)
			$this->response(NULL, 200);

		$postDeleted = $this->content_model7->is_post_deleted($id_content);

		if ($postDeleted)
			$this->response(NULL, 200); 


		$data = $this->commentaire_model4->add_commentaire(
				$this->utils->get_user_id(),
				$id_content,
				$this->put('message'),
				$this->put('url_image')
		);

		$this->notification_model2->add_notification(
			$this->utils->get_user_id(),
			$id_content,
			'contents',
			"commented",
			$this->put('message'),
			$data
		);

		$this->response(array('message' => $this->lang->line("commentaire_added")), 200);
	}

	/*
	 *  Pour effacer un commentaire
	 */
	public function delete_post()
	{
		$result = $this->commentaire_model4->delete_commentaire(
			 $this->obfuscate_id->id_decode($this->post('id')),
			 $this->obfuscate_id->id_decode($this->post('id_content'))
		);

		$this->response(array('message' => $this->lang->line("commentaire_deleted")), 200);
	}

	/*
	 *  Pour modifier un commentaire
	 */
	public function update_post()
	{
		$result = $this->commentaire_model4->update_commentaire(
			 $this->obfuscate_id->id_decode($this->post('id')),
			 $this->post('message')
		);

		if($result)
			$this->response(array('message' => $this->lang->line("commentaire_modified")), 200);
		else 
			$this->response(array('message' => $this->lang->line("commentaire_error_not_modified")), 200);
	}

	/**
     *  Obtenir les commentaires
     */
	public function list_get()
	{
		$result = $this->commentaire_model4->get_commentaires(
				$this->obfuscate_id->id_decode($this->get('id_content')),
				$this->get('page') - 1, 
				25
		);

		// On veut savoir si la personne suit ou pas la conversation
		$result["state_group"] = $this->notification_model2->get_state_in_group(
			$this->utils->get_user_id(),
			$this->obfuscate_id->id_decode($this->get('id_content')) ,
			'contents'
		);

		
		$this->notification_model2->state_to_read(
			 $this->utils->get_user_id(),
			 $this->obfuscate_id->id_decode($this->get('id_content')),
			 'contents'
			);

		$this->response($result, 200);
	}
	
}