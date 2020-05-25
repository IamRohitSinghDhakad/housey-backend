<?php
if(!defined('BASEPATH')) exit('No direct script access allowed');
/**
* Handles notifications for andriod and ios platform
* version: 2.0 (14-08-2018)
*/

class Notification_model extends CI_Model {

    public function __construct() {
        parent::__construct();  
        $this->notify_log_file = 'notification_log.txt';    //notifcation file path
    }
    
    /* Firebase notification for Andriod and ios */
    function send_notification($registrationIds, $notificationMsg){   

        $msg = $notificationMsg;
        $fields = array(
            'registration_ids' 	=> $registrationIds,  //firebase token array
            'data'		=> $msg ,  //msg for andriod
            'notification'      => $msg   //msg for ios
        );

        $headers = array(
            'Authorization: key=' . getenv('NOTIFICATION_KEY'), //firebase API key
            'Content-Type: application/json'
        );
        
        //curl request
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );  //firebase end url
        //curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );
        curl_close( $ch );
        log_event($result, $this->notify_log_file);  //create log of notifcation
        return $result;
    }
    
    //store notifcation in DB
    function save_notification($data){
        return $this->common_model->insertData(NOTIFICATIONS, $data);
    }
    
    /**
     * Prepare notification payload
     * Added in ver 2.0
     */
    public function send_push_notification($token_arr, $title, $body, $reference_id,$type,$badgeCount){
        if(empty($token_arr)){
            return false;
        }
        //prepare notification payload
        $notif_msg = array('title'=>$title, 'body'=> $body, 'reference_id'=>$reference_id, 'type'=> $type, 'click_action'=>'ChatActivity', 'sound'=>'default', 'badge' =>$badgeCount);
        
        $this->send_notification($token_arr, $notif_msg); //send andriod and ios push notification
        return $notif_msg; //return message array
    }
}
