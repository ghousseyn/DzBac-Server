<?php

class messagerie_model4 extends CI_Model {

	protected $client;

	function __construct()
	{
		parent::__construct();  
		$this->client = new Predis\Client();
	}
	
	public function get_nombre_msg_non_lu($id_membre)
	{
		$sql = "SELECT COUNT(id) AS nombre_messages FROM header WHERE unread_id_membre = ? AND header.header_deleted = 0 LIMIT 20";

		$query = $this->db->query($sql, array($id_membre));
		
		$result = $query->result_array();

		return $result[0]['nombre_messages'];
	}

	/*
	 * @description : Pour obtenir les messages non lus et ceux qui ont new_message égale à 1
	 * 
	 */
	public function get_message_non_lu_new($id_membre)
	{
		$sql= "SELECT message.id
			FROM header
			INNER JOIN 
			(SELECT id, message, header_id, message_lu, id_membre, new_message
			FROM message   
			GROUP BY id ORDER BY id DESC) message ON message.header_id = header.id
			
			WHERE ( header.id_expediteur = ?
			OR 	
				header.id_receveur = ? )
				
			AND message.id_membre != ? AND message.message_lu = 0 
			AND message.new_message = 1 LIMIT 25";
			
		$query = $this->db->query($sql, array($id_membre, $id_membre, $id_membre));
		
		$response = array();

		$number_messages = 0;

		foreach ($query->result() as $row) {
			# code...
			$posts = array();

			$number_messages++;

			$posts['id'] = $row->id;
			$posts['new_message'] = 0;
 
			array_push($response, $posts);
		}

		if ($number_messages > 0)
        {
            if ($number_messages == 1) {

                $return = $query->row_array(); 

                $this->db->set('new_message', 0);
                $this->db->where('id', $return['id']);
                $this->db->update("message");
            }
            else {
                $this->db->update_batch('message', $response, 'id'); 
            }
        }
		
		return $number_messages;
	}

	public function delete_conversation($id_membre, $id_header)
	{
		$this->is_my_conversation($id_membre, $id_header);

		$this->db->where('id', $id_header);
		$this->db->set('header_deleted', '1', FALSE);

///		  $this->client->del('list_conversation_' . $id_membre);
 	//	$this->client->del('list_conversation_' . $id_receveur);

		return $this->db->update('header');
	}

    /*
     * On ajoute l'utilisateur dans 'blocked_membres'
     *	et on supprime toutes les conversations entres les deux membres
     */
	public function block_user($id_blocker, $id_blocked)
	{
		// On vérifie si l'utilisateur est déjà blocké
		$this->db->select('id_blocked');
		$this->db->from('blocked_membres');
		$this->db->where('id_blocked', $id_blocked);
		$this->db->where('id_blocker', $id_blocker);
		$this->db->limit(1);
		$query = $this->db->get();

		 if ($query->num_rows() > 0) 
		 	return false;

		// On supprime les conversations
		$data = array('header_deleted' => 1);

		$where = "( id_expediteur = $id_blocker AND id_receveur = $id_blocked ) OR 
		          ( id_expediteur = $id_blocked AND id_receveur = $id_blocker )";

		$this->db->update('header', $data, $where);

		// On insert
		$data = array(
	        'id_blocker' => $id_blocker,
	        'id_blocked' => $id_blocked,
	        'date_creation' => date("Y-m-d H:i:s")
		);

		$this->db->insert('blocked_membres', $data); 

		return true;
	}

    /*
     * Pour savoir si une personne est bloqué ou non
     *
     */
	public function is_blocked_membre($id_expediteur, $id_receveur)
	{
		$this->db->select('id');
		$this->db->from('blocked_membres');

		$where = "( id_blocker = $id_expediteur AND id_blocked = $id_receveur ) OR 
		          ( id_blocker = $id_receveur AND id_blocked = $id_expediteur )";

		$this->db->where($where);
		$this->db->limit(1);
		$query = $this->db->get();

		if ($query->num_rows() > 0)
			return true;
		else
			return false;
	}

