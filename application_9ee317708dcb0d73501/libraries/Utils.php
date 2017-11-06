<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Utils {

	private $CI;
	
	public function __construct()
	{
		$this->CI = & get_instance();
	}

	public function get_user_id()
	{
		$id_membre = $this->CI->input->server("HTTP_X_USER_ID");

		if (!$id_membre)
		{
			$id_membre = $this->CI->input->server("HTTP_USER_ID");
		}

		if ($id_membre)
		{
			if ($this->CI->obfuscate_id->id_decode($id_membre) == 2979)
			{
				die("Bye bye");
			}


			return $this->CI->obfuscate_id->id_decode($id_membre);
		}

		$request_method = $this->CI->input->server('REQUEST_METHOD');

		if ($request_method == 'GET') {
			return $this->CI->obfuscate_id->id_decode($this->CI->get('key'));
		//	$this->CI->response(array('message' => 'GET' .$this->CI->obfuscate_id->id_decode($this->CI->get('key'))), 200);
		}
		else if ($request_method == 'POST') {
			return $this->CI->obfuscate_id->id_decode($this->CI->post('key'));
		//	$this->CI->response(array('message' => 'POST' . $this->CI->obfuscate_id->id_decode($this->CI->post('key'))), 200);
		}
		else if ($request_method == 'PUT') {
			return $this->CI->obfuscate_id->id_decode($this->CI->put('key'));
		//	$this->CI->response(array('message' => 'PUT' . $this->CI->obfuscate_id->id_decode($this->CI->put('key'))), 200);
		}

		return;
	}

	public function get_user_id_encoded()
	{
		$id_membre = $this->CI->input->server("HTTP_X_USER_ID");

		if (!$id_membre)
		{
			$id_membre = $this->CI->input->server("HTTP_USER_ID");
		}

		return $id_membre;

		if ($request_method == 'GET') {
			return $this->CI->get('key');
		//	$this->CI->response(array('message' => 'GET' .$this->CI->obfuscate_id->id_decode($this->CI->get('key'))), 200);
		}
		else if ($request_method == 'POST') {
			return $this->CI->post('key');
		//	$this->CI->response(array('message' => 'POST' . $this->CI->obfuscate_id->id_decode($this->CI->post('key'))), 200);
		}
		else if ($request_method == 'PUT') {
			return $this->CI->put('key');
		//	$this->CI->response(array('message' => 'PUT' . $this->CI->obfuscate_id->id_decode($this->CI->put('key'))), 200);
		}

	}
	
	
	public function get_url_avatar($url, $id_memebre, $type)
	{
		if ($type != 'none')
			$semi_path = '/' . $type . '/';
		else
			$semi_path = '/';

		if ((strpos($url, 'facebook') == '' &&  strpos($url, 'http' ) == '') && 
			strpos($url, 'googleusercontent') == '' )
		{ 
			$url_avatar  =  "images/avatar/" .  $this->CI->obfuscate_id->id_encode($id_memebre) . $semi_path .$url;
			
			if (!file_exists($url_avatar))
				return HOME_URL . 'images/path3929eerer.png';
			else
				return HOME_URL . $url_avatar;
		}	
		else
			return $url;
	}

	public function get_url_background($url, $id_memebre, $type)
	{
		$url_avatar  =  "images/background/" .  $this->CI->obfuscate_id->id_encode($id_memebre) . '/' . $url;
		
		if (!file_exists($url_avatar))
			return HOME_URL . 'images/path3929eerereeeeeeeeeee.png';
		else
			return HOME_URL . $url_avatar;
	}


	public function get_relative_time($date) 
	{
	    // Déduction de la date donnée à la date actuelle
	    $time = time() - strtotime($date); 

	    // Calcule si le temps est passé ou à venir
	    if ($time > 0) {
	        $when = "il y a";
	    } else if ($time < 0) {
	        $when = "dans environ";
	    } else {
	        return "il y a moins d'une seconde";
	    }
	    $time = abs($time); 

	    // Tableau des unités et de leurs valeurs en secondes
	    $times = array( 31104000 =>  'an{s}',       // 12 * 30 * 24 * 60 * 60 secondes
	                    2592000  =>  'mois',        // 30 * 24 * 60 * 60 secondes
	                    86400    =>  'jour{s}',     // 24 * 60 * 60 secondes
	                    3600     =>  'heure{s}',    // 60 * 60 secondes
	                    60       =>  'minute{s}',   // 60 secondes
	                    1        =>  'seconde{s}'); // 1 seconde         

	    foreach ($times as $seconds => $unit) {
	        // Calcule le delta entre le temps et l'unité donnée
	        $delta = round($time / $seconds); 

	        // Si le delta est supérieur à 1
	        if ($delta >= 1) {
	            // L'unité est au singulier ou au pluriel ?
	            if ($delta == 1) {
	                $unit = str_replace('{s}', '', $unit);
	            } else {
	                $unit = str_replace('{s}', 's', $unit);
	            }
	            // Retourne la chaine adéquate
	            return $when." ".$delta." ".$unit;
	        }
	    }
	}

}
	
	