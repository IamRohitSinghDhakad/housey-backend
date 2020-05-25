<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Common controller for admin modules
* version: 2.0 (14-08-2018)
*/

require APPPATH . "third_party/MX/Controller.php";  //include MX library (HMVC library)

class Common_Back_Controller extends MX_Controller {

    public function __construct(){
        parent::__construct();
        $this->admin_user_session_key = ADMIN_USER_SESS_KEY; //user session key
        $this->tbl_users = ADMIN; //admin table
    }
    
    /**
     * Admin session authentication for pages
     * Added in ver 2.0
     */
    public function check_admin_user_session(){
        $page_slug = $this->router->fetch_method();
        $allowed_pages = array('index'); //these pages/methods do not require user authentication
        $allowed_control = 'admin'; //methods of this controller does not require authentication
        $current_control = $this->router->fetch_class(); // get current controller, class = controller
        
        if(!is_admin_logged_in() && (in_array($page_slug,$allowed_pages)) && $current_control == $allowed_control){
            return TRUE; //session is empty and pages is not restricted
        }else{
            //either page is resticted or session exist
            
            if(!is_admin_logged_in()){
                redirect('admin'); //redirect to home/login if session not exit
            }
            
            //user session exists
            $user_sess_data = $_SESSION[ $this->admin_user_session_key ]; //user session array
            $session_u_id = $user_sess_data['adminId']; //user ID
            $where = array('adminID'=>$session_u_id,'status'=>1); //status:0 means active 
            $check = $this->common_model->is_data_exists($this->tbl_users,$where);
            if($check === FALSE){
               //user is either deleted or is inactivated
               $this->admin_logout(); //force logout
            }
            
            if(empty($page_slug)){
               return TRUE; //if slug is empty and session is set
            }
            
            $after_auth = array('index'); //restrict access to these pages if session is set
            if(in_array($page_slug,$after_auth) && $current_control == $allowed_control){
                redirect('admin/dashboard');
            }else{
                return TRUE; 
            }
            
        } 
    }
    
    /**
     * Admin user logout
     * Added in ver 2.0
     */
    function admin_logout($is_redirect=TRUE){
        
        // instead of destroying whole session data, we will just unset biz user session data
        unset($_SESSION[$this->admin_user_session_key]); 
        if($is_redirect)
            redirect('admin', 'refresh');  //redirect only when $is_redirect is set to TRUE
    }
    
    /**
     * Admin authentication for ajax
     * Added in ver 2.0
     */
    public function check_admin_ajax_auth(){
       
        //verify if request is xhr(ajax)
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }
        
        $failed_res = json_encode(array('status'=> -1,'msg'=>'Your session expired. Please login again.','url'=>base_url('admin')));
        if(!is_admin_logged_in()){
            echo $failed_res; exit;
        }

        //user session exists
        $user_data = get_admin_session_data();
        $where = array('adminID'=>$user_data['adminId'],'status'=>1); //status:0 means active 
        $check = $this->common_model->is_data_exists($this->tbl_users,$where);

        if($check===FALSE){
           //user is either deleted or is inactivated
           $this->logout(FALSE); //force logout- $is_redirect is FALSE here because we will redirect user from JS
           echo $failed_res; exit;
        }

        return TRUE; //all good
    }
}