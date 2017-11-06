<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

class Commentaire extends REST_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('commentaire_model2');
        $this->load->model('notification_model');
    }


    public function init_put()
    {


    	$id_encoded = $this->commentaire_model2->initialise(
			 $this->utils->get_user_id(),
			 $this->obfuscate_id->id_decode($this->put('id_content'))
    	);

    	$this->response(array('id' => $id_encoded), 200);
    }


    /**
     *  Insérer un commentaire avec des images
     */
    public function insert_images_post()
	{
		$this->commentaire_model2->add_commentaire_with_images(
				$this->obfuscate_id->id_decode($this->post('id_comment')),
				$this->obfuscate_id->id_decode($this->post('id_content')),
				$this->post('message')
		);

		$this->notification_model->add_notification(
			$this->utils->get_user_id(),
			$this->obfuscate_id->id_decode($this->post('id_content')),
			'contents',
			"commented",
			$this->post('message')
			);

		$this->response(array('message' => $this->lang->line("commentaire_added")), 200);
	}


	/**
     *  Insérer un commentaire simple
     */
	public function insert_put()
	{
		$this->commentaire_model2->add_commentaire(
				$this->utils->get_user_id(),
				$this->obfuscate_id->id_decode($this->put('id_content')),
				$this->put('message'),
				$this->put('url_image')
		);

		$this->notification_model->add_notification(
			$this->utils->get_user_id(),
			$this->obfuscate_id->id_decode($this->put('id_content')),
			'contents',
			"commented",
			$this->put('message')
			);

		$this->response(array('message' => $this->lang->line("commentaire_added")), 200);
	}

	/*
	 *  Pour effacer un commentaire
	 */
	public function delete_post()
	{
		$result = $this->commentaire_model2->delete_commentaire(
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
		$result = $this->commentaire_model2->update_commentaire(
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
		$result = $this->commentaire_model2->get_commentaires(
				$this->obfuscate_id->id_decode($this->get('id_content')),
				$this->get('page') - 1, 
				25
		);

		// On veut savoir si la personne suit ou pas la conversation
		$result["state_group"] = $this->notification_model->get_state_in_group(
			$this->utils->get_user_id(),
			$this->obfuscate_id->id_decode($this->get('id_content')) ,
			'contents'
		);

		
		$this->notification_model->state_to_read(
			 $this->utils->get_user_id(),
			 $this->obfuscate_id->id_decode($this->get('id_content')),
			 'contents'
			);

		$this->response($result, 200);
	}
	
}