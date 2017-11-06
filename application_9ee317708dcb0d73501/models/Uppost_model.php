<?php

class Uppost_model extends CI_Model {

	protected $client;

    function __construct()
    {
        parent::__construct();  
        $this->client = new Predis\Client();
    }

	/*
	 *  On vérifie si il existe déjà un post épingler
	 */
	public function is_post_pinned_exist($id_membre)
	{
		$this->db->select('id');
		$this->db->from('contents');
		$this->db->where('date_creation >', date("Y-m-d H:i:s"));
		$this->db->where('id_membre', $id_membre);
		$this->db->limit(1);
		$query = $this->db->get();

		if ($query->num_rows() > 0)
			return true;
		else
			return false;
	}

	/*
	 * Savoir si la personne a dépassé la limite des deux posts épinglé 
	 * par jour
	 */
	public function is_limit_reached($id_membre)
	{
		$this->db->select('id');
		$this->db->from('contents');
		$this->db->where('date_update >', 'DATE_SUB(NOW(), INTERVAL 1 DAY)');
		$this->db->where('id_membre', $id_membre);
		$this->db->limit(2);
		$query = $this->db->get();

		if ($query->num_rows() >= 2)
			return true;
		else
			return false;
	}

	/*
	 * On met en avant un post en lui ajoutant 40 min  
	 */
	public function pin_content($id_content)
	{
		$this->db->where('id', $id_content);
		$dateNow = DateTime::createFromFormat('Y-m-d H:i:s', date("Y-m-d H:i:s"));
		$dateNow->modify('+40 minutes');
		$dateCustom = $dateNow->format('Y-m-d H:i:s');

		$data = array(         
				'date_creation' => $dateCustom,
				'date_update' => date("Y-m-d H:i:s")
			);

	//	$this->db->set('date_creation', $dateCustom, FALSE);
	//	$this->db->set('date_update', date("Y-m-d H:i:s"), FALSE);
		$this->db->update('contents', $data);
		$this->client->del('contents');
	}


	
}