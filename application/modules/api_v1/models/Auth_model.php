<?php
/**
* Web service model
* Handles web service request
* version: 2.0 ( 14-08-2018 )
*/
class Auth_model extends MY_Model {
    
    function registration($data,$headerInfo){  

        $res = $this->db->select('email,user_type')->where(array('email'=>$data['email']))->get(USERS);         
        if($res->num_rows() == 0){
            $this->db->insert(USERS, $data);
            $last_id = $this->db->insert_id();

            if($last_id){

                //Check device ID is already exist in DB If not empty then delete
                $device_ID = $this->get_device_info(USER_DEVICES, array('device_id' => $headerInfo['device-id']));

                if($device_ID !== FALSE || !empty($device_ID)){

                    $this->common_model->deleteData(USER_DEVICES, array('device_id' => $headerInfo['device-id']));
                }

                $insertData['user_id']  = $last_id;
                $insertData['device_type']  = $headerInfo['device-type'];//1=IOS,2=Android,3=Website
                $insertData['device_id'] = $headerInfo['device-id'];
                $insertData['device_token'] = $headerInfo['device_token'];
                $insertData['device_timezone'] = $headerInfo['device-timezone'];
                $insertData['updated_at'] = datetime();
                $insertData['created_at'] = datetime();

                $this->common_model->insertData(USER_DEVICES,$insertData);

                return array('regType'=>'NR','returnData'=>$this->userInfo(array('userID' => $last_id, 'device_id' => $headerInfo['device-id'])));
                // Normal registration
            }
        }
        else{   

            $result = $res->row();
            if($data['user_type'] == 'seller' && $result->user_type == 'buyer'){

                return array('regType'=>'AE'); //already exist buyer email
            }

            if($data['user_type'] == 'buyer' && $result->user_type == 'seller'){

                return array('regType'=>'AES'); //already exist seller email
            }

            if($data['user_type'] == 'seller' && $result->user_type == 'seller'){

                return array('regType'=>'AESE'); //already exist seller email
            }
            if($data['user_type'] == 'buyer' && $result->user_type == 'buyer'){

                return array('regType'=>'AEBE'); //already exist buyer email
            }
        }

    } //End Function users Registertion users Register

    //User social Signup/Login
    function social($data,$headerInfo,$socialData){ 
        
        // if(!empty($data['email'])){ // Email Not Empty

        //     $userExist = $this->checkUserExist($data["email"],$socialData['social_id'],$socialData['social_type']); //to check user exist in database

        //     if($userExist != FALSE){  //User exist

        //         if($data['user_type'] != $userExist->user_type){

        //             return array('regType'=>'WT'); //Wrong user type
        //         }

        //         $this->checkSocialExist($userExist->userID,$socialData['social_id'],$socialData['social_type']); //check social data exist in DB

        //         if($userExist->status == 1){ //User active
                        
        //             //updating users deviceInfo
        //             $updateDeviceInfo = $this->updateDeviceInfo($userExist->userID,$data,$headerInfo);

        //             $updateData['last_login_at'] = datetime();
        //             $updateUserInfo = $this->common_model->updateFields(USERS,$updateData, array('userID' => $userExist->userID));

        //             return array('regType'=>'SL','returnData'=>$this->userInfo(array('userID' => $userExist->userID, 'device_id' => $headerInfo['device-id'])));
        //             //social login successfully
                   
        //         }else{
        //             return array('regType'=>'IU'); //Inactive User
        //         }
        //     }
        // }
        
        // if(empty($data['email'])){ //Email Empty

        //     $data['email'] = NULL;

        //     $userExist = $this->checkUserExist($data["email"],$socialData['social_id'],$socialData['social_type']); //to check user exist in database

        //     if($userExist != FALSE){  //Social User exist 

        //         if($data['user_type'] != $userExist->user_type){

        //             return array('regType'=>'WT'); //Wrong user type
        //         }
                
        //         if($userExist->status == 1){ // User Active
                        
        //             //updating users deviceInfo
        //             $updateDeviceInfo = $this->updateDeviceInfo($userExist->userID,$data,$headerInfo);

        //             $updateData['last_login_at'] = datetime();
        //             $updateUserInfo = $this->common_model->updateFields(USERS,$updateData, array('userID' => $userExist->userID));

        //             return array('regType'=>'SL','returnData'=>$this->userInfo(array('userID' => $userExist->userID, 'device_id' => $headerInfo['device-id'])));
        //             //social login successfully
                   
        //         }else{

        //             return array('regType'=>'IU'); //Inactive User
        //         }
        //     }
        // }

        //Insert New user
        $last_id = $this->common_model->insertData(USERS,$data);
        if(!empty($last_id)){

            //insert social data in social account
            $insertSocialData = array(
                'user_id' => $last_id,
                'social_id' => $socialData['social_id'],
                'social_type' => $socialData['social_type'],
                'is_signup' => '1', //0:No,1:Yes
                'created_at' => datetime(), 
            );
            $this->common_model->insertData(SOCIAL_ACCOUNT,$insertSocialData);


            //Check device ID is already exist in DB If not empty then delete
            $device_ID = $this->get_device_info(USER_DEVICES, array('device_id' => $headerInfo['device-id']));

            if($device_ID !== FALSE || !empty($device_ID)){

                $this->common_model->deleteData(USER_DEVICES, array('device_id' => $headerInfo['device-id']));
            }

            $insertData['user_id']  = $last_id;
            $insertData['device_type']  = $headerInfo['device-type'];//1=IOS,2=Android,3=Website
            $insertData['device_id'] = $headerInfo['device-id'];
            $insertData['device_token'] = $headerInfo['device_token'] ? sanitize_input_text($headerInfo['device_token']): '';
            $insertData['device_timezone'] = $headerInfo['device-timezone'];
            $insertData['updated_at'] = datetime();
            $insertData['created_at'] = datetime();

            $this->common_model->insertData(USER_DEVICES,$insertData);

            return array('regType'=>'SR','returnData'=>$this->userInfo(array('userID' => $last_id, 'device_id' => $headerInfo['device-id'])));//social registration    
        }

    } //End Function users social signup/login

