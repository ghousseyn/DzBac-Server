<?php

class Commentaire_model4 extends CI_Model {

protected $client;


	function __construct()
	{
		$this->client = new Predis\Client();
	}

	public function initialise($id_membre, $id_content)
	{
		$data = array(
			'id_membre' => $id_membre,
			'id_content' => $id_content,
			'date_creation' => date("Y-m-d H:i:s"),
			'comment_delete' => 2
		);

		$this->db->insert('content_comments', $data);

		return $this->obfuscate_id->id_encode($this->db->insert_id());
	}

	function add_commentaire($id_membre, $id_content, $commentaire, $url_image = null)
	{
		$data = array(         
				'id_membre' => $id_membre,
				'id_content'  => $id_content,
				'url_image' => $url_image,
				'comment_delete' => 0,
				'message' => $commentaire,
				'date_creation' => date("Y-m-d H:i:s")
			);

		$this->db->insert('content_comments' , $data);

		$id_comment = $this->db->insert_id();
		$data['id'] = $id_comment;

		$this->db->where('id', $id_content);
		$this->db->set('comments', 'comments+1', FALSE);
		$this->db->update('contents');


	/*	$pageDB = $this->client->get('commentaire_' . $id_content);

		foreach ($i = 0; i < $pageDB; $i++)
		{
			$this->client->del('commentaire_' . $id_content . '_' . $page);
		}

		$this->client->set('commentaire_' . $id_content . '_' . $page, json_encode($response));
		// On registre le page
		if ($this->client->get('commentaire_' . $id_content) != NULL)
		{
			$pageDB = $this->client->get('commentaire_' . $id_content);
			if ($page > $pageDB) 
			{
				$this->client->set('commentaire_' . $id_content, $page);	
			}
		}
		*/

		$this->client->del('commentaire_' . $id_content);
	
		return $data;
	}

	function add_commentaire_with_images($id_comment, $id_content, $commentaire)
	{
		$this->load->library('images');

		$path_images = 'images/commentaires/'. $this->obfuscate_id->id_encode($id_comment) . '/';
		$path = $path_images . 'thumbnail';

		$data = array(         
				'message' => $commentaire,
				'comment_delete' => 0,
				'url_image' => $this->images->get_path_images($path)
			);

		$this->db->where('id', $id_comment);
		$this->db->update('content_comments', $data);

		$data['id'] = $id_comment;


		$this->db->where('id', $id_content);
		$this->db->set('comments', 'comments+1', FALSE);
		$this->db->update('contents');

		$this->client->del('commentaire_' . $id_content);

		return $data;
	}

	function add_commentaire_with_audio($id_membre, $id_content, $audioname)
	{
		$data = array(         
				'id_membre' => $id_membre,
				'id_content'  => $id_content,
				'audio' => $audioname,
				'comment_delete' => 0,
				'date_creation' => date("Y-m-d H:i:s")
			);

		$this->db->insert('content_comments' , $data);

		$id_comment = $this->db->insert_id();
		$data['id'] = $id_comment;

		$this->db->where('id', $id_content);
		$this->db->set('comments', 'comments+1', FALSE);
		$this->db->update('contents');

		$this->client->del('commentaire_' . $id_content);

		return $data;
	}
	

	/*
	 * Pour modifier un commentaire existant
	 */
	function update_commentaire($id_commentaire, $message)
	{
		$this->db->set('message', $message);
		$this->db->where('id', $id_commentaire);
		$this->db->update('content_comments');

		$this->client->del('commentaire_' . $id_commentaire);
	}

	/*
	 * Pour supprimer un commentaire
	 */
	function delete_commentaire($id_commentaire, $id_content)
	{
		$this->db->set('comment_delete', 1);
		$this->db->where('id', $id_commentaire);
		$this->db->update('content_comments');

		$this->db->where('id', $id_content);
		$this->db->set('comments', 'comments-1', FALSE);
		$this->db->update('contents');

		$this->client->del('commentaire_' . $id_content);
	}
	
	private function get_nombre_pages($type_item, $id_item)
	{
		$type_id_item = 'id_' . $type_item;
     
		$this->db->select('COUNT(id) AS nombre_commentaire');
		$this->db->from('content_comments');
		$this->db->where($type_id_item, $id_item);
		
		$query = $this->db->get();
		
		foreach($query->result_array() as $row)
		{
			$response['nombre_pages'] = ceil($row['nombre_commentaire']  / 4 );
			return $response;
		}
	}
	
	function get_commentaires($id_content, $page, $item_per_page)
	{
		if ($page == 0 && $this->client->get('commentaire_' . $id_content) != NULL)
		{
			return json_decode($this->client->get('commentaire_' . $id_content), true);
		}

		$position = $page  * $item_per_page;
		
		$this->db->select('membres.url_avatar, membres.username, membres.id AS id_membre, membres.level,
                           commentaire.message, commentaire.id, commentaire.date_creation,
                           commentaire.url_image, commentaire.audio, membres.level_contribution') ;
		$this->db->from('content_comments AS commentaire');
		$this->db->join('membres', 'membres.id = commentaire.id_membre');
//		$this->db->join('payements', 'payements.id_membre = commentaire.id_membre');
		$this->db->where('commentaire.comment_delete', '0');
		$this->db->where('id_content', $id_content);
		$this->db->limit($item_per_page, $position);
		$this->db->order_by("commentaire.id", "desc"); 
		$query = $this->db->get();

		$response['comments'] = array();
		

		foreach($query->result_array() as $row)
		{

			$posts = array();

			$posts['id_membre'] = $this->obfuscate_id->id_encode($row['id_membre']);

			$posts['id'] = $this->obfuscate_id->id_encode($row['id']);

			$posts["message"] = $row["message"];

			$posts["level_contribution"] = $row["level_contribution"];

			$posts["level"] = $row["level"];

			$posts["username"] = $row["username"];

			$posts["date_creation"] = $row['date_creation'];	

			$posts['url_avatar'] =  $this->utils->get_url_avatar($row['url_avatar'], $row['id_membre'], 'thumbnail');	

			if (!empty($row['url_image']))
				$posts['url'] = preg_split("/[,]+/",  $row['url_image']); 
			else
				$posts['url'] = array();

			if (!empty($row['audio']))
				$posts['audio'] = HOME_URL . 'audio/' . $row['audio']; 
			else
				$posts['audio'] = NULL;

			$this->load->helper('base');

			if (count($posts['url']) > 0) {
				// On crée le lien des imagess 
				for( $i = 0; $i < count($posts['url']); $i++) {
					# code...
					$posts['url'][$i] = url_images_comment() . $posts['id'] . "/thumbnail/" . $posts['url'][$i];
				}
			} 
			else
				$posts['url'] = array();

			array_push($response["comments"], $posts);
			
		} // end foreach


		/*$this->client->set('commentaire_' . $id_content . '_' . $page, json_encode($response));
		// On registre le page
		if ($this->client->get('commentaire_' . $id_content) != NULL)
		{
			$pageDB = $this->client->get('commentaire_' . $id_content);
			if ($page > $pageDB) 
			{
				$this->client->set('commentaire_' . $id_content, $page);	
			}
		} */
		if ($page == 0)
			$this->client->set('commentaire_' . $id_content, json_encode($response));

		return $response;
	}







	
}