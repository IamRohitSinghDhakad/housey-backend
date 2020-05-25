<?php
class Auth extends Common_Service_Controller {

	public function __construct(){
        parent::__construct();

        $this->load->model('auth_model');
	}

    //Check Social Signup
    function check_social_signup_put(){

        $headerInfo = $this->request_headers; //Get header Info

        if(empty($this->put('social_id'))){ 
            $this->error_response(get_response_message(115)); //error reponse
        }

        if(empty($this->put('user_type'))){  //buyer, seller
            $this->error_response(get_response_message(118)); //error reponse
        }

        if(empty($this->put('social_type'))){  //0:None, 1:Google, 2:Facebook, 3:Twitter, 4:GitHub
            $this->error_response(get_response_message(116)); //error reponse
        }

        if(empty($this->put('device_token'))){ 
            $this->error_response(get_response_message(117)); //error reponse
        }

        $social_id = sanitize_input_text($this->put('social_id'));
        $social_type = $this->put('social_type');
        $data['device_token'] = sanitize_input_text($this->put('device_token'));


        $email = !empty($this->put('email')) ? sanitize_input_text($this->put('email')) : NULL;
        //If email is not empty
        if(!empty($this->put('email'))){

            $userExist = $this->auth_model->checkUserExist($email,$social_id,$social_type); //Check user is exist in DB

            if($userExist !== FALSE){  

                 if($this->put('user_type') != $userExist->user_type){

                    if($this->put('user_type') == 'buyer' && $userExist->user_type == 'seller'){

                        $this->error_response(get_response_message(119)); //error reponse
                    }

                    if($this->put('user_type') == 'seller' && $userExist->user_type == 'buyer'){

                        $this->error_response(get_response_message(180)); //error reponse
                    }

                }

                if($userExist->status == 0){ // User Inactive
                    
                   $this->error_response(get_response_message(111),ACCOUNT_INACTIVE,400,['user_status' => '0']); //error reponse
                }

                $this->auth_model->checkSocialExist($userExist->userID,$social_id,$social_type); //check social data exist in DB

                $this->auth_model->updateDeviceInfo($userExist->userID,$data,$headerInfo); //Update Device Info

                //Update user last login field
                $updateUserData['last_login_at'] = datetime();
                $this->common_model->updateFields(USERS,$updateUserData, array('userID' => $userExist->userID));

                $userDetail = $this->auth_model->userInfo(array('userID'=>$userExist->userID, 'device_id' => $headerInfo['device-id'])); //Get User Info

                $businessInfo = $this->auth_model->seller_buisness_info($userExist->userID); //Get Business Info

                //generate  authtoken
                $auth_token = $this->general_model->generateToken($userDetail->userID,array('user_type'=>$userDetail->user_type,'device_id'=>$headerInfo['device-id']));

                $userDetail->authtoken = $auth_token;

                $this->success_response(get_response_message(121),['user_detail' => $userDetail,'business_info' => (object)$businessInfo, 'social_status' => 1]); // 1: Registered already or Login, success response 
            }

            $this->success_response(get_response_message(104),['user_detail' =>(object)[], 'business_info' => (object)[], 'social_status' => 0]); // 0: Not found, success response
        }

        if(empty($this->put('email'))){ //If email is empty then check social id in DB

            $userExist = $this->auth_model->checkUserExist($email,$social_id,$social_type); //Check user is exist in DB

            if($userExist !== FALSE){ 

                if($this->put('user_type') != $userExist->user_type){

                    if($this->put('user_type') == 'buyer' && $userExist->user_type == 'seller'){

                        $this->error_response(get_response_message(119)); //error reponse
                    }

                    if($this->put('user_type') == 'seller' && $userExist->user_type == 'buyer'){

                        $this->error_response(get_response_message(180)); //error reponse
                    }
                }

                if($userExist->status == 0){ // User Inactive

                   $this->error_response(get_response_message(111),ACCOUNT_INACTIVE,400,['user_status' => '0']); //error reponse
                }

                $data['device_token'] = sanitize_input_text($this->put('device_token'));

                $this->auth_model->updateDeviceInfo($userExist->userID,$data,$headerInfo); //Update Device Info

                //Update user last login field
                $updateUserData['last_login_at'] = datetime();
                $this->common_model->updateFields(USERS,$updateUserData, array('userID' => $userExist->userID));

                $userDetail = $this->auth_model->userInfo(array('userID'=>$userExist->userID, 'device_id' => $headerInfo['device-id'])); //Get User Info

                $businessInfo = $this->auth_model->seller_buisness_info($userExist->userID); //Get Business Info

                //generate  authtoken
                $auth_token = $this->general_model->generateToken($userDetail->userID,array('user_type'=>$userDetail->user_type,'device_id'=>$headerInfo['device-id']));

                $userDetail->authtoken = $auth_token;

                $this->success_response(get_response_message(121),['user_detail' => $userDetail,'business_info' => (object)$businessInfo, 'social_status' => 1]); // 1: Registered already or Login, success response 
            }

            $this->success_response(get_response_message(104),['user_detail' =>(object)[], 'business_info' => (object)[], 'social_status' => 0]); // 0: Not found, success response
        }
    }

