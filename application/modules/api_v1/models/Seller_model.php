<?php
/**
* Seller model
* Seller related DB queries handle
* version: 1.0 ( 31-01-2020 )
*/

class Seller_model extends MY_Model {

	function seller_info($seller_id){

		$this->db->select('user.userID,user.full_name,user.email,business_info.name as business_name,business_info.address as business_address,
			business_info.license,user.avatar, user.is_verified as is_seller_verified');
		$this->db->from(USERS. ' as user');
		$this->db->join(SELLER_BUSINESS_INFO. ' as business_info', ' business_info.user_id = user.userID','left');
		$this->db->where(array('userID' => $seller_id));
		$sql = $this->db->get();
        if(!$sql){
            $this->output_db_error();
        }
        $result = $sql->row();
        if(!empty($result)){
            if (!empty($result->avatar) || $result->avatar != NULL) {
                $image = $result->avatar;
                //check if image consists url- happens in social login case
                if (filter_var($result->avatar, FILTER_VALIDATE_URL)) { 
                    $result->avatar = $image;
                }
                else{
                    $result->avatar = getenv('AWS_CDN_USER_THUMB_IMG').$image; 
                }
            }
            else{
                $result->avatar = getenv('AWS_CDN_USER_PLACEHOLDER_IMG'); //return default image if image is empty
            }
        }
        return $result;
	}

    //function for get seller rating and review for seller info screen
    function seller_rating_review($seller_id){
        $url = getenv('AWS_CDN_USER_THUMB_IMG');
        $default = getenv('AWS_CDN_USER_PLACEHOLDER_IMG');
        
        $this->db->select('user.userID,user.full_name,DATE_FORMAT(rating.created_at, "%e %b %Y") as review_date,rating.review,rating.rating,

            (case when( user.avatar = "" OR user.avatar IS NULL) 
                THEN "'.$default.'"
                when( user.is_avatar_url = "2" ) 
                    THEN user.avatar
                ELSE
                    concat("'.$url.'",user.avatar) 
                END ) as buyer_image');
        $this->db->from(USERS.' as user');
        $this->db->join(RATINGS.' as rating', "rating.rating_by = user.userID",'left');
        $this->db->where('rating.rating_for',$seller_id);
        $data['rating_review'] = $this->db->get()->result();
        $data['rating_count']= $this->seller_rating($seller_id);
        return $data;

    }//End Of Function

    function seller_rating($seller_id){

        $this->db->select('COALESCE(FORMAT(SUM(rating)/count(rating),1),"0") as average_rating,

            COUNT(rating) as total_rating,
            (SELECT COUNT(rating) FROM ratings WHERE rating =1.0 AND rating_for = '.$seller_id.') as one_star,
            (SELECT COUNT(rating) FROM ratings WHERE rating =2.0 AND rating_for = '.$seller_id.') as two_star,
            (SELECT COUNT(rating) FROM ratings WHERE rating =3.0 AND rating_for = '.$seller_id.') as three_star,
            (SELECT COUNT(rating) FROM ratings WHERE rating =4.0 AND rating_for = '.$seller_id.') as four_star,
            (SELECT COUNT(rating) FROM ratings WHERE rating =5.0 AND rating_for = '.$seller_id.') as five_star,

            (SELECT COUNT(review) FROM ratings WHERE review IS NOT NULL AND rating_for = '.$seller_id.') as total_review
            '
            );
        $this->db->from(RATINGS);
        $this->db->where('rating_for',$seller_id);
        $data = $this->db->get()->row();
        return $data;
    }

    //seller order list
    function new_order_list($seller_id,$data){

        $url_placeholder = getenv('AWS_CDN_USER_PLACEHOLDER_IMG');
        $url_image = getenv('AWS_CDN_USER_THUMB_IMG');
        $currency_symbol = getenv('CURRENCY_SYMB');
        $currency_code = getenv('CURRENCY_CODE');

        $this->db->select('order.orderID, order.number, order.seller_id, order.current_status, order.ordered_by_user_id, order.grand_total, "'.$currency_code.'" AS currency_code, "'.$currency_symbol.'" AS currency_symbol, DATE_FORMAT(order.created_at, "%e %b %Y") as order_placed_date, order.created_at as tracking_status_datetime, user.full_name, order_address.phone_dial_code, order_address.country_code, order_address.mobile_number, order_address.house_number, order_address.locality, order_address.city, order_address.zip_code, order_address.country,

            (case when( user.avatar = "" OR user.avatar IS NULL) 
            THEN "'.$url_placeholder.'"
            when( user.is_avatar_url = "2" ) 
            THEN user.avatar
            ELSE
            concat("'.$url_image.'",user.avatar) 
            END ) as buyer_image,');

        $this->db->from(ORDERS.' as order');
        $this->db->join(USERS.' as user', "user.userID = order.ordered_by_user_id",'left');
        $this->db->join(ORDER_ADDRESS.' as order_address', "order_address.order_id = order.orderID",'left');
        $this->db->join(ORDER_ITEMS.' as order_item', "order_item.order_id = order.orderID",'left');
        $this->db->join(PRODUCTS.' as product', "order_item.product_id = product.productID",'left');

        if(!empty($data['searchTerm'])){

            $this->db->group_start();
                $this->db->like('order.number', $data['searchTerm'], 'both');
                $this->db->or_like('product.name', $data['searchTerm'], 'both');
            $this->db->group_end();
        }

        $this->db->where(array('order.seller_id'=> $seller_id, 'order.current_status' => 0));
        $this->db->order_by("order.orderID", "desc");
        $this->db->group_by('order.orderID');
        $this->db->limit($data['limit'], $data['offset']);
        $query = $this->db->get();

        if(!$query){
            $this->output_db_error();
        }

        $orderData = $query->result();
        $total_count = $this->total_order($seller_id, 'new_order', $data);
        foreach ($orderData as $key => $value) {
            $orderData[$key]->products = $this->get_product($value->orderID);
            $orderData[$key]->product = json_decode($this->get_product_json($value->orderID));
        }

        if(!empty($orderData)){
            return array('data_found' => true, 'total_records'=>$total_count->total_records, 'new_order_list'=>$orderData);
        }else{
            return array('data_found' => false, 'total_records'=>0, 'new_order_list'=>$orderData);
        }

    }//End of order listing function

    //seller order list
    function my_order_list($seller_id,$data){

        $url_placeholder = getenv('AWS_CDN_USER_PLACEHOLDER_IMG');
        $url_image = getenv('AWS_CDN_USER_THUMB_IMG');
        $currency_symbol = getenv('CURRENCY_SYMB');
        $currency_code = getenv('CURRENCY_CODE');

        $this->db->select('order.orderID, order.number, order.seller_id, order.current_status as current_status_number, order.ordered_by_user_id, order.grand_total, "'.$currency_code.'" AS currency_code, "'.$currency_symbol.'" AS currency_symbol, DATE_FORMAT(order.created_at, "%e %b %Y") as order_placed_date, order.created_at as tracking_status_datetime, user.full_name, order_address.phone_dial_code, order_address.country_code, order_address.mobile_number, order_address.house_number, order_address.locality, order_address.city, order_address.zip_code, order_address.country,

            (case when( user.avatar = "" OR user.avatar IS NULL) 
            THEN "'.$url_placeholder.'"
            when( user.is_avatar_url = "2" ) 
            THEN user.avatar
            ELSE
            concat("'.$url_image.'",user.avatar) 
            END ) as buyer_image');

        $this->db->select('CASE
            WHEN order.current_status = "0"  
            THEN (CONCAT("Your order placed on, ", DATE_FORMAT(order.created_at, "%D %b %Y - %h:%i %p")))
            WHEN order.current_status = "1"  
            THEN (CONCAT("Your order approved on, ", DATE_FORMAT(order.created_at, "%D %b %Y - %h:%i %p")))
            WHEN order.current_status = "2"  
            THEN (CONCAT("Your order packed on, ", DATE_FORMAT(order.created_at, "%D %b %Y - %h:%i %p")))
            WHEN order.current_status = "3"  
            THEN (CONCAT("Your order shipped on, ", DATE_FORMAT(order.created_at, "%D %b %Y - %h:%i %p")))
            WHEN order.current_status = "4"  
            THEN (CONCAT("Your order delivered on, ", DATE_FORMAT(order.created_at, "%D %b %Y - %h:%i %p")))
            ELSE (CONCAT("Your order cancelled on, ", DATE_FORMAT(order.created_at, "%D %b %Y - %h:%i %p")))
            END as current_status');

        $this->db->select('CASE
            WHEN order.current_status = "0"  
            THEN (CONCAT("Your order placed on, ", order.created_at))
            WHEN order.current_status = "1"  
            THEN (CONCAT("Your order approved on, ", order.updated_at))
            WHEN order.current_status = "2"  
            THEN (CONCAT("Your order packed on, ", order.updated_at))
            WHEN order.current_status = "3"  
            THEN (CONCAT("Your order shipped on, ", order.updated_at))
            WHEN order.current_status = "4"  
            THEN (CONCAT("Your order delivered on, ", order.updated_at))
            ELSE (CONCAT("Your order cancelled on, ", order.updated_at))
            END as tracking_status_datetime');

        $this->db->from(ORDERS.' as order');
        $this->db->join(USERS.' as user', "user.userID = order.ordered_by_user_id",'left');
        $this->db->join(ORDER_ADDRESS.' as order_address', "order_address.order_id = order.orderID",'left');
        $this->db->join(ORDER_ITEMS.' as order_item', "order_item.order_id = order.orderID",'left');
        $this->db->join(PRODUCTS.' as product', "order_item.product_id = product.productID",'left');

        if(!empty($data['searchTerm'])){

            $this->db->group_start();
                $this->db->like('order.number', $data['searchTerm'], 'both');
                $this->db->or_like('product.name', $data['searchTerm'], 'both');
            $this->db->group_end();
        }

        $this->db->where(array('order.seller_id'=> $seller_id, 'order.order_status' => 1));
        $this->db->order_by("order.orderID", "desc");
        $this->db->group_by('order.orderID');
        $this->db->limit($data['limit'], $data['offset']);
        $query = $this->db->get();

        if(!$query){
            $this->output_db_error();
        }
        $orderData = $query->result();
        $total_count = $this->total_order($seller_id, 'my_order', $data);
        foreach ($orderData as $key => $value) {
            $orderData[$key]->products = $this->get_product($value->orderID);
            $orderData[$key]->product = json_decode($this->get_product_json($value->orderID));
            $orderData[$key]->tracking_status = $this->get_tracking_detail($value->orderID);
        }

        if(!empty($orderData)){
            return array('data_found' => true, 'total_records'=>$total_count->total_records, 'my_order_list'=>$orderData);
        }else{
            return array('data_found' => false, 'total_records'=>0, 'my_order_list'=>$orderData);
        }

    }//End of order listing function

    function total_order($seller_id, $type, $data){
        $this->db->select('COUNT(orderID) as total_records');
        $this->db->from(ORDERS.' as order');
        $this->db->join(ORDER_ITEMS.' as order_item', "order_item.order_id = order.orderID",'left');
        $this->db->join(PRODUCTS.' as product', "order_item.product_id = product.productID",'left');

        if(!empty($data['searchTerm'])){

            $this->db->group_start();
                $this->db->like('order.number', $data['searchTerm'], 'both');
                $this->db->or_like('product.name', $data['searchTerm'], 'both');
            $this->db->group_end();
        }

        if($type == 'my_order'){
            $this->db->where(array('order.seller_id'=> $seller_id, 'order.order_status' => 1));

        }else if($type == 'new_order'){

            $this->db->where(array('order.seller_id'=> $seller_id, 'order.current_status' => 0));
        }
        $this->db->group_by('order_item.product_id');
        $sql = $this->db->get();
        $result = $sql->row();
        return $result;
    }

    function get_product($order_id){
        $defaultId = getenv('DEFAULT_CATEGORY_ID'); //Non deletable 
       
        $url_image = getenv('AWS_CDN_PRODUCT_IMG_PATH');
        $url_placeholder = getenv('AWS_CDN_USER_PLACEHOLDER_IMG');

        $this->db->select('GROUP_CONCAT(DISTINCT(category.name) SEPARATOR ",")as category_name,product.productID,product.name,product.feature_image,
            (case when( product.feature_image = "" OR product.feature_image IS NULL) 
                THEN "'.$url_placeholder.'"
            ELSE
                "'.$url_image.'" 
            END ) AS feature_image_url');
        $this->db->from(ORDER_ITEMS.' as order_item');
        $this->db->join(PRODUCTS.' as product', "order_item.product_id = product.productID",'left');
        $this->db->join(PRODUCT_CATEGORY_MAP.' as category_map',"category_map.product_id = product.productID",'left');
        $this->db->join(CATEGORY.' as category', "category_map.category_id = category.categoryID",'left');
        $this->db->where('order_item.order_id',$order_id);
        $this->db->group_by('product.productID');
        $this->db->where('category.categoryID!=',$defaultId);
        $this->db->where('category.parent_category_id!=',$defaultId);
        $this->db->limit(1,0);
        $data = $this->db->get()->row();
        return $data;
    }

    //get product json
    function get_product_json($order_id){

        $this->db->select('order_item.order_info_json as product');
        $this->db->from(ORDER_ITEMS.' as order_item');
        $this->db->where('order_item.order_id',$order_id);
        $data = $this->db->get()->row();
        return $data->product;
    }

    function get_tracking_detail($order_id){
        $this->db->select('orderTrackingID,order_id,order_status as current_status,order_tracking_number');
        $this->db->select('CASE
            WHEN order_status = "0"  
            THEN (CONCAT("Your order placed on, ", DATE_FORMAT(created_at, "%D %b %Y - %h:%i %p")))
            WHEN order_status = "1"  
            THEN (CONCAT("Your order approved on, ", DATE_FORMAT(created_at, "%D %b %Y - %h:%i %p")))
            WHEN order_status = "2"  
            THEN (CONCAT("Your order packed on, ", DATE_FORMAT(created_at, "%D %b %Y - %h:%i %p")))
            WHEN order_status = "3"  
            THEN (CONCAT("Your order shipped on, ", DATE_FORMAT(created_at, "%D %b %Y - %h:%i %p")))
            WHEN order_status = "4"  
            THEN (CONCAT("Your order delivered on, ", DATE_FORMAT(created_at, "%D %b %Y - %h:%i %p")))
            ELSE (CONCAT("Your order cancelled on, ", DATE_FORMAT(created_at, "%D %b %Y - %h:%i %p")))
            END as current_status');

        $this->db->select('CASE
            WHEN order_status = "0"  
            THEN (CONCAT("Your order placed on, ", created_at))
            WHEN order_status = "1"  
            THEN (CONCAT("Your order approved on, ", created_at))
            WHEN order_status = "2"  
            THEN (CONCAT("Your order packed on, ", created_at))
            WHEN order_status = "3"  
            THEN (CONCAT("Your order shipped on, ", created_at))
            WHEN order_status = "4"  
            THEN (CONCAT("Your order delivered on, ", created_at))
            ELSE (CONCAT("Your order cancelled on, ", created_at))
            END as current_tracking_status_datetime');
        $this->db->from(ORDER_TRACKING);
        $this->db->where('order_id',$order_id);
        $data = $this->db->get();
        return $data->result();
    }//end of tracking detail function

}//End Class