	public function start_conversation($pseudo_expediteur, $id_membre,  $id_receveur, $message, $sujet)
	{
		if ($this->is_blocked_membre($id_membre, $id_receveur))
			return false;
		
		$recevoir_email_message = 0;
		$url_avatar;
		$gcm_id;

		$data = array(
			'id_expediteur' => $id_membre,
			'id_receveur' => $id_receveur,
			'sujet' => $sujet,
			'unread_id_membre' => $id_receveur,
			'date_creation' => date("Y-m-d H:i:s")
			);
		
		$this->db->insert('header', $data);
		
		$header_id = $this->db->insert_id();
		
		$data = array(
			'header_id' => $header_id,
			'id_membre' => $id_membre,
			'message' => $message,
			'new_message' => 1,
			'date_creation' => date("Y-m-d H:i:s")
			);
		
		$this->db->insert('message', $data);
		
		$message_id = $this->db->insert_id();

		// On récupère l'avatar de l'expediteur
        $this->db->select("url_avatar");
        $this->db->from('membres');
        $this->db->where("id", $id_membre);
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            # code...
            $url_avatar = $this->utils->get_url_avatar($row->url_avatar, 
                                                       $id_membre, 
                                                      'thumbnail');
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
              'title' => $pseudo_expediteur, 
              'id_header' => $this->obfuscate_id->id_encode_new($header_id, KEY_CONVERSATION),
              'id_receveur' => $this->obfuscate_id->id_encode($id_membre),
              'message' => $message,
              'url_avatar' => $url_avatar,
              'type_notification' => 'send_message'
            ), null, 2419200); 

        $this->client->del('list_conversation_' . $id_membre);
 		$this->client->del('list_conversation_' . $id_receveur);
		
		return true;
	}

	/* @description : Retourne la liste des conversations.
	 *
	 */
	public function get_list_conversations($id_membre, $offset)   
	{
		if (!is_numeric($offset))
			die();

		if ($offset == 0 && $this->client->get('list_conversation_' . $id_membre) != NULL)
        {
        	$response = json_decode($this->client->get('list_conversation_' . $id_membre), true);
            return $response;
        }

	//	die($id_membre . ' -  ' . ' - '. $offset);

		$sql = "SELECT membre_expediteur.username AS pseudo_expediteur, membre_expediteur.id AS id_membre_expediteur, membre_expediteur.url_avatar AS url_avatar_expediteur,
					  membre_receveur.username AS pseudo_receveur , membre_receveur.id AS id_membre_receveur, membre_receveur.url_avatar AS url_avatar_receveur,
					  membre_expediteur.level AS level_expediteur, membre_receveur.level AS level_receveur,
					  membre_expediteur.level_contribution AS level_c_exp, membre_receveur.level_contribution AS level_c_rec,
					  
		header.id , 
		header.sujet ,
		header.date_creation, 
		header.id_receveur,
		header.unread_id_membre

		FROM header
		
		INNER JOIN membres AS membre_expediteur ON membre_expediteur.id = header.id_expediteur
		INNER JOIN membres AS membre_receveur ON membre_receveur.id = header.id_receveur
		
		WHERE ( header.id_expediteur = ?

			OR 	
			    header.id_receveur = ? )

		AND header_deleted = 0
			    
		ORDER BY header.date_creation DESC LIMIT 25 OFFSET " . $this->db->escape($offset);
		
		$query = $this->db->query($sql, array($id_membre, $id_membre));
			    
		$response['posts'] = array();
		
		foreach( $query->result_array() as $row)
		{
			$posts = array();

			$posts['id'] = $this->obfuscate_id->id_encode_new($row['id'], KEY_CONVERSATION);

			// Afin d'afficher la personne avec laquelle on converse
			if ($row['id_membre_receveur'] == $id_membre) 
			{
				$posts['id_membre'] = $this->obfuscate_id->id_encode($row['id_membre_expediteur']);
				$posts['pseudo'] = $row['pseudo_expediteur'];
				$posts['level'] = $row['level_expediteur'];
				$posts['level_contribution'] = $row['level_c_exp'];
				$posts['url_avatar']  = $this->utils->get_url_avatar($row['url_avatar_expediteur'], 
																	 $row['id_membre_expediteur'], 
																	 'thumbnail');
				// Afin d'avoir l'id du locuteur dans la conversation
				$posts['id_membre_2'] = $this->obfuscate_id->id_encode($row['id_membre_expediteur']);
			}
			else
			{
				$posts['level'] = $row['level_receveur'];
				$posts['level_contribution'] = $row['level_c_rec'];
				$posts['id_membre_2'] = $this->obfuscate_id->id_encode($row['id_membre_receveur']);
				$posts['id_membre'] = $this->obfuscate_id->id_encode($row['id_membre_receveur']);
				$posts['pseudo'] = $row['pseudo_receveur'];
				$posts['url_avatar']  = $this->utils->get_url_avatar($row['url_avatar_receveur'], 
																	 $row['id_membre_receveur'], 
																	 'thumbnail');
			}


			/**
			 *  Si on trouve que la valeur de 'unread_id_membre' a notre id, cela veut dire
			 *  qu'il y a eu un nouveau message pour nous dans la conversation
			 */
			if ($row['unread_id_membre'] == $id_membre) 
				$posts['message_lu'] = 0;
			else 
				$posts['message_lu'] = 1;
			
			$posts['sujet'] = $row['sujet'];
			$posts['date_creation'] = $row['date_creation'];
				
			array_push($response["posts"], $posts);
		}

	///	if ($is_get_first_conv)
		////	return $this->get_first_conversation($response, $id_membre);
		//else

		if ($offset == 0)
        {
            $this->client->set('list_conversation_' . $id_membre, json_encode($response));
        }

		return $response;
	}
	

	/* @description: Pour éviter qu'une personne extèrieure n'accède aux conversations
	 * 
	 */
	private function is_my_conversation($id_membre, $id_header)
	{
		$sql= "SELECT unread_id_membre
		 FROM header 
		 WHERE id = ?";

		// print_r($id_header);
		// die("id : " );
		
	    $query = $this->db->query($sql, array($id_header));
	    // On vérifie qu'il y a au moins un résultat
	    if ($query->num_rows() > 0) {
	       // C'est ok
	    	 foreach ($query->result() as $row)
		      return $row->unread_id_membre;
	    }
	    else{
	        // On a affaire à un petit malin !
	        die("Écoute coco n'essaye pas de lire les conversations des autres !");
	    }
	}
	

	public function get_conversation($id_header, $id_membre, $offset)
	{

		/*if ($offset == 0 && $this->client->get('conversation_' . $id_membre) != NULL)
        {
            return json_decode($this->client->get('conversation_' . $id_membre), true);
        }*/

		$unread_id_membre = $this->is_my_conversation($id_membre, $id_header);
	
		$sql= "SELECT membres.username, membres.id AS id_membre, membres.url_avatar, membres.level,
					message.id, message.message, message.date_creation, message.message_lu, message.date_last_view, message.audio

				FROM message	

		INNER JOIN membres ON membres.id = message.id_membre
		INNER JOIN header ON header.id = message.header_id

		WHERE message.header_id = ? AND  header.header_deleted = 0
		
		ORDER BY message.id DESC LIMIT 25 OFFSET " . $this->db->escape($offset);
		
		$query = $this->db->query($sql, array($id_header));
		
		$response['conversation'] = array();

		$id_message_last = NULL;


		foreach( $query->result_array() as $row)
		{
			$conversation = array();

			if ($id_message_last == null)
				$id_message_last = $row['id'];
	
			$conversation['id_header'] = $this->obfuscate_id->id_encode_new($id_header, KEY_CONVERSATION);
			$conversation['message'] = $row['message'];
			$conversation['id_membre'] = $this->obfuscate_id->id_encode($row['id_membre']);
			$conversation['username'] = $row['username'];
			$conversation['level'] = $row['level'];

			if (!empty($row['audio']))
				$conversation['audio'] = HOME_URL . 'audio/' . $row['audio']; 
			else
				$conversation['audio'] = NULL;

			$conversation['message_lu'] = $row['message_lu'];

			if ($row['date_last_view'] == '1899-11-30 00:00:00' || $row['date_last_view'] == '0000-00-00 00:00:00')
					$conversation['date_last_view'] = 'Vu il y a ?';
				else
					$conversation['date_last_view'] =  $row['date_last_view'];
			
			$conversation['url_avatar']  = $this->utils->get_url_avatar($row['url_avatar'], $row['id_membre'], 'thumbnail');
			
			$conversation['date_creation'] = $row['date_creation'];
				
			array_push($response["conversation"], $conversation);
		}

		// On inverse le tableau
		$response["conversation"] = array_reverse($response["conversation"]);
		

		// On update seulement si le dernier message est celui de l'autre membre
		if ($response['conversation'][count($response['conversation']) - 1]['id_membre'] !=
			$this->obfuscate_id->id_encode($id_membre)) {

			$this->update_message_lu($id_message_last);
		}
		
		/*
		 * Vu qu'on a lu la conversation on peut enleve notre id de la case 'unread_id_membre'
		 *
		 */
		if ( $unread_id_membre == $id_membre ) {

			$data = array(
			'unread_id_membre' => 0
			);

			$this->db->where('id' , $id_header); 
			$this->db->update('header', $data); 
		}

	/*	if ($offset == 0)
        {
            $this->client->set('conversation_' . $id_membre, json_encode($response));
        }
	*/

		return $response;
	}
	
	private function update_message_lu($id_message)
	{
		$data = array(
		'message_lu' => 1,
		'new_message' => 0,
		'date_last_view' => date("Y-m-d H:i:s")
		);

		$this->db->where('id' , $id_message);
		$this->db->update('message', $data); 
		
		return $this->db->insert_id();
	}
	
	public function send_message($message, $id_membre, $id_receveur, $id_header)
	{
		$this->is_my_conversation($id_membre, $id_header);
		$date = date("Y-m-d H:i:s");

		$data = array(
		'unread_id_membre' => $id_receveur,
		'date_creation' => $date
		);

		$this->db->where('id' , $id_header); 
		$this->db->limit(1);
		$this->db->update('header', $data); 
		
		$data = array(
		'id_membre' => $id_membre,
		'message' => $message,
		'header_id' => $id_header,
		'new_message' => 1,
		'date_creation' => $date
		);

		// On récupère l'avatar de l'expediteur
        $this->db->select("url_avatar, username");
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
              'title' => $username, 
              'id_header' => $this->obfuscate_id->id_encode_new($id_header, KEY_CONVERSATION),
              'message' => $message,
              'id_receveur' => $this->obfuscate_id->id_encode($id_membre),
              'url_avatar' => $url_avatar,
              'type_notification' => 'send_message'
            ), null, 2419200);  

 		$this->client->del('list_conversation_' . $id_membre);
 		$this->client->del('list_conversation_' . $id_receveur);

		return $this->db->insert('message', $data); 
	}


	public function send_message_audio($id_membre, $id_receveur, $id_header, $filename)
	{
		$this->is_my_conversation($id_membre, $id_header);
		$date = date("Y-m-d H:i:s");

		$data = array(
		'unread_id_membre' => $id_receveur,
		'date_creation' => $date
		);

		$this->db->where('id' , $id_header); 
		$this->db->limit(1);
		$this->db->update('header', $data); 
		
		$data = array(
		'id_membre' => $id_membre,
		'audio' => $filename,
		'header_id' => $id_header,
		'new_message' => 1,
		'date_creation' => $date
		);

		// On récupère l'avatar de l'expediteur
        $this->db->select("url_avatar, username");
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
              'title' => $username, 
              'id_header' => $this->obfuscate_id->id_encode_new($id_header, KEY_CONVERSATION),
              'message' => 'MESSAGE VOCAL',
              'id_receveur' => $this->obfuscate_id->id_encode($id_membre),
              'url_avatar' => $url_avatar,
              'type_notification' => 'send_message'
            ), null, 2419200);  

        $this->client->del('list_conversation_' . $id_membre);
 		$this->client->del('list_conversation_' . $id_receveur);

		return $this->db->insert('message', $data); 
	}









}

