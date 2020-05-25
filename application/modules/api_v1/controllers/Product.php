<?php

/**
* Product controller
* Handles product web service request
* version: 1.0 ( 04-02-2020 )
*/
class Product extends Common_Service_Controller {

	public function __construct(){
        parent::__construct();

        $this->load->model('product_model');
	}

    //Add product
    function product_post(){
        //Check user authentication and get user info from auth token
        $this->check_service_auth();
        $user_id = $this->authData->userID; //current user ID;
        $data['userID'] = $user_id; //current user ID for product detail;
        $data['userType'] = 'seller'; //current user type for product detail;

        $this->form_validation->set_rules('name', 'Product Name','trim|required|min_length[2]|max_length[300]');
        $this->form_validation->set_rules('sku', 'SKU','trim|required|min_length[2]|max_length[64]'); //Product Unique number
        $this->form_validation->set_rules('description', 'Description','trim|required');
        $this->form_validation->set_rules('category', 'Category','trim|required');
        $this->form_validation->set_rules('sub_category', 'Sub Category','trim|required');
        $this->form_validation->set_rules('color', 'Color','trim|required');
        $this->form_validation->set_rules('size', 'Size','trim|required');
        $this->form_validation->set_rules('shipping_applied', 'Shipping Applied','trim|required');  //0:No, 1:Yes

        if($this->post('shipping_applied') == '1'){  //Shipping applied yes then shipping charge required
            $this->form_validation->set_rules('shipping_charge', 'Shipping Charge','trim|required');

            if($this->post('shipping_charge') < 1){
            
                $this->error_response('Shipping charge not be less than 1.'); //error reponse
            }
        }

        if(!empty($this->post('discount'))){  //Discount is not empty then sale price required
            $this->form_validation->set_rules('sale_price', 'Sale Price','trim|required');
            $this->form_validation->set_rules('discount', 'Discount','trim|numeric');

            if($this->post('discount') > 100 || $this->post('discount') < 1){
            
                $this->error_response('Discount must be between 1 and 100'); //error reponse
            }
        }   

        $this->form_validation->set_rules('regular_price', 'Regular Price','trim|required');

        if(!empty($this->post('regular_price'))) {  //If price value is not valid
            if (!preg_match("/^[0-9]{1,10}(([\.]{0,2}[0-9]{0,2}))$/", $this->post('regular_price'))) {

                $this->error_response(get_response_message(137)); //error reponse
            }
        }

        if(!empty($this->post('sale_price'))) {  //If price value is not valid

            if (!preg_match("/^[0-9]{1,10}(([\.]{0,2}[0-9]{0,2}))$/", $this->post('sale_price'))) {

                $this->error_response(get_response_message(137)); //error reponse
            }
        }

        if(!empty($this->post('shipping_charge'))) {  //If price value is not valid

            if (!preg_match("/^[0-9]{1,10}(([\.]{0,2}[0-9]{0,2}))$/", $this->post('shipping_charge'))) {

                $this->error_response('Please input valid shipping price.'); //error reponse
            }
        }

        //set response msg  for form validation
        if($this->form_validation->run() == FALSE){

            $this->error_response(strip_tags(validation_errors())); //error reponse
        }

        $product_name = sanitize_input_text($this->input->post('name')); //product name
        $sku = sanitize_input_text($this->input->post('sku')); //product unique number
        $description = sanitize_input_text($this->input->post('description')); //product description
        $additional_info = sanitize_input_text($this->input->post('additional_info')); //product Addition information
        $category = $this->post('category'); //product category 
        $sub_category = explode(",", $this->post('sub_category')); //product sub category
        array_unshift($sub_category,$category); //Insert category ID in subcategory array first index
        $color = explode(",", $this->post('color')); //product color variant
        $size = explode(",", $this->post('size')); //product size variant
        $shipping_applied = $this->post('shipping_applied'); //0:No, 1:Yes
        $shipping_charge = $this->post('shipping_charge');
        $regular_price = $this->post('regular_price'); //Regular price
        $sale_price = $this->post('sale_price'); //Sale price
        $discount = $this->post('discount'); //Discount percentage
        $in_stock = $this->post('in_stock'); //Product Instock  0:No, 1:Yes
        $is_featured = $this->post('is_featured'); //Product Is featured  0:No, 1:Yes


        //If featured image is empty
        if(empty($_FILES['featured_image']['name'])){
            $this->error_response(get_response_message(139)); //error reponse
        }

        //upload product featured image
        
        $featured_image=NULL;
        $this->load->model('image_model'); //Load image model
        //if image not empty set it for product featured image 
        $upload_img = $this->image_model->upload_image('featured_image', 'product');
        //check for error
        if( array_key_exists("error", $upload_img) && !empty($upload_img['error'])){

            $this->error_response(strip_tags($upload_img['error'])); //error reponse
        }
        //check image name key exist
        if(array_key_exists("image_name", $upload_img)){
            $featured_image = $upload_img['image_name'];
        }

        $insertProduct = array(
            'seller_id' => $user_id,
            'name'  => $product_name,
            'description'  => $description,
            'additional_information'  => $additional_info,
            'feature_image'  => $featured_image,
            'sku'  => $sku,
            'regular_price'  => $regular_price,
            'sale_price'  => $sale_price,
            'sale_discount'  => $discount,
            'in_stock'  => $in_stock,
            'is_featured'  => $is_featured,
            'shipping_applied'  => $shipping_applied,
            'shipping_charge'  => $shipping_charge,
            'updated_at'  => datetime(),
            'created_at'  => datetime(),
        );

        $product_id = $this->common_model->insertData(PRODUCTS,$insertProduct);


        if(!empty($product_id)) {

            //Insert category and subcategory in mapping table
            foreach ($sub_category as $category) { 

                $setCategoryMapData = array(
                    'product_id' => $product_id,
                    'category_id' => $category,
                    'updated_at' => datetime(),
                    'created_at' => datetime(),
                );
                $this->common_model->insertData(PRODUCT_CATEGORY_MAP,$setCategoryMapData);
            }

            //Insert color variant in mapping table
            foreach ($color as $colors) { 

                $setColorMapData = array(
                    'product_id' => $product_id,
                    'variant_id' => 2,   //For color, variant ID is 2
                    'variant_value_id' => $colors,
                    'updated_at' => datetime(),
                    'created_at' => datetime(),
                );
                $this->common_model->insertData(PRODUCT_VARIANT_MAP,$setColorMapData);
            }

            //Insert size variant in mapping table
            foreach ($size as $sizes) { 

                $setSizeMapData = array(
                    'product_id' => $product_id,
                    'variant_id' => 1,   //For size, variant ID is 1
                    'variant_value_id' => $sizes,
                    'updated_at' => datetime(),
                    'created_at' => datetime(),
                );
                $this->common_model->insertData(PRODUCT_VARIANT_MAP,$setSizeMapData);
            }

            //product detail response used in chat functionality
            $productDetail = $this->product_model->productDetail($product_id,$data['userID'],$data['userType']);

            $this->success_response(get_response_message(138),['product_id' =>$product_id, 'product_detail' => $productDetail]);
        }

        $this->error_response(get_response_message(107),SERVER_ERROR,500); //error reponse
    }

