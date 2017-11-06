<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tester extends CI_Controller {
  public function __construct()
    {
        // $this->load does not exist until after you call this
        parent::__construct(); // Construct CI's core so that you can use it

    }
	public function index()
	{
	//	$this->redisObj->save('foo', 'bar', 10);
	//	echo 'zeze '. $this->redisObj->get('foo');



		$client = new Predis\Client();
		$client->set('foo', 'bar');

		//$cmdGetReply = $client->rawCommand('keys id_content_1414??');
		//var_dump($cmdGetReply);
		$cmdGet = $client->createCommand('keys');
		$cmdGet->setArgumentsArray(array('id_content_1414??'));
		$cmdGetReply = $client->executeCommand($cmdGet);
		var_dump($cmdGetReply);

		die();

		$test = array('coco' => 'lol', 'bigno' => 'zbombo', 'ze' => 232323232);
		$client->set('my_array', json_encode($test));
		$value = $client->get('foo');
		$value2 = $client->get('test');
		echo "value = $value";
		echo "value = $value2";
		print_r(json_decode($client->get('my_array')));
		$client->del('foo');
		$value = $client->get('foo');
		echo "value = $value"; 	$client->set('foo', 'bar');
		//return 'lol';
		if ($client->get('foo') != NULL)
			echo 'non null';
		else
			echo 'est null';

	}


}
