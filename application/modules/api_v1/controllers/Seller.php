<?php

/**
* Seller controller
* Handles selller web service request
* version: 1.0 ( 31-01-2020 )
*/
use Kreait\Firebase\Factory;
class Seller extends Common_Service_Controller {

    protected $database;
    protected $dbname = 'badge_count';  //databse key which will update

	public function __construct(){
        parent::__construct();

        $factory = (new Factory)->withServiceAccount(FCPATH.'firebase_json/'.getenv('FIREBASE_JSON_FILE'))->withDatabaseUri(getenv('FIREBASE_DB_URL'));
        $this->database = $factory->createDatabase();

        $this->load->model('seller_model'); //Load seller model handles all seller related DB queries
        $this->load->model('payment_model');
        $this->load->model('notification_model');
	}

    //Insert and Update action of seller business Information
    function business_info_put(){

        //Check user authentication and get user info from auth token
        $this->check_service_auth(); 
        $user_id = $this->authData->userID;
        $this->load->model('auth_model');
        $business_name = sanitize_input_text($this->put('business_name'));
        $license = sanitize_input_text($this->put('license'));
        $address = $this->put('address');
        $latitude = $this->put('latitude');
        $longitude = $this->put('longitude');
        $business_id = $this->uri->segment(5); //Business information ID, used when update business info

        //If id (BusinessId) varibale set but value is empty 
        $urlPosition = explode('/',$_SERVER['REQUEST_URI']);//get url and explode by /
        if(count($urlPosition) == '7'){   //Here ID is set so value is 7

            if(empty($urlPosition[6])){ //If ID is empty
                $this->error_response(get_response_message(134)); 
            }
        }

        //Get user type
        $userType = $this->common_model->get_field_value(USERS,array('userID' => $user_id),'user_type');
        
        if($userType != 'seller'){ //If user is not seller then return error
            $this->error_response(get_response_message(145)); 
        }

        if(empty($business_name)){ //If Business name is empty

            $this->error_response(get_response_message(131)); //error reponse
        }

        //Max and min length validation for business name
        $businessNameLength = strlen(trim($business_name));
        if (isset($business_name) && ($businessNameLength > 100 || $businessNameLength < 5)) {
           
            $this->error_response(get_response_message(132)); //error reponse
        }

        //If address is not empty then lat long is required
        if(!empty($address) && (empty($latitude) || empty($longitude))){ 

            $this->error_response(get_response_message(143)); //error reponse
        }

        //set insert, update data array
        $setData = array(
            'name' => $business_name,
            'license' => $license,
            'address' => $address,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'updated_at' => datetime(),
        );

        //If set ID Param and not empty then update
        if(isset($business_id) && !empty($business_id)){

            $update = $this->common_model->updateFields(SELLER_BUSINESS_INFO,$setData, array('businessInfoID' => $business_id, 'user_id' =>$user_id));
           
            if($update === TRUE){

                $businessInfo = $this->auth_model->seller_buisness_info($user_id); //Get Business Info
                
                $this->success_response(get_response_message(135),['business_info' => (object)$businessInfo]); //success response
            }

            $this->error_response(get_response_message(107),SERVER_ERROR,500); //error reponse
        }

        $userBusinessInfo = $this->common_model->getsingle(SELLER_BUSINESS_INFO, array('user_id' => $user_id));

        if(empty($userBusinessInfo)){
            //When business ID not set then insert as a new row
            $setData['user_id'] = $user_id;
            $setData['created_at'] = datetime();
            $insert_id = $this->common_model->insertData(SELLER_BUSINESS_INFO,$setData);

            if(!$insert_id){

                $this->error_response(get_response_message(107),SERVER_ERROR,500); //error reponse
            }

            $businessInfo = $this->auth_model->seller_buisness_info($user_id); //Get Business Info

            $this->success_response(get_response_message(133),['business_info' => (object)$businessInfo]);
        }

        $this->error_response(get_response_message(146)); //error reponse
    }

