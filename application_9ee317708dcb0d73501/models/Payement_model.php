<?php

class Payement_model extends CI_Model {

 	/*
 	 *  L'utilisateur peut utiliser les features prenium gratuitement que 
 	 *  pendant une semaine
 	 */
	const LIMIT_DAY = 7;


	const ERROR_MULTIPLE_DEVICE = 9999; 


	/* 
	 * On ajoute l'utilisateur dans 'Payements' dans le cas ou il n'existe pas
	 * Si il existe on compare l'android_id avec celui reçu si ils ne sont pas les
	 *  meme on gènere une erreur 
	 */
	public function add_user($id_membre, $android_id)
	{
		$res = array();

		$this->db->select('android_id, date_creation, date_update, type');
		$this->db->where('id_membre', $id_membre);
		$this->db->from('payements');
		$this->db->limit(1);
		$query = $this->db->get();

		 // L'utilisateur n'est pas enregistre
		 if ($query->num_rows() == 0) {

			$data = array(
					'type' => 0, // Par défaut il n'a pas d'abonnement
			 		'id_membre' => $id_membre,
			 		'android_id' => $android_id,
					'date_creation' => date("Y-m-d H:i:s")
				);


			$this->db->insert('payements', $data);

			$message = 'Periode de test';
			$response = 1;
		 }
		 else  
		 {
		 	$user = $query->row();

		 	$dateNow = DateTime::createFromFormat('Y-m-d H:i:s', /*'2017-08-27 14:13:34'*/date("Y-m-d H:i:s"));
		 	$dateUpdate = DateTime::createFromFormat('Y-m-d H:i:s', $user->date_update);
		

			// On vérifie si l'utilisateur a dépassé sa période de test
		   if ($user->type == 0) 
		   {
		   		$dateCreation = DateTime::createFromFormat('Y-m-d H:i:s', $user->date_creation);

			 	$dDiff = $dateCreation->diff($dateNow);
			 	$diffInDays = (int) $dDiff->format("%r%a");

		   	    // La personne a dépassé la prériode de test
			   if ($diffInDays >= self::LIMIT_DAY)  {
			   		$message = 'Periode de test fini';
			   		$response = 0;
			   }
			   else {
			   		$message = 'Periode de test';
			   		$response = 4;
			   }
		   }
		   // L'utilisateur est abandonné mais on vérifie qu'il est toujours valide
		   else 
		   {
		   		$diff = $dateUpdate->diff($dateNow);

		   		$diffInMonths = (int) ($diff->format('%y') * 12) + $diff->format('%m');


			   	 // On vérifie si l'utilisateur est prenium
				switch ($user->type) {

			    case 1: // l'utilisateur a un abonnement d'un mois

			    	if ($diffInMonths >= 1) {
			    		$response = 0;
			    		$message = "Votre abanonnement d'un mois s'est expriré";
			    	}
			    	else {
			    		$response = 1;
			    		$message = "Vous avez un abo de 1 mois";
			    	}

			        break;

			    case 2: // l'utilisateur a un abonnement de 6 mois

			    	if ($diffInMonths >= 6) {
			    		$response = 0;
			    		$message = "Votre abanonnement de 6 mois s'est expriré";
			    	}
			    	else {
			    		$response = 2;
			    		$message = "Vous avez un abo de 6 mois";
			    	}

			        break;

		        case 3: // l'utilisateur a un abonnement d'une année
		       		 
			    	if ($diffInMonths >= 12) {
			    		$response = 0;
			    		$message = "Votre abanonnement d'un ans s'est expriré";
			    	}
			    	else {
			    		$response = 3;
			    		$message = "Vous ave un abo de 1 ans";
			    	}

		       		 break;


 				case 5: // l'utilisateur a un abonnement de trois mois *simple*
		       		 
			    	if ($diffInMonths >= 3) {
			    		$response = 0;
			    		$message = "Votre abanonnement de 3 mois s'est expriré";
			    	}
			    	else {
			    		$response = 5;
			    		$message = "Vous ave un abo de 3 mois";
			    	}

		       		 break;

		       	case 6: // l'utilisateur a un abonnement de trois mois *spéciale*
		       		 
			    	if ($diffInMonths >= 3) {
			    		$response = 0;
			    		$message = "Votre abanonnement de spécial 3 mois s'est expriré";
			    	}
			    	else {
			    		$response = 6;
			    		$message = "Vous ave un abo spécial de 3 mois";
			    	}

		       		 break;	 	


			    default:
			        $message = 'Error default';
				}
		   }

		   // La personne a utilisé le meme compte avec un autre device
		/*   if ($user->android_id != $android_id) {
		   		$response = self::ERROR_MULTIPLE_DEVICE;
		   		$res['error'] = 'Attention double devices detected';
		   }*/
		   


		 } // end else

		
		 $res['code'] = $response;
		// $res['message'] = $message;

		// var_dump($res);

		 return $res['code'];

	}


	public function update($id_membre, $type)
	{
		// On change le type de contribution
		$this->db->where('id', $id_membre);

		$data = array(
			'level_contribution' => (integer) $type
		);

		$this->db->update('membres', $data);
		

		// On change le type d'abonnement
		$this->db->where('id_membre', $id_membre);

		$data = array(
				'type' => $type,
				'date_update' => date("Y-m-d H:i:s")
		);

		return $this->db->update('payements', $data);
	}

/*

	public function update($id_membre, $type)
	{
		$this->db->where('id_membre', $id_membre);

		$data = array(
				'type' => $type,
				'date_update' => date("Y-m-d H:i:s")
		);

		return $this->db->update('payements', $data);
	}
 */
	

	
}