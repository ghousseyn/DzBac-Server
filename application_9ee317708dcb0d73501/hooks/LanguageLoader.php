<?php
class LanguageLoader
{

    function initialize()
    {
        $ci =& get_instance();
        $ci->load->helper('language');

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
        {

        	 $class_loaded = $ci->router->fetch_class();
		     $language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);


		     // $ci->lang->load(Controlleur, langue);
		     $ci->lang->load($class_loaded, 'fr'/*$language*/);
        }
       
    }
}