    // Seller profile
    function my_profile_get(){
        //Check user authentication and get user info from auth token
        $this->check_service_auth();
        $authToken = $this->get_bearer_token();  //get token
        $user_id = $this->authData->userID; //current user ID;
        $headerInfo = $this->request_headers; //Get header Info

        $this->load->model('auth_model');
        $userDetail = $this->auth_model->userInfo(array('userID' => $user_id, 'user_type' =>'seller', 'device_id' => $headerInfo['device-id']));

        $businessInfo = $this->auth_model->seller_buisness_info($user_id); //Get Business Info
        $seller_review = $this->seller_model->seller_rating($user_id); //Get Seller review Info

        if(!empty($userDetail) && !empty($businessInfo)){

            $userDetail->authtoken = $authToken;
            $this->success_response(get_response_message(302),['user_detail' => $userDetail,'business_info' => (object)$businessInfo, 'rating_info' => (object)$seller_review]);
        }

        $this->error_response(get_response_message(104)); //error reponse
    }

    //Update seller profile
    function update_profile_put(){
        //Check user authentication and get user info from auth token
        $this->check_service_auth(); 
        $user_id = $this->authData->userID;
        $headerInfo = $this->request_headers; //Get header Info
        $authToken = $this->get_bearer_token();  //get token

        $full_name = sanitize_input_text($this->put('full_name'));
        $business_name = sanitize_input_text($this->put('business_name'));
        $license = sanitize_input_text($this->put('license'));
        $address = $this->put('address');
        $latitude = $this->put('latitude');
        $longitude = $this->put('longitude');

        if(empty($full_name)){ //If Full name is empty

            $this->error_response(get_response_message(140)); //error reponse
        }

        //Max and min length validation for full name
        $fullNameLength = strlen(trim($full_name));
        if (isset($fullNameLength) && ($fullNameLength > 50 || $fullNameLength < 2)){
           
            $this->error_response(get_response_message(141)); //error reponse
        }

        if(empty($business_name)){ //If Business name is empty

            $this->error_response(get_response_message(131)); //error reponse
        }

        //Max and min length validation for business name
        $businessNameLength = strlen(trim($business_name));
        if (isset($business_name) && ($businessNameLength > 100 || $businessNameLength < 5)) {
           
            $this->error_response(get_response_message(132)); //error reponse
        }

        //If address is not empty then lat long is required
        if(!empty($address) && (empty($latitude) || empty($longitude))){ 

            $this->error_response(get_response_message(143)); //error reponse
        }
        if(isset($full_name) && $this->put('full_name') !== null)
            $updateData['full_name'] = $full_name;

        $updateData['updated_at'] = datetime();

        if(isset($business_name) && $this->put('business_name') !== null)
            $updateBusinessInfo['name'] = $business_name;

        if(isset($license) && $this->put('license') !== null)
            $updateBusinessInfo['license'] = $license;

        if(isset($address) && $this->put('address') !== null)
            $updateBusinessInfo['address'] = $address;

        if(isset($latitude) && $this->put('latitude') !== null)
            $updateBusinessInfo['latitude'] = $latitude;

        if(isset($longitude) && $this->put('longitude') !== null)
            $updateBusinessInfo['longitude'] = $longitude;

        $updateBusinessInfo['updated_at'] = datetime();

        $update = $this->common_model->updateFields(USERS,$updateData, array('userID' => $user_id));

        if($update === TRUE){ //If update successfully 

            $this->load->model('auth_model');
            $this->common_model->updateFields(SELLER_BUSINESS_INFO,$updateBusinessInfo, array('user_id' => $user_id));

            $userDetail = $this->auth_model->userInfo(array('userID' => $user_id, 'user_type' =>'seller', 'device_id' => $headerInfo['device-id']));

            $userDetail->authtoken = $authToken;

            $businessInfo = $this->auth_model->seller_buisness_info($user_id); //Get Business Info

            $this->success_response(get_response_message(142),['user_detail' => $userDetail,'business_info' => (object)$businessInfo]);
        }

        $this->error_response(get_response_message(107),SERVER_ERROR,500); //error reponse
    }