    //Signup API
	function signup_post(){

        $headerInfo = $this->request_headers; //Get header Info

		// field name, error message, validation rules
        $this->form_validation->set_rules('full_name', 'Full Name','trim|required|min_length[2]|max_length[50]');

		$this->form_validation->set_rules('email', 'Email Address','trim|required|min_length[2]|max_length[255]|valid_email');

        $this->form_validation->set_rules('user_type', 'User Type','trim|required'); //Seller, Buyer

    	$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[4]|max_length[32]'); 

        $this->form_validation->set_rules('device_token', 'Device Token', 'trim|required'); 

		//set response msg  for form validation
		if($this->form_validation->run() == FALSE){

            $this->error_response(strip_tags(validation_errors())); //error reponse
		}

        $data['full_name']    = sanitize_input_text($this->input->post('full_name'));
		$data['email']        = sanitize_input_text($this->input->post('email'));			
		$data['password']     = !$this->post('social_id') ? password_hash($this->post('password'), PASSWORD_DEFAULT) : '';
        $data['user_type']    = $this->post('user_type');
        $data['signup_from'] = $this->post('signup_from')? $this->post('signup_from'): ''; //1=IOS,2=Android,3=Website
        $data['profile_timezone'] = $this->post('profile_timezone')? $this->post('profile_timezone'): '';
        $data['updated_at']   = datetime();
        $data['created_at']   = datetime();

        $headerInfo['device_token'] = $this->post('device_token')? sanitize_input_text($this->post('device_token')): NULL;
		$result = $this->auth_model->registration($data,$headerInfo);

		if(is_array($result)){ 

            if(!empty($result['returnData'])){  //Insert data in user device table

                //generate  authtoken
                $auth_token = $this->general_model->generateToken($result['returnData']->userID,array('user_type'=>$result['returnData']->user_type,'device_id'=>$headerInfo['device-id']));
                $result['returnData']->authtoken = $auth_token;

                $businessInfo = $this->auth_model->seller_buisness_info($result['returnData']->userID); //Get Business Info
            }


            switch ($result['regType']){
                case "NR":
                    $this->success_response(get_response_message(105),['user_detail' => $result['returnData'],'business_info' => (object)$businessInfo]); //success response
                break;

                case "AE":
                    $this->error_response(get_response_message(180),EMAIL_EXIST,400,['user_detail' => (object)[]]); //error reponse
                break;

                case "AES":
                    $this->error_response(get_response_message(119),EMAIL_EXIST,400,['user_detail' => (object)[]]); //error reponse
                break;

                case "AESE":
                    $this->error_response(get_response_message(119),EMAIL_EXIST,400,['user_detail' => (object)[]]); //error reponse
                break;

                case "AEBE":
                    $this->error_response(get_response_message(180),EMAIL_EXIST,400,['user_detail' => (object)[]]); //error reponse
                break;

                default:
                    $this->error_response(get_response_message(107),INVALID_PARAM_VALUE,400,['user_detail' => (object)[]]); //error reponse
            }
        }
        else{

            $this->error_response(get_response_message(107),INVALID_PARAM_VALUE,400,['user_detail' => (object)[]]); //error reponse
        }	
	} //End Fn

