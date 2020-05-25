<?php (defined('BASEPATH')) OR exit('No direct script access allowed');
/**
* Core Model for extending our models
* version: 2.0 (Last updated: 11-01-2019)
*/

class MY_Model extends CI_Model {

    function __construct(){
        parent::__construct();
    }
    
    /*
     * Show DB error with json output
     * Added in ver 2.0
     */
    public function output_db_error($msg=''){
        
        $response = array(
            'status'=>FAIL, 
            'message'=> ($msg)? $msg : get_response_message(107), 
            'error_detail'=>$this->db->error()
        );
        echo get_json_output($response); exit;
    }
    
}
