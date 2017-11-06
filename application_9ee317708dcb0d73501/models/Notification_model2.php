<?php
/**
 * @description: Suivre un groupe dans cette class, n'est autre que suivre les nouveaux 
 * commentaires d'un post
 *
 */
class Notification_model2 extends CI_Model {

    protected $client;

    function __construct()
    {
        parent::__construct();  
        $this->client = new Predis\Client();
    }


     /**
      * Cette fonction permet à la personne de suivre l'actualité des commentaires d'un article
      * C'est à dire dés qu'il y a un nouveau commentaire, elle aura une notification 
      *
      */
     public function add_to_group($id_membre, $id_item, $type)
     {
     	// D'abord on vérifie si le membre fait déjà partie du groupe
     	$this->db->select('id');
     	$this->db->from("notification_group");
     	$this->db->where('id_membre',$id_membre);
     	$this->db->where('id_content',$id_item);
     	$this->db->where('type', $type);
     	$this->db->limit(1);
	    $query = $this->db->get();

	    if ($query->num_rows() > 0) {
	        // il existe une ligne dans la table
	           foreach ($query->result() as $row)
			   {
			   	 // On retourne l'id du groupe
			      return $row->id; 
			   }
	    }
	    else{
	    	// Sinon on l'ajoute
	    	
	        $data = array(
			'id_membre' => $id_membre,
			'type' => $type,
			'id_content' => $id_item ,
			'date_creation' => date("Y-m-d H:i:s")
			);

			$this->db->insert('notification_group', $data); 

			// On retourne l'id du groupe
			return $this->db->insert_id();
	    }
     }

     /** 
      * La personne ne recevera plus de notifications d'un certain item,
      * on supprime la ligne
      */
     public function remove_in_group($id_membre, $id_item, $type)
     {
     	$this->db->where('id_membre', $id_membre);
     	$this->db->where('id_content', $id_item);
     	$this->db->where('type', $type);
     	$this->db->limit(1);
		$this->db->delete('notification_group'); 
     }

     /*
      * Savoir si l'utilisateur appartient au groupe
      *
      */
     public function get_state_in_group($id_membre, $id_item, $type_item)
     {
        $this->db->select("id");
        $this->db->from("notification_group");
        $this->db->where("id_membre", $id_membre);
        $this->db->where("id_content", $id_item);
        $this->db->where("type", $type_item);
        $this->db->limit(1);

        $query = $this->db->get();

        if ($query->num_rows() > 0)
            return true;
        else
            return false;
     }



      /** 
      *  Permet de tout effacer quand on supprime par example un item 
      *  On enlève tout les membres du groupe et on supprime les notifications
      */
     public function remove_all_in_group($id_item, $type)
     {
     	// On récupère les id notifications groupes
     	$this->db->select('id');
     	$this->db->from("notification_group");
     	$this->db->where('id_content', $id_item);
     	$this->db->where('type', $type);
     	$query = $this->db->get();

     	$id_notifications_groups = array();

     	foreach ($query->result() as $row) {
     		array_push($id_notifications_groups, $row->id);
     	}
     	
     	///var_dump($id_notifications_groups);

     	// On supprimes les lignes dans "notifications"
     	$this->db->where_in('id_notification_group', $id_notifications_groups);
		$this->db->delete('notifications');

		// Puis on supprime les lignes "notifications_group"
     	$this->db->where('id_content', $id_item);
     	$this->db->where('type', $type);
		$this->db->delete('notification_group'); 
     }
     
     
     /**
      * Permettre de changer le status à "read"
      */
     public function state_to_read($id_membre, $id_item, $type)
     {
        $sql = "UPDATE `notifications` , `notification_group` SET `notifications`.`state` = 'read'
                WHERE `notifications`.`state` = 'unread'
                AND `notifications`.`id_notification_group` = `notification_group`.`id`
                AND `notification_group`.`type` = 'contents'
                AND `notification_group`.`id_content` = $id_item
                AND `notifications`.`id_receveur` = " . $id_membre;

        $this->db->query($sql);
        $this->client->del('notification_' . $id_membre);
     }

     /**
      * Fonction qui permet de notifier tout les membres qui appartiennent au groupe
      *
      */
     public function add_notification($id_membre, $id_item, $type, $verb, $message, $data_)
     {
     	$id_notification_group = $this->add_to_group($id_membre, $id_item, $type);

        $data_notification;
     	$data = array();
        $url_avatar;
        $data_id_receveurs = array();
     	$date_creation = date("Y-m-d H:i:s");

        // On récupère le titre du groupe c'est à dire le titre de l'item que les membres suivent 
        $this->db->select("title");
        $this->db->from('contents');
        $this->db->where("id", $id_item);
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            # code...
            $data_notification = $row->title;
        }

        // On récpuère l'avatar de celui qui notifie
        $this->db->select("url_avatar, username, level");
        $this->db->from('membres');
        $this->db->where("id", $id_membre);
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            # code...
            $level = $row->level;