    function updateDeviceInfo($userId,$data,$headerInfo){

        //Check device ID is already exist in DB If not empty then delete
        $device_ID = $this->get_device_info(USER_DEVICES, array('device_id' => $headerInfo['device-id']));

        if($device_ID !== FALSE || !empty($device_ID)){

            $this->common_model->deleteData(USER_DEVICES, array('device_id' => $headerInfo['device-id']));
        }

        $device_exist = $this->deviceExist($userId,$headerInfo['device-type'],$headerInfo['device-id']);

        if(isset($headerInfo['device_token']) && !empty($headerInfo['device_token'])){
            $data['device_token']  =  $headerInfo['device_token']; //assign device token in data array
        }
        if($device_exist === TRUE) { //Update device info

            //$update['device_type'] = $headerInfo['device-type'];
            //$update['device_id'] = $headerInfo['device-id'];
            $update['device_token'] = $data['device_token'];
            $update['device_timezone'] = $headerInfo['device-timezone'];
            $update['updated_at'] = datetime();

            $update_device = $this->common_model->updateFields(USER_DEVICES,$update,array('device_id' => $headerInfo['device-id']));
        }else{
        
            //Insert device info
            $device_info = array(
                'user_id' =>$userId,
                'device_type' =>$headerInfo['device-type'],
                'device_id' => $headerInfo['device-id'],
                'device_token' =>$data['device_token'],
                'device_timezone' =>$headerInfo['device-timezone'],
                'created_at' =>datetime(),
                'updated_at' =>datetime()
            );
            $add_device = $this->common_model->insertData(USER_DEVICES,$device_info);
            if(!$add_device){

                return FALSE;
            }
        }
       
        return TRUE;
     
    }//End Function Update Device Token
        
