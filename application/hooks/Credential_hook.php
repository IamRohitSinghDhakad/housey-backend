<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 
 * Used when we want to load env file
 */
class Credential_hook{
    
    public function __construct() {
       
    }
    
    public function load_credential() {
        $dotenv = Dotenv\Dotenv::create(FCPATH); //Load .env file
		$dotenv->load();
    }
    
} //End class