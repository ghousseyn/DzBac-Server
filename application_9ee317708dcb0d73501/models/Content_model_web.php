<?php
class Content_model_web extends CI_Model {


function __construct()
{
	//die("ererrrrrrrrrrrrrr");
}
	/*
	 * Crée une ligne dans 'contents' et 
	 *  retourne l'id 
	 */
	public function initialise($id_membre)
	{
		$data = array(
			'id_membre' => $id_membre,
			'date_creation' => date("Y-m-d H:i:s"),
			'content_delete' => 2
		);

		$this->db->insert('contents', $data);

		return $this->obfuscate_id->id_encode($this->db->insert_id());
	}

    /**
     * Nous donne le meilleur post de la journée
     *
     */
	public function get_top_post_daily()
	{
		$this->db->select('contents.id, contents.id_membre AS id_membre, title, likes, views');
		$this->db->from('contents');
		$this->db->where('contents.date_creation > DATE_SUB(NOW(), INTERVAL 1 DAY)');
		$this->db->where("content_delete", 0);
		$this->db->order_by("likes, views", "desc"); 
		$this->db->limit(1);
		
        $query = $this->db->get();

        $response['posts'] = array();
        $posts = array();

		foreach($query->result_array() as $row)
		{
			$posts['id'] = $this->obfuscate_id->id_encode($row['id']);

			$posts['id_membre'] = $this->obfuscate_id->id_encode($row['id_membre']);

			$posts['title'] = $row['title'];

			$posts["likes"] = $row["likes"];

			$posts["views"] = $row["views"];

			array_push($response["posts"], $posts);

		} // end foreach

	//	 $gcm_ids = array("APA91bEGXjGNXYmOUGhm0JOtjH6w1ZVFIQ-6aAr6swfQ3jjQ2sf_llsLKvcuJon_l48dcyFP7oRoRxq2Sg-UDVhP2P0y71tgsGteS-7cfkRk7InAoXy2oK5Fm18yFbdIkBd9dQ_KPMeA");

        $this->load->library('GCMPushMessage');

        $this->db->select('gcm_id');
        $this->db->from('membres');
        $query = $this->db->get();

        $gcm_ids = array();

        foreach ($query->result_array() as $row)
        {
            array_push($gcm_ids, $row['gcm_id']);
        }

        $multiple_arrays = array_chunk($gcm_ids, 1000);

		for( $i = 0; $i < count($multiple_arrays); $i++) 
		{
			$this->gcmpushmessage->setDevices($multiple_arrays[$i]);

			$this->gcmpushmessage->send(null,
	            array(            
	              'title' => $posts['title'], 
	              'id_content' => $posts['id'],
	              'message' => "",
	              'url_avatar' => null,
	              'type_notification' => 'top_post'
	            ), "daily_notification", 86400); 
		}

		return $response;
	}