    //Add product gallery images
    function attachments_post($id){
        //Check user authentication and get user info from auth token
        $this->check_service_auth();
        $product_id = $id;

        $exist = $this->checkProductExist($product_id);
        if($exist === FALSE){

            $this->error_response(get_response_message(159)); //error reponse
        }

        //If gallery image is empty
        if(empty($_FILES['gallery_image']['name'])){
            $this->error_response(get_response_message(152)); //error reponse
        }

        //If 10 gallery image uploaded after that give error 
        $gallery_image_count = $this->product_model->galleryCount($product_id);
        if($gallery_image_count->total_count >= 10){
            $this->error_response('Upload maximum 10 gallery images'); //error reponse
        }

        //upload product gallery image
        $this->load->model('image_model'); //Load image model
        //if image not empty set it for product gallery image 
        $upload_img = $this->image_model->upload_image('gallery_image', 'product_gallery');
        //check for error
        if( array_key_exists("error", $upload_img) && !empty($upload_img['error'])){

            $this->error_response(strip_tags($upload_img['error'])); //error reponse
        }
        //check image name key exist
        if(array_key_exists("image_name", $upload_img)){
            $gallery_image = $upload_img['image_name'];
        }

        $insertGallery = array(
            'product_id' => $product_id,
            'gallery_image'  => $gallery_image,
            'updated_at'  => datetime(),
            'created_at'  => datetime(),
        );

        $attachment_id = $this->common_model->insertData(PRODUCT_ATTACHMENTS,$insertGallery);

        if(!$attachment_id){

            $this->error_response(get_response_message(107),SERVER_ERROR,500); //error reponse
        }
        $attachmentDetail = $this->common_model->getsingle(PRODUCT_ATTACHMENTS, array('productAttachmentID' => $attachment_id));

        if(!empty($attachmentDetail->gallery_image)){
            $attachmentDetail->gallery_image_url = getenv('AWS_CDN_UPLOAD').'product_gallery/';
        }

        $this->success_response(get_response_message(153),['attachment_detail' => $attachmentDetail]);
        
    }

