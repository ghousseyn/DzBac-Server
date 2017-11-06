<?php

class Admin_model extends CI_Model {

	public function debann_user($email)
	{
		$this->db->select('id');
		$this->db->from('membres');
		$this->db->where('email', $email);
		$this->db->limit(1);
		$query = $this->db->get();
		$result = $query->row();

		if ($query->num_rows() > 0)
		{
			$id_membre = $result->id;

			$this->db->select('phone');
			$this->db->from('digits');
			$this->db->where('id_membre', $id_membre);
			$this->db->limit(1);
			$query = $this->db->get();
			$result = $query->row();

			if ($query->num_rows() > 0) 
			{
				$this->db->delete('banned_phones', array('id_membre' => $result->phone)); 
				$this->db->delete('digits', array('phone' => $result->phone)); 
			}

			$this->db->delete('digits', array('id_membre' => $id_membre)); 

			$this->db->delete('banned_membres', array('id_banned' => $id_membre)); 

			$this->db->delete('banned_phones', array('id_membre' => $id_membre)); 

			$this->db->where('id', $id_membre);
			$this->db->set('level', 1, FALSE);
			$this->db->update('membres');

			return true;
		}
		else
		{
			return false;
		}

	}

	public function is_phone_banned($phone)
	{
		$this->db->select('phone');
		$this->db->from('banned_phones');
		$this->db->where('phone', $phone);
		$this->db->limit(1);
		$query = $this->db->get();

		// donc il existe on insère
		if ($query->num_rows() > 0)
			return true;
		else
			return false;
	}

	public function is_phone_already_used($phone, $id_membre)
	{
		$this->db->select('id_membre');
		$this->db->from('digits');
		$this->db->where('phone', $phone);
		$this->db->limit(1);
		$query = $this->db->get();

		if ($query->num_rows() > 0) 
		{
			$result = $query->row();
			return ($result->id_membre != $id_membre); 
		}
		else
			return false;
	}

	public function is_user_already_banned($id_membre)
	{
		$this->db->select('id_banned');
		$this->db->from('banned_membres');
		$this->db->where('id_banned', $id_membre);
		$this->db->limit(1);
		$query = $this->db->get();

		 if ($query->num_rows() > 0) 
		 	return true;
		 else
		 	return false;
	}

/*	public function is_user_already_banned_by_id($id_membre, $android_id)
	{
		$this->db->select('id_banned');
		$this->db->from('banned_membres');
		$this->db->where('id_banned', $id_membre);
		$this->db->where('id_banned', $android_id);
		$this->db->limit(1);
		$query = $this->db->get();

		 if ($query->num_rows() > 0) 
		 	return true;
		 else
		 	return false;
	}*/

	public function bann_user($id_banned, $id_admin)
	{
		$banned = $this->is_user_already_banned($id_banned);

		if ($banned)
			return false;

		/*
		 * Pour savoir qui a banni qui
		 */
		$data = array(
		 		'id_admin' => $id_admin,
		 		'id_banned' => $id_banned,
				'date_creation' => date("Y-m-d H:i:s")
			);


		$this->db->insert('banned_membres', $data);


		// on met level à zero
	 	$this->db->where('id', $id_banned);
		$this->db->set('level', 0, FALSE);
		$this->db->update('membres');


		// On vérifie que le numéro n'existe pas déjà dans "banned_phone"
		$this->db->select('phone');
		$this->db->from('banned_phones');
		$this->db->where('id_membre', $id_banned);
		$this->db->limit(1);
		$query = $this->db->get();

		if ($query->num_rows() == 0)
		{
			// On insert son numéro si il existe dans la table "banned_phone"
			$this->db->select('phone');
			$this->db->from('digits');
			$this->db->where('id_membre', $id_banned);
			$this->db->limit(1);
			$query = $this->db->get();

			// donc il existe on insère
			if ($query->num_rows() > 0)
			{

				$result = $query->row();

				/*
				 * Pour savoir qui a banni qui
				 */
				$data = array(
				 		'id_membre' => $id_banned,
				 		'phone' => $result->phone,
						'date_creation' => date("Y-m-d H:i:s")
					);

				$this->db->insert('banned_phones', $data);
			}
		}

		return true;
	}

	

	
}