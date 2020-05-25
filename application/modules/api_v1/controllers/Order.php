<?php
use Kreait\Firebase\Factory;
class Order extends Common_Service_Controller {

    protected $database;
    protected $dbname = 'badge_count';  //databse key which will update

    public function __construct(){
        parent::__construct();

        $factory = (new Factory)->withServiceAccount(FCPATH.'firebase_json/'.getenv('FIREBASE_JSON_FILE'))->withDatabaseUri(getenv('FIREBASE_DB_URL'));
        $this->database = $factory->createDatabase();

        $this->load->model('order_model');
        $this->load->model('payment_model');
        $this->load->model('notification_model');
    }

    //Accept /reject order by seller
    function accept_reject_patch($id){

        $this->check_service_auth(); // check authorization 
        $user_id = $this->authData->userID; // get user Id 
        $status = $this->patch('status'); //0: Reject, 1: Accept

        if(empty($id)){ //$id = orderID
            $this->error_response(get_response_message(158));
        }

        //Check orderID exist or not
        $isExistOrder = $this->common_model->get_field_value(ORDERS,array('orderID' => $id),'orderID');

        if($isExistOrder === false){  //If OrderID not exist
            $this->error_response('Order is not exist.');
        }

        if($status == ''){  //If status field is empty
            $this->error_response('Status is required');
        }

        $update = $this->common_model->updateFields(ORDERS,array('order_status' => $status), array('orderID' => $id));

        if($update === false){
            $this->error_response(get_response_message(107));

        }else{

            //Manage notification code start
            //user information
            $orderInfo = $this->common_model->is_data_exists(ORDERS,array('orderID'=>$id));
            
            $userDeviceTokens = $this->payment_model->getAllDeviceToken(array('user_id'=>$orderInfo->ordered_by_user_id, 'device_type !='=> '3'));

            //manage notiifcation code end

            $order_status = $this->common_model->get_field_value(ORDERS,array('orderID' => $id),'order_status');

            if($order_status == 0){  //Reject Order

                //$this->rejectNotification($userDeviceTokens,$orderInfo,$user_id,$id);

                $deleteOrder = $this->common_model->deleteData(ORDERS, array('orderID' => $id , 'order_status' => '0'));

                $this->success_response(get_response_message(178));
            }
            if($order_status == 1){  //Acccept order

                //delete Notiifcation for this order and seller
                $this->common_model->deleteData(NOTIFICATIONS, array('notification_for' => $user_id , 'reference_id' => $id, 'notification_type' => 'Order Placed'));

                $updateOrder = $this->common_model->updateFields(ORDERS,array('current_status' => '1', 'updated_at' =>datetime()), array('orderID' => $id));

                $insertData = array('order_id' => $id, 'order_status' => '1', 'updated_at' => datetime(), 'created_at' => datetime());

                $insertTracking = $this->common_model->insertData(ORDER_TRACKING,$insertData);

                $this->acceptNotification($userDeviceTokens,$orderInfo,$user_id,$id);

                $this->success_response(get_response_message(179));
            }
        }
    }

