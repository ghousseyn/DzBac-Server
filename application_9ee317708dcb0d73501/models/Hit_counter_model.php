<?php

class Hit_counter_model extends CI_Model {



	function hit_counter($id_membre, $id_item)
	{
		$table_hit_count = 'content_hits'; // la table exemple 'hit_annonce'
		
		$this->db->select('date_creation');
		$this->db->from($table_hit_count);
		$this->db->where('id_membre',  $id_membre);
		$this->db->where('id_content',  $id_item);
		$this->db->limit(1);

		$query = $this->db->get();
		
		if (count($query->result_array()) != 0) // la personne a déjà vu l'item et on va comparer la date de la vus avec la date actuelle
		{
			foreach($query->result_array() as $row)
			{
			
				$date_time_db = new DateTime($row['date_creation']);
				$date_time_now =  new DateTime('now');
				$interval = date_diff($date_time_db, $date_time_now);
				
				if ( $interval->format('%a') != 0)  // C'est à dire plus  de 24 h, on incremente le nombre de vues de l'item  de +1
				{
					$data = array( // On actualise la date de vue
					'date_creation' => date("Y-m-d H:i:s")
						);

					$this->db->where('id_membre',  $id_membre);
					$this->db->where('id_content',  $id_item);	
					$this->db->limit(1);
					$this->db->update($table_hit_count, $data); 

				//	$this->db->where('id', $id_item);
				//	$this->db->update('contents', array('views' =>))

					if (is_numeric($id_item)) 
					{
						$this->db->where('id', $id_item);
						$this->db->set('views', 'views+1', FALSE);
						$this->db->update('contents');
					}
				}
			}
		}
		else   // la personne n'a jamais vu l'item, on insert une nouvelle ligne et on incremente le nombre de vues de l'item
		{
			$data = array(
				'id_content' => $id_item,
				'id_membre' => $id_membre,
				'date_creation' => date("Y-m-d H:i:s")
			);

			$this->db->insert($table_hit_count, $data); 
			
			if (is_numeric($id_item)) 
			{
				$this->db->where('id', $id_item);
				$this->db->set('views', 'views+1', FALSE);
				$this->db->update('contents');
			}
		}
		
	}
	

	












}