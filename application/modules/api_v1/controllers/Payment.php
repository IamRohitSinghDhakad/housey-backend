<?php
use Kreait\Firebase\Factory;
class Payment extends Common_Service_Controller {

    protected $database;
    protected $dbname = 'badge_count';  //databse key which will update

    public function __construct(){
        parent::__construct();

        $factory = (new Factory)->withServiceAccount(FCPATH.'firebase_json/'.getenv('FIREBASE_JSON_FILE'))->withDatabaseUri(getenv('FIREBASE_DB_URL'));
        $this->database = $factory->createDatabase();

        $this->load->model('payment_model');
        $this->load->model('order_model');
        $this->load->model('notification_model');
        $this->load->library('stripe');
    }

    // This code is for add card in stripe website only
    function add_card_post(){
        $this->check_service_auth(); //check authentication
        $stripe_customer_id = $this->authData->stripe_customer_id;

        $sourceId  = $this->post('sourceId');
        $this->form_validation->set_rules('sourceId', 'Source Id','trim|required');

        //set response msg  for form validation
        if($this->form_validation->run() == FALSE){

            $this->error_response(strip_tags(validation_errors())); //error reponse
        }
        $cardId = $this->stripe->create_card($stripe_customer_id,$sourceId );

        if(!$cardId['status']){
            $this->error_response($cardId['message']); //error reponse

        }else if($cardId['status'] == 1){
            $this->success_response($cardId['message'],['card_detail' => $cardId['data']]);
        }
    }

    // This code is for list all card in stripe
    function card_get(){
        $this->check_service_auth(); //check authentication
        $user_id = $this->authData->userID;
        $card_list = $this->payment_model->getAllCards($user_id);
        if(!empty($card_list)){
            $this->success_response(get_response_message(302), ['card_list' => $card_list]);
        }

        $this->success_response(get_response_message(106));
    }

    // This code is for delete card in stripe
    public function card_delete($id) {

        $this->check_service_auth(); //check authentication
        $stripe_customer_id = $this->authData->stripe_customer_id;

        if(empty($id)){
            $this->error_response(get_response_message(158)); //error reponse
        }

        $card_id = $id;

        $isExits = $this->payment_model->getCardId($card_id); // is exits cardId

        if(!empty($isExits)) {

            if( $isExits->is_default == 1) { //If default card , It will not delete

                $this->error_response(get_response_message(173)); // error response 
            }

            $delete = $this->stripe->delete_card($stripe_customer_id,$isExits->stripe_card_id );

            if($delete['status']==true){

                //delete card from DB
                $where = array('cardID'=>$card_id);  
                $this->common_model->deleteData(CARDS,$where); // delete data 
                $this->success_response(get_response_message(174)); // sucess response 
            }
        } 

        $this->error_response(get_response_message('ID not exist.')); //  id not 
    }

    //card save in database
    public function card_post() {
        $this->check_service_auth();
        $user_id = $this->authData->userID;

        $this->form_validation->set_rules('stripe_card_id','Stripe_card_id','required');
        $this->form_validation->set_rules('card_holder_name','FullName','required');
        $this->form_validation->set_rules('card_last_4_digits','Card_last_4_digit id ','required|min_length[4]|max_length[4]');
        $this->form_validation->set_rules('card_expiry_month','Card_expiry_month','required|numeric');
        $this->form_validation->set_rules('card_expiry_year','Card_expiry_year','required|numeric');
        $this->form_validation->set_rules('card_brand_type','Card_brand_type ','required');

        if($this->form_validation->run() == FALSE) {

            $this->error_response(strip_tags(validation_errors())); //error reponse
        }

        $data['user_id'] = $user_id;
        $data['stripe_card_id'] = $this->post('stripe_card_id');
        $data['card_holder_name'] = $this->post('card_holder_name');
        $data['card_last_4_digits'] = $this->post('card_last_4_digits');
        $data['card_expiry_month'] = $this->post('card_expiry_month');
        $data['card_expiry_year'] = $this->post('card_expiry_year');
        $data['card_brand_type'] = $this->post('card_brand_type');
        $data['created_at'] = datetime();
        $where = array('stripe_card_id'=> $data['stripe_card_id']);
        $isExist = $this->common_model->get_field_value(CARDS, $where,'stripe_card_id');

        if($isExist){
            $this->error_response(get_response_message(171));

        }
        $isExist = $this->payment_model->getDefault($user_id);
        if(empty($isExist)){
            $data['is_default'] = 1;
        } 
        $result = $this->common_model->insertData(CARDS,$data);

        $this->success_response(get_response_message(170), ['card_info' =>$data]); // success response
    }