    //When order reject
    function rejectNotification($userDeviceTokens,$orderInfo,$user_id, $insertOrder){
        if(!empty($userDeviceTokens)){

            //Update badge count Code
            $badgeNotify = 0;   
            $val = $this->database->getReference($this->dbname)->getChild($orderInfo->ordered_by_user_id)->getSnapshot();
            if($val->exists() == TRUE){

                $badgeCount = intval($val->getValue()['count']);
                $badgeCountValue = $badgeCount ? $badgeCount : 0;
                $badgeNotify = $badgeCountValue+1;

                $updateVal = $this->database->getReference($this->dbname)->getChild($orderInfo->ordered_by_user_id)->update(['count' => $badgeCountValue+1]); //update badge count

            }else{
                $updateVal = $this->database->getReference($this->dbname)->getChild($orderInfo->ordered_by_user_id)->update(['count' => 1]); //create new badge count
                $badgeNotify = 1;
            }
            //End Of Update badge count Code

            $tokenArr = array();
            foreach ($userDeviceTokens as $value) {

                if(!empty($orderInfo)){
                    //user address and contact info
                    $address_info = $this->common_model->getsingle(ORDER_ADDRESS,array('order_id'=>$orderInfo->orderID));
                }

                //$refrenID['token'] = $value->device_token; //device token for push notification
                $tokenArr[]  = $value->device_token; //device token for push notification
                $notif_msg['type']          = 'Order Reject';
                $notif_msg['body']          = 'Order with reference number '.$orderInfo->number.' has been rejected by seller.';
                $notif_msg['title']         = 'Order Reject';
                $notif_msg['sound']         = 'default';
                $notif_msg['order_id']      = $insertOrder;
                $notif_msg['badge']      = $badgeNotify;

                $dataNotifiy['notification_title']      = 'Order Reject';
                $dataNotifiy['notification_by']         = $user_id;
                $dataNotifiy['notification_for']        = $value->user_id;
                $dataNotifiy['notification_type']       = 'Order Reject';
                $dataNotifiy['is_read']                 = '0';
                $dataNotifiy['web_push']                 = '0';
                $dataNotifiy['reference_id']            = $insertOrder;
                $dataNotifiy['updated_at']            = datetime();
                $dataNotifiy['created_at']            = datetime();
                $dataNotifiy['notification_payload']    = json_encode($notif_msg);
                $dataNotifiy['notification_message']    = 'Your order with reference number '.$orderInfo->number.' has been rejected by seller.';
                // if($value->device_type < 1){
                //     $dataNotifiy['web_push']                 = '1';
                // }
                //when reference id is not empty then notification done
                //if(!empty($refrenID['token'])){
                $this->payment_model->notification($dataNotifiy,$tokenArr,$notif_msg);
                //}
                $tokenArr = array();
                $buyerID = $value->user_id;
            }
        }

        $notif_msg['type']          = 'Order Reject';
        $notif_msg['body']          = 'Order with reference number '.$orderInfo->number.' has been rejected by seller.';
        $notif_msg['title']         = 'Order Reject';
        $notif_msg['sound']         = 'default';
        $notif_msg['order_id']      = $insertOrder;

        $dataNotifiy['notification_title']      = 'Order Reject';
        $dataNotifiy['notification_by']         = $user_id;
        $dataNotifiy['notification_for']        = $orderInfo->ordered_by_user_id;
        $dataNotifiy['notification_type']       = 'Order Reject';
        $dataNotifiy['is_read']                 = '0';
        $dataNotifiy['web_push']                 = '0';
        $dataNotifiy['reference_id']            = $insertOrder;
        $dataNotifiy['updated_at']            = datetime();
        $dataNotifiy['created_at']            = datetime();
        $dataNotifiy['notification_payload']    = json_encode($notif_msg);
        $dataNotifiy['notification_message']    = 'Your order with reference number '.$orderInfo->number.' has been rejected by seller.';

        $this->notification_model->save_notification($dataNotifiy);
    }

