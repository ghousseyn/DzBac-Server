<?php

class Check_Key  {


	public function check($checkLevel = false)
	{
		$timestart=microtime(true);

		$CI = & get_instance();

		$id_membre = $CI->input->server("HTTP_X_USER_ID");
		$sig = $CI->input->server("HTTP_X_SIG");
		$timestamp = $CI->input->server("HTTP_X_TIMESTAMP");

		
		if ($id_membre != NULL && $sig != NULL && $timestamp != NULL)
		{

			$timestamp_server = time();

			// si le temps est supérieur à celui qu'on définis, on arrête le script
			if ( ($timestamp_server - $timestamp) > TIME_OUT_REQUEST ) 
			{
				echo " timestamp : " . $timestamp . "\n";
				echo " timestamp server : " . $timestamp_server . "\n";
				echo " diff: " . ($timestamp_server - $timestamp ). "\n";
				$CI->response(array('error' => "La requête a expiré")  ,400);
			}

			if (!$checkLevel)
				$CI->db->select('api_key');
			else
				$CI->db->select('api_key, level');

			$CI->db->from('membres');
			$CI->db->where('id', $CI->obfuscate_id->id_decode($id_membre));
			$CI->db->limit(1);
			$result = $CI->db->get();


			if ($result->num_rows() > 0)
			{
				$row = $result->row();  

			    $api_key = $row->api_key;

			    if ($checkLevel) {
			   		$level = $row->level;
			    }

			    $url_called =  DIRECTORY_APP . str_replace('index.php/', '', uri_string());

			 //   if (strpos($url_called, 'upload') !== FALSE)
			 //   	$url_called = $url_called . '/';

			   	// c'est à dire l'url appelé + le temps 
				$data = $url_called . $timestamp . $api_key . $id_membre;	

			   $sig_maked = hash_hmac("sha1", $data , $api_key);

				if ($sig_maked != $sig) {

					echo "api-key : " . $api_key . "\n";
					echo "data : " . $data . "\n";
					echo "hash : " . $sig_maked . "\n";
					echo "user-id : " . $id_membre . "\n";
					echo "user-id decoded: " . $CI->obfuscate_id->id_decode($id_membre) . "\n";

					$CI->response(array('error' => "Invalid API Key." . $api_key)  ,400);
									//	'sig' => $sig,
										//'sig_marked' => $sig_maked,
										//'data' => $data), 400);
				}
			}
			else
				$CI->response(array('error' => "Invalid key." ), 400);

		}
		else
			$CI->response(array('error' => "Invalid params."), 400);


		/*		//Fin du code PHP
		$timeend=microtime(true);
		$time=$timeend-$timestart;
		 
		//Afficher le temps d'éxecution
		$page_load_time = number_format($time, 3);
		echo "Debut du script: ".date("H:i:s", $timestart);
		echo "<br>Fin du script: ".date("H:i:s", $timeend);
		echo "<br>Script execute en " . $page_load_time . " sec";*/
				
	}


}