    //create customer id on stripe
    public function create_customer_put() {

        $this->check_service_auth(); // check authorization
        $user_id = $this->authData->userID; // get userID

        if($this->authData->stripe_customer_id != '') { // check stripe id

            $this->error_response(get_response_message(605),'Invalid ID'); // erroe response
        }
        $info = $this->stripe->create_customer(); // call stripe function

        if($info['status']==true){

            $customerId= $info['data']['id'];
            $where = array('userID'=>$user_id);
            $data = array('stripe_customer_id'=>$customerId);
            $response = $this->common_model->updateFields(USERS, $data, $where); // update stripe customer id

            $this->success_response(get_response_message(201), ['stripe_customer_id' => $customerId]); // success response
        }

        $this->error_response($info['message']); // error response
    }

    //make default card
    public function card_patch($id) {

        $this->check_service_auth(); // check authorization 
        $user_id = $this->authData->userID; // get user Id
        $stripe_customer_id = $this->authData->stripe_customer_id; // get stripe customer Id 

        $status = $this->patch('status'); //There is no use of this only for website it is added. 

        if(empty($id)){ //$id = cardID
            $this->error_response(get_response_message(158));
        }

        $isExist = $this->payment_model->getCard($id);
        
        if(!empty($isExist)) {

            $default = $this->stripe->default_card($stripe_customer_id,$id );

            if($default['status']==true){

                $where = array('user_id'=> $isExist->user_id  ,'is_default'=> 1 );
                $data  = array('is_default'=> 0);
                $response  = $this->common_model->updateFields(CARDS, $data, $where);

                $where1 = array('stripe_card_id'=> $isExist->stripe_card_id);
                $data1 = array('is_default'=> 1);
                $this->common_model->updateFields(CARDS, $data1, $where1);
      
                $this->success_response(get_response_message(172));
            }

            $this->error_response($default['message']); // error response
        }
    } 

