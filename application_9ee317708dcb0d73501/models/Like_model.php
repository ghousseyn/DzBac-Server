<?php

class Like_model extends CI_Model {

	/*
	 * Récupère les utilisateurs qui ont liké un certain post
	 * 
	 */
	public function get_users_likes($id_content, $offset)
	{
		$this->db->select('membres.id as id_membre, membres.username, membres.url_avatar');
		$this->db->from('content_likes');
		$this->db->join('membres', 'membres.id = commentaire.id_membre');
		$this->db->where('valeur', 1);
		$this->db->where('id_content', $id_content);
		$this->db->limit(25 , (string) $offset);
		$this->db->order_by("content_likes.date_creation", "desc"); 
		$query = $this->db->get();

	}


	public function like($id_membre, $id_content)
	{
		$this->client = new Predis\Client();

		$this->db->select('valeur');
		$this->db->from('content_likes');
		$this->db->where('id_membre', $id_membre);
		$this->db->where('id_content', $id_content);
		$this->db->limit(1);
		$query = $this->db->get();


		 // La personne a déjà liker, on regarde la valeur du champ "valeur"
		 if ($query->num_rows() > 0) 
		 {
		 	$valeur;

		 	// Avoir le résult d'une ligne
		 	$result = $query->row();
		 	// Cela veut dire que la personne "aime" à nouveau
		 	if ($result->valeur == 0)
		 	{
		 		$valeur = 1;
		 		$this->client->set('like_' . $id_content . '_' . $id_membre, 1);
		 	}
		 	// La personne n'aime plus
		 	else
		 	{
		 		$valeur = 0;
		 		$this->client->set('like_' . $id_content . '_' . $id_membre, 0);
		 	}

		 	$this->db->where('id_membre', $id_membre);
			$this->db->where('id_content', $id_content);

			$data = array(
				'valeur' => $valeur,
				'date_creation' => date("Y-m-d H:i:s")
			);

		 	$this->db->update('content_likes', $data);


		 	// On incrémente ou décrémente le nombres de likes du contenu
		 	$this->db->where('id', $id_content);

		 	if ($valeur)
				$this->db->set('likes', 'likes+1', FALSE);
			else
				$this->db->set('likes', 'likes-1', FALSE);

			$this->db->update('contents');

			// On incrémente ou décrémente le nombres de likes pour la personne
			// D'abord on récupère l'id de la personne qui poster le contenu
			$this->db->select('id_membre');
			$this->db->from('contents');
			$this->db->where('id', $id_content);

			$query = $this->db->get();
			$row = $query->row();
			$id_membre_content = $row->id_membre;


			$this->db->where('id', $id_membre_content);

			// On évite de faire gagner des likes à la personne,
			// si elle se like elle meme
			if ($id_membre_content !== $id_membre)
			{
				if ($valeur)
					$this->db->set('likes', 'likes+1', FALSE);
				else
					$this->db->set('likes', 'likes-1', FALSE);

				$this->db->update('membres');
			}

		 	return $valeur;
		 }
		 // La personne n'a jamais like
		 else
		 {
		 	$this->client->set('like_' . $id_content . '_' . $id_membre, 1);
		// 	$this->client->set('id_content_' . $id_content, json_encode($response));

		 	$data = array(
		 		'id_membre' => $id_membre,
		 		'id_content' => $id_content,
				'valeur' => 1,
				'date_creation' => date("Y-m-d H:i:s")
			);

			$this->db->insert('content_likes', $data);


			// On incrémente ou décrémente le nombres de likes
		 	$this->db->where('id', $id_content);
			$this->db->set('likes', 'likes+1', FALSE);
			$this->db->update('contents');

			// On incrémente ou décrémente le nombres de likes pour la personne
			// D'abord on récupère l'id de la personne qui poster le contenu
			$this->db->select('id_membre');
			$this->db->from('contents');
			$this->db->where('id', $id_content);

			$query = $this->db->get();
			$row = $query->row();
			$id_membre_content = $row->id_membre;


			$this->db->where('id', $id_membre_content);

			// On évite de faire gagner des likes à la personne,
			// si elle se like elle meme
			if ($id_membre_content !== $id_membre)
			{
				$this->db->set('likes', 'likes+1', FALSE);
				$this->db->update('membres');
			}

			return 1;
		 }


	}

	private function manage_user_likes($id_membre, $id_content)
	{

	}

	
}