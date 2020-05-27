<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
 use \Firebase\JWT\JWT;
/*
 * General Model
 * version: 1.0 (28-01-2020)
 * Auth Token generate and user detail according to Auth Token
 */
class General_model extends MY_Model {
    /**
    * Generate JWT
    * @param int $user_id Current User ID
    * @param array $user_entity Can be any entity related to user
    * (user type, email, device ID etc)
    * @param int $expire_time Expire time in secs (Default 1hour)
    * @return string JWT
    *
    */
    function generateToken($user_id, $user_entity, $expire_time=86400) {

        $issuedAt = time(); //current timestamp
        $notBefore = $issuedAt; //Token to be not validated before given time
        $expire = time() + $expire_time; //Adding time to current timestamp

        $data = [
            'iat' => $issuedAt, // Issued at: time when the token was generated
            'jti' => getenv('JWT_TOKEN_ID'), // Json Token Id: an unique identifier for the token
            'iss' => getenv('SERVER_NAME'), // Issuer (example.com)
            'nbf' => $notBefore, // Not before
            'exp' => $expire, // Expire time
            'data' => [ // Data related to the signer user
                'user_id' => $user_id,
                'device_id' => $user_entity['device_id'], // Can be any entity related to user(user type, email, device ID etc)
            ]
        ];

        $jwt = JWT::encode( $data, getenv('JWT_SECRET_KEY'));
        return $jwt;
    }

    /*
     *  The function is for get userdetail.     
     *  param like userID.     
     *  @return  user detail .     
    */
    function getUserDetail($userId,$deviceId) {
        $this->db->select('users.userID,users.full_name,users.email,users.password,users.avatar,users.profile_timezone,users.status,users.created_at as userCreate_date,device.device_type,device.device_id,device.device_token,device.device_timezone,users.push_alert_status');
        $this->db->from(USERS.' as users');
        $this->db->join(USER_DEVICES.' as device', 'device.user_id = users.userID');
        $this->db->where(array('users.userID' => $userId, 'device.device_id' => $deviceId));
        //$this->db->where('device_id', $deviceId);
        $query = $this->db->get();
        if(!$query){
            $this->output_db_error(); //500 error
        }
        $result = $query->row();
        return $result ;
    }

    //Check user exist or not
    function userExist($user_id){
        $user = $this->common_model->getsingle(USERS,array('userID' => $user_id));

        if(empty($user)){  //If user not exist
            return FALSE;
        } 
        if($user->status == '0'){  //user inactive
            return FALSE;
        }
        return TRUE;
    }
     
} //end of class