    //function for payment
    function payment_post(){
        $this->check_service_auth();
        $user_id = $this->authData->userID; //userID

        $headerInfo = $this->request_headers; //Get header Info

        $this->form_validation->set_rules('payment_mode', 'Payment mode','trim|required');
        $this->form_validation->set_rules('shipping_address_id', 'Shipping address','trim|required');
        $this->form_validation->set_rules('total_amount', 'Total Amount','trim|required|numeric');
        $this->form_validation->set_rules('subtotal_amount', 'Subtotal Amount','trim|required|numeric');
        $this->form_validation->set_rules('shipping_charges', 'Shipping Charge','trim|required|numeric');
        $this->form_validation->set_rules('tax_percent', 'Tax Percent','trim|required');
        $this->form_validation->set_rules('tax_amount', 'Tax Amount','trim|required');
        //$this->form_validation->set_rules('commission_percent', 'Commission Percent','trim|required');
        //$this->form_validation->set_rules('commission_amount', 'Commission Amount','trim|required');
        $this->form_validation->set_rules('is_offer', 'Offer key','trim|numeric'); // 0: No offer Item Payment,  1: payment for offer item

        //set response msg  for form validation
        if($this->form_validation->run() == FALSE){

            $this->error_response(strip_tags(validation_errors())); //error reponse
        }
        //data for create order
        $shipping_add_id = $this->post('shipping_address_id');
        $payment_mode = $this->post('payment_mode');
        $total_amount = $this->post('total_amount');
        $subtotal_amount = $this->post('subtotal_amount');
        $shipping_charges = $this->post('shipping_charges');

        //getting tax percent value
        $tax_value = $this->post('tax_percent');
        $is_offer = $this->post('is_offer');
        //$taxAmount = $this->post('tax_amount');

        //getting commission percent value
        $commission_value = $this->common_model->get_field_value(SETTING_OPTIONS, array('option_name' => 'commission_percent'), 'option_value');  //get commission percent from setting table

        //Payment for offer Item 
        if($is_offer == '1'){

            $this->offerPayment($user_id, $shipping_add_id, $payment_mode, $total_amount, $subtotal_amount, $shipping_charges, $tax_value, $commission_value);

        }else{ // For cart Item payment

            //getting information about cart items
            $cartItems = $this->common_model->GetJoinRecord(CART_ITEMS, 'product_id', PRODUCTS, 'productID',$field_val='',array('user_id'=>$user_id),$group_by='',$order_fld='',$order_type='', $limit = '', $offset = '');

            //getting information about shipping address
            $shippingAddData = $this->common_model->is_data_exists(USER_ADDRESS,array('addressID'=>$shipping_add_id));

            //condition for empty cart
            if(empty($cartItems) ){
                $this->error_response('Your cart is empty, Please add to cart first.'); // error response
            }

            //condition for empty address
            if(empty($shippingAddData)){
                $this->error_response('Invalid Shipping address, Please choose a valid address.'); // error response
            }

            $seller = array();
            foreach ($cartItems as $cartItem) {  //Group cart item by seller ID
                $seller[$cartItem->seller_id][] = $cartItem;
            }
            $responseArr = array();
            foreach($seller as $seller_id){
                //for calculate total item amount
                $item_total = 0;
                $regular_price = 0;
                $sale_price = 0;

                foreach ($seller_id as $key => $value) {
                    
                    //condition for sale price
                    if($value->sale_price != 0.00 ){
                        $sale_price_quantity = $value->sale_price*$value->quantity;
                        $sale_price +=$sale_price_quantity; 
                    }else{
                        $regular_price_quantity = $value->regular_price*$value->quantity;
                        $regular_price +=$regular_price_quantity;    
                    }
                    $shipping = $value->shipping_charge;
                    $sellerID = $value->seller_id;
                }

                $subtotal= strval($sale_price + $regular_price);
                $taxAmount = ($subtotal * $tax_value)/100;

                $commissionAmount = ($subtotal * $commission_value)/100;  //get commission amount from itemtotal


                //create random number for order ID
                $rand = $this->randomString();
                $orderData['number'] = $rand;
                $orderData['seller_id'] = $sellerID;
                $orderData['ordered_by_user_id'] = $user_id; //done
                $orderData['current_status'] = 0; //0:Order Placed 1:Processing, 2:On Hold, 3:Shipped, 4:Delivered, 5:Cancelled, 6:Refunded
                $orderData['item_total'] = $subtotal;//done
                $orderData['tax_percentage'] = $tax_value;//tax percent value
                $orderData['commission_percentage'] = $commission_value;//commission percent value
                $orderData['grand_total'] = $subtotal + $taxAmount + $shipping;//done
                $orderData['tax_amount'] = $taxAmount; // formula:- taxpercent of Total items price 
                $orderData['commission_amount'] = $commissionAmount; // formula:- commissionpercent of item total price 
                $orderData['shipping_price'] = $shipping;//done
                $orderData['payment_mode'] = $payment_mode;
                $orderData['created_at'] = $orderData['updated_at']  = datetime();
                $orderData['payment_status'] = 1; //0:Pending, 1:Paid(Received), 2:Failed

                //If payment mode is stripe then condition true
                if($this->post('payment_mode')==2){
                    
                    $user_card = $this->common_model->getsingle(CARDS, array('user_id' => $user_id,'is_default' => 1));

                    if(empty($user_card)){
                        $this->error_response('Please add card first to do payment.'); // error response
                    }
                    /*=============== Data for create payment ==================*/
                    $customerId = $this->authData->stripe_customer_id;
                    $charge['customer_id'] = $customerId;
                    $charge['source'] = $user_card->stripe_card_id;
                    $charge['amount'] = $total_amount;
                    $charge['currency'] = 'usd';

                    $stripe = $this->stripe->create_charge($charge);
                    if($stripe['status'] == false){
                        $this->error_response($stripe['message']); // error response
                    }

                    $insertOrder = $this->common_model->insertData(ORDERS,$orderData);
                }else{
                    $insertOrder = $this->common_model->insertData(ORDERS,$orderData);
                }

                if(!$insertOrder){
                    $this->error_response('Order not Placed successfully.'); // error response
                }

                //Save data for order tracking status
                $OrderTrackingStatus['order_id'] = $insertOrder;
                $OrderTrackingStatus['order_status'] = 0;
                $OrderTrackingStatus['created_at'] = $OrderTrackingStatus['updated_at']  = datetime();

                //Data insert in order tracking table
                $insertOrderTracking = $this->common_model->insertData(ORDER_TRACKING,$OrderTrackingStatus);

                //Save data for order address
                $orderAddress['order_id'] = $insertOrder;
                $orderAddress['name'] = $shippingAddData->name;
                $orderAddress['phone_dial_code'] = $shippingAddData->phone_dial_code;
                $orderAddress['country_code'] = $shippingAddData->country_code;
                $orderAddress['mobile_number'] = $shippingAddData->mobile_number;
                $orderAddress['house_number'] = $shippingAddData->house_number;
                $orderAddress['locality'] = $shippingAddData->locality;
                $orderAddress['city'] = $shippingAddData->city;
                $orderAddress['zip_code'] = $shippingAddData->zip_code;
                $orderAddress['country'] = $shippingAddData->country;
                $orderAddress['updated_at'] = $orderAddress['created_at'] = datetime();

                $insertOrderAddress = $this->common_model->insertData(ORDER_ADDRESS,$orderAddress);


                //Manage notification code start
                //user information
                $userDeviceTokens = $this->payment_model->getAllDeviceToken(array('user_id'=>$sellerID, 'device_type !='=> '3'));

                $orderInfo = $this->common_model->is_data_exists(ORDERS,array('orderID'=>$insertOrder));

                $this->orderNotification($userDeviceTokens,$orderInfo,$user_id,$insertOrder);

                //manage notiifcation code end

                $send['order_number'] = $rand;
                $send['order_id'] = $insertOrder;
                $send['total_amount'] = $subtotal + $taxAmount + $shipping;
                $send['currency_code'] = getenv('CURRENCY_CODE');
                $send['currency_symbol'] = getenv('CURRENCY_SYMB');
                
                array_push($responseArr,$send);

                $url_image = getenv('AWS_CDN_PRODUCT_IMG_PATH');
                //Data for insert item
                foreach ($seller_id as $key => $sellerVal) {
                    //condition for sale price
                    $orderItems['order_id'] = $insertOrder; 
                    $orderItems['variant_value_id'] = $sellerVal->variant_value_id; 
                    $orderItems['product_id'] = $sellerVal->product_id; 
                    $orderItems['item_quantity'] = $sellerVal->quantity; 
                    $orderItems['updated_at'] = $orderItems['created_at'] = datetime(); 
                    if($sellerVal->sale_price != 0.00){
                        $orderItems['item_price'] = $sellerVal->sale_price; 
                    }else{
                        $orderItems['item_price'] = $sellerVal->regular_price; 
                    }

                    $insertOrderItem = $this->common_model->insertData(ORDER_ITEMS,$orderItems);
                    $delete = $this->common_model->deleteData(CART_ITEMS,array('cartItemID'=>$sellerVal->cartItemID)); 

                    //Product json update in order item
                    $product_json = $this->order_model->get_product_detail($insertOrder);

                    $keys = array_keys($product_json); 
                    $x = $product_json[$keys[count($keys)-1]];
                    
                    $updateItems['order_info_json'] = json_encode($x);
                    $updateItems['updated_at'] = datetime();
                    $updateOrderItem = $this->common_model->updateFields(ORDER_ITEMS,$updateItems,array('orderItemID' => $insertOrderItem));
                    //End Of product json update in order item
                }

                $getSellerOrderCount = $this->payment_model->sellerOrderCount($sellerID);

                if($getSellerOrderCount->sellCount >= 10){
                    $this->common_model->updateFields(USERS, array('is_verified' =>1), array('userID' =>$sellerID, 'user_type' => 'seller')); //Seller is verified Here
                }
            }

            if($this->post('payment_mode')==2){
                
                $this->saveOrderPayment($user_id,$insertOrder,$total_amount,$subtotal_amount,$shipping_charges,$payment_mode,$stripe);  //Save data in order payment and order payment transaction table
            }
           
            $this->success_response(get_response_message(175), ['order_detail' => $responseArr]);
        }

    }//end of function payment


