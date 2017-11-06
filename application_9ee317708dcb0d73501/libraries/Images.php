<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Images {

	protected $path_images;
	protected $CI;
	
	
	function __construct()
	{
		$this->CI = & get_instance();
	}


	function get_path_images($path_item)
	{
		if (file_exists($path_item))   // On vérifie que le dossier existe dans le cas ou la personne n'a pas mis d'images.
		{
			$files = scandir($path_item);
			$files = array_diff($files, array('.', '..'));
			
			$path_images = '';
			$u = 0;
			
			foreach ($files as $file)
			{
				if ($u == count($files) - 1 ) // c'est à dire le dernier élément, afin de ne pas ajouter une virgule.
					$path_images .= $file;
				else
					$path_images .= $file . ',';
					
				$u++;	
			}
			
			return $path_images;
		}
		else
			return '';
	}

	/* Cela concerne l'image de présentation des items 
	 * On supprime toute les images du dossier "facebook" et on ne laisse que l'image de présentation
	 * @param : string , string
	 */
	
	function create_image_fb($url_image_presentation, $path)
	{
		$path_images_fb   = $path  . 'facebook/';
		
		$path_to_avoid = $path_images_fb . $url_image_presentation;

		
		$fb_images =  glob($path_images_fb . '*');

		if ($fb_images != false)
		{
			foreach($fb_images as $fb_image)
			{
				if (is_file($fb_image) && file_exists($fb_image) && $fb_image !== $path_to_avoid)
				{	
					//echo 'IMAGE SUPP  ' . $fb_image. ' A EVIT ' . $url_image_presentation;
					unlink($fb_image);
				}
			}
		}
	}
	
	function delete_upload_images($path)
	{

		$files0 = glob('images'.  $path  .'*');  // la racine
		$files1 = glob('images'.  $path  .'thumbnail/*');   // thumbnail
		$files2 = glob('images'.  $path  .'medium/*');   // medium
		$files3 = glob('images'.  $path  .'facebook/*');   // facebook

		if ($files0 != false)
		{
			foreach($files0 as $file){ // iterate files
				if(is_file($file))
				unlink($file); // delete file
			}
			foreach($files1 as $file){ // iterate files
				if(is_file($file))
				unlink($file); // delete file
			}
			foreach($files2 as $file){ // iterate files
				if(is_file($file))
				unlink($file); // delete file
			}
			
			foreach($files3 as $file){ // iterate files
				if(is_file($file))
				unlink($file); // delete file
			}
			
			$path_thumbnail = 'images'  . $path . 'thumbnail/';
			$path_medium = 'images' . $path .  'medium/';
			$path_facebook = 'images' . $path .  'facebook/';
			$path_main = 'images' . $path;
			
			if (is_dir($path_thumbnail))
				rmdir($path_thumbnail);
			if (is_dir($path_medium))
				rmdir($path_medium);
			if (is_dir($path_facebook))
				rmdir($path_facebook);
			if (is_dir($path_main))
				rmdir($path_main);	
		}

		
		
	}
	
	function delete_current_avatar($path)
	{
		$path_avatar =  'images' . $path;
		
		if (file_exists($path_avatar)) // On vérifie si le dossier d'avatar de l'utilisateur existe
		{
			$files = glob($path_avatar . '*');  // la racine
			foreach($files as $file){ // iterate files
			if(is_file($file))
				unlink($file); // delete file
			}
			
			$files1 = glob($path_avatar . 'thumbnail/*');  // thumbnail
			foreach($files1 as $file){ // iterate files
			if(is_file($file))
				unlink($file); // delete file
			}
		}
		else // sinon on en crée un 
		{
			mkdir( $path_avatar  , 0755, true);
		}
			
	}

	function delete_current_background($path)
	{
		$path_background =  'images' . $path;
		
		if (file_exists($path_background)) // On vérifie si le dossier d'avatar de l'utilisateur existe
		{
			$files1 = glob($path_background . '*');  // racine
			foreach($files1 as $file){ // iterate files
			if(is_file($file))
				unlink($file); // delete file
			}
		}
		else // sinon on en crée un 
		{
			mkdir($path_background  , 0755, true);
		}
			
	}
	
	function delete_current_publish($path)
	{
		
		$files0 = glob('images'.  $path  .'*');  // la racine
		$files1 = glob('images'.  $path  .'thumbnail/*');   // thumbnail
		$files2 = glob('images'.  $path  .'medium/*');   // medium
		$files3 = glob('images'.  $path  .'facebook/*');   // facebook

		
		foreach($files0 as $file){ // iterate files
		if(is_file($file))
		unlink($file); // delete file
		}
		foreach($files1 as $file){ // iterate files
		if(is_file($file))
		unlink($file); // delete file
		}
		foreach($files2 as $file){ // iterate files
		if(is_file($file))
		unlink($file); // delete file
		}
		
		foreach($files3 as $file){ // iterate files
		if(is_file($file))
		unlink($file); // delete file
		}
		
	}
	
	/*  Cette fonction sert à prendre une image par défaut dans le cas où la personne ne choisit pas
	 *  d'image de présentation
	 *  @param: string
	 *	@return: string
	 */

	function get_first_image($path)
	{
		if (is_dir($path))  // On vérifie d'abord que le dossier existe 
		{
			$files = scandir($path);
			$files = array_diff($files, array('.', '..'));

			if (count($files) == 0)
				return 'none';
			else
				return $files[2];  // [0] et [1] c'est '.' et '..'
		}
		else
			return 'none';
	}

	/*
	 * Sert à obtenir tout les chemins des images
	 * @param : string
	 */
	function get_all_images($path)
	{
		if (is_dir($path))  // On vérifie d'abord que le dossier existe 
		{
			$files = scandir($path);
			$files = array_diff($files, array('.', '..'));
		
			return $files;  
		}
	}

	/*
	 * Pour obtenir l'image de présentation d'un post
	 */
	function get_image_presentation($id_encoded, $url_presentation, $subject) 
	{
		$path_file = 'images/posts/'. $id_encoded . '/medium/'
			 . $url_presentation;	

	 	if (file_exists($path_file))
	 	{
	 		return HOME_URL . 'images/posts/'. $id_encoded . '/medium/'
			 . $url_presentation;
	 	}
	 	else 
	 	{
	 		switch ($subject) 
			{
				case 'Italien':

				    return HOME_URL . 'images/categories_matieres/italien.png';

				case 'Sciences':

				    return HOME_URL . 'images/categories_matieres/sciences.png';

				case 'Physique':

					return HOME_URL . 'images/categories_matieres/physique.png';

				case 'Maths':

					return HOME_URL . 'images/categories_matieres/maths.png';

				case 'Arabe':

					return HOME_URL . 'images/categories_matieres/arabe.png';

				case 'Français':

					return HOME_URL . 'images/categories_matieres/francais.png';

				case 'Anglais':

					return HOME_URL . 'images/categories_matieres/anglais.png';

				case 'Espagnol':

					return HOME_URL . 'images/categories_matieres/espagnol.png';

				case 'Allemand':

					return HOME_URL . 'images/categories_matieres/allemand.png';

				case 'Histoire':

					return HOME_URL . 'images/categories_matieres/histoire.png';

				case 'Géographie':

					return HOME_URL . 'images/categories_matieres/geographie.png';

				case 'Philo':

					return HOME_URL . 'images/categories_matieres/philo.png';

				case 'Sciences Islamique':

					return HOME_URL . 'images/categories_matieres/sciences_islamique.png';

				case 'Génie électrique':

					return HOME_URL . 'images/categories_matieres/genie_electrique.png';
				
				default:
					
					return HOME_URL . 'images/splash2.png';
			}

	 	}


				

	}



}