    //login user
    function login_post(){

        $headerInfo = $this->request_headers; //Get header Info 

        // field name, error message, validation rules
        $this->form_validation->set_rules('email','Email Address','trim|required|valid_email');
        $this->form_validation->set_rules('password','password','trim|required');

        $this->form_validation->set_rules('device_token', 'Device Token', 'trim|required'); 
        
        //set response msg  for form validation
        if($this->form_validation->run() == FALSE){

            $this->error_response(strip_tags(validation_errors())); //error reponse
        }

        $data = array();
        $data['email'] = sanitize_input_text($this->post('email'));
        $data['password'] = $this->post('password');
        $data['device_token'] = sanitize_input_text($this->post('device_token'));

        //Check email exist in databse
        $emailExist = $this->common_model->getsingle(USERS, array('email' => $this->post('email')));

        if(empty($emailExist)){
            //Wrong Email
            $this->error_response(get_response_message(112),INVALID_PARAM_VALUE,400,['user_detail' => (object)[]]); //error reponse
        }
        
        //Password is Empty Or NULL And Social ID is not Empty
        if((empty($emailExist->password) || $emailExist->password == NULL) && !empty($emailExist->social_id)){

            $this->error_response(get_response_message(128),INVALID_PARAM_VALUE,400,['user_detail' => (object)[]]); //error reponse
        }
        //Password is Empty Or NULL And Social ID is also Empty
        if((empty($emailExist->password) || $emailExist->password == NULL) && empty($emailExist->social_id)){

            $this->error_response(get_response_message(102),INVALID_PARAM_VALUE,400,['user_detail' => (object)[]]); //error reponse
        }
        
        //Password is Not Empty Or Not NULL
        if(!empty($emailExist->password) || $emailExist->password != NULL ){
            
            //Match password here 
            if(password_verify($this->post('password'), $emailExist->password)){ // Password verified

                //check for user inactive
                if($emailExist->status!=1){
                    //return if user inactive
                    $this->error_response(get_response_message(111),ACCOUNT_INACTIVE,400,['user_status' => '0']); //error reponse
                }

                $updateData = $this->auth_model->updateDeviceInfo($emailExist->userID,$data,$headerInfo); //Update Device Info

                //Update user last login field
                $updateUserData['last_login_at'] = datetime();
                $updateUserInfo = $this->common_model->updateFields(USERS,$updateUserData, array('userID' => $emailExist->userID));

                $userDetail = $this->auth_model->userInfo(array('userID'=>$emailExist->userID, 'device_id' => $headerInfo['device-id'])); //Get User Info
                $businessInfo = $this->auth_model->seller_buisness_info($emailExist->userID); //Get Business Info

                //generate  authtoken
                $auth_token = $this->general_model->generateToken($userDetail->userID,array('user_type'=>$userDetail->user_type,'device_id'=>$headerInfo['device-id']));

                $userDetail->authtoken = $auth_token;

                $this->success_response(get_response_message(121),['user_detail' => $userDetail,'business_info' => (object)$businessInfo]); //success response
            }
            else{ //Not verified password

                $this->error_response(get_response_message(102),INVALID_PARAM_VALUE,400,['user_detail' => (object)[]]); //Wrong Password error reponse
            }
        }
        
    } //End Fn