    //get user info
    function userInfo($where){

        $this->db->select('u.userID, u.full_name, u.email, u.password, u.user_type, u.status, u.avatar, u.signup_from, social_account.social_type, social_account.social_id ,u.stripe_customer_id, u.profile_timezone,u.profile_address,u.profile_country_code
            , u.updated_at, u.created_at, user_device.device_type, user_device.device_id, user_device.device_token, user_device.device_timezone, u.push_alert_status, u.is_verified as is_seller_verified');
        $this->db->from(USERS.' as u');
        $this->db->join(USER_DEVICES.' as user_device',' user_device.user_id = u.userID','left');
        $this->db->join(SOCIAL_ACCOUNT.' as social_account',' social_account.user_id = u.userID','left');
        $this->db->where($where); 
        $query = $this->db->get(); 
        if(!$query){
            $this->output_db_error();
        }

        $result = $query->row();
        if(!empty($result)){
            if (!empty($result->avatar) || $result->avatar != NULL) {
                $image = $result->avatar;
                //check if image consists url- happens in social login case
                if (filter_var($result->avatar, FILTER_VALIDATE_URL)) { 
                    $result->avatar = $image;
                }
                else{
                    $result->avatar = getenv('AWS_CDN_USER_THUMB_IMG').$image; 
                }
            }
            else{
                $result->avatar = getenv('AWS_CDN_USER_PLACEHOLDER_IMG'); //return default image if image is empty
            }
        }
        $result->user_address = $this->default_address($result->userID);
        return $result;
    } //End Function usersInfo

    //get user default address
    function default_address($user_id){

        $this->db->select('*');
        $this->db->from(USER_ADDRESS);
        $this->db->where(array('user_id' => $user_id, 'is_default' => 1));
        $query = $this->db->get(); 
        if(!$query){
            $this->output_db_error();
        }

        $result = $query->row();
        return $result;
    }

    //Get seller type user business information
    function seller_buisness_info($userID){

        $this->db->select('seller_biz_info.businessInfoID, seller_biz_info.name as business_name, seller_biz_info.license , seller_biz_info.address , seller_biz_info.latitude , seller_biz_info.longitude');

        $this->db->from(SELLER_BUSINESS_INFO.' as seller_biz_info');
        $this->db->join(USERS.' as user',' seller_biz_info.user_id = user.userID');
        $this->db->where(array('user_id' => $userID));
        $query = $this->db->get();
        if(!$query){
            $this->output_db_error();
        }
        $result = $query->row();
        return $result;
    }

    //Check device exist in db
    function deviceExist($userId,$deviceType,$deviceId){
        $this->db->select('*');
        $this->db->from(USER_DEVICES);
        $this->db->where(array('user_id'=>$userId,'device_id'=>$deviceId,'device_type'=> $deviceType));
        $result = $this->db->get()->row();
        if(!empty($result)){
            return TRUE;
        }
        else
            return FALSE;
    }

    //Check user exist in DB
    function checkUserExist($email,$social_id,$social_type){

        $query = $this->db->query('SELECT userID,full_name,email,password,user_type
            ,status,social_id,social_type FROM '.USERS.' LEFT JOIN '.SOCIAL_ACCOUNT.' ON '.USERS.'.userID = '.SOCIAL_ACCOUNT.'.user_id WHERE '.USERS.'.email = "'.$email.'" OR ('.SOCIAL_ACCOUNT.'.social_id = "'.$social_id.'" AND '.SOCIAL_ACCOUNT.'.social_type = "'.$social_type.'")') ; 
        $isExist = $query->row();

        if(!empty($isExist)){
            return $isExist;
        }
        else
            return FALSE;

    }

    //Check social data exist in DB
    function checkSocialExist($user_id,$social_id,$social_type){
        $query = $this->db->query('SELECT social_id,social_type,user_id FROM '.SOCIAL_ACCOUNT.' WHERE social_id = "'.$social_id.'" AND social_type = "'.$social_type.'" ') ; 
        $isExist = $query->row(); 

        if(!empty($isExist)){
            return $isExist;
        }
        else{
            $insertData = array(
                'user_id' => $user_id,
                'social_id' => $social_id,
                'social_type' => $social_type,
                'created_at' => datetime(), 
            );
            $insert_id = $this->common_model->insertData(SOCIAL_ACCOUNT,$insertData);
            if(!$insert_id){
                return FALSE;
            }
            return $insert_id;
        }
    }


   function get_device_info($table, $where){ 
        $this->db->select('device_id');
        $this->db->from($table);
        $this->db->where($where);
        $ret = $this->db->get()->result();
        if(!empty($ret)){
            return $ret;
        }
        else
            return FALSE;
    }

}//End Class
?>
