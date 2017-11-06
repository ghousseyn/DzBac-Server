<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

class Admin extends REST_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('admin_model');
    }

    public function deban_post()
    {
        $email = $this->post('email');
        
        if($this->admin_model->debann_user($email))
        {
            $this->response(array('message' => 'Utilisateur débanni'), 200);
        }
        else 
        {
            $this->response(array('message' => 'Utilisateur non trouvé'), 200);
        }
    }

    public function update_level_post()
    {
        $this->db->set('level', 1, FALSE);
        $this->db->update('membres');
    }

    public function message_to_all_post()
    {// 
        if ($this->post('password') != '6a7ec24ddfd203b38445989caa2be8ab2e5a477c')
            $this->response(NULL, 403);

        $id_expediteur = 10;
        $gcm_ids = array();

        $sujet = $this->post('sujet');
        $message = $this->post('message');

        /*
         * On récupère l'id de tout les utilisateurs sauf celui de Team DzBac
         */
        $this->db->select("id, gcm_id");
        $this->db->from('membres');
        $this->db->where('id !=', 10);
       // $this->db->where('id', 11);
        $query = $this->db->get();

        $gcm_ids = array();

        if ($query->num_rows() >= 1) 
        {
            
            foreach ($query->result_array() as $row)
            {
                // On commence une nouvelle conversation

                $data = array(
                    'id_expediteur' => $id_expediteur,
                    'id_receveur' => $row['id'],
                    'sujet' => $sujet,
                    'unread_id_membre' => $row['id'],
                    'date_creation' => date("Y-m-d H:i:s")
                );
        
                $this->db->insert('header', $data); 

                $id_header = $this->db->insert_id();

                // On insert le message dans la conversation

                $data = array(
                    'header_id' => $id_header,
                    'id_membre' => $id_expediteur,
                    'message' => $message,
                    'new_message' => 1,
                    'date_creation' => date("Y-m-d H:i:s")
                );

                $this->db->insert('message', $data); 

                // Pour les notifications push
                array_push($gcm_ids, $row['gcm_id']);
            }

        }
            

        $url_avatar;

        // On récupère l'avatar de l'expediteur
        $this->db->select("url_avatar, username");
        $this->db->from('membres');
        $this->db->where("id", $id_expediteur);
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            # code...
            $url_avatar = $this->utils->get_url_avatar($row->url_avatar, 
                                                       $id_expediteur, 
                                                      'thumbnail');

            $pseudo_expediteur = $row->username;
        }

        $this->load->library('GCMPushMessage');

        $multiple_arrays = array_chunk($gcm_ids, 1000);


        for( $i = 0; $i < count($multiple_arrays); $i++) 
        {
            $this->gcmpushmessage->setDevices($multiple_arrays[$i]);

            $this->gcmpushmessage->send(null,
                array(            
                  'title' => $sujet, 
                  'id_header' => $this->obfuscate_id->id_encode_new($id_header, KEY_CONVERSATION),
                  'id_receveur' => $this->obfuscate_id->id_encode($id_expediteur),
                  'message' => $message,
                  'url_avatar' => $url_avatar,
                  'type_notification' => 'send_message'
                ), null, 86400 * 5); 
        }


        $this->response(array('message' => 'Message envoye'), 200);
    }


    public function graduation_post()
    {
    	if ($this->post('password') != 'c7a0b41248bcb6d53cacf92509eafa70abd48fe6')
    		$this->response(NULL, 403);

    	$id_membre = $this->post('id_membre');
    	$message = $this->post('message');
    	$title = $this->post('title');
    	$level = $this->post('level');

    		// on met level à zero
	 	$this->db->where('id', $id_membre);
		$this->db->set('level', $level, FALSE);
		$this->db->update('membres');

		$this->load->library('GCMPushMessage');

		// On récupère l'id gmc du membre 
        $this->db->select("gcm_id");
        $this->db->from('membres');
        $this->db->where("id", $id_membre);
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            # code...
            $gcm_id = $row->gcm_id;
        }

        $this->gcmpushmessage->setDevices($gcm_id);

        $this->gcmpushmessage->send(null,
            array(   
              'message' => $message,
              'level' => $level,
              'type_notification' => 'level_user'
            ), null, 2419200); 
	
		$this->response(array('message' => 'Grade modifié !'), 200);
    }


    /**
      * Pour bannir un utilisateur de l'application
      */
    public function bann_put()
    {

    	$id_banned = $this->obfuscate_id->id_decode($this->put('id_banned'));

    	$result = $this->admin_model->bann_user(
    		$id_banned,
			$this->utils->get_user_id()
    	);

    	if ($result)
    	{
    		$this->load->library('GCMPushMessage');

    		// On récupère l'id gmc du membre banni
	        $this->db->select("gcm_id");
	        $this->db->from('membres');
	        $this->db->where("id", $id_banned);
	        $query = $this->db->get();

	        foreach ($query->result() as $row) {
	            # code...
	            $gcm_id = $row->gcm_id;
	        }

	        $this->gcmpushmessage->setDevices($gcm_id);

	        $this->gcmpushmessage->send(null,
	            array(   
	              'message' => $this->lang->line("admin_you_have_been_banned"),
	              'type_notification' => 'user_banned'
	            ), null, 2419200); 
    	
    		$this->response(array('message' => $this->lang->line("admin_user_banned")), 200);
    	}
    	else
    	{
    		$this->response(array('message' => $this->lang->line("admin_user_already_banned")), 200);
    	}
	}

	
}