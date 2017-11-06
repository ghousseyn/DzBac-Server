<?php
class User_model6 extends CI_Model {

    protected $client;

    function __construct()
    {
        parent::__construct();  
        $this->client = new Predis\Client();
    }


     function get_ranking()
     {
     	$this->db->select('membres.id as id_membre, membres.username, membres.url_avatar,
     	membres.level,
     	membres.url_background, membres.likes, membres.level_contribution');
     	$this->db->from("membres");
     	$this->db->order_by("likes", "desc"); 
     	$this->db->limit(50);

     	$query = $this->db->get();

     	$response['rankings'] = array();

     	 foreach($query->result_array() as $row)
		{
			$posts = array();

			$posts['id_membre'] = $this->obfuscate_id->id_encode($row['id_membre']);

			$posts["level_contribution"] = $row["level_contribution"];

			$posts['username'] = $row['username'];
			$posts['level'] = $row['level'];

			$posts['url_avatar'] = $this->utils->get_url_avatar($row['url_avatar'], $row['id_membre'], 'thumbnail');
			$posts['url_background'] = $this->utils->get_url_background(
													$row['url_background'] ,
													$row['id_membre'],
													'background');

			$posts['likes'] = $row['likes'];

			array_push($response["rankings"], $posts);
			
		} // end foreach

		return $response;
     }

       function get_user($offset, $data)
     {
     	$this->db->select('membres.id as id_membre, membres.username, membres.url_avatar, membres.about,
     	membres.level, membres.date_last_activity, membres.localisation, membres.url_background');
     	$this->db->from("membres");

     	//var_dump($data);

     	if (array_key_exists('localisation', $data))
			$this->db->where("localisation", $data['localisation']);

		if (array_key_exists('about', $data))
			$this->db->where("about", $data['about']);

		if (array_key_exists('date_last_activity', $data) && $data['date_last_activity'] == 'Dernière activité')
			$this->db->order_by("date_last_activity", "desc");
		else
			$this->db->order_by("date_creation", "desc"); 

		if (array_key_exists('mot_cle', $data)) {
    	 /*	$mot_cle = $this->db->escape('%' . $data['mot_cle'] . '%');
    	 	$where = "(username LIKE $data['mot_cle'])";
			$this->db->where($where); */
    	}

   //  	$this->db->order_by("likes", "desc"); 
     	$this->db->limit(75 , (string) $offset);

     	$query = $this->db->get();

     	$response['users'] = array();

     	 foreach($query->result_array() as $row)
		{
			$posts = array();

			$posts['id_membre'] = $this->obfuscate_id->id_encode($row['id_membre']);

			$posts['username'] = $row['username'];
			$posts['level'] = $row['level'];
			$posts['about'] = $row['about'];
			$posts['date_last_activity'] = $row['date_last_activity'];
			$posts['localisation'] = $row['localisation'];
			$posts['url_avatar'] = $this->utils->get_url_avatar($row['url_avatar'], $row['id_membre'], 'thumbnail');
			$posts['url_background'] = $this->utils->get_url_background(
												$row['url_background'] ,
												$row['id_membre'],
												'background');
		

			array_push($response["users"], $posts);
			
		} // end foreach

		return $response;
     }

    function get_friends($digits_ids)
    {
    	$this->db->select('membres.id as id_membre, membres.username, membres.url_avatar,
     	membres.level,
     	membres.url_background');
     	$this->db->from("membres");
     	$this->db->join('digits', 'digits.id_membre = membres.id');
     	$this->db->where_in('digits.digits_id', $digits_ids);

     	$query = $this->db->get();

     	$response['rankings'] = array();

     	 foreach($query->result_array() as $row)
		{
			$posts = array();

			$posts['id_membre'] = $this->obfuscate_id->id_encode($row['id_membre']);

			$posts['username'] = $row['username'];
			$posts['level'] = $row['level'];

			$posts['url_avatar'] = $this->utils->get_url_avatar($row['url_avatar'], $row['id_membre'], 'thumbnail');
			$posts['url_background'] = $this->utils->get_url_background(
													$row['url_background'] ,
													$row['id_membre'],
													'background');

			array_push($response["rankings"], $posts);
			
		} // end foreach

		return $response;
    }
     
    function register($pseudo , $password, $email, $gcm_id)
     {//

     	if ($this->is_email_availaible($email) > 0)
     	{
     		$response['error'] = 'Erreur cette adresse e-mail existe déjà !';
     		$response['success'] = 0;
     		return $response;  
     	}


		$data = array(
			'username' => $pseudo,
			'password' =>  $password ,
			'level' => 1,
			'ip_adress' => $_SERVER['REMOTE_ADDR'],
			'gcm_id'  => $gcm_id,
			'email' =>  strtolower($email),
			'api_key' => sha1(strtolower($email) . $password), //email + password
			'date_creation' => date("Y-m-d H:i:s")
		);

		$this->db->insert('membres', $data); 

		$id_membre =  $this->db->insert_id();

		$this->send_message_new_register($id_membre);

		$id_membre_encoded = $this->obfuscate_id->id_encode($id_membre);

		//print_r($this->db->last_query());

		$data['id_membre'] = $id_membre_encoded;
		$data['url_avatar'] = 'http://www.squalala.com/images/avatar/default_avatar.png';
		$data['username'] = $pseudo;
 
		return $data;
     }
     
     function register_facebook($pseudo, $password, $id_facebook,  $email, $url_avatar, $gcm_id)
     {

     	if ( $this->is_email_availaible($email) > 0 )
     	{
     		$response['error'] = 'Erreur ce compte est déjà inscris !';
     		$response['success'] = 0;
     		return $response;  
     	}
     
	     $data = array(
			'username' => $pseudo ,
			'password' =>  $password ,
			'gcm_id'  => $gcm_id,
			'ip_adress' => $_SERVER['REMOTE_ADDR'],
			'level' => 1,
			'email' =>  strtolower($email),
			'url_avatar' => $url_avatar,
			'id_facebook' => $id_facebook,
			'api_key' => sha1(strtolower($email) . $password),
			'date_creation' => date("Y-m-d H:i:s")
		);
		
		$this->db->insert('membres', $data); 
		
		
		$id_membre =  $this->db->insert_id();  
		
		$this->send_message_new_register($id_membre);

		$id_membre_encoded = $this->obfuscate_id->id_encode($id_membre);

		$data = array();

		$data['id_membre'] = $id_membre_encoded;
		$data['username'] = $pseudo;
		$data['message'] = "Compte crée";
		
		return $data;
     }

     function register_google($pseudo, $id_google,  $email, $url_avatar, $gcm_id)
     {

     	if ( $this->is_email_availaible($email) > 0 )
     	{
     		$response['error'] = 'Erreur ce compte est déjà inscris !';
     		$response['success'] = 0;
     		return $response;  
     	}
     
	     $data = array(
			'username' => $this->utils->stripAccents($pseudo) ,
			'password' =>  $id_google ,
			'gcm_id'  => $gcm_id,
			'level' => 1,
			'url_avatar' => $url_avatar,
			'email' =>  strtolower($email),
			'id_google' => $id_google,
			'api_key' => sha1(strtolower($email) . $id_google),
			'date_creation' => date("Y-m-d H:i:s")
		);
		
		$this->db->insert('membres', $data); 
		
		$id_membre =  $this->db->insert_id();  

		$this->send_message_new_register($id_membre);

		$id_membre_encoded = $this->obfuscate_id->id_encode($id_membre);

		$data = array();

		$data['id_membre'] = $id_membre_encoded;
		$data['username'] = $pseudo;
		$data['message'] = "Compte crée";

		return $data;
     }

    private function send_message_new_register($id_receveur)
	{
	/*	$this->load->model('messagerie_model');

		$pseudo_expediteur = "Vlagos";
		$id_membre = '17';
		$sujet = "Bienvenue sur Squalala !";
		$message = "Salut, je suis le développeur de l'application, si vous avez besoin d'aide n'hésitez pas à me contacter, et surtout votre avis" .
		 " m'intéresse afin de pouvoir améliorer Squalala, merci !";

		$this->messagerie_model->start_conversation($pseudo_expediteur,$id_membre, $id_receveur, $message, $sujet);
		*/
	}


     
     
     function is_email_availaible($email)
     {
		$sql = "SELECT email FROM membres WHERE email = ? LIMIT 1";
		$query = $this->db->query($sql,  $email);
		
		return count($query->result_array());
     }

     private function update_gcm_id($id_membre, $gcm_id)
     {
     	$this->db->where('id', $id_membre);
		$this->db->update('membres', array('gcm_id' => $gcm_id)); 
     }
     
     
     function login($email, $mdp, $gcm_id)
     {
		$sql = "SELECT  password, email, username, level, id, url_avatar, url_background FROM membres WHERE email = ? LIMIT 1";
			     
		$query = $this->db->query($sql,  $email);
		
		foreach ( $query->result_array() as $row)
		{
			$mdp_db = $row['password'];
			$id_membre = $row['id'];
			$pseudo = $row["username"];
			$level = $row['level'];
			$url_avatar = $this->utils->get_url_avatar($row['url_avatar'], $row['id'], 'thumbnail');
		}
		
		if ( isset($mdp_db)) 
		{
			if ($mdp == $mdp_db)
			{
				$data = array("url_avatar" => $url_avatar,
							  "id_membre" => $this->obfuscate_id->id_encode($id_membre),
							  "username" => $pseudo,
							  "level" => $level,
							  'url_background' => $user['url_background'] = $this->utils->get_url_background(
													$row['url_background'] ,
													$id_membre,
													'background')
				); 

				$this->update_gcm_id($id_membre, $gcm_id);
				
				return $data; // Connexion réussis.
			}	
			else
				return 0; // C'est le mauvais mot de passe.
		}
		else	
			return 0; // L'email n'existe pas dans la base de donnée.
		
     }
     
     function login_facebook($id_facebook, $gcm_id)
     {
	
	   $sql =  "SELECT  username ,id, url_avatar, level, id, url_background FROM membres
		    	WHERE id_facebook = ? LIMIT 1";

	   $query = $this->db->query($sql,  $id_facebook);

	   if ($query->num_rows() > 0)
	   {
	   		foreach ( $query->result_array() as $row)
			{
				$data = array(
					      "url_avatar"  => $this->utils->get_url_avatar($row['url_avatar'], $row['id'], 'thumbnail'),
						  "id_membre"   => $this->obfuscate_id->id_encode($row['id']),
						  "username"    => $row["username"],
						  "level" => $row['level'],
						  'url_background' => $user['url_background'] = $this->utils->get_url_background(
													$row['url_background'] ,
													$row['id'],
													'background')
				);	


				$this->update_gcm_id($row['id'], $gcm_id);

				return $data;  
			}
	   }
	   else
	   {
	   		return 0;
	   }
     }

     function login_google($id_google, $gcm_id)
     {
	
	   $sql =  "SELECT  username, id, url_avatar, level, url_background FROM membres 
				WHERE id_google = ? LIMIT 1";

	   $query = $this->db->query($sql,  $id_google);

	   if ($query->num_rows() > 0)
	   {
	   		foreach ( $query->result_array() as $row)
			{
				$data = array(
					      "url_avatar"  => $this->utils->get_url_avatar($row['url_avatar'], $row['id'], 'thumbnail'),
						  "id_membre"   => $this->obfuscate_id->id_encode($row['id']),
						  "username"    => $row["username"],
						  "level" => $row['level'],
						  'url_background' => $user['url_background'] = $this->utils->get_url_background(
													$row['url_background'] ,
													$row['id'],
													'background')
				);	

				$this->update_gcm_id($row['id'], $gcm_id);

				return $data;  
			}
	   }
	   else
	   {
	   		return 0;
	   }
     }
     
      function get_informations($id_membre)
     {

     /*	if ($this->client->get('user_' . $id_membre) != NULL)
        {
            return json_decode($this->client->get('user_' . $id_membre), true);
        }
*/
		$this->db->select('username, localisation, about, date_creation, url_avatar, url_background, likes,  date_last_activity, level_contribution,
			(SELECT COUNT(id) FROM following WHERE id_followed = ' . $this->db->escape($id_membre)  .' AND is_following = 1) AS abonnes,
			(SELECT is_following FROM following WHERE id_followed = ' . $this->db->escape($id_membre)  .' AND id_follower = ' . $this->utils->get_user_id() . ') AS is_following');
		$this->db->from('membres');
		$this->db->where('id', $id_membre);

		$query = $this->db->get();

		foreach($query->result_array() as $row)
		{
			$user = array();

			$user['username'] = $row['username'];
			$user['localisation'] = $row['localisation'];
			$user['about'] = $row['about'];
			$user['likes'] = $row['likes'];
			$user['abonnes'] = $row['abonnes'];
			$user['level_contribution'] = $row['level_contribution'];

			if (!$row['is_following'] == null)
				$user['is_following'] = $row['is_following'];
			else
				$user['is_following'] = '0';

			$user['date_creation'] = $row['date_creation'];
			$user['date_last_activity'] = $row['date_last_activity'];


			$user['url_avatar_large'] = $this->utils->get_url_avatar($row['url_avatar'], $id_membre, 'none');
			$user['url_avatar'] = $this->utils->get_url_avatar($row['url_avatar'], $id_membre, 'thumbnail');
			$user['url_background'] = $this->utils->get_url_background(
													$row['url_background'] ,
													$id_membre,
													'background');

//			$this->client->set('user_' . $id_membre, json_encode($user));


			return $user;
		}
     }

      function get_level_contribution($id_membre)
     {
		$this->db->select('level_contribution');
		$this->db->from('membres');
		$this->db->where('id', $id_membre);

		$query = $this->db->get();

		foreach($query->result_array() as $row)
		{
			$user = array();

			return $row['level_contribution'];
		}
     }
     
     
     function update_informations($id_membre, $data)
     {

     	if (array_key_exists('level', $data)) {
		    return false;
		}
		else if (array_key_exists('phone', $data) && array_key_exists('digits_id', $data)) {

			$phone = $this->db->escape($data['phone']);
			$digits_id =  $this->db->escape($data['digits_id']);

			$time = date("Y-m-d H:i:s");

			$sql = "INSERT INTO digits (digits_id, id_membre, phone, date_creation)
			        VALUES (?, ?, ?, ?)
			        ON DUPLICATE KEY UPDATE 
			            digits_id=VALUES(digits_id), 
			            id_membre=VALUES(id_membre), 
			            phone=VALUES(phone),
			            date_creation=VALUES(date_creation)";

			$query = $this->db->query($sql, array( $digits_id,
			                                       $id_membre,
			                                       $phone,
			                                       $time
			                                      ));

		}
		else {

			unset($data['digits_id']);

			$this->db->where('id', $id_membre);
			$this->db->update('membres', $data);	
		}


		$this->client->del('user_' . $id_membre);
     }

     
     
     
 }
     
     