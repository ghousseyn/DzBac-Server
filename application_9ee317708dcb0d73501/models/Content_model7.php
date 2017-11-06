<?php
class Content_model7 extends CI_Model {

protected $client;

function __construct()
{
	//die("ererrrrrrrrrrrrrr");
	$this->client = new Predis\Client();
		//$this->client->set('testzee', 'zezzzzzzz');
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

	public function is_post_deleted($id_content)
	{
		$this->db->select('content_delete');
		$this->db->from('contents');
		$this->db->where('id', $id_content);
		$this->db->limit(1);
		$query = $this->db->get();

		 if ($query->num_rows() > 0)
		 {
		 	$result = $query->row();

		 	if ((int) $result->content_delete == 1)
		 		return true;
		 	else
		 		return false;
		 } 
		 else
		 	return true;
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
		$order_by_likes = null, $mot_cle = null, $format = null, $tags_id, $is_me)
	{

		$this->db->select('contents.id, contents.id_membre AS id_membre, title, subject, type, contents.likes, contents.views, contents.comments, url_video, audio, content_delete, membres.url_avatar, membres.username,
		 contents.url_presentation, membres.level_contribution'); 
	/*	$this->db->select('contents.id, contents.id_membre AS id_membre, title, subject, type, contents.likes, contents.views, contents.comments, url_video, audio, content_delete,
		 contents.url_presentation');*/

		 if ($offset == 0 && $type == null && $subject == null && $order_by_views == 0 &&
	 		 $order_by_likes == 0 && $mot_cle == null && $format == null && !$is_me
		  && $this->client->get('contents') != NULL) {
		  	$response = json_decode($this->client->get('contents'), true);
		    $response['redis'] = true;
		 	return $response;
		 }

		$this->db->from('contents');
	//	

	    if ($tags_id != null) {
	    	$this->db->join('tags_contents', 'tags_contents.id_content = contents.id');
	    }
	//	
		$this->db->join('membres', 'membres.id = contents.id_membre');
		
		$this->db->group_by('contents.id'); 
    	$this->db->where("content_delete", 0);

    	 if ($mot_cle != null ) {
    	 	$mot_cle = $this->db->escape('%' . $mot_cle . '%');
    	 	$where = "(title LIKE $mot_cle OR content LIKE $mot_cle)";
			$this->db->where($where);
    	}
       // $this->db->where("content_delete", 3);
       
  //      $this->db->distinct();

    	//$tags_id_test = array(1, 2, 3, 4, 5);
    	//$this->db->where_in('tags_contents.id_tag', $tags_id_test);

        if ($tags_id != null) {
			$this->db->where_in('tags_contents.id_tag', $tags_id);
		} 

        if ($type != null)
        	 $this->db->where("type", $type);

       	if ($subject != null)
        	 $this->db->where("subject", $subject);

    	if ($format != null) {
    		// Des posts avec des images 
    		if ($format == 'i') {
    			$this->db->where('url IS NOT NULL');
    			$this->db->where('audio IS NULL');
    		}
    		// Des posts avec seulement des audios
    		else if ($format == 'a') {
    			$this->db->where('audio IS NOT NULL');
    		}
    		else if ($format == 'v') {
    			$this->db->where('url_video IS NOT NULL');
    			$this->db->where('audio IS NULL');
    		}
    		// Des posts avec uniquement du texte
    	/*	elseif ($format == 't') {
    			$this->db->where('url IS NOT NULL');
    		}
		*/

    	}

    	if ($is_me) 
    		$this->db->where('id_membre', $id_membre);

      if ($order_by_views == 1 && $order_by_likes == 1) 
       		$this->db->order_by("contents.likes, views", "desc"); 
      	else if ($order_by_likes == 1)
       		$this->db->order_by("contents.likes", "desc"); 
       	else if ($order_by_views == 1)
       		$this->db->order_by("contents.views", "desc"); 
       	else
       		//$this->db->order_by("contents.id", "desc"); 
	       	$this->db->order_by("contents.date_creation", "desc"); 	

	//	$this->db->order_by("contents.date_creation", "desc"); 	
     //  	else
        
        $this->db->limit(25 , (string) $offset);
        $query = $this->db->get();

        $response['posts'] = array();

      /*  array_push($response["posts"], array(
        		'id' => $this->obfuscate_id->id_encode(95375),
        		'id_membre' =>  $this->obfuscate_id->id_encode(9193),
        		'title' => 'فلنساعد أخانا و نرد له جميله',
        		'subject' => 'DzBac',
        		'type' => 'Annonce',
        		'url_avatar' => $this->utils->get_url_avatar('20160603_150929-1.jpg', 9193, 'thumbnail'),
        		'username' => 'Abla Mohamed Amine',
        		'is_audio' => 1,
        		'likes' => 0,
        		'views' => 0,
        		'comments' => 0,
        		'url_presentation' =>  $this->images->get_image_presentation( $this->obfuscate_id->id_encode(95375),
			 'URL', 'Autres')
        	));  */

		foreach($query->result_array() as $row)
		{
			$posts = array();

			$posts['id'] = $this->obfuscate_id->id_encode($row['id']);

			$posts['id_membre'] = $this->obfuscate_id->id_encode($row['id_membre']);

			$posts['title'] = $row['title'];

			$posts['subject'] = $row['subject'];

			$posts["level_contribution"] = $row["level_contribution"];

			$posts["type"] = $row["type"]; 

			$posts['url_avatar'] = $this->utils->get_url_avatar($row['url_avatar'], $row['id_membre'], 'thumbnail');

		//		$posts['url_avatar'] = 'http://static.commentcamarche.net/www.commentcamarche.net/faq/images/18803-linux-online-inc.png';

		$posts['username'] = $row['username'];

		//$posts['username'] = 'No';

			$posts["content_delete"] = $row["content_delete"]; 

			if (!empty($row['url_video']))
				$posts["is_video"] = 1;
			else
				$posts["is_video"] = 0;

			if (!empty($row['audio']))
				$posts["is_audio"] = 1;
			else
				$posts["is_audio"] = 0;

			$posts["likes"] = $row["likes"];

			$posts["views"] = $row["views"];

			$posts["comments"] = $row["comments"];

			$posts["offset"] = $offset;

			$posts["url_presentation"] = $this->images->get_image_presentation($posts['id'],
			 $row['url_presentation'], $row['subject']);	

			list($posts['width_url_image_presentation'],
			 $posts['height_url_image_presentation']) = getimagesize($posts['url_presentation']);

//die($posts["url_presentation"]);
			
			array_push($response["posts"], $posts);
			
		} // end foreach

	 if ($offset == 0 && $type == null && $subject == null && $order_by_views == 0 &&
	  $order_by_likes == 0 && $mot_cle == null && $format == null && !$is_me) {
	 	$this->client->set('contents', json_encode($response));
	 }
		

		return $response;
	}

	/*
	 *
	 *
	 */
	public function get_contents_likes($id_membre, $offset)
	{

		$this->db->select('contents.id, contents.id_membre AS id_membre, title, subject, type, contents.likes, contents.views, contents.comments, audio, membres.url_avatar, membres.username,
		 contents.url_presentation, contents.date_creation, membres.level_contribution');
		$this->db->from('contents');
		$this->db->join('content_likes', 'content_likes.id_content = contents.id');
		$this->db->join('membres', 'membres.id = contents.id_membre');

		$this->db->group_by('contents.id'); 
		$this->db->group_by('content_likes.date_creation'); 
		
       // $this->db->where("content_delete", 3);
        $this->db->where("content_delete", 0);
        $this->db->where('content_likes.id_membre', $id_membre);
    	$this->db->where('content_likes.valeur', 1);

    //
       	$this->db->order_by("content_likes.date_creation", "desc"); 
        
        $this->db->limit(25 , (string) $offset);
        $query = $this->db->get();

        $response['posts'] = array();

		foreach($query->result_array() as $row)
		{
			$posts = array();

			$posts['id'] = $this->obfuscate_id->id_encode($row['id']);

			$posts['url_avatar'] = $this->utils->get_url_avatar($row['url_avatar'], $row['id_membre'], 'thumbnail');

			$posts['username'] = $row['username'];

			$posts["level_contribution"] = $row["level_contribution"];

			$posts['id_membre'] = $this->obfuscate_id->id_encode($row['id_membre']);

			$posts['title'] = $row['title'];

			$posts['subject'] = $row['subject'];

			$posts["type"] = $row["type"];

			$posts["date_creation"] = $row["date_creation"];

			$posts["likes"] = $row["likes"];

			$posts["views"] = $row["views"];

			if (!empty($row['audio']))
				$posts["is_audio"] = 1;
			else
				$posts["is_audio"] = 0;

			$posts["comments"] = $row["comments"];

			$posts["url_presentation"] = $this->images->get_image_presentation($posts['id'],
			 $row['url_presentation'], $row['subject']);	

			list($posts['width_url_image_presentation'],
			 $posts['height_url_image_presentation']) = getimagesize($posts['url_presentation']);
			
			array_push($response["posts"], $posts);
			
		} // end foreach

		return $response;
	}

	private function get_tags_id($id_content)
	{
		$this->db->select('id_tag');
		$this->db->from('tags_contents');
		$this->db->where("id_content", $id_content);
		$this->db->limit(5);
        $query = $this->db->get();

        $str = '';

        if ($query->num_rows() > 0)
		{
		   foreach ($query->result() as $row)
		   {
		      $str .= $row->id_tag . ',';
		   }
		}

		$str = rtrim($str, ',');

        return $str;
	}

	public function get_content($id_membre, $id_content)
	{
		if ($this->client->get('id_content_' . $id_content) != NULL)
		{
//$response["contents"];
			$response = json_decode($this->client->get('id_content_' . $id_content), true);

			//return $response;

			if ($this->client->get('like_' . $id_content . '_' . $id_membre) != NULL)
			{
				$valeur_aime = $this->client->get('like_' . $id_content . '_' . $id_membre);
				$response['contents'][0]['valeur_aime'] = $valeur_aime;
			}
			else
			{
				$this->db->select('valeur');
				$this->db->from('content_likes');
				$this->db->where('id_membre', $id_membre);
				$this->db->where('id_content', $id_content);
				$this->db->limit(1);

				$query2 = $this->db->get();

				 if ($query2->num_rows() > 0) {

				 	// Avoir le résult d'une ligne
				 	$result = $query2->row();
				 	$response['contents'][0]['valeur_aime'] = $result->valeur;
				 	$this->client->set('like_' . $id_content . '_' . $id_membre, $result->valeur);
		   		 }
		   		 else {
		   		 	$response['contents'][0]['valeur_aime'] = 0;
		   		 	$this->client->set('like_' . $id_content . '_' . $id_membre, 0);
		   		 }
			}

			return $response;
		//	return json_decode($this->client->get('id_content_' . $id_content));
		}

		$this->db->select('contents.id, contents.id_membre, contents.filename, contents.secteur, contents.url_video, audio,
			username, url_avatar, title, subject, type, contents.url, contents.date_creation, content, comments');
		$this->db->from('contents');
		$this->db->join('membres', 'membres.id = contents.id_membre');
       //$this->db->where("content_delete", 3);
		$this->db->where("content_delete", 0);
        $this->db->where("contents.id", $id_content);
        $this->db->limit(1);
        $query = $this->db->get();

        $response['contents'] = array();

		foreach($query->result_array() as $row)
		{
			$posts = array();

			$this->db->select('valeur');
			$this->db->from('content_likes');
			$this->db->where('id_membre', $id_membre);
			$this->db->where('id_content', $id_content);
			$this->db->limit(1);

			$query2 = $this->db->get();

			 if ($query2->num_rows() > 0) {

			 	// Avoir le résult d'une ligne
			 	$result = $query2->row();
			 	$posts['valeur_aime'] = $result->valeur;

	   		 }
	   		 else {
	   		 	$posts['valeur_aime'] = 0;
	   		 }

	   		$posts['tags'] = json_encode($this->get_tags_id($id_content));

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

			$posts["date_creation"] = $row["date_creation"];

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

		$this->client->set('id_content_' . $id_content, json_encode($response));

		return $response;
	}

	public function insert($id_content, $data, $is_modification)
	{

		$tags_id = json_decode($data['tags_id'], true);

		unset($data['tags_id']);
		
		//$data['content_delete'] = 3;
		$data['content_delete'] = 0;

		$this->db->where('id', $id_content);
		$this->db->update('contents', $data);

		// Si c'est une modification on delete tout et on réinsère
		if ($is_modification)
		{
			$this->db->where('id_content', $id_content);
			$this->db->delete('tags_contents'); 
		}

		$data_tags = array();

		// On récupère l'id des tags
		foreach ($tags_id as $tagId) {
			# code...

			$tag_content = array(
				'id_content' => $id_content,
				'id_tag' => $tagId
			);

			array_push($data_tags, $tag_content);
		}

		$this->db->insert_batch('tags_contents', $data_tags); 

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

		$this->client->del('contents');
		$this->client->del('id_content_' . $id_content);
	}


	public function delete($id_content)
	{
		$this->db->where('id', $id_content);
		$this->db->update('contents', array('content_delete' => 1));

		$this->db->where('id', $id_content);
		$this->db->update('contents', array('id_membre_del' => $this->utils->get_user_id()));

		//

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

		$this->client->del('contents');
		$this->client->del('id_content_' . $id_content);
	}


	public function update($id_content, $data)
	{
		$this->db->where('id', $id_content);
		$this->db->update('contents', $data);

		$this->client->del('contents');
		$this->client->del('id_content_' . $id_content);
	}



}
