<?php

class Signalisation_model extends CI_Model {


	function signale($id_membre, $id_content)
	{
		$data = array(
	 		'id_membre' => $id_membre,
	 		'id_content' => $id_content,
			'date_creation' => date("Y-m-d H:i:s")
		);

		$this->db->insert('signalements', $data);
	}

	function delete_signalement($id_content)
	{
		$this->db->where('id_content', $id_content);

		$data = array(
			'is_deleted' => 1,
			'date_deleted' => date("Y-m-d H:i:s")
		);

	 	$this->db->update('signalements', $data);
	}

	function get_posts_signaled($offset)
	{

		$this->db->select('contents.id, contents.id_membre AS id_membre, title, subject, type, likes, views, comments, url_video,
		 contents.url_presentation');
		$this->db->from('contents');
		$this->db->join('signalements', 'signalements.id_content = contents.id');
        $this->db->where("content_delete", 0);
        $this->db->where("signalements.is_deleted", 0);
	//    $this->db->order_by("contents.date_creation", "desc"); 	
	    $this->db->distinct();
        
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

			array_push($response["posts"], $posts);
			
		} // end foreach

		return $response;
	}



}
