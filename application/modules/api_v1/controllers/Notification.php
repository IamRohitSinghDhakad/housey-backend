<?php

/**
* Notification controller
* Handles notification request
* version: 1.0 ( 17-02-2020 )
*/
class Notification extends Common_Service_Controller {

	public function __construct(){
        parent::__construct();

        $this->load->model('NotificationNew_model');
	}

    function push_alert_status_patch(){
        $this->check_service_auth();
        $user_id = $this->authData->userID;

        $status = $this->patch('status');
        if(empty($status) && $status !=0 ){  //required
            $this->error_response('Status is required');
        }

        $where = array('userID' => $user_id);
        $key = 'push_alert_status';
        $pushStatus = $this->common_model->get_field_value(USERS, $where, $key);

        if($pushStatus === FALSE){
            $this->error_response();
        }

        if($status == '1'){
            $updateStatus = '1';
            $msg = 'Your notification is on now';
        } else {
            $updateStatus = '0';
            $msg = 'Your notification is off now';
        }

        $update_data = array('push_alert_status'=>$updateStatus);
        $this->common_model->updateFields(USERS, $update_data, $where);

        $this->success_response($msg);
    }

    function webPushNotifiction_patch(){
        header("Access-Control-Allow-Origin: *");

        $status = $this->patch('status');

        $this->check_service_auth();
        $data['userId'] = $this->authData->userID;
        $notifiycount = $this->NotificationNew_model->getAllNotificationsCount($data);
        $notifiyData = $this->NotificationNew_model->getSingleNotifications($data);

        if($notifiyData){
            $where = array('notificationID'=>$notifiyData->notificationID);
            $data = array('web_push'=>1);

            $this->common_model->updateFields(NOTIFICATIONS,$data,$where);
        }

        if($notifiyData){
            $countData = ($notifiycount)? $notifiycount->count : 0;

            $this->success_response(get_response_message(302), ['data_found' => true, 'total' => $countData, 'notification' => $notifiyData]);
        }else{

            $this->success_response(get_response_message(106), ['data_found' => false]); //error reponse
        }
    }// End function

    // Notification list
    function notificationList_get(){
        $this->check_service_auth(); //check authentication
        $data['userId'] = $this->authData->userID;

        $notificationData = $this->NotificationNew_model->get_notification($data);
        $notification_count = $this->NotificationNew_model->getAllNotificationsCount($data);
        $total_count =  ($notification_count)? $notification_count->count : 0;
        if(!empty($notificationData)){

            $this->success_response(get_response_message(302), ['data_found' => true, 'count' => $total_count, 'notification' => $notificationData]);

        }else{

            $this->success_response(get_response_message(106), ['data_found' => false]); //error reponse
        }
    }//end of notification list function

    //Read Unread notification
    function readNotification_post(){
        $this->check_service_auth(); //check authentication
        $data['userId'] = $this->authData->userID;
        $notification_id = $this->post('notification_id');

        $this->form_validation->set_rules('notification_id', 'Notification ID','trim|required|numeric');

        //set response msg  for form validation
        if($this->form_validation->run() == FALSE){
            $this->error_response(strip_tags(validation_errors())); //error reponse
        }

        $notificationData = $this->common_model->updateFields(NOTIFICATIONS,array('is_read'=>'1'),array('notification_for'=>$data['userId'],'notificationID'=>$notification_id));

        if($notificationData === True){
            $notification_count = $this->NotificationNew_model->getAllNotificationsCount($data);
            $total_count = ($notification_count)? $notification_count->count : 0;

            $this->success_response(get_response_message(302), ['count' => $total_count, 'notification' => $notificationData]);

        }else{

            $this->error_response(get_response_message(107));
        }
    }

} //End Class