    //Delete attachment
    function attachments_delete($id){
        //Check user authentication and get user info from auth token
        $this->check_service_auth();
        $attachment_id = $id;

        $delete = $this->common_model->deleteData(PRODUCT_ATTACHMENTS, array('productAttachmentID' => $attachment_id));


        if($delete === TRUE){

            $getAvatar = $this->common_model->get_field_value(PRODUCT_ATTACHMENTS, array('productAttachmentID' => $attachment_id),'gallery_image'); //get user previous image to delete

            $this->load->model('image_model');
            if($getAvatar != NULL){

                $imgPath = 'product_gallery';
                //Delete previous image of user when new image upload
                $del = $this->image_model->delete_image($imgPath,$getAvatar);
            }
           $this->success_response(get_response_message(154)); 
        }

        $this->error_response(get_response_message(107),SERVER_ERROR,500); //error reponse
    }

    //category subcategory list
    function category_list_get(){

        $catSubcatList = $this->product_model->getCatSubcat();
        
        if(!$catSubcatList){
            //set failure msg
            $this->success_response(get_response_message(106)); //error reponse
        }
        //set success msg
        $this->success_response(get_response_message(302),['category'=> $catSubcatList]);
    }

    //PRODUCT LIST API FOR BUYER
    function index_get($id=''){

        $data['userID'] = $this->get('userID'); //for wishlist

        if($this->get('userID') == ''){
            $this->error_response('User ID is required.'); //User ID is required for wishlist
        }
        
        $data['userType'] = $this->get('userType'); //for smilar product show or hide for buyer and  seller respectively
        if(!empty($id)){ //Product detail code here

            //Check product exist or not according to the id
            $exist = $this->checkProductExist($id);
            if($exist === FALSE){
                
                //$this->error_response(get_response_message(159)); //error reponse
                $this->error_response('This product is not longer available. Product deleted by seller.'); //error reponse
            }

            //Product Detail Get
            $productDetail = $this->product_model->productDetail($id,$data['userID'],$data['userType']);
            if(!$productDetail){
                
                $this->success_response(get_response_message(106));
            }
            
            $response = $this->success_response(get_response_message(302), ['product_detail' => $productDetail]);
        }else{

            //Product list code start from here
            $data['searchTerm'] = $this->get('searchTerm');

            if(!empty($this->get('size'))){
                $data['size'] = explode(",", $this->get('size'));
            }
            if(!empty($this->get('category'))){
                $data['category'] = explode(",", $this->get('category'));
            }
            if(!empty($this->get('deal'))){
                $data['deal'] = $this->get('deal'); //product list by deal ID
            }
            if(!empty($this->get('color'))){
                $data['color'] = explode(",", $this->get('color'));
            }
            if(!empty($this->get('price'))){
                $price = explode("|", $this->get('price'));
                $data['price_from'] = $price[0];
                $data['price_to'] = $price[1];
            }

            if(!empty($data['color']) && !empty($data['size'])){

                $data['variants'] = array_merge($data['color'],$data['size']);
            }

            if(!empty($this->get('sort'))){

                if($this->get('sort') == 'popular'){

                    $data['popular'] = $this->get('sort');
                }else{

                    $explode = explode("|", $this->get('sort'));
                    if($explode[1] == 'desc'){

                        $data['price_high'] = $explode[1] ;

                    }else if($explode[1] == 'asc'){
                        $data['price_low'] = $explode[1] ;
                    }
                }
            }
            $data['offset'] = $this->get('offset');
            $data['limit'] = $this->get('limit');

            if(empty($data['limit'])){
                $data['limit'] = 20;
            }
            if(empty($data['offset'])){
               $data['offset'] = 0; 
            }

            $productList = $this->product_model->productList($data); 

            if(empty($productList['product_list'])){ //If NO product found
                $this->success_response(get_response_message(106),['data_found' =>false]); 
            }

            $pagingValue['limit'] = $data['limit'];
            $pagingValue['offset'] = $data['offset'];
            $pagingValue['total_records'] = $productList['total_records'];
            $pagingValue['url'] = 'api/v1/product';
            $pagingValue['searchTerm'] = $this->get('searchTerm') ? $this->get('searchTerm') : '';
            $pagingValue['size'] = $this->get('size') ? $this->get('size') : '';
            $pagingValue['color'] = $this->get('color') ? $this->get('color') : '';
            $pagingValue['category'] = $this->get('category') ? $this->get('category') : '';
            $pagingValue['price'] = $this->get('price') ? $this->get('price') : '';
            $pagingValue['sort'] = $this->get('sort') ? $this->get('sort') : '';
            $pagingValue['userID'] = $this->get('userID') ? $this->get('userID') : 0;

            $paging = paginationValue($pagingValue);
            $productList['paging'] = json_decode($paging);
            $this->success_response(get_response_message(302), $productList);
        }
    }