    //offer Item payment
    function offerPayment($user_id, $shipping_add_id, $payment_mode, $total_amount, $subtotal_amount, $shipping_charges, $tax_value, $commission_value){

        //getting information about offer items
        $offerItems = $this->common_model->GetJoinRecord(OFFER_ITEMS, 'product_id', PRODUCTS, 'productID',$field_val='',array('buyer_id'=>$user_id),$group_by='',$order_fld='',$order_type='', $limit = '', $offset = '');

        //getting information about shipping address
        $shippingAddData = $this->common_model->is_data_exists(USER_ADDRESS,array('addressID'=>$shipping_add_id));

        //condition for empty address
        if(empty($shippingAddData)){
            $this->error_response('Invalid Shipping address, Please choose a valid address.'); // error response
        }

        $responseArr = array();
        foreach ($offerItems as $cartItem) {  

            $offer_price = 0;
            if($cartItem->product_offer_price != 0.00 ){
                $price_quantity = $cartItem->product_offer_price*$cartItem->quantity;
                $offer_price +=$price_quantity; 
            }
            $sellerID = $cartItem->seller_id;
        }

        
        $subtotal= strval($offer_price);
        $shipping = $shipping_charges;
        $taxAmount = ($subtotal * $tax_value)/100;

        $commissionAmount = ($subtotal * $commission_value)/100;  //get commission amount from itemtotal

        //create random number for order ID
        $rand = $this->randomString();
        $orderData['number'] = $rand;
        $orderData['seller_id'] = $sellerID;
        $orderData['ordered_by_user_id'] = $user_id; //done
        $orderData['current_status'] = 0; //0:Order Placed 1:Processing, 2:On Hold, 3:Shipped, 4:Delivered, 5:Cancelled, 6:Refunded
        $orderData['item_total'] = $subtotal;//done
        $orderData['tax_percentage'] = $tax_value;//tax percent value
        $orderData['commission_percentage'] = $commission_value;//commission percent value
        $orderData['grand_total'] = $subtotal + $taxAmount + $shipping;//done
        $orderData['tax_amount'] = $taxAmount; // formula:- taxpercent of Total items price 
        $orderData['commission_amount'] = $commissionAmount; // formula:- commissionpercent of item total price
        $orderData['shipping_price'] = $shipping;//done
        $orderData['payment_mode'] = $payment_mode;
        $orderData['created_at'] = $orderData['updated_at']  = datetime();
        $orderData['payment_status'] = 1; //0:Pending, 1:Paid(Received), 2:Failed

        //If payment mode is stripe then condition true
        if($this->post('payment_mode')==2){
            
            $user_card = $this->common_model->getsingle(CARDS, array('user_id' => $user_id,'is_default' => 1));

            if(empty($user_card)){
                $this->error_response('Please add card first to do payment.'); // error response
            }
            /*=============== Data for create payment ==================*/
            $customerId = $this->authData->stripe_customer_id;
            $charge['customer_id'] = $customerId;
            $charge['source'] = $user_card->stripe_card_id;
            $charge['amount'] = $total_amount;
            $charge['currency'] = 'usd';

            $stripe = $this->stripe->create_charge($charge);
            if($stripe['status'] == false){
                $this->error_response($stripe['message']); // error response
            }

            $insertOrder = $this->common_model->insertData(ORDERS,$orderData);
        }else{
            $insertOrder = $this->common_model->insertData(ORDERS,$orderData);
        }

        if(!$insertOrder){
            $this->error_response('Order not Placed successfully.'); // error response
        }

        //Save data for order tracking status
        $OrderTrackingStatus['order_id'] = $insertOrder;
        $OrderTrackingStatus['order_status'] = 0;
        $OrderTrackingStatus['created_at'] = $OrderTrackingStatus['updated_at']  = datetime();

        //Data insert in order tracking table
        $insertOrderTracking = $this->common_model->insertData(ORDER_TRACKING,$OrderTrackingStatus);

        //Save data for order address
        $orderAddress['order_id'] = $insertOrder;
        $orderAddress['name'] = $shippingAddData->name;
        $orderAddress['phone_dial_code'] = $shippingAddData->phone_dial_code;
        $orderAddress['country_code'] = $shippingAddData->country_code;
        $orderAddress['mobile_number'] = $shippingAddData->mobile_number;
        $orderAddress['house_number'] = $shippingAddData->house_number;
        $orderAddress['locality'] = $shippingAddData->locality;
        $orderAddress['city'] = $shippingAddData->city;
        $orderAddress['zip_code'] = $shippingAddData->zip_code;
        $orderAddress['country'] = $shippingAddData->country;
        $orderAddress['updated_at'] = $orderAddress['created_at'] = datetime();

        $insertOrderAddress = $this->common_model->insertData(ORDER_ADDRESS,$orderAddress);

        //Manage notification code start
        //user information
        $userDeviceTokens = $this->payment_model->getAllDeviceToken(array('user_id'=>$sellerID, 'device_type !='=> '3'));

        $orderInfo = $this->common_model->is_data_exists(ORDERS,array('orderID'=>$insertOrder));

        $this->orderNotification($userDeviceTokens,$orderInfo,$user_id,$insertOrder);

        //manage notiifcation code end

        $send['order_number'] = $rand;
        $send['order_id'] = $insertOrder;
        $send['total_amount'] = $subtotal + $taxAmount + $shipping;
        $send['currency_code'] = getenv('CURRENCY_CODE');
        $send['currency_symbol'] = getenv('CURRENCY_SYMB');
        
        array_push($responseArr,$send);
        $url_image = getenv('AWS_CDN_PRODUCT_IMG_PATH');
        //Data for insert item
        foreach ($offerItems as $key => $offerItem) {
            //condition for sale price
            $orderItems['order_id'] = $insertOrder; 
            $orderItems['variant_value_id'] = $offerItem->variant_value_id; 
            $orderItems['product_id'] = $offerItem->product_id; 
            $orderItems['item_quantity'] = $offerItem->quantity; 
            $orderItems['updated_at'] = $orderItems['created_at'] = datetime(); 
            $orderItems['item_price'] = $offerItem->product_offer_price; 
           
            $insertOrderItem = $this->common_model->insertData(ORDER_ITEMS,$orderItems);
            $delete = $this->common_model->deleteData(OFFER_ITEMS,array('offerItemID'=>$offerItem->offerItemID)); 

            //Product json update in order item
            $product_json = $this->order_model->get_product_detail($insertOrder);
            if(!empty($product_json)){  //Replace value of sale price in array
                foreach($product_json as $key => $value){
                  $product_json[$key]->sale_price = $offerItem->product_offer_price;
                }
            }
            $keys = array_keys($product_json); 
            $x = $product_json[$keys[count($keys)-1]];
            
            $updateItems['order_info_json'] = json_encode($x);
            $updateItems['updated_at'] = datetime();
            $updateOrderItem = $this->common_model->updateFields(ORDER_ITEMS,$updateItems,array('orderItemID' => $insertOrderItem));
            //End Of product json update in order item
        }

        $getSellerOrderCount = $this->payment_model->sellerOrderCount($sellerID);

        if($getSellerOrderCount->sellCount >= 10){
            $this->common_model->updateFields(USERS, array('is_verified' =>1), array('userID' =>$sellerID, 'user_type' => 'seller')); //Seller is verified Here
        }

        if($this->post('payment_mode')==2){
            
            $this->saveOrderPayment($user_id,$insertOrder,$total_amount,$subtotal_amount,$shipping_charges,$payment_mode,$stripe);  //Save data in order payment and order payment transaction table
        }
       
        $this->success_response(get_response_message(175), ['order_detail' => $responseArr]);
        
    }


