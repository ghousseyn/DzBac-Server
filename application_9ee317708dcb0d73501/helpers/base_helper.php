<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

if (!function_exists('url')) {

    function url_images()
    {
       return HOME_URL . 'images/posts/';
    }

    function url_images_comment()
    {
       return HOME_URL . 'images/commentaires/';
    }

}