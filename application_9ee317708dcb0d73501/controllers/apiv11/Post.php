<?php defined('BASEPATH') OR exit('No direct script access allowed');


require_once APPPATH.'/libraries/REST_Controller.php';

class Post extends REST_Controller {

	function __construct()
    {
        parent::__construct();
        $this->load->model("content_model7");


        if (CHECK_REQUEST) {
            $this->check_key->check();
        }
    }

    public function init_put()
    {
        $id_membre = $this->utils->get_user_id();

    	$id_encoded = $this->content_model7->initialise(
			 $this->utils->get_user_id()
    	);

    	$this->response(array('id' => $id_encoded), 200);
    }

    public function delete_post()
    {
    	$this->content_model7->delete(
    		 $this->obfuscate_id->id_decode($this->post('id_content'))
    	);

    	$this->response(array('message' => $this->lang->line("post_supprime")), 200);
    }

    public function insert_post()
    {
        $this->load->model('upload_model');

    	$data = json_decode($this->post('data'), true);

    	$this->content_model7->insert(
    		 $this->obfuscate_id->id_decode($this->post('id_content')),
    		 $data,
             $this->post('is_modification')
    	);

      /*  $path_images = 'images/posts/'. $this->post('id_content') . '/';
        $path = $path_images . 'thumbnail';
        $url_image_presentation = $data['url_presentation'];

        $this->upload_model->update_info_images(
            $url_image_presentation,
            $this->obfuscate_id->id_decode($this->post('id_content')) ,
            $this->images->get_path_images($path)
        );*/

        $this->load->model("notification_model");

        // On ajoute l'utilisateur qui poste l'item dans son propre groupe
        $this->notification_model->add_to_group(
         $this->utils->get_user_id() ,
         $this->obfuscate_id->id_decode($this->post('id_content')),
         'contents');

    	$this->response(array('message' => $this->lang->line("post_ajouter")), 200);
    }


    /**
     *  
     *
     */
    public function test_insert_get() {

        $this->db->select('id, secteur');
        $this->db->from('contents');
        $this->db->where("content_delete", 0);
        
        $query = $this->db->get();

        $data_tags = array();

        foreach ($query->result_array() as $row)
        {
        //  echo $row['secteur'];
          $id_content = $row['id'];

           switch ($row['secteur']) {

                case 'all':

                    $tags_id = array(1, 2, 3, 4, 5);

                        // On récupère l'id des tags
                    foreach ($tags_id as $tagId) {
                        # code...

                        $tag_content = array(
                            'id_content' => $id_content,
                            'id_tag' => $tagId
                        );

                        array_push($data_tags, $tag_content);
                    }
                    # code...
                    break;

                case 'sc':

                    $tag_content = array(
                    'id_content' => $id_content,
                    'id_tag' => 1
                    );

                    array_push($data_tags, $tag_content);

                    break;

                case 'mat':

                    $tag_content = array(
                    'id_content' => $id_content,
                    'id_tag' => 2
                );

                    array_push($data_tags, $tag_content);
                # code...
                    break;
                    
                case 'matech':

                    $tag_content = array(
                        'id_content' => $id_content,
                        'id_tag' => 3
                    );

                    array_push($data_tags, $tag_content);
                # code...
                    break;
                    
                case 'let':

                    $tag_content = array(
                        'id_content' => $id_content,
                        'id_tag' => 4
                    );

                    array_push($data_tags, $tag_content);
                # code...
                    break;
                    
                case 'ges':

                    $tag_content = array(
                        'id_content' => $id_content,
                        'id_tag' => 5
                    );

                    array_push($data_tags, $tag_content);
                # code...
                    break;              
                
                default:
                    # code...
                    break;
            }
        }

        $this->db->insert_batch('tags_contents', $data_tags); 

        die('Ajout terminé !');
    }