    function orderNotification($userDeviceTokens,$orderInfo,$user_id, $insertOrder){

        if(!empty($userDeviceTokens)){

            //Update badge count Code
            $badgeNotify = 0;          
            $val = $this->database->getReference($this->dbname)->getChild($orderInfo->seller_id)->getSnapshot();
            if($val->exists() == TRUE){

                $badgeCount = intval($val->getValue()['count']);
                $badgeCountValue = $badgeCount ? $badgeCount : 0;
                $badgeNotify = $badgeCountValue+1;

                $updateVal = $this->database->getReference($this->dbname)->getChild($orderInfo->seller_id)->update(['count' => $badgeCountValue+1]); //update badge count

            }else{
                $updateVal = $this->database->getReference($this->dbname)->getChild($orderInfo->seller_id)->update(['count' => 1]); //create new badge count
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

                $notif_msg['type']          = 'Order Placed';
                $notif_msg['body']          = 'You have received a new order with reference number '.$orderInfo->number;
                $notif_msg['title']         = 'Order Placed';
                $notif_msg['sound']         = 'default';
                $notif_msg['order_id']      = $insertOrder;
                $notif_msg['badge']      = $badgeNotify;

                $dataNotifiy['notification_title']      = 'Order Placed';
                $dataNotifiy['notification_by']         = $user_id;
                $dataNotifiy['notification_for']        = $value->user_id;
                $dataNotifiy['notification_type']       = 'Order Placed';
                $dataNotifiy['is_read']                 = '0';
                $dataNotifiy['web_push']                 = '0';
                $dataNotifiy['reference_id']            = $insertOrder;
                $dataNotifiy['updated_at']            = datetime();
                $dataNotifiy['created_at']            = datetime();
                $dataNotifiy['notification_payload']    = json_encode($notif_msg);
                $dataNotifiy['notification_message']    = 'You have received a new order with reference number '.$orderInfo->number;
                // if($value->device_type < 1){
                //     $dataNotifiy['web_push']                 = '1';
                // }
                //when reference id is not empty then notification done
                //if(!empty($refrenID['token'])){
                $this->payment_model->notification($dataNotifiy,$tokenArr,$notif_msg);
                //}
                $tokenArr = array();
                $sellerID = $value->user_id;
            }
        }

        $notif_msg['type']          = 'Order Placed';
        $notif_msg['body']          = 'Order with reference number '.$orderInfo->number.' has been placed';
        $notif_msg['title']         = 'Order Placed';
        $notif_msg['sound']         = 'default';
        $notif_msg['order_id']      = $insertOrder;

        $dataNotifiy['notification_title']      = 'Order Placed';
        $dataNotifiy['notification_by']         = $user_id;
        $dataNotifiy['notification_for']        = $orderInfo->seller_id;
        $dataNotifiy['notification_type']       = 'Order Placed';
        $dataNotifiy['is_read']                 = '0';
        $dataNotifiy['web_push']                 = '0';
        $dataNotifiy['reference_id']            = $insertOrder;
        $dataNotifiy['updated_at']            = datetime();
        $dataNotifiy['created_at']            = datetime();
        $dataNotifiy['notification_payload']    = json_encode($notif_msg);
        $dataNotifiy['notification_message']    = 'Your order with reference number '.$orderInfo->number.' has been placed';

        $this->notification_model->save_notification($dataNotifiy);
    }

