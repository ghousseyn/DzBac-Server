<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Obfuscate_id {


		public function id_encode($id) 
		{
			return base_convert((9999999999 - (float) $id) , 12 , 32 );
		}

		public function id_encode_new($id, $key_hash)
		{ 
			$hashids = new Hashids\Hashids($key_hash, HASH_LENGHT);

			return $hashids->encode($id);
		}


		public function id_decode_new($id, $key_hash) 
		{
			$hashids = new Hashids\Hashids($key_hash, HASH_LENGHT);

			return $hashids->decode($id) [0];
		}

	    public function id_decode($fakeid) 
	    {
	    /*	var_dump($fakeid);
	    	//var_dump(9999999999 - (float) base_convert($fakeid, 32, 12));
	    	if ((string) 9999999999 - (float) base_convert($fakeid, 32, 12) != '10') {
	    		die("maintenance");
	    	} */

			return 9999999999 - (float) base_convert($fakeid, 32, 12);
		}



}