    //Update seller profile image
    function update_avatar_post(){
        //Check user authentication and get user info from auth token
        $this->check_service_auth(); 
        $user_id = $this->authData->userID;
        $headerInfo = $this->request_headers; //Get header Info
        $authToken = $this->get_bearer_token();  //get token
        
        $profile_image=NULL;
        if(!empty($_FILES['profile_picture']['name'])){

            $this->load->model('image_model'); //Load image model
            //if image not empty set it for user image 
            $upload_img = $this->image_model->upload_image('profile_picture', 'profile');
            //check for error
            if(array_key_exists("error",$upload_img) && !empty($upload_img['error'])){

                $this->error_response(strip_tags($upload_img['error'])); //error reponse
            }
            //check image name key exist
            if(array_key_exists("image_name", $upload_img)){
                $profile_image = $upload_img['image_name'];
            }

            $getAvatar = $this->common_model->get_field_value(USERS, array('userID' => $user_id),'avatar'); //get user previous image

            $update = $this->common_model->updateFields(USERS,array('avatar' =>$profile_image,'is_avatar_url' => '1'), array('userID' => $user_id));

            if($update === TRUE){

                $this->load->model('auth_model');
                if($getAvatar != NULL){

                    $imgPath = 'profile';
                    //Delete previous image of user when new image upload
                    $del = $this->image_model->delete_image($imgPath,$getAvatar);
                }

                $userDetail = $this->auth_model->userInfo(array('userID' => $user_id, 'user_type' =>'seller', 'device_id' => $headerInfo['device-id']));
                $userDetail->authtoken = $authToken;

                $businessInfo = $this->auth_model->seller_buisness_info($user_id); //Get Business Info
                $this->success_response(get_response_message(144),['user_detail' => $userDetail,'business_info' => (object)$businessInfo]);
            }

            $this->error_response(get_response_message(107),SERVER_ERROR,500); //error reponse
        }

        $this->error_response(get_response_message(147)); //error reponse
    }

    //Seller info for product detail page
    function seller_info_get(){

        $seller_id = $this->get('seller_id');

        if(empty($seller_id)){
            $this->error_response(get_response_message(158)); //error reponse
        }
       
        $seller_info = $this->seller_model->seller_info($seller_id);
        $seller_rating = $this->seller_model->seller_rating_review($seller_id);

        if(!empty($seller_info)){

            $this->success_response(get_response_message(302),['seller_info' => $seller_info,
                'rating' => ($seller_rating) ? $seller_rating : (object)[] ]);
        }

        $this->error_response(get_response_message(104)); //error reponse
    }

    //function for new order list
    function new_order_get(){
        $this->check_service_auth();
        $seller_id = $this->authData->userID;

        $data['limit'] = $this->get('limit');
        $data['offset'] = $this->get('offset');
        $data['searchTerm'] = $this->get('searchTerm');
        
        if(empty($this->get('limit'))){
            $data['limit'] = 20;
        }
        if(empty($this->get('offset'))){
           $data['offset'] = 0; 
        }

        $newOrderList = $this->seller_model->new_order_list($seller_id,$data);

        $this->success_response(get_response_message(302), $newOrderList);
    }//End of new order list

    //function for seller my order list
    function my_order_get(){
        $this->check_service_auth();
        $seller_id = $this->authData->userID;

        $data['limit'] = $this->get('limit');
        $data['offset'] = $this->get('offset');
        $data['searchTerm'] = $this->get('searchTerm');
        
        if(empty($this->get('limit'))){
            $data['limit'] = 20;
        }
        if(empty($this->get('offset'))){
           $data['offset'] = 0; 
        }

        $myOrderList = $this->seller_model->my_order_list($seller_id,$data);

        $this->success_response(get_response_message(302), $myOrderList);
    }//End of new order list

