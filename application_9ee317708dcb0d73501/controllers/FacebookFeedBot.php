<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FacebookFeedBot extends CI_Controller {

	private $app_secret = '4fff0529d0cfd4bb94f1f84217ed310a';

	private $accessToken = 'EAAK69XqW5rABAHFguagAMvyZCOPkkkdhhmZAUdGZALcskW9IoaGYJeCVVXyuk6rBO8VritzMC1fhg24Koyejdt2Na2U3QbNqmmXsHksaiU1wguQe1x7K3nLBkkKsncz1UAMYAO9WZBBrMVigYQmIwrdRdbIP7KIE8yNNLJugugZDZD';

	private $fb;

	function __construct()
	{
		parent::__construct();

//		die('stop');

		$this->fb = new Facebook\Facebook([
		  'app_id' => '768513439884976',
		  'app_secret' => $this->app_secret,
		  'default_graph_version' => 'v2.5'
		]);

		$this->fb->setDefaultAccessToken($this->accessToken);

		  /**
     * Nous donne le meilleur post de la journée
     *
     */
		$this->db->select('contents.id, title, type, audio, contents.url, content, subject, likes, views, date_creation');
		$this->db->from('contents');
		$this->db->where("content_delete", 0);
		$this->db->where("(type='Résumé' OR type='Sujet')");
	//	$this->db->where('subject !=', 'Autres');
		$this->db->where('contents.date_creation > DATE_SUB(NOW(), INTERVAL 1 DAY)');
		$this->db->order_by("likes, views", "desc"); 

		$this->db->limit(10);
		
        $query = $this->db->get();

        //echo $this->db->last_query();

        $response['posts'] = array();
        $posts = array();

		foreach($query->result_array() as $row)
		{
			$posts['id'] = $this->obfuscate_id->id_encode($row['id']);

			$posts['title'] = $row['title'];

			$posts['type'] = $row['type'];

			$posts['subject'] = $row['subject'];

			$posts['date_creation'] = $row['date_creation'];

			$posts['content'] = $row['content'];

			$posts["likes"] = $row["likes"];

			$posts["views"] = $row["views"];

			if(empty($row['audio']))
				$posts['audio'] = null;
			else
				$posts['audio'] = $row['audio'];

			if (!empty($row['url']))
				$posts['url'] = preg_split("/[,]+/",  $row['url']); 
			else
				$posts['url'] = array();

			if (count($posts['url']) > 0) {
				// On crée le lien des imagess 
				for( $i = 0; $i < count($posts['url']); $i++) {
					# code...
					$posts['url'][$i] = url_images() . $posts['id'] . "/" . $posts['url'][$i];
				}
			} 
			else
				$posts['url'] = array();

			if ($posts['audio'] == null && count($posts['url']) > 0) 
			{
				$params = array();

				$batchArray = [
				    'photo-one' => $this->fb->request('POST', '/me/feed', [
			      	'message' => $posts['title'] . "\n\n" . $posts['content'] . "\n\n" .
			      	"للمزيد من الملخصات و الدروس و التمارين حمل تطبيق DzBac
https://goo.gl/9Ajh6i",
			    	])  


		/*		$batchArray = [
				    'photo-one' => $this->fb->request('POST', '/me/feed', [
			      	'message' => $posts['title'] . "\n\n" . $posts['content'] . "\n\n" .
			      	"Découvre les meilleurs cours pour le BAC : https://goo.gl/CDMsXw",
			    	])*/
				];  

				for( $i = 0; $i < count($posts['url']); $i++)
				{

					$batch = [
					  'photo-one' => $this->fb->request('POST', '/me/photos', [
					      'published' =>false,
					      'source' => $this->fb->fileToUpload($posts['url'][$i]),
					    ]) 
					];

					try {
					  $responses = $this->fb->sendBatchRequest($batch);
					  
					} catch(Facebook\Exceptions\FacebookResponseException $e) {
					  // When Graph returns an error
					  echo 'Graph returned an error: ' . $e->getMessage();
					  exit;
					} catch(Facebook\Exceptions\FacebookSDKException $e) {
					  // When validation fails or other local issues
					  echo 'Facebook SDK returned an error: ' . $e->getMessage();
					  exit;
					}

						foreach ($responses as $key => $response_) {
							  if ($response_->isError()) {
							    $e = $response_->getThrownException();
							    echo '<p>Error! Facebook SDK Said: ' . $e->getMessage() . "\n\n";
							    echo '<p>Graph Said: ' . "\n\n";
							    var_dump($e->getResponse());
							  } else {
							    echo "<p>(" . $key . ") HTTP status code: " . $response_->getHttpStatusCode() . "<br />\n";

							    $id = json_decode($response_->getBody(), true)['id'];
							   // var_dump($batchArray['photo-one']);
							    //print_r($batchArray['photo-one']);
							  // $batchArray['photo-one'] = $response->getGraphObject()->asArray();
							      echo "Mon nom est " , get_class($batchArray['photo-one']) , "\n";
							    $class_methods = get_class_methods($batchArray['photo-one']);

							foreach ($class_methods as $method_name) {
							    echo "$method_name\n";
							}

								$params['attached_media['. $i .']'] = '{"media_fbid":"'.$id .'"}';
							
							  }
					}

				} // end for

					$batchArray['photo-one']->setParams($params);

					print_r($batchArray);

					try {
					  $responses = $this->fb->sendBatchRequest($batchArray);
					  
					} catch(Facebook\Exceptions\FacebookResponseException $e) {
					  // When Graph returns an error
					  echo 'Graph returned an error: ' . $e->getMessage();
					  exit;
					} catch(Facebook\Exceptions\FacebookSDKException $e) {
					  // When validation fails or other local issues
					  echo 'Facebook SDK returned an error: ' . $e->getMessage();
					  exit;
					}

						foreach ($responses as $key => $response_) {
						  if ($response_->isError()) {
						    $e = $response_->getThrownException();
						    echo '<p>Error! Facebook SDK Said: ' . $e->getMessage() . "\n\n";
						    echo '<p>Graph Said: ' . "\n\n";
						    var_dump($e->getResponse());
						  } else {
						    echo "<p>(" . $key . ") HTTP status code: " . $response_->getHttpStatusCode() . "<br />\n";
						    echo "Response: " . json_decode($response_->getBody(), true)['id'] . "</p>\n\n";
						    echo "<hr />\n\n";
						  }
					}

			} // end if

		//	break;

		} // end foreach

	//	 $gcm_ids = array("APA91bEGXjGNXYmOUGhm0JOtjH6w1ZVFIQ-6aAr6swfQ3jjQ2sf_llsLKvcuJon_l48dcyFP7oRoRxq2Sg-UDVhP2P0y71tgsGteS-7cfkRk7InAoXy2oK5Fm18yFbdIkBd9dQ_KPMeA");
		//print_r($response);

		
	}


	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		//$this->load->view('welcome_message');
	}



}