    //When order accept
    function acceptNotification($userDeviceTokens,$orderInfo,$user_id, $insertOrder){
        if(!empty($userDeviceTokens)){

            //Update badge count Code
            $badgeNotify = 0;
            $val = $this->database->getReference($this->dbname)->getChild($orderInfo->ordered_by_user_id)->getSnapshot();
            if($val->exists() == TRUE){

                $badgeCount = intval($val->getValue()['count']);
                $badgeCountValue = $badgeCount ? $badgeCount : 0;
                $badgeNotify = $badgeCountValue+1;

                $updateVal = $this->database->getReference($this->dbname)->getChild($orderInfo->ordered_by_user_id)->update(['count' => $badgeCountValue+1]); //update badge count

            }else{
                $updateVal = $this->database->getReference($this->dbname)->getChild($orderInfo->ordered_by_user_id)->update(['count' => 1]); //create new badge count
                $badgeNotify = 1;
            }
            //End Of Update badge count Code

            $tokenArr = array();
            foreach ($userDeviceTokens as $value) {

                if(!empty($orderInfo)){
                    //user address and contact info
                    $address_info = $this->common_model->getsingle(ORDER_ADDRESS,array('order_id'=>$orderInfo->orderID));
                }

                //$refrenID['token'] = $value->device_token; //device token for push notification
                $tokenArr[] = $value->device_token; //device token for push notification
                $notif_msg['type']          = 'Order Accept';
                $notif_msg['body']          = 'Order with reference number '.$orderInfo->number.' has been accepted by seller.';
                $notif_msg['title']         = 'Order Accept';
                $notif_msg['sound']         = 'default';
                $notif_msg['order_id']      = $insertOrder;
                $notif_msg['badge']      = $badgeNotify;

                $dataNotifiy['notification_title']      = 'Order Accept';
                $dataNotifiy['notification_by']         = $user_id;
                $dataNotifiy['notification_for']        = $value->user_id;
                $dataNotifiy['notification_type']       = 'Order Accept';
                $dataNotifiy['is_read']                 = '0';
                $dataNotifiy['web_push']                 = '0';
                $dataNotifiy['reference_id']            = $insertOrder;
                $dataNotifiy['updated_at']            = datetime();
                $dataNotifiy['created_at']            = datetime();
                $dataNotifiy['notification_payload']    = json_encode($notif_msg);
                $dataNotifiy['notification_message']    = 'Your order with reference number '.$orderInfo->number.' has been accepted by seller.';
                // if($value->device_type < 1){
                //     $dataNotifiy['web_push']                 = '1';
                // }
                //when reference id is not empty then notification done
                //if(!empty($refrenID['token'])){
                $this->payment_model->notification($dataNotifiy,$tokenArr,$notif_msg);
                //}
                $tokenArr = array();
                $buyerID = $value->user_id;
            }
        }

        $notif_msg['type']          = 'Order Accept';
        $notif_msg['body']          = 'Order with reference number '.$orderInfo->number.' has been accepted by seller.';
        $notif_msg['title']         = 'Order Accept';
        $notif_msg['sound']         = 'default';
        $notif_msg['order_id']      = $insertOrder;

        $dataNotifiy['notification_title']      = 'Order Accept';
        $dataNotifiy['notification_by']         = $user_id;
        $dataNotifiy['notification_for']        = $orderInfo->ordered_by_user_id;
        $dataNotifiy['notification_type']       = 'Order Accept';
        $dataNotifiy['is_read']                 = '0';
        $dataNotifiy['web_push']                 = '0';
        $dataNotifiy['reference_id']            = $insertOrder;
        $dataNotifiy['updated_at']            = datetime();
        $dataNotifiy['created_at']            = datetime();
        $dataNotifiy['notification_payload']    = json_encode($notif_msg);
        $dataNotifiy['notification_message']    = 'Your order with reference number '.$orderInfo->number.' has been accepted by seller.';

        $this->notification_model->save_notification($dataNotifiy);
    }

    //Order Detail seller side
    function detail_get($id){
        $this->check_service_auth();
        $user_id = $this->authData->userID;
        if(empty($id)){ //$id = orderID
            $this->error_response(get_response_message(158));
        }

        //Check orderID exist or not
        $isExistOrder = $this->common_model->get_field_value(ORDERS,array('orderID' => $id),'orderID');

        if($isExistOrder === false){  //If OrderID not exist
            $this->error_response('Order is not exist.');
        }
        $orderDetail = $this->order_model->get_order_detail($id,$user_id);
        $this->success_response(get_response_message(302), ['order_detail' => $orderDetail]);
    }

    //Order Detail buyer side
    function buyer_order_detail_get($id){
        $this->check_service_auth();
        $user_id = $this->authData->userID;
        if(empty($id)){ //$id = orderID
            $this->error_response(get_response_message(158));
        }

        //Check orderID exist or not
        $isExistOrder = $this->common_model->get_field_value(ORDERS,array('orderID' => $id),'orderID');

        if($isExistOrder === false){  //If OrderID not exist
            $this->error_response('This order is no longer available. Order rejected by seller.');
        }
        $orderDetail = $this->order_model->buyer_order_detail($id,$user_id);
        $this->success_response(get_response_message(302), ['order_detail' => $orderDetail]);
    }

}//END OF CLASS
