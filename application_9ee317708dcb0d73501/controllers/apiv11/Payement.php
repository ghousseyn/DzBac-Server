<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

class Payement extends REST_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('payement_model');
    }

    public function check_post()
    {
        $id_membre = $this->post('id_membre');
        $android_id = $this->post('android_id');

        $res = $this->payement_model->add_user($id_membre, $android_id);
        $this->response($res, 200);
    }

    /*
     *   Type 5 représente un abonnement de trois mois simple
     *   Type 6 répresente un abo qui permet mettre en avant ses postes
     *
     */
    public function update_post()
    {
        $type = $this->post('type');
        $email = $this->post('email');

        if (!($type >= 0 && $type <= 6))
            $this->response(array('message' => "ERREUR ! Le type d'abonnement doit etre compris entre 0 et 6"), 200);


        // On récupère l'id de l'utilisateur et le gcm_id grace à l'email
        $this->db->select('id, email, gcm_id');
        $this->db->from('membres');
        $this->db->where('email', $email);
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() == 0)
            $this->response(array('message' => "ERREUR ! Ce compte n'existe pas !"), 200);
        else
        {
            $user = $query->row();
            $id_membre = $user->id;
            $gcm_id = $user->gcm_id;

            $is_updated = $this->payement_model->update($id_membre, $type);

            if ($is_updated)
            {
                if ($type == 1) 
                    $message = 'Vous avez maintenant un abonnement premium de 1 mois';
                else if ($type == 2)
                    $message = 'Vous avez maintenant un abonnement premium de 6 mois';
                else if ($type == 3)
                    $message = 'Vous avez maintenant un abonnement premium de 1 ans';
                else if ($type == 5)
                    $message = 'Vous avez maintenant un abonnement premium de 3 mois type petit malin';
                else if ($type == 6)
                    $message = 'Vous avez maintenant un abonnement premium de 3 mois type légende';
                else 
                    $message = "Vous n'avez aucun abonnement !";

                $config = Array(
                    'protocol' => 'smtp',
                    'smtp_host' => 'ssl://smtp.googlemail.com',
                    'smtp_port' => 465,
                    'smtp_user' => 'test@gmail.com',
                    'smtp_pass' => 'test',
                    'mailtype'  => 'html', 
                    'charset'   => 'utf-8'
                );

                $this->load->library('email', $config);

                $this->db->where('email', $email);
                $this->db->from('membres');
                $query = $this->db->get();
                $ret = $query->row();

                $this->email->from('test@gmail.com', 'Team DzBac');
                $this->email->to($email);    
                $this->email->subject('Activation de votre compte DzBac');
                $this->email->message('Salutation ' . $ret->username . '<br /><br />' .$message . '<br /><br />Cordialement,' . '<br /><br />' . 'Team DzBac');

                $this->email->send();

                $this->email->set_newline("\r\n");

// Set to, from, message, etc.

$result = $this->email->send();

                $this->load->library('GCMPushMessage');

                $this->gcmpushmessage->setDevices($gcm_id);

                $this->gcmpushmessage->send(null,
                    array(            
                      'title' => 'Compte prenium', 
                      'message' => $message,
                      'type_notification' => 'payement'
                    ), null, 2419200); 

                 $this->response(array('message' => "Compte modifié",
                                       'result' => $message), 200);
            }
            else
                $this->response(array('message' => "ERREUR 20 ! la personne n'a pas été updaté"), 200);

            
        }

    }


	
}