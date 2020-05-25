<?php
class Order_model extends MY_Model {

    public function __construct(){
        parent::__construct();

        $this->load->model('seller_model'); //Load seller model handles all seller related DB queries
    }

    //function for detail of order
    function get_order_detail($order_id){

        $url_placeholder = getenv('AWS_CDN_USER_PLACEHOLDER_IMG');
        $url_image = getenv('AWS_CDN_USER_THUMB_IMG');
        $currency_symbol = getenv('CURRENCY_SYMB');
        $currency_code = getenv('CURRENCY_CODE');

        $this->db->select('order.orderID,order.number as order_number, order.order_status as accept_reject_status, order.current_status as current_status_number
            ,order.tracking_number,order.item_total,order.shipping_price,order.payment_mode,order.payment_status,order.grand_total, order.tax_percentage, order.tax_amount, "'.$currency_code.'" AS currency_code, "'.$currency_symbol.'" AS currency_symbol,order.order_type,order.created_at,
            order.ordered_by_user_id, order.commission_percentage, order.commission_amount,  user.full_name, 

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
            END as current_tracking_status_datetime');

        $this->db->from(ORDERS. ' as order');
        $this->db->join(USERS.' as user', "user.userID = order.ordered_by_user_id",'left');
        $this->db->where('orderID',$order_id);
        $data = $this->db->get();
        $orderData = $data->row();

        $orderData->tracking_status = $this->get_tracking_detail($orderData->orderID);
        $orderData->order_address = $this->get_address_detail($orderData->orderID);
        $orderData->products = $this->get_product_detail($orderData->orderID); //old and not in use
        $orderData->product = $this->get_product_json($orderData->orderID);
        return $orderData;
    }//end of order listing function

    //function for detail of order at buyer side
    function buyer_order_detail($order_id){

        $url_placeholder = getenv('AWS_CDN_USER_PLACEHOLDER_IMG');
        $url_image = getenv('AWS_CDN_USER_THUMB_IMG');
        $currency_symbol = getenv('CURRENCY_SYMB');
        $currency_code = getenv('CURRENCY_CODE');

        $this->db->select('order.orderID,order.number as order_number,order.ordered_by_user_id, order.order_status 
            as accept_reject_status, order.current_status as current_status_number,order.tracking_number,
            order.item_total,order.shipping_price,order.payment_mode,order.payment_status, order.tax_percentage, order.tax_amount,
            order.grand_total,"'.$currency_code.'" AS currency_code, "'.$currency_symbol.'" AS currency_symbol,order.order_type,order.created_at,
            order.seller_id,user.full_name, user.is_verified as is_seller_verified, 

            COALESCE(FORMAT(SUM(ratings.rating)/count(ratings.rating),1),"0") as average_rating,
            COALESCE(COUNT(ratings.ratingID),"0") as rating_count,

            (case when( user.avatar = "" OR user.avatar IS NULL) 
            THEN "'.$url_placeholder.'"
            when( user.is_avatar_url = "2" ) 
            THEN user.avatar
            ELSE
            concat("'.$url_image.'",user.avatar) 
            END ) as seller_image');

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
            END as current_tracking_status_datetime');

        $this->db->from(ORDERS. ' as order');
        $this->db->join(USERS.' as user', "user.userID = order.seller_id",'left');
        $this->db->join(RATINGS.' as ratings', "ratings.rating_for = order.seller_id",'left');
        $this->db->where('orderID',$order_id);
        $data = $this->db->get();
        $orderData = $data->row();

        $orderData->tracking_status = $this->get_tracking_detail($orderData->orderID);
        $orderData->order_address = $this->get_address_detail($orderData->orderID);
        $orderData->products = $this->get_product_detail($orderData->orderID); //old and not in use
        $orderData->product = $this->get_product_json($orderData->orderID);
        $orderData->rating = $this->get_rating($orderData->seller_id,$orderData->ordered_by_user_id, $order_id);
        return $orderData;
    }//end of order detail function

    function get_product_detail($order_id){

        $defaultId = getenv('DEFAULT_CATEGORY_ID');
        $url_image = getenv('AWS_CDN_PRODUCT_IMG_PATH');
        $url_placeholder = getenv('AWS_CDN_USER_PLACEHOLDER_IMG');
        $currency_symbol = getenv('CURRENCY_SYMB');
        $currency_code = getenv('CURRENCY_CODE');
        $this->db->select('order_item.variant_value_id,
            GROUP_CONCAT(DISTINCT(category.name) SEPARATOR ",")as category_name,
             
            product.productID,product.name,product.regular_price,product.sale_price,product.in_stock,order_item.item_quantity,product.status,product.feature_image,product.sale_price,product.description,product.sku, "'.$currency_code.'" AS currency_code, "'.$currency_symbol.'" AS currency_symbol,
                GROUP_CONCAT(DISTINCT(variant.variant_value) SEPARATOR ", ") as variant_value,

                (case when( product.feature_image = "" OR product.feature_image IS NULL) 
                THEN "'.$url_placeholder.'"
                ELSE
                    "'.$url_image.'" 
                END ) AS feature_image_url');
        $this->db->from(PRODUCTS.' as product');
        $this->db->join(ORDER_ITEMS.' as order_item', "order_item.product_id = product.productID",'left');
        $this->db->join(PRODUCT_CATEGORY_MAP.' as product_cat_map',"product_cat_map.product_id = product.productID",'left');
        $this->db->join(CATEGORY.' as category', "product_cat_map.category_id = category.categoryID",'left');
        $this->db->join(VARIANT_VALUES. ' as variant' ,"FIND_IN_SET(variant.variantValueID,order_item.variant_value_id) > 0");
        $this->db->where('order_item.order_id',$order_id);
        $this->db->where('category.categoryID!=',$defaultId);
        $this->db->where('category.parent_category_id!=',$defaultId);
        $this->db->group_by('order_item.orderItemID');
        $data = $this->db->get()->result();
        
        foreach ($data as $key => $value) {

            $this->db->select('variants.variantID,variants.name');
            $this->db->from(VARIANTS.' as variants');    
            $this->db->where(array('status'=>'1'));
            $query = $this->db->get();

            $data[$key]->variants = $query->result();

            foreach ($data[$key]->variants as $k => $val) {

                $this->db->select('variant.variant_value');
                $this->db->from(ORDER_ITEMS.' as order_item');
                $this->db->join(VARIANT_VALUES. ' as variant' ,"FIND_IN_SET(variant.variantValueID,'".$value->variant_value_id."') > 0");
                $this->db->where(array('variant.variant_id'=>$val->variantID));   
                $this->db->group_by('variant.variantValueID');   
                $query = $this->db->get();  
                $data[$key]->variants[$k]->value = $query->row()->variant_value;
            }
        } 
        return $data;
    }//end of get category

    //get product json
    function get_product_json($order_id){

        $this->db->select('order_item.order_info_json');
        $this->db->from(ORDER_ITEMS.' as order_item');
        $this->db->where('order_item.order_id',$order_id);
        $data = $this->db->get()->result();

        $result = array();
        foreach ($data as $key => $value) {
           
            $result[$key] = json_decode($value->order_info_json);
        }
        return $result;
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

    //function for tracking data
    function get_address_detail($order_id){
        $this->db->select('*');
        $this->db->from(ORDER_ADDRESS);
        $this->db->where('order_id',$order_id);
        $data = $this->db->get();
        return $data->row();
    }//end of get_address_detail  function

    //function for get rating review given by user to seller
    function get_rating($seller_id, $buyer_id,$order_id){
        $this->db->select('*');
        $this->db->from(ORDER_RATINGS);
        $this->db->where(array ('rating_for' => $seller_id, 'rating_by' => $buyer_id, 'order_id' => $order_id));
        $query = $this->db->get();
        if(!$query){
            $this->output_db_error();
        }
        $result = ($query->row()) ? $query->row() : new stdClass();
        return $result;

    }//end of function
}
