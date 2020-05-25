<?php
/**
* Web service cart model
* Handles web service get cart request
*/
class Cart_model extends MY_Model {

    function getCartItems($user_id,$product_id,$variants){

        $this->db->select('cart_item.cartItemID, cart_item.user_id, cart_item.product_id,cart_item.variant_value_id,cart_item.quantity');
        $this->db->from(CART_ITEMS.' as cart_item');    
        $this->db->where(array('user_id'=>$user_id, 'product_id'=>$product_id,'variant_value_id'=>$variants));
        
        $query = $this->db->get(); 
        $cartItem = $query->row();
        return $cartItem;
    }

    function prepare_select_cart_item_query($data,$is_count=true){ 
        $currency_symbol = getenv('CURRENCY_SYMB');
        $currency_code = getenv('CURRENCY_CODE');
        $url_image = getenv('AWS_CDN_PRODUCT_THUMB_IMG');
        $url_placeholder = getenv('AWS_CDN_USER_PLACEHOLDER_IMG');
      
        if($is_count === FALSE){
            $this->db->select('cart_item.cartItemID,product.productID,
                product.name,product.sku,product.seller_id,product.regular_price,
                "'.$currency_code.'" AS currency_code,"'.$currency_symbol.'" AS currency_symbol,product.sale_price,product.in_stock,
                product.status,cart_item.quantity,cart_item.variant_value_id,
                product.shipping_applied,product.shipping_charge,
                GROUP_CONCAT(DISTINCT(pro_cat_map1.category_id) SEPARATOR ", ") as category_id,

                GROUP_CONCAT(DISTINCT(category1.name) SEPARATOR ", ") as category_name,

                (case when( product.feature_image = "" OR product.feature_image IS NULL) 
                THEN "'.$url_placeholder.'"
                ELSE
                concat("'.$url_image.'", product.feature_image) 
                END ) as feature_image
            ');
        
        }else{ 
           
            $this->db->select('COUNT(DISTINCT(cart_item.cartItemID)) as total_records');
        }        
        $this->db->from(PRODUCTS.' as product'); 
        $this->db->join(CART_ITEMS.' as cart_item', ' product.productID = cart_item.product_id','left'); 

        $this->db->join(PRODUCT_CATEGORY_MAP. ' as pro_cat_map', ' product.productID = pro_cat_map.product_id','left');
        $this->db->join(PRODUCT_CATEGORY_MAP. ' as pro_cat_map1', ' product.productID = pro_cat_map1.product_id','left');

        $this->db->join(CATEGORY. ' as category', ' category.categoryID = pro_cat_map.category_id');
        $this->db->join(CATEGORY. ' as category1', ' category1.categoryID = pro_cat_map1.category_id');

        $this->db->join(VARIANT_VALUES. ' as variant' ,"FIND_IN_SET(variant.variantValueID,cart_item.variant_value_id) > 0");

        $this->db->where('cart_item.user_id', $data['user_id']);

    }

    function cartItemList($data){
        //get total records count
        $this->prepare_select_cart_item_query($data,true);
        $sql = $this->db->get();
        if(!$sql){
            $this->output_db_error();
        }
        $count_result = $sql->row();
        $total_count = $count_result->total_records;

        //get cart list
        $this->prepare_select_cart_item_query($data,false);
        $this->db->group_by('cart_item.cartItemID');
        $this->db->limit($data['limit'], $data['offset']);
        $this->db->order_by('cart_item.cartItemID', 'desc');
        $sql = $this->db->get(); 
        if(!$sql){
            $this->output_db_error();
        }
        $cart_list = $sql->result();

        foreach ($cart_list as $key => $value) { 
            $cart_list[$key]->variant = $this->getCartItemVariants($value);
        }

        $pricing_detail = $this->cartItemPricingDetail($data);
        $tax_detail = $this->cartItemTaxDetail($pricing_detail);
        return array('total_records'=>$total_count, 'cart_list'=>$cart_list,'pricing_detail' => $pricing_detail, 'tax_detail' => $tax_detail);
    }

    function cartItemTaxDetail($pricing_detail){ //For Tax calcualtion
        $tax_detail = array();
        $tax_detail['tax_percent'] = $this->common_model->get_field_value(SETTING_OPTIONS, array('option_name' => 'tax_percent'), 'option_value');

        $tax_detail['tax_amount'] = number_format(($pricing_detail->subtotal * $tax_detail['tax_percent'])/100, 2,'.', '');
        return $tax_detail;
    }

    function cartItemPricingDetail($data){
        $currency_symbol = getenv('CURRENCY_SYMB');
        $currency_code = getenv('CURRENCY_CODE');
        $this->db->select('IF(SUM(cart_item.quantity) IS NOT NULL,SUM(cart_item.quantity),0) AS total_item, 

            GROUP_CONCAT(DISTINCT(product.seller_id) SEPARATOR ", ") as seller_id,

            GROUP_CONCAT(DISTINCT(cart_item.product_id) SEPARATOR ", ") as product_id,

            "'.$currency_code.'" AS currency_code,"'.$currency_symbol.'" AS currency_symbol,

            IF(SUM(IF(product.sale_price =0.00,(product.regular_price*cart_item.quantity),
            (product.sale_price*cart_item.quantity))) IS NOT NULL,

            SUM(IF(product.sale_price =0.00,(product.regular_price*cart_item.quantity),
            (product.sale_price*cart_item.quantity))),

            0) AS subtotal');
        $this->db->from(CART_ITEMS.' as cart_item'); 
        $this->db->join(PRODUCTS.' as product', ' product.productID = cart_item.product_id','left'); 
        $this->db->where(array('cart_item.user_id' => $data['user_id'],'product.in_stock' => '1'));
        $sql = $this->db->get();
        $pricing_detail = $sql->row();
        
        if( $pricing_detail->seller_id != '' ){
            $pricing_detail->shipping_charge = $this->shippingDetail($pricing_detail->seller_id, $pricing_detail->product_id);

            $tax_percent = $this->common_model->get_field_value(SETTING_OPTIONS, array('option_name' => 'tax_percent'), 'option_value');

            $tax_amount = number_format(($pricing_detail->subtotal * $tax_percent)/100, 2,'.', '');


            $pricing_detail->total_amount = number_format($pricing_detail->subtotal + $pricing_detail->shipping_charge + $tax_amount ,2,'.', '');//convert into 2 decimal points
        }else{
            $pricing_detail->total_amount = "0.00";
        }
        return $pricing_detail;
    }

    function shippingDetail($seller_id,$product_id){
        $query = $this->db->query('SELECT SUM(shipping) AS shipping_charge FROM( SELECT MAX(shipping_charge) as shipping FROM products WHERE seller_id IN('.$seller_id.')  AND productID IN('.$product_id.') GROUP BY seller_id ) AS shipping_charge') ;
        $shipping_charge = $query->row(); 
        return $shipping_charge->shipping_charge;
    }

    function getCartItemVariants($value){
        $this->db->select('variants.variantID,variants.name');
        $this->db->from(VARIANTS.' as variants');    
        $query = $this->db->get(); 
        $variants  = $query->result();

        foreach ($variants as $key => $value1) {

            $this->db->select('variant.variantValueID,
            variant.variant_value');
            $this->db->from(CART_ITEMS. ' as cart_item');
            $this->db->join(VARIANT_VALUES. ' as variant', "FIND_IN_SET(variant.variantValueID,cart_item.variant_value_id) > 0");
            $this->db->where(array('cartItemID'=>$value->cartItemID,'variant_id'=>$value1->variantID));
            $query = $this->db->get();
            $variants[$key]->variant_value = $query->row();

        }
        return $variants;
    }

    /* Get total records of any table */
    function get_total_count($table, $where=''){

        $this->db->select('SUM(quantity) as total_records');
        $this->db->from($table);
        if(!empty($where))
            $this->db->where($where);
        
        $query = $this->db->get();
        return $query->row(); //total records
    } 
} //End Class