    //Delete Product
    function index_delete($id){
        //Check user authentication and get user info from auth token
        $this->check_service_auth();
        $user_id = $this->authData->userID;

        if(empty($id)){
            $this->error_response(get_response_message(158));   
        }

        $product_id = $id;

        $exist = $this->checkProductExist($product_id);
        if($exist === FALSE){
            
            $this->error_response(get_response_message(159)); //error reponse
        }

        $where = array('productID' => $product_id);

        //Check product seller ID 
        $product_seller = $this->common_model->get_field_value(PRODUCTS,$where,'seller_id');

        //If product seller ID not match with logged user then give error 
        if($product_seller != $user_id){

           $this->error_response(get_response_message(156));
        }

        $delete = $this->common_model->deleteData(PRODUCTS, $where);


        if($delete === TRUE){

            $getAvatar = $this->common_model->get_field_value(PRODUCTS, $where,'feature_image'); //get product feature image to delete

            $this->load->model('image_model');
            if($getAvatar != NULL){

                $imgPath = 'product';
                //Delete previous image of user when new image upload
                $del = $this->image_model->delete_image($imgPath,$getAvatar);
            }

            //Get product attachments for delete from s3 bucket
            $attachments = $this->product_model->allAttachment(array('product_id' => $product_id));
           
            $this->common_model->deleteData(PRODUCT_ATTACHMENTS, array('product_id' => $product_id)); //Delete product gallery images

            if(!empty($attachments)){
                $imgPath = 'product_gallery';
                
                foreach ($attachments as $attachment) {

                    //Delete previous attachment(gallery image) when delete product
                    $this->image_model->delete_image($imgPath,$attachment['gallery_image']);
                }
            }

           $this->success_response(get_response_message(157)); 
        }

        $this->error_response(get_response_message(107),SERVER_ERROR,500); //error reponse
    }