    public function saveOrderPayment($user_id,$order_id,$total_amount,$subtotal_amount,$shipping_charges,$payment_mode,$stripe){

        //Save data for order payment table
        $orderPayment['order_id'] = $order_id;
        $orderPayment['user_id'] = $user_id;
        $orderPayment['payment_type'] = 1; //1 for order payment,2 for refund payment
        $orderPayment['payment_amount'] = $total_amount;
        $orderPayment['payment_status'] = 5; //1 for pending(default), 2 for initiated, 3 for failed, 4 for cancelled, 5 for completed
        $orderPayment['payment_mode'] = $payment_mode;
        $orderPayment['created_at'] = $orderPayment['updated_at'] = datetime();
        $orderPayment['transaction_number'] = $stripe['data']['balance_transaction'];

        $insertOrderPayment = $this->common_model->insertData(ORDER_PAYMENTS,$orderPayment);

        if(!empty($insertOrderPayment)){
            $paymentTransaction['transaction_number'] = $stripe['data']['balance_transaction'];
            $paymentTransaction['order_payment_id'] = $insertOrderPayment;
            $paymentTransaction['transaction_response'] = json_encode($stripe);
            $paymentTransaction['user_id'] = $user_id;
            $paymentTransaction['transaction_amount'] = $total_amount;
            $paymentTransaction['transaction_status'] = 1;
            $paymentTransaction['payment_transaction_mode'] = 2;
            $paymentTransaction['created_at'] = $paymentTransaction['updated_at']  = datetime();
            //insert into order payment transanction
            $insertPyamentTransaction = $this->common_model->insertData(ORDER_PAYMENT_TRANSACTIONS,$paymentTransaction);
        }
    }

    //function for random string generate function
    function randomString(){
        $seed = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
        shuffle($seed); // probably optional since array_is randomized; this may be redundant
        $rand = '';
        foreach (array_rand($seed, 4) as $k) $rand .= $seed[$k];
        
        $rand =$rand.date('y').date("s").date("i").date("H").date("dm"); //Random  invoice  Create
        return $rand;
    }
}