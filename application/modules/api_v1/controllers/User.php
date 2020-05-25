<?php

/**
* User controller
* Handles user web service request
* version: 1.0 ( 05-02-2020 )
*/
use Kreait\Firebase\Factory;
class User extends Common_Service_Controller {

    protected $database;
    protected $dbname = 'badge_count';  //databse key which will update

	public function __construct(){
        parent::__construct();

        $factory = (new Factory)->withServiceAccount(FCPATH.'firebase_json/'.getenv('FIREBASE_JSON_FILE'))->withDatabaseUri(getenv('FIREBASE_DB_URL'));
        $this->database = $factory->createDatabase();

        $this->load->model('user_model'); //Load user model handles all user (buyer) related DB queries
        $this->load->model('payment_model');
        $this->load->model('notification_model');
	}

    // Buyer user profile
    function my_profile_get(){
        //Check user authentication and get user info from auth token
        $this->check_service_auth();
        $user_id = $this->authData->userID; //current user ID;
        $headerInfo = $this->request_headers; //Get header Info 
        $authToken = $this->get_bearer_token();  //get token

        $this->load->model('auth_model');
        $userDetail = $this->auth_model->userInfo(array('userID' => $user_id, 'user_type' =>'buyer', 'device_id' => $headerInfo['device-id']));

        if(!empty($userDetail)){
            $userDetail->authtoken = $authToken;
            $this->success_response(get_response_message(302),['user_detail' => $userDetail,'business_info' => (object)[]]);
        }

        $this->error_response(get_response_message(104)); //error reponse
    }

    //Update buyer user profile
    function update_profile_put(){
        //Check user authentication and get user info from auth token
        $this->check_service_auth(); 
        $user_id = $this->authData->userID;
        $headerInfo = $this->request_headers; //Get header Info
        $authToken = $this->get_bearer_token();  //get token

        $full_name = sanitize_input_text($this->put('full_name'));
        $profile_address = sanitize_input_text($this->put('profile_address'));
        $profile_country_code = sanitize_input_text($this->put('profile_country_code'));
        if(empty($full_name)){ //If Full name is empty

            $this->error_response(get_response_message(140)); //error reponse
        }

        if(empty($profile_address)){ //If profile_address is empty

            $this->error_response('Address is required'); //error reponse
        }

        if(empty($profile_country_code)){ //If profile_country_code is empty

            $this->error_response('Country code is required'); //error reponse
        }

        //Max and min length validation for full name
        $fullNameLength = strlen(trim($full_name));
        if (isset($fullNameLength) && ($fullNameLength > 50 || $fullNameLength < 2)){
           
            $this->error_response(get_response_message(141)); //error reponse
        }
        if(isset($full_name) && $this->put('full_name') !== null)
            $updateData['full_name'] = $full_name;

        if(isset($profile_address) && $this->put('profile_address') !== null)
            $updateData['profile_address'] = $profile_address;

        if(isset($profile_country_code) && $this->put('profile_country_code') !== null)
            $updateData['profile_country_code'] = $profile_country_code;

        $updateData['updated_at'] = datetime();

        $update = $this->common_model->updateFields(USERS,$updateData, array('userID' => $user_id));

        if($update === TRUE){ //If update successfully 

            $this->load->model('auth_model');
            $userDetail = $this->auth_model->userInfo(array('userID' => $user_id, 'user_type' =>'buyer', 'device_id' => $headerInfo['device-id']));
            $userDetail->authtoken = $authToken;

            $this->success_response(get_response_message(142),['user_detail' => $userDetail,'business_info' => (object)[]]);
        }

        $this->error_response(get_response_message(107),SERVER_ERROR,500); //error reponse
    }

    //Update user (buyer) profile image
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

                $userDetail = $this->auth_model->userInfo(array('userID' => $user_id, 'user_type' =>'buyer', 'device_id' => $headerInfo['device-id']));

                $userDetail->authtoken = $authToken;
                
