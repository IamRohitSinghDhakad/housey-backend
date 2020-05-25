<?php
/**
* Product model
* Product related DB queries handle
* version: 1.0 ( 04-02-2020 )
*/

class Product_model extends MY_Model {

    function getCatSubcat(){
        $default_category_id = getenv('DEFAULT_CATEGORY_ID'); //Non deletable category ID
        $url_placeholder = getenv('AWS_CDN_CATEGORY_PLACEHOLDER_IMG');
        $url_image = getenv('CDN_CATEGORY_MEDIUM_IMG');
        $this->db->select('cat.categoryID,cat.name,cat.description,
            cat.image,cat.parent_category_id,cat.status,

            (case when(cat.image = "" OR cat.image IS NULL) 
            THEN "'.$url_placeholder.'"
                ELSE
                    concat("'.$url_image.'", cat.image) 
                END ) as image');
        $this->db->from(CATEGORY.' as cat');    
        $this->db->where(array('parent_category_id'=>'0', 'cat.categoryID !='=> $default_category_id));
        $query = $this->db->get(); 
        if(!$query){
            $this->output_db_error();
        }
        $catSubcat = $query->result();

        foreach ($catSubcat as $key => $value) {
            $this->db->select('subcat.categoryID,subcat.name,
                subcat.description,subcat.image,
                subcat.parent_category_id,subcat.status');
            $this->db->from(CATEGORY.' as subcat');  
            $this->db->where(array('parent_category_id'=>$value->categoryID));
            $query = $this->db->get();  
            $catSubcat[$key]->sub_category = $query->result(); 
        }
        return $catSubcat;
    }

    //get variants list data
    function getVariants(){
        $this->db->select('*');
        $this->db->from(VARIANTS.' as variants');    
        $this->db->where(array('status'=>'1'));
        $query = $this->db->get(); 
        $variants = $query->result();

        foreach ($variants as $key => $value) {
            $this->db->select('*');
            $this->db->from(VARIANT_VALUES.' as variantValue');  
            $this->db->where(array('variant_id'=>$value->variantID,'status'=>'1'));
            $query = $this->db->get();  
            $variants[$key]->variant_value = $query->result(); 
        }
        return $variants;
    }

    function prepare_select_product_query($data,$is_count=true){ 

        $url_image = getenv('AWS_CDN_PRODUCT_IMG_PATH');
        $url_placeholder = getenv('AWS_CDN_USER_PLACEHOLDER_IMG');
        $currency_symbol = getenv('CURRENCY_SYMB');
        $currency_code = getenv('CURRENCY_CODE');

        if($is_count === FALSE){

            $this->db->select('product.productID,product.name,
                "'.$currency_code.'" AS currency_code,"'.$currency_symbol.'" AS currency_symbol,product.regular_price,
                product.sale_price,product.in_stock,product.status,product.feature_image,product.sale_discount,

                GROUP_CONCAT(DISTINCT(pro_cat_map1.category_id) SEPARATOR "") as category_id,

                GROUP_CONCAT(DISTINCT(category1.name) SEPARATOR ",") as category_name,

                GROUP_CONCAT(DISTINCT(pro_var_map1.variant_value_id) SEPARATOR ",") as variant_id,

                GROUP_CONCAT(DISTINCT(variant1.variant_value) SEPARATOR ",") as variant_value,

                IF((select user_id from wishlist where product_id=product.productID AND user_id= '.$data["userID"].'
                ) IS NOT NULL,1,0 ) AS is_wishlist,

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

        $this->db->join(USERS. ' as user', ' user.userID = product.seller_id');

        $this->db->join(PRODUCT_CATEGORY_MAP. ' as pro_cat_map', ' product.productID = pro_cat_map.product_id');
        $this->db->join(PRODUCT_CATEGORY_MAP. ' as pro_cat_map1', ' product.productID = pro_cat_map1.product_id');

        $this->db->join(CATEGORY. ' as category', ' category.categoryID = pro_cat_map.category_id');
        $this->db->join(CATEGORY. ' as category1', ' category1.categoryID = pro_cat_map1.category_id');

        $this->db->join(DEAL_ITEMS. ' as deal_item', ' deal_item.product_id = product.productID','left');


        $this->db->join(PRODUCT_VARIANT_MAP. ' as pro_var_map', ' product.productID = pro_var_map.product_id');
        $this->db->join(PRODUCT_VARIANT_MAP. ' as pro_var_map1', ' product.productID = pro_var_map1.product_id');

        $this->db->join(VARIANT_VALUES. ' as variant', ' variant.variantValueID = pro_var_map.variant_value_id');
        $this->db->join(VARIANT_VALUES. ' as variant1', ' variant1.variantValueID = pro_var_map1.variant_value_id');
        
        $this->db->where(array('product.status'=>'1','user.status' => '1'));

        //for search by name
        if($data['searchTerm'] != ''){
            $this->db->like('product.name', $data['searchTerm'],'both');
        } 

        //filter by category and subcategory
        if(!empty($data['category']))  {
            $this->db->where_in('pro_cat_map.category_id', $data['category']);
        }

        //filter by deal ID
        if(!empty($data['deal']))  {
            $this->db->where('deal_item.deal_id', $data['deal']);
        } 

        //filter by size
        if(!empty($data['color']) && empty($data['size'])){

            $this->db->where_in('pro_var_map.variant_value_id', $data['color']);
        }

        //filter by color
        if(!empty($data['size']) && empty($data['color'])){
            $this->db->group_start();
            $this->db->or_where_in('pro_var_map.variant_value_id', $data['size']);
            $this->db->group_end();
        }

        //Filter by both color and size
        if(!empty($data['color']) && !empty($data['size'])){

            //$this->db->where_in('pro_var_map.variant_value_id', implode(',', array_map('intval', $data['variants'])));

            $this->db->where_in('pro_var_map.variant_value_id', $data['color']);
            $this->db->where_in('pro_var_map1.variant_value_id', $data['size']);
        }
        
        if(isset($data['price_from'])){
            //filter by price between two price
            if(($data['price_from']!= '') && !empty($data['price_to'])){ 

               $this->db->where("IF(product.sale_price = 0.00, product.regular_price >= ".$data['price_from'].", product.sale_price >= ".$data['price_from'].") ", NULL, FALSE);

               $this->db->where("IF(product.sale_price = 0.00 , product.regular_price <= ".$data['price_to'].", product.sale_price <= ".$data['price_to'].") ", NULL, FALSE);
            }
        }

        //filter by product of low price first
        if(!empty($data['price_low'])){

            $this->db->order_by("IF(product.sale_price = 0.00 ,product.regular_price ,product.sale_price ) ASC", NULL, FALSE);
        }

        //filter by product of hight price first
        if(!empty($data['price_high'])){

            $this->db->order_by("IF(product.sale_price = 0.00 ,product.regular_price  ,product.sale_price) DESC", NULL, FALSE);
        }
    }

    //Get product list
    function productList($data){
        //get total records count
        $this->prepare_select_product_query($data,true);
        $sql = $this->db->get();
        if(!$sql){
            $this->output_db_error();
        }
        $count_result = $sql->row();
        $total_count = $count_result->total_records;

        //get product list
        $this->prepare_select_product_query($data,false);
        $this->db->group_by('pro_cat_map.product_id');
        $this->db->limit($data['limit'], $data['offset']);
        $this->db->order_by('product.productID', 'desc');
        $sql = $this->db->get();
        if(!$sql){
            $this->output_db_error();
        }
        $product_list = $sql->result();
        $maxMinVal = $this->minMaxPrice();
        return array('data_found' =>true,'currency_symbol' =>getenv('CURRENCY_SYMB'), 'min_max_price' => $maxMinVal,'product_list'=>$product_list,'total_records'=>$total_count);
    }

    //Min Max price of product
    function minMaxPrice(){

        $this->db->select(' MAX(product.regular_price) AS maxPrice,

        (select min(sale_price) from '.PRODUCTS.' where sale_price != 0.00) AS minPrice');
        $this->db->from(PRODUCTS.' as product'); 
        $sql = $this->db->get(); 
        if(!$sql){
            $this->output_db_error();
        }
        $priceval = $sql->row();
        return $priceval;
    }

    //all attachment (gallery image) of product
    function allAttachment($where){
        $this->db->select('gallery_image');
        $this->db->from(PRODUCT_ATTACHMENTS);
        $this->db->where($where);
        $sql = $this->db->get();
        if(!$sql){
            $this->output_db_error();
        }
        $result = $sql->result_array();
        return $result;
    }

    //Seller product list
    function prepare_seller_product_query($data,$is_count=true){ 

        $url_image = getenv('AWS_CDN_PRODUCT_IMG_PATH');
        $url_placeholder = getenv('AWS_CDN_USER_PLACEHOLDER_IMG');
        $currency_symbol = getenv('CURRENCY_SYMB');
        $currency_code = getenv('CURRENCY_CODE');

        if($is_count === FALSE){

            $this->db->select('product.productID,product.name,
                "'.$currency_code.'" AS currency_code,"'.$currency_symbol.'" AS currency_symbol,product.regular_price,
                product.sale_price,product.in_stock,product.status,product.sale_discount,product.feature_image,product.description,product.additional_information,

                GROUP_CONCAT(DISTINCT(pro_cat_map1.category_id) SEPARATOR ",") as category_id,

                GROUP_CONCAT(DISTINCT(category1.name) SEPARATOR ",") as category_name,

                GROUP_CONCAT(DISTINCT(pro_var_map1.variant_value_id) SEPARATOR ",") as variant_id,

                GROUP_CONCAT(DISTINCT(variant1.variant_value) SEPARATOR ",") as variant_value,

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

        $this->db->join(PRODUCT_CATEGORY_MAP. ' as pro_cat_map', ' product.productID = pro_cat_map.product_id','left');
        $this->db->join(PRODUCT_CATEGORY_MAP. ' as pro_cat_map1', ' product.productID = pro_cat_map1.product_id','left');

        $this->db->join(CATEGORY. ' as category', ' category.categoryID = pro_cat_map.category_id');
        $this->db->join(CATEGORY. ' as category1', ' category1.categoryID = pro_cat_map1.category_id');


        $this->db->join(PRODUCT_VARIANT_MAP. ' as pro_var_map', ' product.productID = pro_var_map.product_id','left');
        $this->db->join(PRODUCT_VARIANT_MAP. ' as pro_var_map1', ' product.productID = pro_var_map1.product_id','left');

        $this->db->join(VARIANT_VALUES. ' as variant', ' variant.variantValueID = pro_var_map.variant_value_id');
        $this->db->join(VARIANT_VALUES. ' as variant1', ' variant1.variantValueID = pro_var_map1.variant_value_id');
        
        $this->db->where(array('product.status'=>'1','product.seller_id' => $data['user_id']));

        //for search by name
        if($data['searchTerm'] != ''){
            $this->db->like('product.name', $data['searchTerm'],'both');
        } 
    }

    //Get seller product 
    function myProduct($data){
        //get total records count
        $this->prepare_seller_product_query($data,true);
        $sql = $this->db->get();
        if(!$sql){
            $this->output_db_error();
        }
        $count_result = $sql->row();
        $total_count = $count_result->total_records;

        //get product list
        $this->prepare_seller_product_query($data,false);
        $this->db->group_by('pro_cat_map.product_id');
        $this->db->limit($data['limit'], $data['offset']);
        $this->db->order_by('product.productID', 'desc');
        $sql = $this->db->get();
        if(!$sql){
            $this->output_db_error();
        }
        $product_list = $sql->result();
        return array('data_found' =>true, 'product_list'=>$product_list,'total_records'=>$total_count);
    }

    //get product detail
    function productDetail($product_id,$user_id,$userType){

        $currency_symbol = getenv('CURRENCY_SYMB');
        $currency_code = getenv('CURRENCY_CODE');
        $url_image = getenv('AWS_CDN_PRODUCT_IMG_PATH');
        $url_placeholder = getenv('AWS_CDN_USER_PLACEHOLDER_IMG');
        $url_seller = getenv('AWS_CDN_USER_THUMB_IMG');
        $gallery_url = getenv('AWS_CDN_PRODUCT_GALLERY_IMG_PATH');

        $this->db->select('product.productID,product.name,
            product.description,product.additional_information,product.feature_image,
            product.sku,"'.$currency_code.'" AS currency_code,"'.$currency_symbol.'" AS currency_symbol,product.regular_price,product.sale_price,
            product.in_stock,product.status,product.sale_discount,product.shipping_charge,
            product.is_featured,product.shipping_applied,

            GROUP_CONCAT(DISTINCT(pro_cat_map1.category_id) SEPARATOR ",") as category_id,

            GROUP_CONCAT(DISTINCT(category1.name) SEPARATOR ",") as category_name,

            (case when( product.feature_image = "" OR product.feature_image IS NULL) 
            THEN "'.$url_placeholder.'"
            ELSE
                "'.$url_image.'"
            END ) AS feature_image_url,seller.userID as seller_id,seller.full_name,seller.is_verified as is_seller_verified,

            (SELECT COALESCE(FORMAT(SUM(ratings.rating)/count(ratings.rating),1),"0") from ratings where rating_for = product.seller_id) as average_rating,

            (SELECT  COALESCE(COUNT(ratings.ratingID),"0") from ratings where rating_for = product.seller_id) as rating_count,

            IF((select user_id from wishlist where product_id=product.productID AND user_id= '.$user_id.'
                ) IS NOT NULL,1,0 ) AS is_wishlist,

            (case when( seller.avatar = "" OR seller.avatar IS NULL) 
            THEN "'.$url_placeholder.'"
            when( seller.is_avatar_url = "2" ) 
            THEN seller.avatar
            ELSE
            concat("'.$url_seller.'",seller.avatar) 
            END ) as seller_image');

        $this->db->from(PRODUCTS.' as product'); 
        $this->db->join(PRODUCT_CATEGORY_MAP. ' as pro_cat_map', ' product.productID = pro_cat_map.product_id','left');
        $this->db->join(PRODUCT_CATEGORY_MAP. ' as pro_cat_map1', ' product.productID = pro_cat_map1.product_id','left');

        $this->db->join(CATEGORY. ' as category', ' category.categoryID = pro_cat_map.category_id');
        $this->db->join(CATEGORY. ' as category1', ' category1.categoryID = pro_cat_map1.category_id');

        $this->db->join(USERS. ' as seller', ' seller.userID = product.seller_id','left');

        $this->db->where(array('product.productID'=> $product_id,'product.status' => '1'));

        $query = $this->db->get(); 
        $productDetail = $query->row();
        //$productDetail->category = $this->getProductCategory($productDetail->category_id,$productDetail->productID);

        //get gallery images of product
        $this->db->select('galleryImage.productAttachmentID,
            galleryImage.product_id, galleryImage.gallery_image,

            (case when( galleryImage.gallery_image = "" OR galleryImage.gallery_image IS NULL) 
            THEN "'.$url_placeholder.'"
            ELSE
                "'.$gallery_url.'"
            END ) AS gallery_image_url');
        $this->db->from(PRODUCT_ATTACHMENTS.' as galleryImage');  
        $this->db->where(array('product_id'=>$productDetail->productID));
        $query = $this->db->get();  
        $productDetail->gallery_images = $query->result(); 
        

        //get variant and their values of product
        $this->db->select('variants.variantID,variants.name');
        $this->db->from(VARIANTS.' as variants');    
        $this->db->where(array('status'=>'1'));
        $query = $this->db->get(); 
        $variants = $productDetail->variant = $query->result();

        foreach ($variants as $key => $value) {
            $this->db->select('(pro_var_map.variant_value_id) AS mapping_id,variant.variantValueID,
            variant.variant_value');
            $this->db->from(PRODUCT_VARIANT_MAP. ' as pro_var_map');
            $this->db->join(VARIANT_VALUES. ' as variant', ' variant.variantValueID = pro_var_map.variant_value_id');
            $this->db->where(array('pro_var_map.product_id'=>$productDetail->productID, 'pro_var_map.variant_id'=>$value->variantID));
            $query = $this->db->get();
            $variants[$key]->variant_value = $query->result();

        }

        if($userType == 'buyer' || $userType == ''){
            //get similar product (sorting by category)
            $this->db->select('similarProduct.productID,similarProduct.name,similarProduct.feature_image,
                similarProduct.sku,similarProduct.regular_price,"'.$currency_code.'" AS currency_code,"'.$currency_symbol.'" AS currency_symbol,
                similarProduct.sale_price,similarProduct.in_stock,similarProduct.status,

                GROUP_CONCAT(DISTINCT(pro_cat_mapp1.category_id) SEPARATOR ",") as category_id,

                GROUP_CONCAT(DISTINCT(category1.name) SEPARATOR ",") as category_name,

                IF((select user_id from wishlist where product_id=similarProduct.productID AND user_id= '.$user_id.'
                ) IS NOT NULL,1,0 ) AS is_wishlist,

               (case when( similarProduct.feature_image = "" OR similarProduct.feature_image IS NULL) 
                THEN "'.$url_placeholder.'"
                ELSE
                    "'.$url_image.'" 
                END ) AS feature_image_url
            ');
            $this->db->from(PRODUCTS.' as similarProduct');  

            $this->db->join(PRODUCT_CATEGORY_MAP. ' as pro_cat_mapp', ' similarProduct.productID = pro_cat_mapp.product_id','left');

            $this->db->join(PRODUCT_CATEGORY_MAP. ' as pro_cat_mapp1', ' similarProduct.productID = pro_cat_mapp1.product_id','left');

            $this->db->join(CATEGORY. ' as category', ' category.categoryID = pro_cat_mapp.category_id');
            $this->db->join(CATEGORY. ' as category1', ' category1.categoryID = pro_cat_mapp1.category_id');
            $this->db->where(array('similarProduct.status'=>'1','similarProduct.productID !=' => $product_id));

            //filter by category
            $this->db->where('FIND_IN_SET(pro_cat_mapp.category_id,"'.$productDetail->category_id.'")');
            $this->db->where('category1.parent_category_id !=' ,'0');

            $this->db->group_by('pro_cat_mapp.product_id');
            $this->db->order_by('similarProduct.productID','DESC');
            $this->db->limit(20);
            $query = $this->db->get(); //lq();
            $productDetail->similar_products = $query->result(); 
        }
        return $productDetail;

    }//end of funtion

    // function getProductCategory($cateory_id,$product_id){
    //     //get category and their subcategory of product
    //     $this->db->select('category.categoryID,category.name,category.parent_category_id');
    //     $this->db->from(CATEGORY.' as category');    
    //     $this->db->where(array('status'=>'1',''));
    //     $query = $this->db->get(); 
    //     $variants = $productDetail->variant = $query->result();

    //     foreach ($variants as $key => $value) {
    //         $this->db->select('(pro_var_map.variant_value_id) AS mapping_id,variant.variantValueID,
    //         variant.variant_value');
    //         $this->db->from(PRODUCT_VARIANT_MAP. ' as pro_var_map');
    //         $this->db->join(VARIANT_VALUES. ' as variant', ' variant.variantValueID = pro_var_map.variant_value_id');
    //         $this->db->where(array('pro_var_map.product_id'=>$productDetail->productID, 'pro_var_map.variant_id'=>$value->variantID));
    //         $query = $this->db->get();
    //         $variants[$key]->variant_value = $query->result();

    //     }
    // }

    function galleryCount($productID){
        $this->db->select('count(productAttachmentID) as total_count');
        $this->db->from(PRODUCT_ATTACHMENTS);
        $this->db->where('product_id',$productID);
        $sql = $this->db->get();
        $result = $sql->row();
        return $result;
    }

    function featured_product(){

        $currency_symbol = getenv('CURRENCY_SYMB');
        $currency_code = getenv('CURRENCY_CODE');
        $url_image = getenv('AWS_CDN_PRODUCT_IMG_PATH');
        $url_placeholder = getenv('AWS_CDN_USER_PLACEHOLDER_IMG');
        
        $this->db->select('product.productID,product.name,product.feature_image,
                product.sku,product.regular_price,"'.$currency_code.'" AS currency_code,"'.$currency_symbol.'" AS currency_symbol,
                product.sale_price,product.in_stock,product.status,

                GROUP_CONCAT(DISTINCT(pro_cat_mapp.category_id) SEPARATOR ",") as category_id,

                GROUP_CONCAT(DISTINCT(category.name) SEPARATOR ",") as category_name,

               (case when( product.feature_image = "" OR product.feature_image IS NULL) 
                THEN "'.$url_placeholder.'"
                ELSE
                    "'.$url_image.'" 
                END ) AS feature_image_url
            ');
        $this->db->from(PRODUCTS.' as product'); 

        $this->db->join(PRODUCT_CATEGORY_MAP. ' as pro_cat_mapp', ' product.productID = pro_cat_mapp.product_id');

        $this->db->join(CATEGORY. ' as category', ' category.categoryID = pro_cat_mapp.category_id','left');
    
        $this->db->where(array('product.is_featured' => '1', 'product.status' => '1'));
        $this->db->group_by('pro_cat_mapp.product_id');
        $this->db->order_by('product.productID','DESC');
        $this->db->limit(20);
        $sql = $this->db->get(); 
        if(!$sql){
            $this->output_db_error();
        }
        $result = $sql->result(); 
        return $result;
    }

}//End Class
?>
