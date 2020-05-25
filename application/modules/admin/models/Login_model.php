<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login_model extends CI_Model{

	public function __construct(){
        parent::__construct();
    }
		
	public function login($email,$password){
		$where = array('email'=>$email);
		$this->db->where($where);
		$query = $this->db->get(ADMIN);
		
		if($query->num_rows() > 0){
			$hashed_password = $query->row()->password;
			if(password_verify($password,$hashed_password)){		
			$row = $query->row();
			$id = $row->adminID;
			$email = $row->email;
			$_SESSION[ADMIN_USER_SESS_KEY]['adminId'] = $row->adminID;
			$_SESSION[ADMIN_USER_SESS_KEY]['email'] = $row->email;
			$_SESSION[ADMIN_USER_SESS_KEY]['fullName'] = $row->full_name;
			if(!empty($row->profile_photo)){
				$_SESSION[ADMIN_USER_SESS_KEY]['profile_photo'] = $row->profile_photo;
			}
			return array('type'=>'LS');;

 			}else{
 				return array('type'=>'PDM');
 			}
 		}
 		else{
 			return array('type'=>'EDM');
 		}
 		return FALSE;
	}//END OF LOGIN FUNCTION
	
}
