
<?php
/**
* User model
* Buyer related DB queries handle
* version: 1.0 ( 14-02-2020 )
*/

class User_model extends MY_Model {

    function addressList($user_id){

        $this->db->select('addressID,name,phone_dial_code,country_code,mobile_number,
            house_number,locality,city,zip_code,country,is_default');
        $this->db->from(USER_ADDRESS);
        $this->db->where(array('user_id' =>$user_id));
        $sql = $this->db->get();
        if(!$sql){
            $this->output_db_error();
        }
        $result = $sql->result();
        return $result;
    }

    function prepare_select_user_wishlist_query($user_id,$limit,$offset,$is_count=true){ 

        $url_image = getenv('AWS_CDN_PRODUCT_IMG_PATH');
        $url_placeholder = getenv('AWS_CDN_USER_PLACEHOLDER_IMG');
        $currency_symbol = getenv('CURRENCY_SYMB');
        $currency_code = getenv('CURRENCY_CODE');

        if($is_count === FALSE){
            $this->db->select('product.productID,product.name,
            product.sku,product.regular_price,"'.$currency_code.'" AS currency_code,"'.$currency_symbol.'" AS currency_symbol,
            product.sale_price,product.in_stock,product.status,product.feature_image,

            GROUP_CONCAT(DISTINCT(pro_cat_map1.category_id) SEPARATOR ",") as category_id,

            GROUP_CONCAT(DISTINCT(category1.name) SEPARATOR ",") as category_name,

            (case when( product.feature_image = "" OR product.feature_image IS NULL) 
                THEN "'.$url_placeholder.'"
                ELSE
                    "'.$url_image.'" 
                END ) AS feature_image_url
            ');

        }else{ 
           
            $this->db->select('COUNT(DISTINCT(product.productID)) as total_records');
        }        
        $this->db->from(PRODUCTS.' as product'); 

        $this->db->join(USER_WISHLIST.' as user_wishlist', ' product.productID = user_wishlist.product_id','left'); 

        $this->db->join(PRODUCT_CATEGORY_MAP. ' as pro_cat_map', ' product.productID = pro_cat_map.product_id','left');
        $this->db->join(PRODUCT_CATEGORY_MAP. ' as pro_cat_map1', ' product.productID = pro_cat_map1.product_id','left');

        $this->db->join(CATEGORY. ' as category', ' category.categoryID = pro_cat_map.category_id');
        $this->db->join(CATEGORY. ' as category1', ' category1.categoryID = pro_cat_map1.category_id');

        $this->db->where(array('user_wishlist.user_id'=>$user_id));
    }

    function userWishlist($user_id,$limit,$offset){

        //get total records count
        $this->prepare_select_user_wishlist_query($user_id,$limit,$offset,true);
        $sql = $this->db->get(); 
        if(!$sql){
            $this->output_db_error();
        }
        $count_result = $sql->row();
        $total_count = $count_result->total_records;
        
        //get WISHLIST list
        $this->prepare_select_user_wishlist_query($user_id,$limit,$offset,false);
        
        $this->db->group_by('pro_cat_map.product_id');
        $this->db->limit($limit, $offset);
        $query = $this->db->get(); 
        if(!$query){
            $this->output_db_error();
        }
        $user_wishlist = $query->result();
        return array('data_found' => true,'total_records'=>$total_count, 'user_wishlist'=>$user_wishlist); 
    }

    //user order list
    function get_order_list($user_id){
        $this->db->select('orderID,number,seller_id,current_status,ordered_by_user_id,grand_total,created_at');
        $this->db->select('CASE
            WHEN current_status = "0"  
            THEN (CONCAT("Your order placed on, ", DATE_FORMAT(created_at, "%D %b %Y - %h:%i %p")))
            WHEN current_status = "1"  
            THEN (CONCAT("Your order approved on, ", DATE_FORMAT(created_at, "%D %b %Y - %h:%i %p")))
            WHEN current_status = "2"  
            THEN (CONCAT("Your order packed on, ", DATE_FORMAT(created_at, "%D %b %Y - %h:%i %p")))
            WHEN current_status = "3"  
            THEN (CONCAT("Your order shipped on, ", DATE_FORMAT(created_at, "%D %b %Y - %h:%i %p")))
            WHEN current_status = "4"  
            THEN (CONCAT("Your order delivered on, ", DATE_FORMAT(created_at, "%D %b %Y - %h:%i %p")))
            ELSE (CONCAT("Your order cancelled on, ", DATE_FORMAT(created_at, "%D %b %Y - %h:%i %p")))
            END as current_status');

        $this->db->select('CASE
            WHEN current_status = "0"  
            THEN (CONCAT("Your order placed on, ", created_at))
            WHEN current_status = "1"  
            THEN (CONCAT("Your order approved on, ", updated_at))
            WHEN current_status = "2"  
            THEN (CONCAT("Your order packed on, ", updated_at))
            WHEN current_status = "3"  
            THEN (CONCAT("Your order shipped on, ", updated_at))
            WHEN current_status = "4"  
            THEN (CONCAT("Your order delivered on, ", updated_at))
            ELSE (CONCAT("Your order cancelled on, ", updated_at))
            END as current_tracking_status_datetime');
        
        $this->db->from(ORDERS);
        $this->db->where('ordered_by_user_id',$user_id);
        $this->db->order_by("orderID", "desc");
        $data = $this->db->get();
        $orderData = $data->result();
        foreach ($orderData as $key => $value) {
            $orderData[$key]->products = $this->get_product($value->orderID);
            $orderData[$key]->product = json_decode($this->get_product_json($value->orderID));
        }
        return $orderData;
    }//End of order listing function

    function get_product($order_id){
        $defaultId = getenv('DEFAULT_CATEGORY_ID'); //Non deletable 
       
        $url_image = getenv('AWS_CDN_PRODUCT_IMG_PATH');
        $url_placeholder = getenv('AWS_CDN_USER_PLACEHOLDER_IMG');
        $currency_symbol = getenv('CURRENCY_SYMB');
        $currency_code = getenv('CURRENCY_CODE');

        $this->db->select('GROUP_CONCAT(DISTINCT(category.name) SEPARATOR ",")as category_name,"'.$currency_code.'" AS currency_code,"'.$currency_symbol.'" AS currency_symbol,
            product.name,product.feature_image,product.productID,
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

}//End Class
