<?php
class Offer extends Common_Service_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('offer_model');
    }

    function offer_item_post(){
    	$this->check_service_auth();
        $user_id = $this->authData->userID;

        $this->form_validation->set_rules('product_id', 'Product ID','trim|required');
        $this->form_validation->set_rules('product_name', 'Product Name','trim|required');
        $this->form_validation->set_rules('quantity', 'Product Quantity','trim|required|numeric');
        $this->form_validation->set_rules('feature_image', 'Feature Image','trim|required');
        $this->form_validation->set_rules('seller_id', 'Seller ID','trim|required|numeric');
        $this->form_validation->set_rules('variant', 'Variant','trim|required',array('required' => 'Please select color and size.'));
        $this->form_validation->set_rules('category', 'Category','trim|required');
        $this->form_validation->set_rules('category_name', 'Category Name','trim|required');
        $this->form_validation->set_rules('sale_price', 'Sale Price','trim|required');
        $this->form_validation->set_rules('regular_price', 'Regular Price','trim|required');
        $this->form_validation->set_rules('offer_price', 'Offer Price','trim|required');

        $variants = $this->post('variant');
		$array = array_filter(array_map('trim', explode(',', $variants)));
		asort($array);
		$variants = implode(',', $array); //sort variants id in asc order	

		$categories = $this->post('category');
		$categoryArray = array_filter(array_map('trim', explode(',', $categories)));
		asort($categoryArray);
		$categories = implode(',', $categoryArray); //sort categories id in asc order		

        //set response msg  for form validation
		if($this->form_validation->run() == FALSE){

			$this->error_response(strip_tags(validation_errors())); //error reponse
		}

		$product = $this->common_model->getsingle(PRODUCTS,array('productID' => $this->post('product_id')));

        if(empty($product)){  //If product not exist
            $this->error_response(get_response_message(159)); //error reponse
        }

		//get product 
		$getofferProduct = $this->offer_model->getOfferItems($user_id, $this->post('product_id'), $variants);
		if(!empty($getofferProduct)){ //if not empty offer for that product and user id then update  offer item
		
			$updateData['product_id'] = $this->post('product_id');
			$updateData['product_name'] = $this->post('product_name');
			$updateData['feature_image'] = $this->post('feature_image');
			$updateData['variant_value_id'] = $variants;
			$updateData['quantity'] = $this->post('quantity');
			$updateData['category_id'] = $categories;
			$updateData['category_name'] = $this->post('category_name');
			$updateData['buyer_id'] = $user_id;
			$updateData['seller_id'] = $this->post('seller_id');
			$updateData['product_sale_price'] = $this->post('sale_price');
			$updateData['product_regular_price'] = $this->post('regular_price');
			$updateData['product_offer_price'] = $this->post('offer_price');
			$updateData['updated_at'] = datetime();

			$updateCart = $this->common_model->updateFields(OFFER_ITEMS,$updateData, array('buyer_id' => $user_id, 'product_id'=>$this->post('product_id'),'variant_value_id'=>$variants));

			$this->success_response('Offer Item has been updated successfully', ['offer_id' => $getofferProduct->offerItemID]);

		}else{ //add product in offer item

			$dataInsert['product_id'] = $this->post('product_id');
			$dataInsert['product_name'] = $this->post('product_name');
			$dataInsert['feature_image'] = $this->post('feature_image');
			$dataInsert['variant_value_id'] = $variants;
			$dataInsert['quantity'] = $this->post('quantity');
			$dataInsert['category_id'] = $categories;
			$dataInsert['category_name'] = $this->post('category_name');
			$dataInsert['buyer_id'] = $user_id;
			$dataInsert['seller_id'] = $this->post('seller_id');
			$dataInsert['product_sale_price'] = $this->post('sale_price');
			$dataInsert['product_regular_price'] = $this->post('regular_price');
			$dataInsert['product_offer_price'] = $this->post('offer_price');
			$dataInsert['created_at'] = datetime();
			$dataInsert['updated_at'] = datetime();

			$offerItemID = $this->common_model->insertData(OFFER_ITEMS,$dataInsert);

			if(!$offerItemID){
            	//check for item added in offer or not
            	$this->error_response(get_response_message(107)); //error reponse
	        }

	        $this->success_response('Offer Item has been added successfully', ['offer_id' => $offerItemID]);
		}	
    }

    function list_get(){
		$this->check_service_auth();
        $user_id = $this->authData->userID;
		$offer_id = $this->get('offer_id');

		if(empty($this->get('offer_id'))){
			$this->error_response('Offer ID is required.'); //error reponse
		}
		$offerItemID = $this->common_model->get_field_value(OFFER_ITEMS, array('offerItemID' =>$offer_id ), 'offerItemID');

		if($offerItemID === FALSE){
			$this->error_response('Offer not found.'); //error reponse
		}

		$buyerOfferItemID = $this->common_model->get_field_value(OFFER_ITEMS, array('offerItemID' =>$offer_id,'buyer_id' => $user_id ), 'offerItemID');

		if($buyerOfferItemID === FALSE){
			$this->error_response("This is not your offer"); //error reponse
		}

        $offerItemList = $this->offer_model->offerItemList($user_id, $offer_id);

        if(!$offerItemList){
             
            $this->success_response(get_response_message(106));
        }

        $this->success_response(get_response_message(302),$offerItemList);
    }
}