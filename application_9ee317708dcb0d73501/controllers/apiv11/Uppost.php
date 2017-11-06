<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

class Uppost extends REST_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('uppost_model');
    }

    public function pin_content_post()
    {
        if ($this->uppost_model->is_post_pinned_exist($this->utils->get_user_id()))
            $this->response(array('message' => 'Vous avez déjà un post épinglé'), 200);

        if ($this->uppost_model->is_limit_reached($this->utils->get_user_id()))
            $this->response(array('message' => 'Vous avez dépasse la limite des posts épinglé par jour'), 200);

        $id_content =  $this->obfuscate_id->id_decode($this->post('id_content'));
        $this->uppost_model->pin_content($id_content);
        $this->response(array('message' => 'Votre post a été mis en avant'), 200);
    }

  
	
}