    public function test_switch_get() {

            $id_content = 85;
            $data['secteur'] = $this->get('sec');
            $data_tags = array();

            switch ($data['secteur']) {

            case 'all':

                $tags_id = array(1, 2, 3, 4, 5);

                    // On récupère l'id des tags
                foreach ($tags_id as $tagId) {
                    # code...

                    $tag_content = array(
                        'id_content' => $id_content,
                        'id_tag' => $tagId
                    );

                    array_push($data_tags, $tag_content);
                }
                # code...
                break;

            case 'sc':

                $tag_content = array(
                'id_content' => $id_content,
                'id_tag' => 1
                );

                array_push($data_tags, $tag_content);

                break;

            case 'mat':

                $tag_content = array(
                'id_content' => $id_content,
                'id_tag' => 2
            );

                array_push($data_tags, $tag_content);
            # code...
                break;
                
            case 'matech':

                $tag_content = array(
                    'id_content' => $id_content,
                    'id_tag' => 3
                );

                array_push($data_tags, $tag_content);
            # code...
                break;
                
            case 'let':

                $tag_content = array(
                    'id_content' => $id_content,
                    'id_tag' => 4
                );

                array_push($data_tags, $tag_content);
            # code...
                break;
                
            case 'ges':

                $tag_content = array(
                    'id_content' => $id_content,
                    'id_tag' => 5
                );

                array_push($data_tags, $tag_content);
            # code...
                break;              
            
            default:
                # code...
                break;
        }

        print_r($data_tags);
        die();
    }

    public function test_get(){
$servername = "localhost";
$username = "kaddouri";
$password = "5ehu8y2yz";

// Create connection
$conn = new mysqli($servername, $username, $password, "packer_bac");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
echo "Connected successfully";

$conn->query('SET NAMES utf8mb4');
echo $conn->character_set_name();
if (!$conn->set_charset("utf8mb4")) {
    printf("Erreur lors du chargement du jeu de caractères utf8mb4 : %s\n", $conn->error);
} else {
    printf("Jeu de caractères courant : %s\n", $conn->character_set_name());
}

 var_dump($conn->get_charset());
die();
$result = $conn->query("SELECT message FROM content_comments WHERE id =250204");
while ($row = $result->fetch_assoc()) {
    echo " id = " . $row['message'] . "\n";
}

print_r($result); die('erer');


      //  $this->db->query('SET NAMES ut8mb4');
        $id_content = 85;
        $data_tags = array();
        $tags_id = array("4" , "2", "5");
       foreach ($tags_id as $tagId) {
            # code...

            $tag_content = array(
                'id_content' => $id_content,
                'id_tag' => $tagId
            );

            print_r($tag_content);
            array_push($data_tags, $tag_content);
        }
        die();
    }

    public function update_post()
    {
    	$data = json_decode($this->post('data') , true);

    	$this->content_model7->update(
    		 $this->obfuscate_id->id_decode($this->post('id_content')),
    		 $data
    	);
        
    	$this->response(array('message' => $this->lang->line("Post modifié !")), 200);
    }

    /*
     * Obtenir un post particulier
     *
     */
    public function content_get()
    {

        $id_content = $this->obfuscate_id->id_decode($this->get('id_content'));
        
		$result = $this->content_model7->get_content(
			$this->utils->get_user_id(),
			$id_content
		);

        $this->load->model('hit_counter_model');

        $this->hit_counter_model->hit_counter(
            $this->utils->get_user_id(),
            $id_content
        );

		$this->response($result, 200);
    }

    /*
     * Obtenir la liste des posts
     * On peut même obtenir la list des posts d'un utilisateur en particulier
     * et la liste des posts qu'il a aimé
     */
	public function list_get()
	{
		$offset = ($this->get('page') * 25 ) - 25;
        $id_membre = $this->obfuscate_id->id_decode($this->get('key'));

        // Pour savoir si on cherche au niveau des likes ou pas
        if ($this->get('is_likes'))
        {
            $result = $this->content_model7->get_contents_likes(
                $id_membre,
                $offset
             );
        }
        else 
        {
            $tags_id = json_decode($this->get('tags') , true);

            if (count($tags_id) == 0)
                $tags_id = null;

            $result = $this->content_model7->get_contents(
                $id_membre,
                $offset,
                $this->get('type'),
                $this->get('subject'),
                $this->get('order_by_views'),
                $this->get('order_by_likes'),
                $this->get('mot_cle'),
                $this->get('format'),
                $tags_id,
                $this->get('is_me')
            );
        }

		$this->response($result, 200);

	}

}