    //Update Product
    function update_post(){

        //Check user authentication and get user info from auth token
        $this->check_service_auth();
        $user_id = $this->authData->userID; //current user ID;
        $data['userID'] = $user_id; //current user ID for product detail;
        $data['userType'] = 'seller'; //current user type for product detail;

        $this->form_validation->set_rules('product_id', 'Product ID','trim|required');
        $this->form_validation->set_rules('name', 'Product Name','trim|required|min_length[2]|max_length[300]');
        $this->form_validation->set_rules('sku', 'SKU','trim|required|min_length[2]|max_length[64]'); //Product Unique number
        $this->form_validation->set_rules('description', 'Description','trim|required');
        $this->form_validation->set_rules('category', 'Category','trim|required');
        $this->form_validation->set_rules('sub_category', 'Sub Category','trim|required');
        $this->form_validation->set_rules('color', 'Color','trim|required');
        $this->form_validation->set_rules('size', 'Size','trim|required');
        $this->form_validation->set_rules('shipping_applied', 'Shipping Applied','trim|required');  //0:No, 1:Yes

        if($this->post('shipping_applied') == '1'){  //Shipping applied yes then shipping charge required
            $this->form_validation->set_rules('shipping_charge', 'Shipping Charge','trim|required');

            if($this->post('shipping_charge') < 1){
            
                $this->error_response('Shipping charge not be less than 1.'); //error reponse
            }
        }

        if(!empty($this->post('discount'))){  //Discount is not empty then sale price required
            $this->form_validation->set_rules('sale_price', 'Sale Price','trim|required');
            $this->form_validation->set_rules('discount', 'Discount','trim|numeric');

            if($this->post('discount') > 100 || $this->post('discount') < 1){
            
                $this->error_response('Discount must be between 1 and 100'); //error reponse
            }
        }   

        $this->form_validation->set_rules('regular_price', 'Regular Price','trim|required');

        if(!empty($this->post('regular_price'))) {  //If price value is not valid
            if (!preg_match("/^[0-9]{1,10}(([\.]{0,2}[0-9]{0,2}))$/", $this->post('regular_price'))) {

                $this->error_response(get_response_message(137)); //error reponse
            }
        }

        if(!empty($this->post('sale_price'))) {  //If price value is not valid

            if (!preg_match("/^[0-9]{1,10}(([\.]{0,2}[0-9]{0,2}))$/", $this->post('sale_price'))) {

                $this->error_response(get_response_message(137)); //error reponse
            }
        }

        //set response msg  for form validation
        if($this->form_validation->run() == FALSE){

            $this->error_response(strip_tags(validation_errors())); //error reponse
        }

        //Check product exist or not 
        $exist = $this->checkProductExist($this->input->post('product_id'));
        if($exist === FALSE){ //If Not exist or inactive
            
            $this->error_response(get_response_message(159)); //error reponse
        }

        $where = array('productID' => $this->input->post('product_id'));

        //Check product seller ID 
        $product_seller = $this->common_model->get_field_value(PRODUCTS,$where,'seller_id');

        //If product seller ID not match with logged user then give error 
        if($product_seller != $user_id){

           $this->error_response(get_response_message(156));
        }

        $product_name = sanitize_input_text($this->input->post('name')); //product name
        $sku = sanitize_input_text($this->input->post('sku')); //product unique number
        $description = sanitize_input_text($this->input->post('description')); //product description
        $additional_info = sanitize_input_text($this->input->post('additional_info')); //product Addition information
        $category = $this->post('category'); //product category 
        $sub_category = explode(",", $this->post('sub_category')); //product sub category
        array_unshift($sub_category,$category); //Insert category ID in subcategory array first index
        $color = explode(",", $this->post('color')); //product color variant
        $size = explode(",", $this->post('size')); //product size variant
        $shipping_applied = $this->post('shipping_applied'); //0:No, 1:Yes
        $shipping_charge = $this->post('shipping_charge');
        $regular_price = $this->post('regular_price'); //Regular price
        $sale_price = $this->post('sale_price'); //Sale price
        $discount = $this->post('discount'); //Discount percentage
        $in_stock = $this->post('in_stock'); //Product Instock  0:No, 1:Yes
        $is_featured = $this->post('is_featured'); //Product Is featured  0:No, 1:Yes


        //If featured image is empty
        // if(empty($_FILES['featured_image']['name'])){
        //     $this->error_response(get_response_message(139)); //error reponse
        // }

        //upload product featured image
        //$featured_image=NULL;

        $updateProduct = array(
            'seller_id' => $user_id,
            'name'  => $product_name,
            'description'  => $description,
            'additional_information'  => $additional_info,
            'sku'  => $sku,
            'regular_price'  => $regular_price,
            'sale_price'  => $sale_price,
            'sale_discount'  => $discount,
            'in_stock'  => $in_stock,
            'is_featured'  => $is_featured,
            'shipping_applied'  => $shipping_applied,
            'shipping_charge'  => $shipping_charge,
            'updated_at'  => datetime(),
        );

        if(!empty($_FILES['featured_image']['name'])){
            $this->load->model('image_model'); //Load image model
            //if image not empty set it for product featured image 
            $upload_img = $this->image_model->upload_image('featured_image', 'product');
            //check for error
            if( array_key_exists("error", $upload_img) && !empty($upload_img['error'])){

                $this->error_response(strip_tags($upload_img['error'])); //error reponse
            }
            //check image name key exist
            if(array_key_exists("image_name", $upload_img)){
                $featured_image = $upload_img['image_name'];
                $updateProduct['feature_image'] = $featured_image;
            }
        }

        $update = $this->common_model->updateFields(PRODUCTS,$updateProduct,$where);

        //Delete previous category data from table
        $this->common_model->deleteData(PRODUCT_CATEGORY_MAP, array('product_id' =>$this->input->post('product_id')));

        //Insert category and subcategory in mapping table
        foreach ($sub_category as $category) { 

            $setCategoryMapData = array(
                'product_id' => $this->input->post('product_id'),
                'category_id' => $category,
                'updated_at' => datetime(),
                'created_at' => datetime(),
            );
            $this->common_model->insertData(PRODUCT_CATEGORY_MAP,$setCategoryMapData);
        }

        //Delete previous color variant data from table
        $this->common_model->deleteData(PRODUCT_VARIANT_MAP, array('product_id' =>$this->input->post('product_id'),'variant_id' => 2));

        //Insert color variant in mapping table
        foreach ($color as $colors) { 

            $setColorMapData = array(
                'product_id' => $this->input->post('product_id'),
                'variant_id' => 2,   //For color, variant ID is 2
                'variant_value_id' => $colors,
                'updated_at' => datetime(),
                'created_at' => datetime(),
            );
            $this->common_model->insertData(PRODUCT_VARIANT_MAP,$setColorMapData);
        }

        //Delete previous size variant data from table
        $this->common_model->deleteData(PRODUCT_VARIANT_MAP, array('product_id' =>$this->input->post('product_id'),'variant_id' => 1));

        //Insert size variant in mapping table
        foreach ($size as $sizes) { 

            $setSizeMapData = array(
                'product_id' => $this->input->post('product_id'),
                'variant_id' => 1,   //For size, variant ID is 1
                'variant_value_id' => $sizes,
                'updated_at' => datetime(),
                'created_at' => datetime(),
            );
            $this->common_model->insertData(PRODUCT_VARIANT_MAP,$setSizeMapData);
        }

        //product detail response used in chat functionality
        $productDetail = $this->product_model->productDetail($this->input->post('product_id'),$data['userID'],$data['userType']);

        $this->success_response(get_response_message(160),['product_detail' => $productDetail]);
    }

