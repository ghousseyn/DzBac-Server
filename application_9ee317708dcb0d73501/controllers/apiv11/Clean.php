<?php defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH.'/libraries/REST_Controller.php';

class Clean extends REST_Controller {


	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_model5');
	}

	function test_valeuraime_get()
	{
		$this->client = new Predis\Client();

		$id_membre = 10;
		$id_content = 172198;

		$response = json_decode($this->client->get('id_content_' . $id_content), true);

			//return $response;

		if ($this->client->get('like_' . $id_content . '_' . $id_membre) != NULL)
		{
			$valeur_aime = $this->client->get('like_' . $id_content . '_' . $id_membre);
			$response['contents'][0]['valeur_aime'] = $valeur_aime;
		}

		$this->response($response, 200);
	}

	function backups_get()
	{
		$backupFile = 'packer_bac' . date("Y-m-d-H-i-s") . '.gz';
		$command = "mysqldump --opt -h localhost -u kaddouri -p 5ehu8y2yz packer_bac | gzip > $backupFile";
		system($command);
		echo '<pre>';

 // Affiche le résultat de la commande "ls" et retourne
 // la dernière lignes dans $last_line. Stocke la valeur retournée
 // par la commande shelle dans $retval.
 $last_line = system('ls', $retval);

 // Affichage d'autres informations
 echo '
</pre>
<hr />La dernière ligne en sortie de la commande : ' . $last_line . '
<hr />Valeur retournée : ' . $retval;

		die('mysql OK');
	//	include 'closedb.php';
	}




function foldersize($path) {
    $total_size = 0;
    $files = scandir($path);
    $cleanPath = rtrim($path, '/'). '/';

    foreach($files as $t) {
        if ($t<>"." && $t<>"..") {
            $currentFile = $cleanPath . $t;
            if (is_dir($currentFile)) {
                $size = $this->foldersize($currentFile);
                $total_size += $size;
            }
            else {
                $size = filesize($currentFile);
                $total_size += $size;
            }
        }   
    }

    return $total_size;
}