                $this->success_response(get_response_message(144),['user_detail' => $userDetail,'business_info' => (object)[]]);
            }

            $this->error_response(get_response_message(107),SERVER_ERROR,500); //error reponse
        }

        $this->error_response(get_response_message(147)); //error reponse
    }

    //Leave Feedback
    function feedback_post(){

        $this->check_service_auth();

        $user_id = $this->authData->userID;

        $this->load->library('smtp_email'); //load smtp library

        $this->form_validation->set_rules('feedback', 'Feedback','trim|required|min_length[5]|max_length[300]');

        //set response msg  for form validation
        if($this->form_validation->run() == FALSE){

            $this->error_response(strip_tags(validation_errors())); //error reponse
        }

        $data['user_id'] = $user_id;
        $data['description'] = sanitize_input_text($this->post('feedback'));
        $data['created_at'] = datetime();


        $to = getenv('TO_SEND_MAIL');
        $subject = SITE_NAME."- Feedback";
        $message = $data['description'];
        //send mail
        $check   =  $this->smtp_email->send_mail($to,$subject,$message);

        if($check !== TRUE){
            $this->error_response($check); //error reponse
        }

        $feedback_id = $this->common_model->insertData(FEEDBACKS,$data);

        $this->success_response(get_response_message(155));  
    }

    //Add address of user
    function address_post(){

        $this->check_service_auth();
        $user_id = $this->authData->userID;

        $this->form_validation->set_rules('name', 'Name','trim|required|min_length[2]|max_length[100]');
        $this->form_validation->set_rules('phone_number', 'Phone Number','trim|required|numeric|regex_match[/^[0-9]/]|min_length[5]');
        $this->form_validation->set_rules('house_number', 'House Number','trim|required|max_length[50]');
        $this->form_validation->set_rules('street', 'Street','trim|required');
        $this->form_validation->set_rules('city', 'City','trim|required|max_length[100]');
        $this->form_validation->set_rules('pincode', 'Pincode','trim|required|numeric|max_length[20]');
        $this->form_validation->set_rules('dial_code', 'Dial Code','trim|required');
        $this->form_validation->set_rules('country', 'Country','trim|required');

        if (!preg_match('/^(0|[0-9]\d*)$/',$this->post('phone_number'))){

            $this->error_response('Phone number is not valid.'); //error reponse
        }

        if (!preg_match('/^(0|[0-9]\d*)$/',$this->post('pincode'))){

            $this->error_response('Pin code is not valid.'); //error reponse
        }

        //set response msg  for form validation
        if($this->form_validation->run() == FALSE){
            $this->error_response(strip_tags(validation_errors()));
        }
 
        $name = sanitize_input_text($this->post('name')); // Person name on which item delivered
        $phone_number = sanitize_input_text($this->post('phone_number'));
        $house_number = sanitize_input_text($this->post('house_number'));
        $street = sanitize_input_text($this->post('street'));
        $city = sanitize_input_text($this->post('city'));
        $pincode = sanitize_input_text($this->post('pincode'));
        $country_code = sanitize_input_text($this->post('country_code')); //IN, US
        $dial_code = sanitize_input_text($this->post('dial_code')); //+91, +97
        $country = sanitize_input_text($this->post('country')); 
        //$is_default = $this->post('is_default');  //Making default address

        //check user profile address is empty or not
        $isProfileAddress = $this->common_model->getsingle(USERS,array('userID' =>$user_id));

        if($isProfileAddress->profile_address == null && $isProfileAddress->profile_country_code == null){

            $updateProfileAddress =  array(
                'profile_address' => $house_number.','.$street.','.$city.','.$country,
                'profile_country_code' => $country_code
            );

            $this->common_model->updateFields(USERS,$updateProfileAddress,array('userID' =>$user_id));  //Update field for user profile address
        }

        //check user has added address before
        $isAddress = $this->common_model->getsingle(USER_ADDRESS,array('user_id' =>$user_id));

        $insertData = array(
            'user_id' => $user_id,
            'name' => $name,
            'phone_dial_code' => $dial_code,
            'country_code' => $country_code,
            'mobile_number' => $phone_number,
            'house_number' => $house_number,
            'locality' => $street,
            'city' => $city,
            'zip_code' => $pincode,
            'country' => $country,
            'updated_at' => datetime(),
            'created_at' => datetime(),
        );
        if(empty($isAddress)){
            $insertData['is_default'] = 1; //make default address if user add first address
        }
        
        $addressID = $this->common_model->insertData(USER_ADDRESS,$insertData);
        if(!empty($addressID)){
            $address= $this->common_model->getsingle(USER_ADDRESS,array('addressID' =>$addressID));

            $this->success_response(get_response_message(161),['address_list'=> $address]);
        }

        $this->error_response(get_response_message(107),SERVER_ERROR,500); //error reponse
    }

    // User all address
    function address_get(){
        $this->check_service_auth();
        $user_id = $this->authData->userID;

        $addressList = $this->user_model->addressList($user_id);  

        if(!$addressList){ //check for address avalability

            $this->success_response(get_response_message(106));
        }
      
        $this->success_response(get_response_message(302),['address_list' => $addressList]);
    }

    //Delete address
    function address_delete($id){

        $this->check_service_auth();
        $user_id = $this->authData->userID;

        if(empty($id)){
            $this->error_response(get_response_message(158));   
        }

        $address_id = $id;

        $exist = $this->checkAddressExist($address_id); //Check address exist or not
        if($exist === FALSE){
            
            $this->error_response(get_response_message(162)); //error reponse
        }

        $delete = $this->common_model->deleteData(USER_ADDRESS,array('addressID' => $address_id));

        if($delete === TRUE){

            $addressList = $this->user_model->addressList($user_id);
            $this->success_response(get_response_message(163),['address_list' => $addressList]);
        }

        $this->error_response(get_response_message(107),SERVER_ERROR,500); //error reponse
    }

    //Edit address
    function address_put($id){
        $this->check_service_auth();
        $user_id = $this->authData->userID;

        if(empty($id)){
            $this->error_response(get_response_message(158));   
        }

        $address_id = $id;
        $exist = $this->checkAddressExist($address_id); //Check address exist or not
        if($exist === FALSE){
            
            $this->error_response(get_response_message(162)); //error reponse
        }

        $name = sanitize_input_text($this->put('name'));
        $phone_number = sanitize_input_text($this->put('phone_number'));
        $house_number = sanitize_input_text($this->put('house_number'));
        $street = sanitize_input_text($this->put('street'));
        $city = sanitize_input_text($this->put('city'));
        $pincode = sanitize_input_text($this->put('pincode'));
        $dial_code = sanitize_input_text($this->put('dial_code'));
        $country_code = sanitize_input_text($this->put('country_code')); //IN, US
        $country = sanitize_input_text($this->put('country')); 
        if(empty($name)){ //If name is empty

            $this->error_response('Name is required.'); //error reponse
        }

        //Max and min length validation for name
        $nameLength = strlen(trim($name));
        if (isset($nameLength) && ($nameLength > 100 || $nameLength < 2)){
           
            $this->error_response('Name should be between of 2 and 100 characters.'); //error reponse
        }

        if(empty($phone_number)){ //If phone munber  is empty

            $this->error_response('Phone number is required.'); //error reponse
        }

        if (!preg_match('/^(0|[0-9]\d*)$/', $phone_number)){

            $this->error_response('Phone number is not valid.'); //error reponse
        }

        if(empty($house_number)){ //If house munber  is empty

            $this->error_response('House number is required.'); //error reponse
        }
        if(empty($street)){ //If street munber  is empty

            $this->error_response('Street is required.'); //error reponse
        }
        if(empty($city)){ //If city is empty

            $this->error_response('City is required.'); //error reponse
        }
        if(empty($pincode)){ //If pin code  is empty

            $this->error_response('Pincode is required.'); //error reponse
        }

        if (!preg_match('/^(0|[0-9]\d*)$/',$pincode)){

            $this->error_response('Pin code is not valid.'); //error reponse
        }

        if(empty($dial_code)){ //If dial code  is empty

            $this->error_response('Dial code is required.'); //error reponse
        }
 

        $updateData = array(
            'user_id' => $user_id,
            'name' => $name,
            'phone_dial_code' => $dial_code,
            'country_code' => $country_code,
            'mobile_number' => $phone_number,
            'house_number' => $house_number,
            'locality' => $street,
            'city' => $city,
            'zip_code' => $pincode,
            'country' => $country,
            'updated_at' => datetime(),
        );

        $update = $this->common_model->updateFields(USER_ADDRESS,$updateData, array('addressID' =>$address_id, 'user_id' => $user_id));

        if($update === TRUE){
            $address= $this->common_model->getsingle(USER_ADDRESS,array('addressID' =>$address_id));

            $this->success_response(get_response_message(164),['address'=> $address]);
        }

        $this->error_response(get_response_message(107),SERVER_ERROR,500); //error reponse
    }

    private function checkAddressExist($addressID){
        $address = $this->common_model->getsingle(USER_ADDRESS,array('addressID' => $addressID));

        if(empty($address)){  //If product not exist
            return FALSE;
        }
        return TRUE;
    }

    //Add and remove product in wishlist
    function wishlist_put(){

        $this->check_service_auth();
        $user_id = $this->authData->userID;

        if(empty($this->put('product_id'))){
            $this->error_response(get_response_message(158)); //error reponse
        }
        $product_id = $this->put('product_id');
        
        $getWishlist = $this->common_model->getsingle(USER_WISHLIST,array('user_id'=> $user_id, 'product_id' => $product_id)); //check product already in wishlist
    
        if(empty($getWishlist)){ //If product not in wishlist then add
            $dataInsert['user_id'] = $user_id;
            $dataInsert['product_id'] = $product_id;
            $dataInsert['created_at'] = datetime();

            $insertWishlist = $this->common_model->insertData(USER_WISHLIST,$dataInsert);

            $this->success_response(get_response_message(166),['is_wishlist'=> "1"]);
            
        }else{ //else remove from wishlist
            $removeWishlist = $this->common_model->deleteData(USER_WISHLIST, array('user_id' => $user_id, 'product_id' => $product_id));

            if($removeWishlist === true){

                $this->success_response(get_response_message(167),['is_wishlist'=> "0"]);
            }
        }
    }

    //Remove all product from wishlist
    function clear_wishlist_delete(){

        $this->check_service_auth();
        $user_id = $this->authData->userID;

        $getWishlist = $this->common_model->getsingle(USER_WISHLIST,array('user_id'=> $user_id)); //check product added or not in wishlist

        if(empty($getWishlist)){ //If product not in wishlist then add

            $this->error_response('No any product found in your wishlist'); //error reponse
        }

        //Remove from wishlist
        $removeWishlist = $this->common_model->deleteData(USER_WISHLIST, array('user_id' => $user_id));

        if($removeWishlist === true){

            $this->success_response('All Products successfully removed from your wishlist.');
        }  
    }

    //User wishlist Listing
    function wishlist_list_get(){
        $this->check_service_auth();
        $user_id = $this->authData->userID;

        $offset = $this->get('offset');
        $limit = $this->get('limit');
        
        if(empty($limit)){
            $limit = 20;
        }
        if(empty($offset)){
           $offset = 0; 
        }

        $getUserWishlist = $this->user_model->userWishlist($user_id,$limit,$offset);  

        if($getUserWishlist['total_records'] == 0){ //check for wishlist avalability
            $this->success_response(get_response_message(106), ['data_found' => false]);  
        }
        
        $this->success_response(get_response_message(302),$getUserWishlist);  
    }

    //fundtion for user order list
    function my_order_get(){
        $this->check_service_auth();
        $user_id = $this->authData->userID;
        $orderList = $this->user_model->get_order_list($user_id);

        if(empty($orderList)){

            $this->success_response(get_response_message(106),['data_found' => false]);
        }

        $this->success_response(get_response_message(302),['data_found' => true, 'order_list' => $orderList]);
    }//End of order list

    //Rating and Review by buyer to seller
    function rating_review_post(){
        $this->check_service_auth();
        $user_id = $this->authData->userID;
        $full_name = $this->authData->full_name;
        $this->form_validation->set_rules('rating', 'Rating','trim|required');
        $this->form_validation->set_rules('rating_for', 'Rating For','trim|required|numeric');
        $this->form_validation->set_rules('review', 'Review','trim');
        $this->form_validation->set_rules('order_id', 'Order ID','trim|required|numeric');

        $orderID = $this->input->post('order_id');

        //set response msg  for form validation
        if($this->form_validation->run() == FALSE){

            $this->error_response(strip_tags(validation_errors())); //error reponse
        } 

        $datainsert = array(
            'rating_by' => $user_id, 
            'rating_for' => $this->input->post('rating_for'),
            'rating' => $this->input->post('rating'),
            'review' => ($this->input->post('review')) ? $this->input->post('review') : NULL,
            'updated_at' => datetime(),
            'created_at' => datetime(),
        );

        $insertReview = $this->common_model->insertData(RATINGS, $datainsert);

        if(!$insertReview){
            $this->error_response(get_response_message(107)); //error reponse
        }

        //Manage notification code start
        //user information
        $ratingInfo = $this->common_model->is_data_exists(RATINGS,array('ratingID'=>$insertReview));
        
        $userDeviceTokens = $this->payment_model->getAllDeviceToken(array('user_id'=>$this->input->post('rating_for'), 'device_type !='=> '3'));

        $this->reviewNotification($userDeviceTokens,$user_id,$full_name,$insertReview,$ratingInfo,$orderID);

        //manage notiifcation code end

        $insertOrderRating = array(
            'rating_by' => $user_id, 
            'rating_for' => $this->input->post('rating_for'),
            'order_id' => $this->input->post('order_id'),
            'rating' => $this->input->post('rating'),
            'review' => ($this->input->post('review')) ? $this->input->post('review') : NULL,
            'updated_at' => datetime(),
            'created_at' => datetime(),
        );

        $insertOrderRating = $this->common_model->insertData(ORDER_RATINGS, $insertOrderRating);
        $this->success_response(get_response_message(181));
    }

    //When buyer give review to seller
    function reviewNotification($userDeviceTokens,$user_id,$full_name,$insertReview,$ratingInfo,$orderID){

        if(!empty($userDeviceTokens)){

            //Update badge count Code
            $badgeNotify = 0;
            $val = $this->database->getReference($this->dbname)->getChild($ratingInfo->rating_for)->getSnapshot();
            if($val->exists() == TRUE){

                $badgeCount = intval($val->getValue()['count']);
                $badgeCountValue = $badgeCount ? $badgeCount : 0;
                $badgeNotify = $badgeCountValue+1;

                $updateVal = $this->database->getReference($this->dbname)->getChild($ratingInfo->rating_for)->update(['count' => $badgeCountValue+1]); //update badge count

            }else{
                $updateVal = $this->database->getReference($this->dbname)->getChild($ratingInfo->rating_for)->update(['count' => 1]); //create new badge count
                $badgeNotify = 1;
            }
            //End Of Update badge count Code

            $tokenArr = array();
            foreach ($userDeviceTokens as $value) {

                //$refrenID['token'] = $value->device_token; //device token for push notification
                $tokenArr[] = $value->device_token; //device token for push notification
                $notif_msg['type']          = 'Seller Review';
                $notif_msg['body']          = 'You have new review by '.$full_name;
                $notif_msg['title']         = 'Got New Review';
                $notif_msg['sound']         = 'default';
                $notif_msg['order_id']      = $orderID;  //Review ID
                $notif_msg['badge']      = $badgeNotify;

                $dataNotifiy['notification_title']      = 'Got New Review';
                $dataNotifiy['notification_by']         = $user_id;
                $dataNotifiy['notification_for']        = $value->user_id;
                $dataNotifiy['notification_type']       = 'Seller Review';
                $dataNotifiy['is_read']                 = '0';
                $dataNotifiy['web_push']                 = '0';
                $dataNotifiy['reference_id']            = $orderID;
                $dataNotifiy['updated_at']            = datetime();
                $dataNotifiy['created_at']            = datetime();
                $dataNotifiy['notification_payload']    = json_encode($notif_msg);
                $dataNotifiy['notification_message']    = 'You have new review by '.$full_name;
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

        $notif_msg['type']          = 'Seller Review';
        $notif_msg['body']          = 'You have new review by '.$full_name;
        $notif_msg['title']         = 'Got New Review';
        $notif_msg['sound']         = 'default';
        $notif_msg['order_id']      = $orderID;

        $dataNotifiy['notification_title']      = 'Got New Review';
        $dataNotifiy['notification_by']         = $user_id;
        $dataNotifiy['notification_for']        = $ratingInfo->rating_for;
        $dataNotifiy['notification_type']       = 'Seller Review';
        $dataNotifiy['is_read']                 = '0';
        $dataNotifiy['web_push']                 = '0';
        $dataNotifiy['reference_id']            = $orderID;
        $dataNotifiy['updated_at']            = datetime();
        $dataNotifiy['created_at']            = datetime();
        $dataNotifiy['notification_payload']    = json_encode($notif_msg);
        $dataNotifiy['notification_message']    = 'You have new review by '.$full_name;

        $this->notification_model->save_notification($dataNotifiy);
    }

} //End Class