	public function get_contents($id_membre, $offset, $type = null, $subject = null, $order_by_views = null,
		$order_by_likes = null, $mot_cle = null, $secteurs = null, $is_me)
	{

		$this->db->select('contents.id, contents.id_membre AS id_membre, title, subject, type, likes, views, comments, url_video,
		 contents.url_presentation');
		$this->db->from('contents');
		//$this->db->join('membres', 'membres.id = contents.id_membre');
       // $this->db->where("content_delete", 3);
        $this->db->where("content_delete", 0);

        if ($type != null)
        	 $this->db->where("type", $type);

       	if ($subject != null)
        	 $this->db->where("subject", $subject);

    	if ($secteurs != null) {
	    	$this->db->where_in('secteur', $secteurs);
    	}

    	if ($mot_cle != null ) {
    	 	$this->db->like('title', $mot_cle); 
    	 	$this->db->or_like('content', $mot_cle); 
    	}

    	if ($is_me) 
    		$this->db->where('id_membre', $id_membre);

      if ($order_by_views == 1 && $order_by_likes == 1) 
       		$this->db->order_by("likes, views", "desc"); 
      	else if ($order_by_likes == 1)
       		$this->db->order_by("likes", "desc"); 
       	else if ($order_by_views == 1)
       		$this->db->order_by("views", "desc"); 
       	else
	       	$this->db->order_by("contents.date_creation", "desc"); 	

	//	$this->db->order_by("contents.date_creation", "desc"); 	
     //  	else

       	
        
        $this->db->limit(25 , (string) $offset);
        $query = $this->db->get();

        $response['posts'] = array();

		foreach($query->result_array() as $row)
		{
			$posts = array();

			$posts['id'] = $this->obfuscate_id->id_encode($row['id']);

			$posts['id_membre'] = $this->obfuscate_id->id_encode($row['id_membre']);

			$posts['title'] = $row['title'];

			$posts['subject'] = $row['subject'];

			$posts["type"] = $row["type"];

			if (!empty($row['url_video']))
				$posts["is_video"] = 1;
			else
				$posts["is_video"] = 0;

			$posts["likes"] = $row["likes"];

			$posts["views"] = $row["views"];

			$posts["comments"] = $row["comments"];

			$posts["url_presentation"] = $this->images->get_image_presentation($posts['id'],
			 $row['url_presentation'], $row['subject']);	


			list($posts['width_url_image_presentation'],
			 $posts['height_url_image_presentation']) = getimagesize($posts['url_presentation']);

//die($posts["url_presentation"]);
			
			array_push($response["posts"], $posts);
			
		} // end foreach

		return $response;
	}

	/*
	 *
	 *
	 */
	public function get_contents_likes($id_membre, $offset)
	{

		$this->db->select('contents.id, contents.id_membre AS id_membre, title, subject, type, likes, views, comments,
		 contents.url_presentation, contents.date_creation');
		$this->db->from('contents');
		$this->db->join('content_likes', 'content_likes.id_content = contents.id');
       // $this->db->where("content_delete", 3);
        $this->db->where("content_delete", 0);
        $this->db->where('content_likes.id_membre', $id_membre);
    	$this->db->where('content_likes.valeur', 1);
       	$this->db->order_by("content_likes.date_creation", "desc"); 
        
        $this->db->limit(25 , (string) $offset);
        $query = $this->db->get();

        $response['posts'] = array();

		foreach($query->result_array() as $row)
		{
			$posts = array();

			$posts['id'] = $this->obfuscate_id->id_encode($row['id']);

			$posts['id_membre'] = $this->obfuscate_id->id_encode($row['id_membre']);

			$posts['title'] = $row['title'];

			$posts['subject'] = $row['subject'];

			$posts["type"] = $row["type"];

			$posts["date_creation"] = $row["date_creation"];

			$posts["likes"] = $row["likes"];

			$posts["views"] = $row["views"];

			$posts["comments"] = $row["comments"];

			$posts["url_presentation"] = $this->images->get_image_presentation($posts['id'],
			 $row['url_presentation'], $row['subject']);	

			list($posts['width_url_image_presentation'],
			 $posts['height_url_image_presentation']) = getimagesize($posts['url_presentation']);
			
			array_push($response["posts"], $posts);
			
		} // end foreach

		return $response;
	}

	public function get_content($id_content)
	{
		$this->db->select('contents.id, contents.id_membre, contents.filename, contents.secteur, contents.url_video, contents.audio,
			username, url_avatar, title, subject, type, contents.url, contents.date_creation, content, comments');
		$this->db->from('contents');
		$this->db->join('membres', 'membres.id = contents.id_membre');
       // $this->db->where("content_delete", 3);
		$this->db->where("content_delete", 0);
        $this->db->where("contents.id", $id_content);
        $query = $this->db->get();

        $response['contents'] = array();

		foreach($query->result_array() as $row)
		{
			$posts = array();

			$posts['id'] = $this->obfuscate_id->id_encode($row['id']);

			$posts['secteur'] = $row['secteur'];

			$posts['title'] = $row['title'];

			$posts['subject'] = $row['subject'];

			if(empty($row['url_video']))
				$posts['url_video'] = null;
			else
				$posts['url_video'] = $row['url_video'];

			if(empty($row['audio']))
				$posts['audio'] = null;
			else
				$posts['audio'] = $row['audio'];
			

			$posts['filename'] = $row['filename'];

			$posts['url_avatar'] = $this->utils->get_url_avatar($row['url_avatar'], $row['id_membre'], 'thumbnail');

			$posts['username'] = $row['username'];

			$posts['id_membre'] = $this->obfuscate_id->id_encode($row['id_membre']);

			$posts['comments'] = $row['comments'];

			$posts["type"] = $row["type"];

			$posts["content"] = $row["content"];

			$posts["date_creation"] = $this->utils->get_relative_time($row["date_creation"]);

			if (!empty($row['url']))
				$posts['url'] = preg_split("/[,]+/",  $row['url']); 
			else
				$posts['url'] = array();

			//$this->load->helper('base');

			if (count($posts['url']) > 0) {
				// On crée le lien des imagess 
			for( $i = 0; $i < count($posts['url']); $i++) {
				# code...
				$posts['url'][$i] = url_images() . $posts['id'] . "/" . $posts['url'][$i];
			}
			} 
			else
				$posts['url'] = array();

			array_push($response["contents"], $posts);
			
		} // end foreach

		return $response;
	}

	public function insert($id_content, $data, $is_modification)
	{
		//$data['content_delete'] = 3;
		$data['content_delete'] = 0;
		//$data['title'] = stripslashes($data['title']);
		//$data['content'] = stripslashes($data['content']);


		$this->db->where('id', $id_content);
		$this->db->update('contents', $data);

		// Cela veut dire que c'est un tout nouveau post
		// Donc on notifie les utilisateurs
		if (!$is_modification) 
		{
			// On récupère leurs gcm_id
			$this->db->select("gcm_id");
	        $this->db->from('membres');
	        $this->db->join('following', 'membres.id = following.id_follower');
	        $this->db->where("following.id_followed",  $this->utils->get_user_id());
	        $this->db->where("following.is_following",  1);
	        $query = $this->db->get();

	        $gcm_ids = array();

	        foreach ($query->result_array() as $row)
	        {
	            array_push($gcm_ids, $row['gcm_id']);
	        }

	        // On récupère l'username name de l'abonné et son avatar
			$this->db->select("username, url_avatar");
	        $this->db->from('membres');
	        $this->db->where("id", $this->utils->get_user_id());
	        $row = $this->db->get()->row();

	        $username = $row->username;
	        $url_avatar = $this->utils->get_url_avatar($row->url_avatar, $this->utils->get_user_id(), 'thumbnail');

			$this->load->library('GCMPushMessage');

 	        $multiple_arrays = array_chunk($gcm_ids, 1000);

			for( $i = 0; $i < count($multiple_arrays); $i++) 
			{
				$this->gcmpushmessage->setDevices($multiple_arrays[$i]);

				$this->gcmpushmessage->send(null,
		            array(            
		              'title' => $username, 
		              'id_content' => $this->obfuscate_id->id_encode($id_content),
		              'message' => $data['title'],
		              'url_avatar' => $url_avatar,
		              'type_notification' => 'new_post'
		            ), null, 86400); 
			}


	     

		}
	}


	public function delete($id_content)
	{
		$this->db->where('id', $id_content);
		$this->db->update('contents', array('content_delete' => 1));

		// On enlève les likes de l'utilisateur
		// D'abord on récupère leur nombre
		$this->db->select('likes, id_membre');
		$this->db->from('contents');
		$this->db->where('id', $id_content);

		$query = $this->db->get();
		$row = $query->row();
		$likes = $row->likes;
		$id_membre_content = $row->id_membre;

		$this->db->where('id', $id_membre_content);

		$this->db->set('likes', 'likes-' . $likes, FALSE);
		$this->db->update('membres');
	}


	public function update($id_content, $data)
	{
		$this->db->where('id', $id_content);
		$this->db->update('contents', $data);
	}



}
