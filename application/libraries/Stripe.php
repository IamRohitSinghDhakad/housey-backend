<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

class Stripe{

    public function __construct () {
        $this->ci =& get_instance();
        $secret_key = getenv('STRIPE_SK');
        $publishable_key = getenv('STRIPE_PK');
        Stripe\Stripe::setApiKey($secret_key);
    }
    
    //create token for transaction, this token will be used when creating a customer, This API is generally used when we want process user card info on our own. Otherwise we can use Stripe elements, pre-built UI components, to create a payment form that securely collects your customer’s card information without you needing to handle sensitive card data
    function addCardAccount($name,$number,$exp_month,$exp_year,$cvv){
        
            $success = 0;
            try {
                $result = Stripe\Token::create(
                            array(
                            "card" => array(
                                    "number" => $number,
                                    "exp_month" => $exp_month,
                                    "exp_year" => $exp_year,
                                    "cvc" => $cvv
                                    ) 
                            )
                    ); 
                $success = 1;

            } catch(Stripe_CardError $e) {
                    $error[] = $e->getMessage();
            } catch (Stripe_InvalidRequestError $e) {
                    // Invalid parameters were supplied to Stripe's API
                    $error[] = $e->getMessage();
            } catch (Stripe_AuthenticationError $e) {
                    // Authentication with Stripe's API failed
                    $error[] = $e->getMessage();
            } catch (Stripe_ApiConnectionError $e) {
                    // Network communication with Stripe failed
                    $error[] = $e->getMessage();
            } catch (Stripe_Error $e) {
                    // Display a very generic error to the user, and maybe send
                    // yourself an email
                    $error[] = $e->getMessage();
            } catch (Exception $e) {
                    // Something else happened, completely unrelated to Stripe
                    $error[] = $e->getMessage();
            }

            if ($success != 1){
                $response = array('status'=> FAIL ,'message' => $error);
                print_r(json_encode($response));die();
            }else{
                    if(isset($result['id']) && !empty($result['id'])){
                            return $result['id'];
                    }else{
                            return false;
                    }     
            }        
    }    