    //Social Signup/Login
    function social_post(){

        $headerInfo = $this->request_headers; //Get header Info
        // field name, error message, validation rules
        $this->form_validation->set_rules('full_name', 'Full Name','trim|required|min_length[2]|max_length[50]');

        $this->form_validation->set_rules('email', 'Email Address','trim|min_length[2]|max_length[255]|required|valid_email');

        $this->form_validation->set_rules('user_type', 'User Type','trim|required'); //Seller, Buyer
        $this->form_validation->set_rules('social_id', 'Social ID','trim|required'); //Social ID

        $this->form_validation->set_rules('social_type', 'Social Type','trim|required'); //0:None, 1:Google, 2:Facebook, 3:Twitter, 4:GitHub

        $this->form_validation->set_rules('device_token', 'Device Token', 'trim|required'); 

        //check social ID exist in DB 
        $isSocialIdExist = $this->common_model->getsingle(SOCIAL_ACCOUNT, array('social_id' => $this->post('social_id')));

        $isEmailExist = $this->common_model->getsingle(USERS, array('email' => $this->post('email')));
        
        //set response msg  for form validation
        if($this->form_validation->run() == FALSE){
            $this->error_response(strip_tags(validation_errors())); //error reponse
        }

        if(!empty($isSocialIdExist)){  //If social ID exist in DB 
            $this->error_response(get_response_message(165)); //error reponse
        }

        if(!empty($isEmailExist)){  //If email already exist

            //$this->error_response(get_response_message(127),EMAIL_EXIST,400); //error reponse

            if($this->input->post('user_type') == 'seller' && $isEmailExist->user_type == 'buyer'){

                $this->error_response(get_response_message(180),EMAIL_EXIST,400); //error reponse
            }

            if($this->input->post('user_type') == 'buyer' && $isEmailExist->user_type == 'seller'){

                $this->error_response(get_response_message(119),EMAIL_EXIST,400); //error reponse
            }
        }

        $profile_image=NULL;
        if (filter_var($this->input->post('profile_image'), FILTER_VALIDATE_URL)) {
           $profile_image = $this->input->post('profile_image');
        }
       

        $data['full_name']    = sanitize_input_text($this->input->post('full_name'));
        $data['email']        = $this->input->post('email') ? sanitize_input_text($this->input->post('email')) : NULL;                 
        $data['user_type']    = $this->post('user_type');
        $data['avatar']       = $profile_image;
        $data['is_avatar_url']= ($profile_image == NULL) ? '0': '2';
        $data['profile_timezone'] = $this->post('profile_timezone')? $this->post('profile_timezone'): '';
        $data['signup_from'] = $this->post('signup_from')? $this->post('signup_from'): ''; //1=IOS,2=Android,3=Website
        $data['signup_type'] = '2'; //   1:Regular, 2:Social
        $data['updated_at']   = datetime();
        $data['created_at']   = datetime();

        $socialData['social_type']  = $this->input->post('social_type');           
        $socialData['social_id']    = sanitize_input_text($this->input->post('social_id'));  

        $headerInfo['device_token'] = sanitize_input_text($this->post('device_token'));
        $result = $this->auth_model->social($data,$headerInfo,$socialData);
        if(is_array($result)){

            if(!empty($result['returnData'])){ 
                //generate  authtoken
                $auth_token = $this->general_model->generateToken($result['returnData']->userID,array('user_type'=>$result['returnData']->user_type,'device_id'=>$headerInfo['device-id']));

                $result['returnData']->authtoken = $auth_token;
                
                $businessInfo = $this->auth_model->seller_buisness_info($result['returnData']->userID); //Get Business Info
            }


            switch ($result['regType']) {
                // case "SL":
                //     $this->success_response(get_response_message(121),['user_detail' => $result['returnData'],'business_info' => (object)$businessInfo,'social_status' => 1]); // 1: Login, success response
                // break;

                case "SR":
                    $this->success_response(get_response_message(105),['user_detail' => $result['returnData'],'business_info' => (object)$businessInfo,'social_status' => 2]); //2: Register, success response
                break;

                // case "IU":
                //     $this->error_response(get_response_message(111),ACCOUNT_INACTIVE,400,['user_status' => '0']); //error reponse
                // break;

                // case "WT":
                //     $this->error_response(get_response_message(119)); //error reponse
                // break;
                // case "WT":
                //     $this->error_response(get_response_message(119)); //error reponse
                // break;

                default:
                    $this->error_response(get_response_message(107),INVALID_PARAM_VALUE,400,['user_detail' => (object)[]]); //error reponse
            }
        }
        else{
        
            $this->error_response(get_response_message(107),INVALID_PARAM_VALUE,400,['user_detail' => (object)[]]); //error reponse
        } 
    }