function format_size($size) {
	$units = explode(' ', 'B KB MB GB TB PB');

    $mod = 1024;

    for ($i = 0; $size > $mod; $i++) {
        $size /= $mod;
    }

    $endIndex = strpos($size, ".")+3;

    return substr( $size, 0, $endIndex).' '.$units[$i];
}


	public function info_get()
	{
		echo phpinfo();
	}


	public function sizet_get()
	{

	/*  $f = realpath(APPPATH . '../images/posts/');
	    $io = popen ( '/usr/bin/du -sk ' . $f, 'r' );
	    $size = fgets ( $io, 4096);
	    $size = substr ( $size, 0, strpos ( $size, "\t" ) );
	    pclose ( $io );
	    echo 'Directory: ' . $f . ' => Size: ' . $size; */

	    $units = explode(' ', 'B KB MB GB TB PB');
	    $SIZE_LIMIT = 5368709120; // 5 GB
	    $disk_used = $this->foldersize(realpath(APPPATH . '../images/posts/'));

	    $disk_remaining = $SIZE_LIMIT - $disk_used;

	    echo("<html><body>");
	    echo('diskspace used: ' . $this->format_size($disk_used) . '<br>');
	    echo( 'diskspace left: ' . $this->format_size($disk_remaining) . '<br><hr>');
	    echo("</body></html>");


	    die();
	}


	public function sizeta_get()
	{

	/*  $f = realpath(APPPATH . '../images/posts/');
	    $io = popen ( '/usr/bin/du -sk ' . $f, 'r' );
	    $size = fgets ( $io, 4096);
	    $size = substr ( $size, 0, strpos ( $size, "\t" ) );
	    pclose ( $io );
	    echo 'Directory: ' . $f . ' => Size: ' . $size; */

	    $units = explode(' ', 'B KB MB GB TB PB');
	    $SIZE_LIMIT = 5368709120; // 5 GB
	    $disk_used = $this->foldersize(realpath(APPPATH . '../images/commentaires/'));

	    $disk_remaining = $SIZE_LIMIT - $disk_used;

	    echo("<html><body>");
	    echo('diskspace used: ' . $this->format_size($disk_used) . '<br>');
	    echo( 'diskspace left: ' . $this->format_size($disk_remaining) . '<br><hr>');
	    echo("</body></html>");


	    die();
	}



	public function insert_com_put()
	{
			// Check si le user n'est pas banni
		$this->load->model('admin_model');
		  $this->load->model('commentaire_model3');

		$banned = $this->admin_model->is_user_already_banned($this->utils->get_user_id());

		if ($banned)
			$this->response(NULL, 500);

    	$id_encoded = $this->commentaire_model3->initialise(
			 $this->utils->get_user_id(),
			 $this->obfuscate_id->id_decode($this->put('id_content'))
    	);

    	$this->response(array('id' => $id_encoded), 200);
	}

	public function fix_date_get()
	{
		$this->db->select('id');
		$this->db->from('membres');
    	$this->db->where("date_last_activity", '0000-00-00 00:00:00');

    	$query = $this->db->get();
    	$data = array();

		foreach($query->result_array() as $row)
		{
			array_push($data, array('date_last_activity' => '1900-01-01 00:00:00', 
									'id' => $row['id']));
		}


		
		$this->db->update_batch('membres', $data, 'id'); 

		die();
	}


	public function test_insert_put()
	{
		$this->load->model('admin_model');

		$banned = $this->admin_model->is_user_already_banned(10);

		var_dump($banned);

		if ($banned)
			$this->response(NULL, 500);

			$data = array(         
				'id_membre' => 10,
				'id_content'  => 66,
				'url_image' => "rest",
				'comment_delete' => 0,
				'message' => 'hey man rerzerzerzerzer aaa',
				'date_creation' => date("Y-m-d H:i:s")
			);

		$this->response(array($this->put('test') . 'result d' . $banned => $this->db->insert('content_comments' , $data)), 200);

		$this->db->where('id', $id_content);
		$this->db->set('comments', 'comments+1', FALSE);
		$this->db->update('contents');
	}


	/*
     * Supprimer les posts caché
     *
     */
	public function delete_contents_get()
	{
			ini_set("pcre.backtrack_limit", "23001337");
		ini_set("pcre.recursion_limit", "23001337");
		ini_set('memory_limit', '-1');
		
		$this->db->select('contents.id, content_delete, audio, url_video, filename, views, likes, comments, type, date_creation, url_presentation');
		$this->db->from('contents');
    	$this->db->where("content_delete", 0); 
    //	$this->db->where("date_creation <=", '2016-05-16 23:09:29');
    //    $this->db->where("subject", 'Autres'); 
    	//$this->db->where("type", 'Aide');
    	$this->db->where("date_creation <=", '2017-05-30 23:09:29');
    	$this->db->where("views <=", '100');
    	$this->db->where("likes <=", '5');
    	$this->db->where("comments <=", '5'); 

    	$query = $this->db->get();

    	$countAudio = 0;
    	$countVideo = 0;
    	$countfilename = 0;

// echo 'encode url_presentation : ' . $this->obfuscate_id->id_encode(15932) . ' * ';
	//print_r($query->result_array()); 	die();
    

    //	echo realpath(APPPATH . '../audio') ; die();

    	$response['contents'] = array();

    	$ids_to_delete = array();

		foreach($query->result_array() as $row)
		{
			$posts = array();
			$posts['id'] = $row['id'];

			array_push($ids_to_delete, $row['id']);

			if(!empty($row['url_video']))
			{
				$countVideo++;
				$posts['url_video'] = str_replace("http://www.squalala.xyz/dz_bac/videos/", "", $row['url_video']);

				$path_file = realpath(APPPATH . '../videos') . '/' . $posts['url_video'];

				if (file_exists($path_file) && !is_dir($path_file)) {
					unlink($path_file);
				}
			}

			if (!empty($row['audio']))
			{
				$countAudio++;
				$posts['audio'] = $row['audio'];

				$path_file = realpath(APPPATH . '../audio') . '/' . $posts['audio'];

				if (file_exists($path_file) && !is_dir($path_file)) {
					unlink($path_file);
				}
			}

			if (!empty($row['filename']))
			{
				$countfilename++;
				$posts['filename'] = $row['filename'];

				$path_file = realpath(APPPATH . '../uploads') . '/' . $posts['filename'];

				if (file_exists($path_file) && !is_dir($path_file)) {
					unlink($path_file);
				}
			}

			// suppresion du dossier des images
			$path = realpath(APPPATH . '../images/posts/' . $this->obfuscate_id->id_encode($row['id']) . '/');
			$this->rrmdir($path);

			$posts['content_delete'] = $row['content_delete'];

			array_push($response["contents"], $posts);
		}


		$response['message'] = 'Nombre de fichier :' . $countfilename . '\n'; 
		$response['message'] .= "Nombre d'audio :" . $countAudio . "\n"; 
		$response['message'] .= 'Nombre de vidéo :' . $countVideo . '\n'; 

		$this->db->where_in('id', $ids_to_delete);
		$this->db->delete('contents');

		$this->db->where_in('id_content', $ids_to_delete);
		$this->db->delete('tags_contents');

		$this->db->where_in('id_content', $ids_to_delete);
		$this->db->delete('notification_group');

	/*	$this->load->helper('file');

		$id_encoded = $this->obfuscate_id->id_encode(15);

		$path = realpath(APPPATH . '../images/posts/' . $id_encoded . '/');

		$this->rrmdir($path);

		//echo realpath(APPPATH . '../images/posts/' . $id_encoded);

		//print_r(get_dir_file_info(realpath(APPPATH . '../images/posts/' . $id_encoded), $top_level_only = TRUE));
		*/
		//print_r($response);
		$this->response($response, 200);
	}

	/*
     * Supprimer les posts caché
     */
	public function delete_contents_hidden_get()
	{
		$this->db->select('contents.id, content_delete, audio, url_video, filename, views, likes, comments, type, date_creation, url_presentation');
		$this->db->from('contents');
    	$this->db->where("content_delete", 1); 

    	$query = $this->db->get();

    	$countAudio = 0;
    	$countVideo = 0;
    	$countfilename = 0;

    	$response['contents'] = array();

    	$ids_to_delete = array();

		foreach($query->result_array() as $row)
		{
			$posts = array();
			$posts['id'] = $row['id'];

			array_push($ids_to_delete, $row['id']);

			if(!empty($row['url_video']))
			{
				$countVideo++;
				$posts['url_video'] = str_replace("http://www.squalala.xyz/dz_bac/videos/", "", $row['url_video']);

				$path_file = realpath(APPPATH . '../videos') . '/' . $posts['url_video'];

				if (file_exists($path_file) && !is_dir($path_file)) {
					unlink($path_file);
				}
			}

			if (!empty($row['audio']))
			{
				$countAudio++;
				$posts['audio'] = $row['audio'];

				$path_file = realpath(APPPATH . '../audio') . '/' . $posts['audio'];

				if (file_exists($path_file) && !is_dir($path_file)) {
					unlink($path_file);
				}
			}

			if (!empty($row['filename']))
			{
				$countfilename++;
				$posts['filename'] = $row['filename'];

				$path_file = realpath(APPPATH . '../uploads') . '/' . $posts['filename'];

				if (file_exists($path_file) && !is_dir($path_file)) {
					unlink($path_file);
				}
			}

			// suppresion du dossier des images
			$path = realpath(APPPATH . '../images/posts/' . $this->obfuscate_id->id_encode($row['id']) . '/');
			$this->rrmdir($path);

			$posts['content_delete'] = $row['content_delete'];

			array_push($response["contents"], $posts);
		}


		$response['message'] = 'Nombre de fichier :' . $countfilename . '\n'; 
		$response['message'] .= "Nombre d'audio :" . $countAudio . "\n"; 
		$response['message'] .= 'Nombre de vidéo :' . $countVideo . '\n'; 

		$this->db->where_in('id', $ids_to_delete);
		$this->db->delete('contents');

		$this->db->where_in('id_content', $ids_to_delete);
		$this->db->delete('tags_contents');

		$this->db->where_in('id_content', $ids_to_delete);
		$this->db->delete('notification_group');

		$this->response($response, 200);
	}

	/*
     * Supprimer les posts initialisé qui date d'hier voir plus
     *
     */
	public function delete_contents_init_get()
	{
		$this->db->select('contents.id, content_delete, audio, url_video, filename, views, likes, comments, type, date_creation, url_presentation');
		$this->db->from('contents');
    	$this->db->where("content_delete", 2); 
    	$this->db->where("date_creation <=", date("Y-m-d H:i:s", strtotime("-24 hours")));

    	$query = $this->db->get();

    	$countAudio = 0;
    	$countVideo = 0;
    	$countfilename = 0;

    	$response['contents'] = array();

    	$ids_to_delete = array();

		foreach($query->result_array() as $row)
		{
			$posts = array();
			$posts['id'] = $row['id'];

			array_push($ids_to_delete, $row['id']);

			if(!empty($row['url_video']))
			{
				$countVideo++;
				$posts['url_video'] = str_replace("http://www.squalala.xyz/dz_bac/videos/", "", $row['url_video']);

				$path_file = realpath(APPPATH . '../videos') . '/' . $posts['url_video'];

				if (file_exists($path_file) && !is_dir($path_file)) {
					unlink($path_file);
				}
			}

			if (!empty($row['audio']))
			{
				$countAudio++;
				$posts['audio'] = $row['audio'];

				$path_file = realpath(APPPATH . '../audio') . '/' . $posts['audio'];

				if (file_exists($path_file) && !is_dir($path_file)) {
					unlink($path_file);
				}
			}

			if (!empty($row['filename']))
			{
				$countfilename++;
				$posts['filename'] = $row['filename'];

				$path_file = realpath(APPPATH . '../uploads') . '/' . $posts['filename'];

				if (file_exists($path_file) && !is_dir($path_file)) {
					unlink($path_file);
				}
			}

			// suppresion du dossier des images
			$path = realpath(APPPATH . '../images/posts/' . $this->obfuscate_id->id_encode($row['id']) . '/');
			$this->rrmdir($path);

			$posts['content_delete'] = $row['content_delete'];

			array_push($response["contents"], $posts);
		}


		$response['message'] = 'Nombre de fichier :' . $countfilename . '\n'; 
		$response['message'] .= "Nombre d'audio :" . $countAudio . "\n"; 
		$response['message'] .= 'Nombre de vidéo :' . $countVideo . '\n'; 

		$this->db->where_in('id', $ids_to_delete);
		$this->db->delete('contents');

		$this->db->where_in('id_content', $ids_to_delete);
		$this->db->delete('tags_contents');

		$this->db->where_in('id_content', $ids_to_delete);
		$this->db->delete('notification_group');

		$this->response($response, 200);
	}

	/*
     * Supprimer les vues de plus de 24h
     */
	public function delete_content_hits_get()
	{
		$_date = date("Y-m-d H:i:s", strtotime("-24 hours"));

		$this->db->query("DELETE 
				FROM content_hits
				Where date_creation <= '$_date'");
		
		$this->response(NULL, 200);
	}


	/*
	 *
	 * Supprimer les messages avec headers qui ont été caché
	 *
	 */
	public function delete_messages_get()
	{
		$query = $this->db->query('SELECT audio
									FROM message
									INNER JOIN header
									  ON message.header_id = header.id
									Where header_deleted = 1');

		foreach($query->result_array() as $row)
		{
			$posts = array();

			if (!empty($row['audio']))
			{
				$posts['audio'] = $row['audio'];

				$path_file = realpath(APPPATH . '../audio') . '/' . $posts['audio'];

				if (file_exists($path_file) && !is_dir($path_file)) {
					unlink($path_file);
				}
			}

		}

		$this->db->query('DELETE message
									FROM message
									INNER JOIN header
									  ON message.header_id = header.id
									Where header_deleted = 1');

		$this->db->query('DELETE header 
							FROM header WHERE  header_deleted = 1');


		$this->response(NULL, 200);
	}


	/*
     * Supprimer les commentaires caché
     *
     */
	public function delete_coms_hidden_get()
	{
		ini_set("pcre.backtrack_limit", "23001337");
		ini_set("pcre.recursion_limit", "23001337");
		ini_set('memory_limit', '-1');

		$this->db->select('id, url_image, comment_delete');
		$this->db->from('content_comments');
    	$this->db->where("comment_delete", 1);

    	$query = $this->db->get();  

    	$response['coms'] = array();

    	$ids_to_delete = array();

    	$counterImage = 0;

		foreach($query->result_array() as $row)
		{
			$posts = array();
			$posts['id'] = $row['id'];

			if (!empty($row['url_image']))
				$posts['url'] = preg_split("/[,]+/",  $row['url_image']); 
			else
				$posts['url'] = array();

			if (count($posts['url']) > 0) {
				$counterImage++;
			}

			array_push($ids_to_delete, $row['id']);

			// suppresion du dossier des images
			$path = realpath(APPPATH . '../images/commentaires/' . $this->obfuscate_id->id_encode($row['id']) . '/');
			$this->rrmdir($path);

			$posts['comment_delet'] = $row['comment_delete'];

			array_push($response["coms"], $posts);
		}

		$this->db->where_in('id', $ids_to_delete);
		$this->db->delete('content_comments');

		$response['count'] = $counterImage;
		$this->response($response, 200);
	}

		/*
     * Supprimer les commentaires caché
     *
     */
	public function delete_coms_get()
	{
		ini_set("pcre.backtrack_limit", "23001337");
		ini_set("pcre.recursion_limit", "23001337");
		ini_set('memory_limit', '-1');
	/*	$this->db->select('id, url_image, comment_delete');
		$this->db->from('content_comments');
    	$this->db->where("comment_delete", 0);

    	$query = $this->db->get();  */

    	$query = $this->db->query('SELECT * FROM content_comments WHERE id_content
		 NOT IN (SELECT id FROM contents)');

//		print_r($query->result_array());

//		die();

    	$response['coms'] = array();

    	$ids_to_delete = array();

    	$counterImage = 0;

		foreach($query->result_array() as $row)
		{
			$posts = array();
			$posts['id'] = $row['id'];

			if (!empty($row['url_image']))
				$posts['url'] = preg_split("/[,]+/",  $row['url_image']); 
			else
				$posts['url'] = array();

			if (count($posts['url']) > 0) {
				$counterImage++;
			}

			array_push($ids_to_delete, $row['id']);

			// suppresion du dossier des images
			$path = realpath(APPPATH . '../images/commentaires/' . $this->obfuscate_id->id_encode($row['id']) . '/');
			$this->rrmdir($path);

			$posts['comment_delet'] = $row['comment_delete'];

			array_push($response["coms"], $posts);
		}

		$this->db->where_in('id', $ids_to_delete);
		$this->db->delete('content_comments');

		$response['count'] = $counterImage;
		$this->response($response, 200);
	}

	/*
     * Supprimer les commentaires caché des posts supprimé
     */
	public function delete_coms_hidden_post_hidden_get()
	{
		ini_set("pcre.backtrack_limit", "23001337");
		ini_set("pcre.recursion_limit", "23001337");
		ini_set('memory_limit', '-1');

    	$query = $this->db->query('SELECT * FROM content_comments WHERE id_content
		 NOT IN (SELECT id FROM contents) ');

    	$response['coms'] = array();

    	$ids_to_delete = array();

    	$counterImage = 0;

		foreach($query->result_array() as $row)
		{
			$posts = array();
			$posts['id'] = $row['id'];

			if (!empty($row['url_image']))
				$posts['url'] = preg_split("/[,]+/",  $row['url_image']); 
			else
				$posts['url'] = array();

			if (count($posts['url']) > 0) {
				$counterImage++;
			}

			array_push($ids_to_delete, $row['id']);

			// suppresion du dossier des images
			$path = realpath(APPPATH . '../images/commentaires/' . $this->obfuscate_id->id_encode($row['id']) . '/');
			$this->rrmdir($path);

			$posts['comment_delet'] = $row['comment_delete'];

			array_push($response["coms"], $posts);
		}

		$this->db->where_in('id', $ids_to_delete);
		$this->db->delete('content_comments');

		$response['count'] = $counterImage;
		$this->response($response, 200);
	}


	/*
	*
	* Supprimer les lignes avec content_delete = 2 jusqu'à un jour avant la date d'aujourd'hui
	*
	*/
	public function delete_contents2_get()
	{
		$this->db->select('contents.id, content_delete, audio, url_video, filename, date_creation');
		$this->db->from('contents');
    	$this->db->where("content_delete", 2);
    	$this->db->where("date_creation <", "CURRENT_DATE() - 2");

    	$query = $this->db->get();

    	$countAudio = 0;
    	$countVideo = 0;
    	$countfilename = 0;

    	$response['contents'] = array();

    	$ids_to_delete = array();

		foreach($query->result_array() as $row)
		{
			$posts = array();
			$posts['id'] = $row['id'];

			array_push($ids_to_delete, $row['id']);

			if(!empty($row['url_video']))
			{
				$countVideo++;
				$posts['url_video'] = str_replace("http://www.squalala.xyz/dz_bac/videos/", "", $row['url_video']);

				$path_file = realpath(APPPATH . '../videos') . '/' . $posts['url_video'];

				if (file_exists($path_file) && !is_dir($path_file)) {
					unlink($path_file);
				}
			}

			if (!empty($row['audio']))
			{
				$countAudio++;
				$posts['audio'] = $row['audio'];

				$path_file = realpath(APPPATH . '../audio') . '/' . $posts['audio'];

				if (file_exists($path_file) && !is_dir($path_file)) {
					unlink($path_file);
				}
			}

			if (!empty($row['filename']))
			{
				$countfilename++;
				$posts['filename'] = $row['filename'];

				$path_file = realpath(APPPATH . '../uploads') . '/' . $posts['filename'];

				if (file_exists($path_file) && !is_dir($path_file)) {
					unlink($path_file);
				}
			}

			// suppresion du dossier des images
			$path = realpath(APPPATH . '../images/posts/' . $this->obfuscate_id->id_encode($row['id']) . '/');
			$this->rrmdir($path);

			$posts['content_delete'] = $row['content_delete'];

			array_push($response["contents"], $posts);
		}


		$response['message'] = 'Nombre de fichier :' . $countfilename . '\n'; 
		$response['message'] .= "Nombre d'audio :" . $countAudio . "\n"; 
		$response['message'] .= 'Nombre de vidéo :' . $countVideo . '\n'; 

		$this->db->where_in('id', $ids_to_delete);
		$this->db->delete('contents');

		$this->db->where_in('id_content', $ids_to_delete);
		$this->db->delete('tags_contents');

	//	$this->db->where_in('id_content', $ids_to_delete);
	//	$this->db->delete('notification_group');

	/*	$this->load->helper('file');

		$id_encoded = $this->obfuscate_id->id_encode(15);

		$path = realpath(APPPATH . '../images/posts/' . $id_encoded . '/');

		$this->rrmdir($path);

		//echo realpath(APPPATH . '../images/posts/' . $id_encoded);

		//print_r(get_dir_file_info(realpath(APPPATH . '../images/posts/' . $id_encoded), $top_level_only = TRUE));
		*/
		//print_r($response);
		$this->response($response, 200);
	}



   private function rrmdir($dir) { 
   if (is_dir($dir)) { 
     $objects = scandir($dir); 
     foreach ($objects as $object) { 
       if ($object != "." && $object != "..") { 
         if (is_dir($dir."/".$object))
           $this->rrmdir($dir."/".$object);
         else
           unlink($dir."/".$object); 
       } 
     }
     rmdir($dir); 
   } 
 }


}