    //create customer
    function create_customer($email='', $token = ''){
        
        try{
            $customer = Stripe\Customer::create(array(
              // "email" => $email, 
              // "source" => $token,
            ));
            
            if(!isset($customer->id))
                return array('status'=>false,'message'=>'Something went wrong');
            
            return array('status'=>true,'message'=>'Customer created successfully', 'data'=>$customer); //success
           
        }catch(Exception $e){
            //echo 'dsfsdf';die;
            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message);
        }  
    }
   
    //Charge a customer on basis of customer ID
    function charge_customer($amount, $custId, $currency='USD'){
        
        $amount = round($amount,2); 
        
        try{

            $charge = Stripe\Charge::create(array(
                        "amount" => $amount, //amount in cents, for $1 it should be 100 cents
                        "currency" => $currency,
                        "customer" => $custId
                    ));
            $var = $charge->balance_transaction;
            
            if(!isset($charge->balance_transaction))
                return array('status'=>false,'message'=>'Something went wrong');
            
            return array('status'=>true,'message'=>'Transaction completed successfully', 'data'=>$charge); //success
        }catch(Exception $e){
            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message);
        }
    }

    //create card (Add card)
    function create_card($stripe_customer_id,$sourceId ){
        try{

            $card = Stripe\Customer::createSource(
              $stripe_customer_id,
              [
                'source' => $sourceId ,
              ]
            );

            if(!isset($card->id))
                return array('status'=>false,'message'=>'Something went wrong');
            return array('status'=>true,'message'=>'Card added successfully', 'data'=>$card); //success

        }catch(Exception $e){
            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message);
        } 
    }

    //Delete Card 
    // function delete_card($stripe_customer_id,$card_id){
    //     try{

    //         $delCard = Stripe\Customer::deleteSource($stripe_customer_id,$card_id);
            
    //         if(!$delCard || $delCard->deleted != 1)
    //             return array('status'=>false,'message'=>'Something went wrong');
    //         return array('status'=>true,'message'=>'Card deleted successfully', 'data'=>$delCard); //success

    //     }catch(Exception $e){
    //         $message = $e->getMessage();
    //         return array('status'=>false,'message'=>$message);
    //     } 
    // }

    function delete_card($stripe_customer_id,$cardID){
        try{

            $delCard = Stripe\Customer::deleteSource($stripe_customer_id,$cardID);
            
            if(!$delCard || $delCard->deleted != 1)
                return array('status'=>false,'message'=>'Something went wrong');
            return array('status'=>true,'message'=>'Card deleted successfully', 'data'=>$delCard); //success

        }catch(Exception $e){
            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message);
        } 
    }//end function

    //List all card

    function card_list($stripe_customer_id){
        try{

            $cards = Stripe\Customer::allSources(
              $stripe_customer_id,
              [
                'object' => 'card',
                'limit' => 100
              ]
            );
            //pr($cards);
            if(!$cards['data'])
                return array('status'=>false,'message'=>'No card found');
            return array('status'=>true,'message'=>'Card found successfully', 'card_list'=>$cards); //success

        }catch(Exception $e){
            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message);
        } 
    }
    
    //create a product that represents the service you intend to offer to your customers for subscription 
    function create_product($name, $type='service'){
        try{
            $product = \Stripe\Product::create([
                'name' => $name,
                'type' => $type,
            ]);
            
            if(empty($product))
                return array('status'=>false,'message'=>'Something went wrong');
            
            return array('status'=>true,'message'=>'Product created successfully', 'data'=>$product); //success
            
        }catch(Exception $e){
            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message);
        }
    }
    
    //create a plan which will be attached to a product
    function create_plan($product_id, $interval, $currency,  $amount, $nickname=''){
        try{
            $plan = \Stripe\Plan::create([
                    'product' => $product_id,
                    'nickname' => $nickname,
                    'interval' => $interval,
                    'currency' => $currency,
                    'amount' => $amount, //amount in cents, for $1 it should be 100 cents
                  ]);
            
            if(empty($plan))
                return array('status'=>false,'message'=>'Something went wrong');
            
            return array('status'=>true,'message'=>'Plan created successfully', 'data'=>$plan); //success
            
        }catch(Exception $e){
            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message);
        }
    }
    
    //Retrieves the plan with the given ID.
    function get_plan($plan_id){
        try{
            $plan = \Stripe\Plan::retrieve($plan_id);
            if(empty($plan)){
                return array('status'=>false,'message'=>'Something went wrong');
            }
            
            return array('status'=>true,'message'=>'Plan retrieved successfully', 'data'=>$plan); //success
            
        } catch (Exception $e) {
            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message);
        }
    }
    
    //Subscribe the customer to the plan
    function create_subscription($customer_id, $plan_id){
        try{
            $subscription = \Stripe\Subscription::create([
                'customer' => $customer_id,
                'items' => [['plan' => $plan_id]],
            ]);
            
            if(empty($subscription)){
                return array('status'=>false,'message'=>'Something went wrong');
            }
            
            return array('status'=>true,'message'=>'Subscribed successfully', 'data'=>$subscription); //success
            
        }catch (Exception $e) {
            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message);
        }
    }
    
    //Retrieves the subscription with the given ID. Customer who owns the subscription have unique subscription ID.
    function get_subscription($subscription_id){
        try{
            $subscription = \Stripe\Subscription::retrieve($subscription_id);
            
            if(empty($subscription)){
                return array('status'=>false,'message'=>'Something went wrong');
            }
            
            return array('status'=>true,'message'=>'Retrieved successfully', 'data'=>$subscription); //success
            
        }catch (Exception $e) {
            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message);
        }
       
    }
    
    //Cancels a customer’s subscription
    //By default, the cancellation takes effect immediately for $at_period_end = false
    //If you want to cancel the subscription at the end of the current billing period (i.e., for the duration of time the customer has already paid for), provide an at_period_end value of true
    function cancel_subscription($subscription_id, $at_period_end = false){
        try{
            $sub = \Stripe\Subscription::retrieve($subscription_id);
            $subscription = $sub->cancel(['at_period_end' => $at_period_end]);
            if(empty($subscription)){
                return array('status'=>false,'message'=>'Something went wrong');
            }
            
            return array('status'=>true,'message'=>'Retrieved successfully', 'data'=>$subscription); //success
            
        }catch (Exception $e) {
            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message);
        }
    }
    
    function save_bank_account_id($holderName,$dob,$country,$currency,$routingNumber,$accountNo,$ssnLast,$postalCode){

        if(!empty($holderName)){
            $names = explode(" ", $holderName);
        }
        
        $dob = explode("-", $dob);
        $success = 0;
        
        try {
            $acct = Stripe\Account::create(array(

                "country" => "US",
                "type" => 'custom',
                "external_account" => array(
                    "object" => "bank_account",
                    "country" => $country,
                    "currency" => $currency,
                    "routing_number" => $routingNumber,
                    "account_number" => $accountNo,
                ),
                "tos_acceptance" => array(
                    "date" => time(),
                    "ip" => $_SERVER['SERVER_ADDR']
                ),
            ));
            $success = 1;
            
        } catch(Stripe_CardError $e) {
            $error[] = $e->getMessage();
        } catch (Stripe_InvalidRequestError $e) {
            // Invalid parameters were supplied to Stripe's API
            $error[] = $e->getMessage();
        } catch (Stripe_AuthenticationError $e) {
            // Authentication with Stripe's API failed
            $error[] = $e->getMessage();
        } catch (Stripe_ApiConnectionError $e) {
            // Network communication with Stripe failed
            $error[] = $e->getMessage();
        } catch (Stripe_Error $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            $error[] = $e->getMessage();
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            $error[] = $e->getMessage();
        }

        if ($success != 1){
            $response = array('status'=> FAIL ,'message' => $error);
            print_r(json_encode($response));die();
        }else{
            $acct_id = $acct->id; 
            $account = Stripe\Account::retrieve($acct_id);
            $account->legal_entity->dob->year = $dob[0];
            $account->legal_entity->dob->month = $dob[1];
            $account->legal_entity->dob->day = $dob[2];
            $account->legal_entity->first_name = $names[0];
            $account->legal_entity->last_name = $names[1];
            $account->legal_entity->type = "individual";

            //$account->legal_entity->address->line1 = $address;
            $account->legal_entity->address->postal_code = $postalCode;
            //$account->legal_entity->address->city = $city;
            //$account->legal_entity->address->state = $state;
            $account->legal_entity->ssn_last_4 = $ssnLast;

            $account->save();
           
            if(isset($acct->id) && !empty($acct->id)){
                return $acct->id;
            }else{
                return false;
            }
        }
    }
    
    /*
     * Charge a customer on basis of token/source/card
     */
    // function create_charge($source, $amount, $currency='usd'){
        
    //     $amount = round(($amount*100),2); //convert to cents
        
    //     try{
            
    //         $charge = \Stripe\Charge::create(array(
    //             "amount" => $amount,
    //             "currency" => $currency,
    //             "source" => $source, //source or token
    //         ));
            
    //         if(empty($charge)){
    //             return array('status'=>false,'message'=>'Something went wrong');
    //         }
            
    //         return array('status'=>true, 'message'=>'Charged successfully', 'data'=>$charge); //success
            
    //     }catch (Exception $e) {

    //         $message = $e->getMessage();
    //         return array('status'=>false,'message'=>$message);
    //     }
    // }

    function create_charge($charge_data){
        
        $amount = round($charge_data['amount']*100); //convert to cents
        
        $charge_arr = array(
            "amount" => $amount,
            "currency" => $charge_data['currency'],
            "source" => $charge_data['source'], //source or token or card ID
            "customer" => $charge_data['customer_id']
        );
        // if($charge_data['source_type'] === 'card'){
        //     $charge_arr['customer'] = $charge_data['customer_id'];
        // }
        try{
           
            $charge = \Stripe\Charge::create($charge_arr);
            
            if(empty($charge)){
                return array('status'=>false,'message'=>'Something went wrong');
            }
             
            return array('status'=>true, 'message'=>'Charged successfully', 'data'=>$charge); //success
            
        }catch (Exception $e) {

            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message);
        }
    }
    
    /*
     * Transfer fund from main stripe account to custom account (connected account)
     * This is done when admin wants to keep a commission(some % of amount) from a payemnt and pay remaning amount to customer
     * Note: Transfer from connected account to its attached bank account will be done automatically (via Stripe Payout)
     */
    function stripe_to_custom_account_transfer($acc_id, $amount, $currency='usd'){
        
        $amount = round(($amount*100),2); //convert to cents
        
        try{
            $transfer = \Stripe\Transfer::create( array(
                "amount" => $amount,
                "currency" => $currency,
                "destination" => $acc_id,

            ));
            if(empty($transfer)){
                return array('status'=>false,'message'=>'Something went wrong');
            }
            
            return array('status'=>true, 'message'=>'Funds transfered successfully', 'data'=>$transfer); //success
            
        }catch (Exception $e) {

            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message);
        }
    }
    
    //create a refund
    function refundToCard($chargeId){
        
        try{

            $refund = \Stripe\Refund::create(array(
                      "charge" =>$chargeId 
                    ));
            if(isset($refund ->id) && !empty($refund ->id)){

                return array('status'=>true,'message'=>'ok','data'=>$refund ->id);
            }
        }catch(Exception $e){

            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message,'data'=>'');
        }       
    }
        
       
    function owner_pay_byBankId($data){

        $amount = round($data['amount'],2);
        $secret_key = $this->ci->config->item('secret_key');

        Stripe\Stripe::setApiKey($secret_key);

        $success = 0;
        try{
            $transfer = \Stripe\Transfer::create(array( 
                "amount" => $amount, 
                "currency" => $data['currency'], 
                "destination" => $data['bankAccId'], 
                "description" => "Amount Transfer To Admin",
                "transfer_group" => "APOIM"

            ));

        $success = 1;
            
        } catch(Stripe_CardError $e) {
            $error[] = $e->getMessage();
        } catch (Stripe_InvalidRequestError $e) {
            // Invalid parameters were supplied to Stripe's API
            $error[] = $e->getMessage();
        } catch (Stripe_AuthenticationError $e) {
            // Authentication with Stripe's API failed
            $error[] = $e->getMessage();
        } catch (Stripe_ApiConnectionError $e) {
            // Network communication with Stripe failed
            $error[] = $e->getMessage();
        } catch (Stripe_Error $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            $error[] = $e->getMessage();
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            $error[] = $e->getMessage();
        }

        if ($success != 1){
            $response = array('status'=> FAIL ,'message' => $error);
            print_r(json_encode($response));die();
        }else{
            if(isset($transfer->balance_transaction) && !empty($transfer->balance_transaction)){                
                return $transfer;
            }else{
                return false;
            }     
        }        
    } 
        
    //update customer token
    function update_customer($customer_id,$attrs=array()){
          $cu = \Stripe\Customer::retrieve($customer_id);
            
        try{

            foreach($attrs as $k => $v){
              $cu->$k = $v;
            }
        
            $upd_status =  $cu->save();
            
            if(empty($upd_status)){
             return false;
            }
            
            return true;
           
        }catch(Exception $e){
            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message);
        }  
    }

    //make card default
    function default_card($stripe_customer_id,$cardID) {
        
        try{

            $default = \Stripe\Customer::retrieve($stripe_customer_id);
            $default->default_source=$cardID;
            $default->save(); 

            if(isset($default)){
                return array('status'=>true,'message'=>'ok','data'=> $default);
            }

        }catch(Exception $e) {

            $message = $e->getMessage();
            return array('status'=>false,'message'=>$message,'data'=>'');
        }
    }       
}