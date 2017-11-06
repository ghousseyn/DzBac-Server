<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Example
 *
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array.
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 * @author		Phil Sturgeon
 * @link		http://philsturgeon.co.uk/code/
*/

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';

class Example extends REST_Controller
{
	function __construct()
    {
        // Construct our parent class
        parent::__construct();

        $this->load->model('notification_model');

        var_dump($this->utils->get_user_id());
        var_dump($this->notification_model->get_state_in_group(
            $this->utils->get_user_id(),
            $this->obfuscate_id->id_decode($this->get('id_content')) ,
            'contents'
        ));

        die( $this->notification_model->get_state_in_group(
            $this->utils->get_user_id(),
            $this->obfuscate_id->id_decode($this->get('id_content')) ,
            'contents'
        ));

     //  die($this->utils->get_user_id() + "");


     /*   if ($this->input->server("HTTP_USER_ID")) {
            echo 'not null ' . $this->utils->get_user_id();
        }
        else {
            echo $this->input->server('REQUEST_METHOD') .' null ' . $this->utils->get_user_id();
        }

        die();
         die( $this->input->server("HTTP_USER_ID") . "method "  . $this->input->server('REQUEST_METHOD'));
       // $this->check_key->check();*/
    }

   /* function user_delete()
    {
        die("key " . $this->delete('key'));
    }*/



    function user_get()
    {

$test = $this->obfuscate_id->id_encode_new(35, KEY_CONVERSATION);

        $value = $this->obfuscate_id->id_encode_new(35, KEY_COMMENT);

        $decoed = $this->obfuscate_id->id_decode_new($value, KEY_COMMENT);



print_r($decoed);
        if (is_array($decoed)) {
            die("tab" );
        }else {
            die("not tab");
        }
        
        $this->response(array(  strtotime('31 january 2008').  str_replace('index.php/', '', current_url()).  " HASH_CONVERSATION " .uri_string() => $this->obfuscate_id->id_decode_new($test, KEY_CONVERSATION),
                              "HASH_COMMENT" => $this->obfuscate_id->id_encode_new(35, KEY_COMMENT)), 200); 
       /* var_dump($hashids);
        var_dump(current_url());
        var_dump(uri_string());
       var_dump($this->input->server("HTTP_API_KEY")); //_X_API_KEY
       var_dump($this->input->server("apikey")); //_X_API_KEY
       var_dump($this->input->server("http-api-key")); //_X_API_KEY
       var_dump($this->input->server("http_api_key")); //_X_API_KEY*/
      // var_dump($_SERVER);

        if(!$this->get('id'))
        {
        	$this->response(NULL, 400);
        }

        // $user = $this->some_model->getSomething( $this->get('id') );
    	$users = array(
			1 => array('id' => 1, 'name' => 'Some Guy' . $this->input->server("PI-KEY"), 'email' => 'example1@example.com', 'fact' => 'Loves swimming'),
			2 => array('id' => 2, 'name' => 'Person Face', 'email' => 'example2@example.com', 'fact' => 'Has a huge face'),
			3 => array('id' => 3, 'name' => 'Scotty', 'email' => 'example3@example.com', 'fact' => 'Is a Scott!', array('hobbies' => array('fartings', 'bikes'))),
		);
		
    	$user = @$users[$this->get('id')];
    	
        if($user)
        {
            $this->response($users, 200); // 200 being the HTTP response code
        }

        else
        {
            $this->response(array('error' => 'User could not be found'), 404);
        }
    }
    
    function user_post()
    {

        //$this->some_model->updateUser( $this->get('id') );
        $message = array('id' => $this->utils->get_user_id(), 'name' => $this->post('name'), 'email' => $this->post('email'), 'message' => 'ADDED!');
        
        $this->response($message, 200); // 200 being the HTTP response code
    }
    
    function user_delete()
    {
    	//$this->some_model->deletesomething( $this->get('id') );
        $message = array('id' => $this->get('id'), 'message' => 'DELETED!' , 'key' => $this->delete('key'));
        
        $this->response($message, 200); // 200 being the HTTP response code
    }
    
    function users_get()
    {
        //$users = $this->some_model->getSomething( $this->get('limit') );
        $users = array(
			array('id' => 1, 'name' => 'Some Guy', 'email' => 'example1@example.com'),
			array('id' => 2, 'name' => 'Person Face', 'email' => 'example2@example.com'),
			3 => array('id' => 3, 'name' => 'Scotty', 'email' => 'example3@example.com', 'fact' => array('hobbies' => array('fartings', 'bikes'))),
		);
        
        if($users)
        {
            $this->response($users, 200); // 200 being the HTTP response code
        }

        else
        {
            $this->response(array('error' => 'Couldn\'t find any users!'), 404);
        }
    }


	public function send_post()
	{
		var_dump($this->request->body);
	}


	public function send_put()
	{
		var_dump($this->put('foo'));
	}
}