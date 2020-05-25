<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Check whether the site is offline or not.
 * Used when we want to move website in maintenance mode
 */
class Maintenance_hook {
    
    public function __construct() {
        log_message('debug','Accessing maintenance hook!');
    }
    
    public function go_offline() {
        
        // Get the CI instance
        $this->CI =& get_instance();
        
        $mode = $this->CI->config->item('maintenance_mode');
        
        if(isset($mode) && $this->CI->config->item('maintenance_mode') === TRUE){
            //include(APPPATH.'views/maintenance_view.php');
            echo $this->CI->load->view('maintenance_view', '', TRUE); exit;
        }
        
    }
    
} //End class