    //Reset password OR Forgot password
    function reset_password_put(){

        $email = sanitize_input_text($this->put('email'));
        $this->load->library('smtp_email'); //load smtp library

        if(empty($email)){ //if email empty

            $this->error_response(get_response_message(136)); //error reponse
        }

        if (isset($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) { //Email validation

            $this->error_response(get_response_message(112)); //error reponse
        }

        // get user details 
        $result = $this->common_model->getsingle(USERS, array('email'=> $email));  

        if(!$result){//check email exist or not

            $this->error_response(get_response_message(112)); //error reponse
        }

        $to= $result->email; //User email where email will send
        //generate new password
        $random = substr(md5(mt_rand()), 0, 10); 
        //hash password
        $new_password = password_hash($random, PASSWORD_DEFAULT); 
        //update password in table
        $updateData = $this->common_model->updateFields(USERS, array('password'=>$new_password, 'updated_at' =>datetime()), array('userID'=>$result->userID));
        
        //set data for mail template 
        $data['name'] = $result->full_name;
        $data['password'] = $random;

        $subject = SITE_NAME."- Reset Password";
        $message = $this->load->view('email/reset_password',$data,TRUE);
        //send mail
        $check   =  $this->smtp_email->send_mail($to,$subject,$message);

        if($check !== TRUE){
            $this->error_response($check); //error reponse
        }
        //set success response 
        $this->success_response(get_response_message(130)); //success response
    }

    //user log out
    function logout_delete(){
        $this->check_service_auth();
        $headerInfo = $this->request_headers; //Get header Info
        //empty device token on when user logged out
        $this->common_model->deleteData(USER_DEVICES,array('device_id'=>$headerInfo['device-id']));
        //set msg for success
        $this->success_response(get_response_message(125)); //success response
    }

    //change user password
    function change_password_post(){
        $this->check_service_auth();
        $user_id  = $this->authData->userID;

        //set validation rule
        $this->form_validation->set_rules('new_password', 'New password', 'trim|required|min_length[4]|max_length[32]');
        $this->form_validation->set_rules('confirm_password', 'Confirm password', 'trim|required|matches[new_password]');

        //get user id from auth data
        $where = array('userID'=>$user_id);
        //Get user detail
        $userDetail = $this->auth_model->userInfo($where);

        if(!empty($userDetail->password)){

            $this->form_validation->set_rules('old_password', 'Current password', 'required');
        }

        //set response msg  for form validation
        if($this->form_validation->run() == FALSE){

            $this->error_response(strip_tags(validation_errors())); //error reponse
        }

        //get data
        $oldPassword = sanitize_input_text($this->post('old_password'));
        $newPassword = sanitize_input_text($this->post('new_password'));
        $newPasswordHash = password_hash(sanitize_input_text($this->post('new_password')) , PASSWORD_DEFAULT);
        
        if(!empty($userDetail->password)){ //If user password is not empty

            //password verify
            if(password_verify($oldPassword, $userDetail->password)){
                //check curent and new password are same
                if(password_verify($newPassword, $userDetail->password)){
                    //set msg for new password are same 
                    $this->error_response(get_response_message(149)); 
                }

                $this->update_password($newPasswordHash,$where); //update password
            }else{
                //set msg for password not match with current password
                $this->error_response(get_response_message(151)); 
            }
        }

        //If user password is empty and It is social user
        if(empty($userDetail->password) && !empty($userDetail->social_id)){
            
            $this->update_password($newPasswordHash,$where); //update password
        }
    }

    private function update_password($newPasswordHash,$where){
        
        //set data for update 
        $updatedata = array('password' => $newPasswordHash,'updated_at' => datetime());
        //update password
        $result = $this->common_model->updateFields(USERS, $updatedata, $where);
        //check password update
        if($result === FALSE){
            //if not set msg
            $this->error_response(get_response_message(107)); 
        }
        //set msg for success
       $this->success_response(get_response_message(150)); 
    }

    function content_get(){

        //$this->check_service_auth();
        
        if($_SERVER['CI_ENV'] == 'testing'){
            $data['term_and_condition'] = 'http://localhost:4200/termandcondition';
            $data['policy'] = 'http://localhost:4200/privacypolicy';

        }else if($_SERVER['CI_ENV'] == 'development'){
           $data['term_and_condition'] = 'https://dev.qvazon.com/termandcondition';
           $data['policy'] = 'https://dev.qvazon.com/privacypolicy';
        }
        else if($_SERVER['CI_ENV'] == 'production'){
            $data['term_and_condition'] = 'https://www.qvazon.com/termandcondition';
            $data['policy'] = 'https://www.qvazon.com/privacypolicy';
        }

        $where = array('option_name' => 'term_content');
        $termAndConditionData = $this->common_model->getsingle(CONTENT_OPTIONS, $where, 'option_value');

        $policy = $this->common_model->getsingle(CONTENT_OPTIONS, array('option_name' =>'term_policy'), 'option_value');

        
        if(empty($termAndConditionData->option_value) && empty($policy->option_value)){

            $response = array('status'=>FAIL,'message'=> get_response_message(106), 'content' => '','content_url'=>'');
            $this->response($response);
        }

        if(!empty($termAndConditionData))
            $content['termAndCondition'] = $termAndConditionData->option_value;

        if(!empty($policy))
            $content['policy'] = $policy->option_value; 

        if(!empty($content && $data)){
            $response = array('status'=> SUCCESS,'message'=>get_response_message(200), 'content' => $content,'content_url'=>$data);
            $this->response($response);
        }else{
            $response = array('status'=>FAIL,'message'=> get_response_message(106), 'content' => '','content_url'=>'');
            $this->response($response);
        }

    }

} //End Class