            $username = $row->username;

            $url_avatar = $this->utils->get_url_avatar($row->url_avatar, 
                                                       $id_membre, 
                                                      'thumbnail');
        }

        // On séléctionne tout les utilisateurs à notifier
        $this->db->select('id, id_membre');
        $this->db->from('notification_group');
        $this->db->where('id_content', $id_item);
        $this->db->where('type', $type);

        $query = $this->db->get();

       
        
        if ($query->num_rows() > 1) 
        {
        	
        	foreach ($query->result() as $row)
        	{
        		$post = array();
        	
        		// On ne met pas de notification pour nous même
        		if ($row->id_membre != (string) $id_membre) 
                {
        	
        			$post['id_notifieur'] = $id_membre; // Celui qui notifie tout le monde
        			$post['id_receveur'] = $row->id_membre;
        			$post['date_creation'] = $date_creation;
        			$post['verb'] = $verb;
        			$post['id_notification_group'] = $row->id;
        			$post['new_notification'] = 1;
        			$post['state'] = 'unread';
        			$post['data'] = $data_notification;

                    // On supprime le cache
                    $this->client->del('notification_' . $row->id_membre);

                    // Pour les notifications push
                    array_push($data_id_receveurs, $row->id_membre);
        	
        			array_push($data, $post);
        		}
        	}
        	

        	// Maintenant on insert toutes les notifications des membres
        	$this->db->insert_batch('notifications', $data);

             $data_['id_membre'] = $this->obfuscate_id->id_encode($id_membre);
             $data_['id'] = $this->obfuscate_id->id_encode($data_['id']);
             $data_['url_avatar_notifieur'] = $url_avatar;

            if (array_key_exists('audio', $data_) && !empty($data_['audio']))
                    $data_['audio'] = HOME_URL . 'audio/' . $data_['audio']; 
                else
                    $data_['audio'] = NULL;

            if (array_key_exists('url_image', $data_) && !empty($data_['url_image']))
                $data_['url'] = preg_split("/[,]+/",  $data_['url_image']); 
            else
                $data_['url'] = array();


            if (count($data_['url']) > 0) {
                // On crée le lien des imagess 
                for( $i = 0; $i < count($data_['url']); $i++) {
                    # code...
                    $data_['url'][$i] = url_images_comment() . $data_['id'] . "/thumbnail/" . $data_['url'][$i];
                }
            } 
            else
               $data_['url'] = array();


            // On envoit des notifications push aux receveurs
            // D'abord, on récupère leurs gcm_id ,
            $this->db->select('gcm_id');
            $this->db->from('membres');
            $this->db->where_in('id', $data_id_receveurs);
            $query = $this->db->get();

            $gcm_ids = array();

            foreach ($query->result_array() as $row)
            {
                array_push($gcm_ids, $row['gcm_id']);
            }

            if (strlen($message) > 50) {
                substr($message, 0, 50);
            }


            $this->load->library('GCMPushMessage');

            $this->gcmpushmessage->setDevices($gcm_ids);

            /*
            $data = array(         
                'id_membre' => $id_membre,
                'id_content'  => $id_content,
                'url_image' => $url_image,
                'comment_delete' => 0,
                'message' => $commentaire,
                'date_creation' => date("Y-m-d H:i:s")
            );
            */

            $this->gcmpushmessage->send(null,
                array(            
                  'title' => $data_notification, 
                  'id_content' => $this->obfuscate_id->id_encode($id_item),
                  'message' => $message,
                  'url_avatar' => $url_avatar,
                  'type_notification' => 'notification',
                  'id_membre' => $data_['id_membre'],
                  'url_avatar_notifieur' => $data_['url_avatar_notifieur'],
                  'date_creation' => date("Y-m-d H:i:s"),
                  'audio' => $data_['audio'],
                  'url' => $data_['url'],
                  'username' => $username,
                  'id' => $data_['id'],
                  'level' => $level
                ), null, 2419200); 


        }

     
     }

     /*
      * Obtenir le nombre de notifications non-lu
      */

     public function get_number_notification_unread($id_membre) 
     {
        $this->db->where('state', 'unread');
        $this->db->where('id_receveur', $id_membre);
        $this->db->from('notifications');
        $this->db->limit(20);
        $response = array();
        $response['nombre_notifications'] = $this->db->count_all_results();
        return $response;
     }

     /*
      *  Mettre toutes les notifications en lus
      */
     public function set_all_notifications_read($id_membre)
     {
        $this->db->set('new_notification', 0);
        $this->db->set('state', 'read');
        $this->db->where('id_receveur', $id_membre);
        $this->db->update("notifications");

        $this->client->del('notification_' . $id_membre);
     }

     public function get_notifications($id_membre, $offset)
     {

        if ($offset == 0 && $this->client->get('notification_' . $id_membre) != NULL)
        {
            return json_decode($this->client->get('notification_' . $id_membre), true);
        }

     	$this->db->select(
     	"notification.id,
     	 notification.id_notifieur,
     	 notification.verb, 
     	 notification.state,
         notification.data,
     	 notification.date_creation,
         notification_group.id_content,
         notification_group.type,
     	 membres.username,
         membres.id AS id_membre,
         membres.url_avatar,
         membres.level_contribution
         ");

     	$this->db->from("notifications AS notification");
     	$this->db->join('membres', 'membres.id = notification.id_notifieur');
        $this->db->join('notification_group', 'notification_group.id = notification.id_notification_group');
     //   $this->db->join('payements', 'payements.id_membre = notification.id_notifieur');
     	$this->db->where("id_receveur", $id_membre);
        $this->db->order_by("id", "desc"); 
     	$this->db->limit(25 , (string) $offset);
     	$query = $this->db->get();

     	$response['posts'] = array();

        $this->load->helper("base");
        
        $updates_array = array();

        $num_rows = 0;

        foreach($query->result() as $row)
        {
        	$update_array = array();
            $posts = array();

            $num_rows++;
            
            $update_array['id'] = $row->id;
            $update_array['new_notification'] = 0;

            $posts['notification'] = $this->type_notification($row->username, $row->data, $row->verb);

            $posts["date_creation"] = $row->date_creation;   

            $posts["id_content"] = $this->obfuscate_id->id_encode($row->id_content); 

            $posts["id_membre"] = $this->obfuscate_id->id_encode($row->id_membre); 

            $posts["level_contribution"] = $row->level_contribution;

            $posts["type"] = $row->type; 

            $posts["verb"] = $row->verb; 

            $posts["state"] = $row->state; 

            $posts['url_avatar'] =  $this->utils->get_url_avatar($row->url_avatar, $row->id_membre, 'thumbnail');   

            array_push($response["posts"], $posts);
            array_push($updates_array, $update_array);
        } 
        
        if (!empty($query->row_array()))
        {
             // On update new_notification
            if ($num_rows == 1) {

                $return = $query->row_array(); 

                $this->db->set('new_notification', 0);
                $this->db->where('id', $return['id']);
                $this->db->update("notifications");
            }
            else {  //var_dump($updates_array);
                $this->db->update_batch('notifications', $updates_array, 'id'); 

            }
        }

        if ($offset == 0)
        {
            $this->client->set('notification_' . $id_membre, json_encode($response));
        }

        return $response;
     }

     public function get_new_notifications($id_membre)
     {
        
        $this->db->select(
        "notification.id,
         notification.id_notifieur,
         notification.verb, 
         notification.data,
         notification.date_creation,
         notification_group.id_content,
         notification_group.type,
         membres.username,
         membres.id AS id_membre,
         membres.url_avatar
         ");

        $this->db->from("notifications AS notification");
        $this->db->join('membres', 'membres.id = notification.id_notifieur');
        $this->db->join('notification_group', 'notification_group.id = notification.id_notification_group');
        $this->db->where("id_receveur", $id_membre);
        $this->db->where("new_notification", 1);
        $this->db->limit(25);
        $query = $this->db->get();

        $response['posts'] = array();

        $updates_array = array();

        $this->load->helper("base");

        $num_rows = 0;

        foreach($query->result() as $row)
        {
            $update_array = array();

            $num_rows++;

            $posts = array();

            $update_array['id'] = $row->id;
            $update_array['new_notification'] = 0;

            $posts['notification'] = $this->type_notification_device($row->username, $row->data, $row->verb);

            $posts["date_creation"] = $row->date_creation;   

            $posts["id_content"] = $this->obfuscate_id->id_encode($row->id_content); 

            $posts["type"] = $row->type; 

            $posts["verb"] = $row->verb; 

            $posts['url_avatar'] =  $this->utils->get_url_avatar($row->url_avatar, $row->id_membre, 'thumbnail');   

            array_push($response["posts"], $posts);
            array_push($updates_array, $update_array);
        } 

        if (!empty($query->row_array()))
        {
             // On update new_notification
            if ($num_rows == 1) {

                $return = $query->row_array(); 

                $this->db->set('new_notification', 0);
                $this->db->where('id', $return['id']);
                $this->db->update("notifications");
            }
            else {
                $this->db->update_batch('notifications', $updates_array, 'id'); 
            }
        }

        return $response;
     }


     private function type_notification($pseudo, $data, $verb)
     {
        switch ($verb) {
            case 'commented':
               
                return '<b>' . $pseudo . "</b> a commenté " . $data;
                //return $pseudo . " a commenté " . $data;
            
            default:
                # code...
                break;
        }
     }

     private function type_notification_device($pseudo, $data, $verb)
     {
        switch ($verb) {
            case 'commented':
               
                return $pseudo . " a commenté " . $data;
            
            default:
                # code...
                break;
        }
     }


}