    private function checkProductExist($productID){
        $product = $this->common_model->getsingle(PRODUCTS,array('productID' => $productID));

        if(empty($product)){  //If product not exist
            return FALSE;
        }
        if($product->status == '0'){ //Product inactive
            return FALSE;
        }
        return TRUE;
    }

    //Product list of seller
    function list_get(){
        $this->check_service_auth();
        $data['user_id'] = $this->authData->userID;
        $data['searchTerm'] = $this->get('searchTerm');
        $data['limit'] = $this->get('limit');
        $data['offset'] = $this->get('offset');

        if(empty($data['limit'])){
            $data['limit'] = 20;
        }
        if(empty($data['offset'])){
           $data['offset'] = 0; 
        }

        $myProduct = $this->product_model->myProduct($data); 

        if(empty($myProduct['product_list'])){ //If NO product found
            $this->success_response(get_response_message(106),['data_found' =>false]); 
        }
        $pagingValue['limit'] = $data['limit'];
        $pagingValue['offset'] = $data['offset'];
        $pagingValue['total_records'] = $myProduct['total_records'];
        $pagingValue['url'] = 'api/v1/product/my-product';
        $paging = paginationValueSeller($pagingValue);
        $myProduct['paging'] = json_decode($paging);
        $this->success_response(get_response_message(302), $myProduct);
    }

    //Get variant list
    function variant_list_get(){

        $variantList = $this->product_model->getVariants();
        if(!$variantList){
            
            $this->success_response(get_response_message(106)); //error reponse
        }
        //set success msg
        $this->success_response(get_response_message(302),['variant'=> $variantList]);
    }

    function featured_get(){

        $featured = $this->product_model->featured_product();

        if(empty($featured)){
            $this->success_response(get_response_message(106)); //error reponse
        }

        $this->success_response(get_response_message(302),['featured_list'=> $featured]);
    }

} //End Class