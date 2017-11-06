<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

class Telephony extends REST_Controller {

    function __construct()
    {
        parent::__construct();
    }

    public function call_post()
    {
        $id_receveur = $this->obfuscate_id->id_decode($this->post('id_receveur'));
        $link_call = $this->post('link_call');
        $id_header = $this->post('id_header');
        $id_membre = $this->utils->get_user_id();

        // On récupère l'avatar de l'expediteur
        $this->db->select("username, url_avatar");
        $this->db->from('membres');
        $this->db->where("id", $id_membre);
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            # code...
            $url_avatar = $this->utils->get_url_avatar($row->url_avatar, 
                                                       $id_membre, 
                                                      'thumbnail');

            $username = $row->username;
        }

        // On récupère l'id gmc du receveur
        $this->db->select("gcm_id");
        $this->db->from('membres');
        $this->db->where("id", $id_receveur);
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            # code...
            $gcm_id = $row->gcm_id;
        }



        $this->load->library('GCMPushMessage');

        $this->gcmpushmessage->setDevices($gcm_id);

        $this->gcmpushmessage->send(null,
            array(            
              'title' => $username. ' vous appelle...', 
              'link_call' => $link_call,
              'username' => $username,
              'id_header' => $id_header,
              'id_receveur' => $this->obfuscate_id->id_encode($id_membre),
              'url_avatar' => $url_avatar,
              'type_notification' => 'call'
            ), null, 10); 


        $this->response(array("message" => "Appel en cours..."), 200);
    }

 
	
}