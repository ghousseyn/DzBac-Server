<?php
class Upload_model extends CI_Model {


     function Upload_model()
     {
          parent::__construct();  
     }  
     
     public function get_info_upload($id_item_encoded, $type_item)
     {
		$response['posts'] = array();
		
		//$this->db->start_cache();	
		$this->db->select('url_image_presentation');
		$this->db->from($type_item);
		$this->db->where($type_item . '_supprime', '0');
		$this->db->where('id', $this->obfuscate_id->id_decode($id_item_encoded));
		$this->db->limit(1);

		$query = $this->db->get();
		
		foreach($query->result_array() as $row)
		{
			$posts = array();
			
			$posts['image_presentation'] = $row['url_image_presentation'];
			
			array_push($response["posts"], $posts);
		}
	
		return $response;
     
     }

     public function is_mon_item($type_item, $id_membre, $id_item)
     {
     	$this->db->select('id');
     	$this->db->from($type_item);
     	$this->db->where('id', $id_item);
     	$this->db->where('id_membre', $id_membre);
     	$this->db->limit(1);
     	$result = $this->db->get();

     	if ($result->num_rows() > 0){
	       // C'est ok
		 	return true;
	    }
	    else{
	        // On a affaire à un petit malin !
	        return false;
	    }

     //	return $result->result_array();
     }
     
     function update_info_images($url_image_presentation, $id_item, $url_images)
     {
		$data = array(         
				'url' => $url_images,
				'url_presentation' => $url_image_presentation
			);

		$this->db->where('id', $id_item);
		
		return $this->db->update('contents', $data); 
     }

     function update_url_avatar($path_avatar, $id_membre)
     {
		$file_name = scandir($path_avatar);
		$file_name = array_diff($file_name, array('.', '..'));
		
		$data = array(         
					'url_avatar' => $file_name[2]    // [0] c'est "." et [1] c'est ".."
				);

		$this->db->where('id', $id_membre);
		$this->db->update('membres', $data); 

		
		return $file_name[2];
		
     }

     function update_url_background($path_background, $id_membre)
     {
		$file_name = scandir($path_background);
		$file_name = array_diff($file_name, array('.', '..'));
		
		$data = array(         
					'url_background' => $file_name[2]    // [0] c'est "." et [1] c'est ".."
				);

		$this->db->where('id', $id_membre );
		$this->db->update('membres', $data); 

		return $file_name[2];
		
     }
     
     
     
     
     
}