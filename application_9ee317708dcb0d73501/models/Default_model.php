<?php 

class Default_model extends CI_Model {

function __construct()
{
    // Call the Model constructor
    parent::__construct();
    $this->db->query("SET time_zone='+1:00'");
}


}