    //Change Order Status
    function change_status_patch($id){

        $this->check_service_auth(); // check authorization 
        $user_id = $this->authData->userID; // get user ID
        $status = $this->patch('status'); //0:Order Placed 1:Approved, 2:Packed, 3:Shipped, 4:Delivered, 5:Cancelled, 6:Refunded

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

        $update = $this->common_model->updateFields(ORDERS,array('current_status' => $status, 'updated_at' => datetime()), array('orderID' => $id));

        $insertData = array('order_id' => $id, 'order_status' => $status, 'updated_at' => datetime(), 'created_at' => datetime());

        $insertTracking = $this->common_model->insertData(ORDER_TRACKING,$insertData);

        //Manage notification code start
        //user information
        $orderInfo = $this->common_model->is_data_exists(ORDERS,array('orderID'=>$id));
        
        $userDeviceTokens = $this->payment_model->getAllDeviceToken(array('user_id'=>$orderInfo->ordered_by_user_id, 'device_type !='=> '3'));

        $this->statusNotification($userDeviceTokens,$orderInfo,$user_id,$id,$status);

        //manage notiifcation code end


        if(!$insertTracking){
            $this->error_response(get_response_message(107));

        }
        $tracking = $this->seller_model->get_tracking_detail($id);
        $this->success_response('Order status changed successfully.', ['tracking_status' => $tracking]);
    }

    //When order status change
    function statusNotification($userDeviceTokens,$orderInfo,$user_id,$insertOrder,$status){

        if($status == '0'){
           $status = 'Order Placed';
        }else if($status == '1'){
            $status = 'Approved';
        }else if($status == '2'){
            $status = 'Packed';
        }else if($status == '3'){
            $status = 'Shipped';
        }else if($status == '4'){
            $status = 'Delivered';
        }else if($status == '5'){
            $status = 'Cancelled';
        }else if($status == '6'){
            $status = 'Refunded';
        }
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
                $notif_msg['type']          = 'Order '.$status;
                $notif_msg['body']          = 'Order with reference number '.$orderInfo->number.' has been '.$status.' by seller.';
                $notif_msg['title']         = 'Order '.$status;
                $notif_msg['sound']         = 'default';
                $notif_msg['order_id']      = $insertOrder;
                $notif_msg['badge']      = $badgeNotify;

                $dataNotifiy['notification_title']      = 'Order '.$status;
                $dataNotifiy['notification_by']         = $user_id;
                $dataNotifiy['notification_for']        = $value->user_id;
                $dataNotifiy['notification_type']       = 'Order '.$status;
                $dataNotifiy['is_read']                 = '0';
                $dataNotifiy['web_push']                 = '0';
                $dataNotifiy['reference_id']            = $insertOrder;
                $dataNotifiy['updated_at']            = datetime();
                $dataNotifiy['created_at']            = datetime();
                $dataNotifiy['notification_payload']    = json_encode($notif_msg);
                $dataNotifiy['notification_message']    = 'Your order with reference number '.$orderInfo->number.' has been '.$status.' by seller.';
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

        $notif_msg['type']          = 'Order '.$status;
        $notif_msg['body']          = 'Order with reference number '.$orderInfo->number.' has been '.$status.' by seller.';
        $notif_msg['title']         = 'Order '.$status;
        $notif_msg['sound']         = 'default';
        $notif_msg['order_id']      = $insertOrder;

        $dataNotifiy['notification_title']      = 'Order '.$status;
        $dataNotifiy['notification_by']         = $user_id;
        $dataNotifiy['notification_for']        = $orderInfo->ordered_by_user_id;
        $dataNotifiy['notification_type']       = 'Order '.$status;
        $dataNotifiy['is_read']                 = '0';
        $dataNotifiy['web_push']                 = '0';
        $dataNotifiy['reference_id']            = $insertOrder;
        $dataNotifiy['updated_at']            = datetime();
        $dataNotifiy['created_at']            = datetime();
        $dataNotifiy['notification_payload']    = json_encode($notif_msg);
        $dataNotifiy['notification_message']    = 'Your order with reference number '.$orderInfo->number.' has been '.$status.' by seller.';

        $this->notification_model->save_notification($dataNotifiy);
    }

} //End Class