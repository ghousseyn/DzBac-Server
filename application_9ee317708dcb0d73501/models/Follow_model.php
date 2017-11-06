<?php

class Follow_model extends CI_Model {

	protected $client;

    function __construct()
    {
        parent::__construct();  
        $this->client = new Predis\Client();
    }
	

	function add_new_follower($id_follower, $id_followed)
	{
		$this->client->del('user_' . $id_followed);
		
		// D'abord on check si la perosnne ne l'avait pas followé auparavant 
		// pur ne changer que la valeur is_following à 1
		$this->db->select('id, is_following');
        $this->db->from('following');
        $this->db->where('id_follower', $id_follower);
        $this->db->where('id_followed', $id_followed);
        $this->db->limit(1);


        $query = $this->db->get();
        
        // Cela veut dire que la personne a déjà followé
        if ($query->num_rows() == 1) 
        {

        	$row = $query->row();

        	// La personne est déjà ajouté, on ne fait rien
        	if ($row->is_following)
        	{
        		return false;
        	}
        	else
        	{
        		$this->db->where('id', $row->id);
        		$this->db->set('date_modification', date("Y-m-d H:i:s"));
				$this->db->set('is_following', 1, FALSE);
				$this->db->update('following');

				return true;
        	}
        }
        else // La personne ne l'a jamais followé
        {
        	$data = array(         
				'id_follower' => $id_follower,
				'id_followed'  => $id_followed,
				'is_following' => 1,
				'date_creation' => date("Y-m-d H:i:s")
			);

			$this->db->insert('following' , $data);

			// On envoie une notification, le prevenant qu'il a un nouvel abonné

			// On récupère l'username name de l'abonné et son avatar
			$this->db->select("username, url_avatar");
	        $this->db->from('membres');
	        $this->db->where("id", $id_follower);
	        $row = $this->db->get()->row();

	        $username_follower = $row->username;
	        $url_avatar = $this->utils->get_url_avatar($row->url_avatar, $id_follower, 'thumbnail');


			// On récupère l'id gmc du receveur
	        $this->db->select("gcm_id");
	        $this->db->from('membres');
	        $this->db->where("id", $id_followed);
	        $row = $this->db->get()->row();

	        $gcm_id = $row->gcm_id;

			$this->load->library('GCMPushMessage');

	        $this->gcmpushmessage->setDevices($gcm_id);

	        $this->gcmpushmessage->send(null,
	            array(            
	              'title' => "Follower", 
	              'message' => $username_follower . " s'est abonné(e) à vous",
	              // Pour que la personne puisse consulter le profil de son abonné
	              'id_receveur' => $this->obfuscate_id->id_encode($id_follower),
	              'url_avatar' => $url_avatar,
	              'type_notification' => 'following'
	            ), null, 86400);  



			return true;
        }
		
	}

	function delete_follower($id_follower, $id_followed)
	{
		$this->db->where('id_follower', $id_follower);
		$this->db->where('id_followed', $id_followed);
		$this->db->set('date_modification', date("Y-m-d H:i:s"));
		$this->db->set('is_following', 0, FALSE);
		$this->db->update('following');
		$this->client->del('user_' . $id_followed);
	}



	/*
	 * Retourne la liste des followers d'un certains utilisateurs
	 */
	function get_followers($offset, $id_followed)
	{
		$this->db->select('membres.id AS id_membre, username, url_avatar');
        $this->db->from('following');
        $this->db->join('membres', 'membres.id = following.id_follower');
        $this->db->where('id_followed', $id_followed);
        $this->db->where('is_following', 1);
        $this->db->limit(25 , (string) $offset);
        $this->db->order_by("following.date_creation", "desc"); 

        $query = $this->db->get();

        $response['users'] = array();

     	foreach($query->result_array() as $row)
		{
			$user = array();

			$user['id_membre'] = $this->obfuscate_id->id_encode($row['id_membre']);

			$user['username'] = $row['username'];

			$user['url_avatar'] = $this->utils->get_url_avatar($row['url_avatar'], $row['id_membre'], 'thumbnail');

			array_push($response["users"], $user);
			
		} // end foreach

		return $response;
	}

	/*
	 * Retourne la liste des personnes qu'on suit nous même
	 */
	function get_my_followings($offset, $id_follower)
	{
		$this->db->select('membres.id AS id_membre, username, url_avatar');
        $this->db->from('following');
        $this->db->join('membres', 'membres.id = following.id_followed');
        $this->db->where('id_follower', $id_follower);
        $this->db->where('is_following', 1);
        $this->db->limit(25 , (string) $offset);
        $this->db->order_by("following.date_creation", "desc"); 


        $query = $this->db->get();

        $response['users'] = array();

     	foreach($query->result_array() as $row)
		{
			$user = array();

			$user['id_membre'] = $this->obfuscate_id->id_encode($row['id_membre']);

			$user['username'] = $row['username'];

			$user['url_avatar'] = $this->utils->get_url_avatar($row['url_avatar'], $row['id_membre'], 'thumbnail');

			array_push($response["users"], $user);
			
		} // end foreach

		return $response;
	}




	
}