<?php defined('BASEPATH') OR exit('No direct script access allowed');


require_once APPPATH.'/libraries/REST_Controller.php';

class Post extends REST_Controller {

	function __construct()
    {
        parent::__construct();
        $this->load->model("content_model2");


        if (CHECK_REQUEST) {
            $this->check_key->check();
        }
    }

    public function init_put()
    {

    	$id_encoded = $this->content_model2->initialise(
			 $this->utils->get_user_id()
    	);

    	$this->response(array('id' => $id_encoded), 200);
    }

    public function decode_id_dzbac_get()
    {
        $this->response(array('id' => $this->obfuscate_id->id_decode($this->get('id_content'))), 200);
    }

    public function delete_post()
    {
    	$this->content_model2->delete(
    		 $this->obfuscate_id->id_decode($this->post('id_content'))
    	);

    	$this->response(array('message' => $this->lang->line("post_supprime")), 200);
    }

    public function insert_post()
    {
        $this->load->model('upload_model');
        
    	$data = json_decode($this->post('data') , true);


    	$this->content_model2->insert(
    		 $this->obfuscate_id->id_decode($this->post('id_content')),
    		 $data
    	);

      /*  $path_images = 'images/posts/'. $this->post('id_content') . '/';
        $path = $path_images . 'thumbnail';
        $url_image_presentation = $data['url_presentation'];

        $this->upload_model->update_info_images(
            $url_image_presentation,
            $this->obfuscate_id->id_decode($this->post('id_content')) ,
            $this->images->get_path_images($path)
        );*/

        $this->load->model("notification_model");

        // On ajoute l'utilisateur qui poste l'item dans son propre groupe
        $this->notification_model->add_to_group(
         $this->utils->get_user_id() ,
         $this->obfuscate_id->id_decode($this->post('id_content')),
         'contents');

    	$this->response(array('message' => $this->lang->line("post_ajouter")), 200);
    }

    public function update_post()
    {
    	$data = json_decode($this->post('data') , true);

    	$this->content_model2->update(
    		 $this->obfuscate_id->id_decode($this->post('id_content')),
    		 $data
    	);
        
    	$this->response(array('message' => $this->lang->line("Post modifié !")), 200);
    }

    /*
     * Obtenir un post particulier
     *
     */
    public function content_get()
    {

        $id_content = $this->obfuscate_id->id_decode($this->get('id_content'));
        
		$result = $this->content_model2->get_content(
			$this->utils->get_user_id(),
			$id_content
		);

        $this->load->model('hit_counter_model');

        $this->hit_counter_model->hit_counter(
            $this->utils->get_user_id(),
            $id_content
        );

		$this->response($result, 200);
    }

     /*
     * Obtnir le meilleur post de la journée
     *
     */
    public function top_daily_post_get()
    {
        $offset = ($this->get('page') * 25 ) - 25;
      
        $result = $this->content_model2->get_top_post_daily();

       

        $this->response($result, 200);

    }

    /*
     * Obtenir la liste des posts
     * On peut même obtenir la list des posts d'un utilisateur en particulier
     * et la liste des posts qu'il a aimé
     */
	public function list_get()
	{
		$offset = ($this->get('page') * 25 ) - 25;
        $id_membre = $this->obfuscate_id->id_decode($this->get('key'));

        // Pour savoir si on cherche au niveau des likes ou pas
        if ($this->get('is_likes'))
        {
            $result = $this->content_model2->get_contents_likes(
                $id_membre,
                $offset
             );
        }
        else 
        {
            $result = $this->content_model2->get_contents(
                $id_membre,
                $offset,
                $this->get('type'),
                $this->get('subject'),
                $this->get('order_by_views'),
                $this->get('order_by_likes'),
                $this->get('mot_cle'),
                $this->get('is_me')
            );
        }

		$this->response($result, 200);

	}

}