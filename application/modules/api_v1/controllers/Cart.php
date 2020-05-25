<?php
class Cart extends Common_Service_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('cart_model');
    }

    function cart_post(){
    	$this->check_service_auth();
        $user_id = $this->authData->userID;

        $this->form_validation->set_rules('product_id', 'Product ID','trim|required');
        $this->form_validation->set_rules('product_quantity', 'Product Quantity','trim|required|numeric');
        $this->form_validation->set_rules('variant', 'Variant','trim|required',array('required' => 'Please select color and size.'));

        $variants = $this->post('variant');
		$array = array_filter(array_map('trim', explode(',', $variants)));
		asort($array);
		$variants = implode(',', $array); //sort variants id in asc order		

        //set response msg  for form validation
		if($this->form_validation->run() == FALSE){

			$this->error_response(strip_tags(validation_errors())); //error reponse
		}

		//check product available or not before update
		$availableProduct = $this->common_model->getsingle(PRODUCTS,array('productID'=>$this->post('product_id')));

		if(empty($availableProduct)){
			$this->error_response(get_response_message(159)); //error reponse
		}

		//get product 
		$getcartProduct = $this->cart_model->getCartItems($user_id, $this->post('product_id'), $variants);
		if(!empty($getcartProduct)){ //if not empty cart for that product and user id then update quantity of cart item
		
			$updateData['quantity'] = $getcartProduct->quantity + $this->post('product_quantity');
			$updateData['updated_at'] = datetime();

			$updateCart = $this->common_model->updateFields(CART_ITEMS,$updateData, array('user_id' => $user_id, 'product_id'=>$this->post('product_id'),'variant_value_id'=>$variants));

			if($updateCart === TRUE){

				$this->success_response(get_response_message(168));
			}

		}else{ //add product in user cart 
			$dataInsert['user_id'] = $user_id;
			$dataInsert['product_id'] = $this->post('product_id');
			$dataInsert['quantity'] = $this->post('product_quantity');
			$dataInsert['variant_value_id'] = $variants;
			$dataInsert['updated_at'] = datetime();
			$dataInsert['created_at'] = datetime();

			$cartID = $this->common_model->insertData(CART_ITEMS,$dataInsert);

			if(!$cartID){
            	//check for item added in cart or not
            	$this->error_response(get_response_message(107)); //error reponse
	        }

	        $this->success_response(get_response_message(168));
		}	
    }

    function list_get(){
		$this->check_service_auth();
        $user_id = $this->authData->userID;

        $data['limit'] = $this->get('limit');
        $data['offset'] = $this->get('offset');
        $data['user_id'] = $user_id;

        if(empty($data['limit'])){
            $data['limit'] = 100;
        }
        if(empty($data['offset'])){
           $data['offset'] = 0; 
        }

        $cartItemList = $this->cart_model->cartItemList($data);

        if(!$cartItemList){
             
            $this->success_response(get_response_message(106));
        }

        $this->success_response(get_response_message(302),$cartItemList);
    }

    //Delete cart item of user
    function cart_delete($id=''){
    	$this->check_service_auth();
    	$data['limit'] = $this->delete('limit');
        $data['offset'] = $this->delete('offset');
       	$clearAll = $this->delete('clearAll');
      
        if(empty($data['limit'])){
            $data['limit'] = 100;
        }
        if(empty($data['offset'])){
           $data['offset'] = 0; 
        }
        $data['user_id'] = $this->authData->userID;
        $cartItemId = $id;

        if(!empty($id) || $id != ''){

			$getCartItemOfUser = $this->common_model->getsingle(CART_ITEMS,array('user_id'=>$data['user_id'], 'cartItemID' => $cartItemId));

			if(!$getCartItemOfUser || empty($getCartItemOfUser)){

				$this->error_response("You don't have this item in cart to delete"); //error reponse
			}

			$deleteCartItem = $this->common_model->deleteData(CART_ITEMS, array('user_id'=>$data['user_id'], 'cartItemID' => $cartItemId));
		}
        
		else{
			
			$deleteCartItem = $this->common_model->deleteData(CART_ITEMS, array('user_id'=>$data['user_id']));
		}


		$cartItemList = $this->cart_model->cartItemList($data);

		if($deleteCartItem === TRUE){

        	$this->success_response('Your cart item has been deleted successfully.',$cartItemList);
		}else{
			$this->error_response(get_response_message(107)); //error reponse
		}
    }

    //Cart increment and decrement
    function alter_put($id){
    	$this->check_service_auth();
        $data['user_id'] = $this->authData->userID;
        $cartItemId = $id; //cart Item ID
        $productQuantity = $this->put('product_quantity');
        $productID = $this->put('product_id');
        $type = $this->put('type'); //increase decrease

        if(empty($id) || empty($productID)){
        	
        	$this->error_response(get_response_message(158)); //error reponse
        }

        if(empty($this->put('product_quantity'))){
        	
        	$this->error_response(get_response_message(169)); //error reponse
        }

        if(empty($this->put('type'))){
        	
        	$this->error_response('Type is required.'); //error reponse
        }

		$cartItemOfUser = $this->common_model->getsingle(CART_ITEMS,array('user_id'=>$data['user_id'], 'cartItemID' => $cartItemId));

		if(!$cartItemOfUser || empty($cartItemOfUser)){
			
    		$this->error_response("You don't have item in cart to update."); //error reponse
		}

		if($type == 'decrease'){ //To decrese the cart item value
			$updateData['quantity'] = $cartItemOfUser->quantity - $productQuantity;
			$updateData['updated_at'] = datetime();

			$updateCart = $this->common_model->updateFields(CART_ITEMS,$updateData, array('user_id' => $data['user_id'], 'cartItemID'=>$cartItemId));

			//get pricing detail after update 
			$pricing_detail = $this->cart_model->cartItemPricingDetail($data);
			$tax_detail = $this->cart_model->cartItemTaxDetail($pricing_detail);

			$getUpdatedCartItem = $this->common_model->getsingle(CART_ITEMS,array('user_id'=>$data['user_id'], 'cartItemID' => $cartItemId));

			if($updateCart === TRUE){

	    		$this->success_response('Your cart item has been updated successfully.',['cart_list' => $getUpdatedCartItem, 'pricing_detail' => $pricing_detail, 'tax_detail' => $tax_detail]);
			}
		}

		if($type == 'increase'){ //To increase the cart item value

			//check product available or not before update
			$availableProduct = $this->common_model->getsingle(PRODUCTS,array('productID'=>$productID));

			//check for out of stock item
			if($availableProduct->in_stock == '0'){

				$this->error_response("Item is out of stock."); //error reponse
			}

			$updateData['quantity'] = $cartItemOfUser->quantity + $productQuantity;
			$updateData['updated_at'] = datetime();

			$updateCart = $this->common_model->updateFields(CART_ITEMS,$updateData, array('user_id' => $data['user_id'], 'cartItemID'=>$cartItemId));

			//get pricing detail after update 
			$pricing_detail = $this->cart_model->cartItemPricingDetail($data);
			$tax_detail = $this->cart_model->cartItemTaxDetail($pricing_detail);

			$getUpdatedCartItem = $this->common_model->getsingle(CART_ITEMS,array('user_id'=>$data['user_id'], 'cartItemID' => $cartItemId));

			if($updateCart === TRUE){

				$this->success_response('Your cart item has been updated successfully.',['cart_list' => $getUpdatedCartItem, 'pricing_detail' => $pricing_detail, 'tax_detail' => $tax_detail]);
			}
		}
    }

    function cart_count_get(){
        $this->check_service_auth();
        $user_id = $this->authData->userID;

        $cartcount = $this->cart_model->get_total_count(CART_ITEMS,array('user_id'=>$user_id));
        $count = $cartcount->total_records;

        $this->success_response(get_response_message(302),['cart_count' => $count]);
    }
}