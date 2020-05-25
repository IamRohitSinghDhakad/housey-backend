<?php
/**
* Web service notification model
* Handles web service get notification request
*/
class NotificationNew_model extends MY_Model {

    function getSingleNotifications($data){
        $where = array('u.userID'=>$data['userId'],'nt.web_push'=>"0",'nt.is_read'=>"0");
        $this->db->select('nt.*');
        $this->db->from(NOTIFICATIONS.' as nt');
        $this->db->join(USERS.' as u','u.userID = nt.notification_for');
        $this->db->group_by('nt.notification_for');
        $this->db->where($where);
        $query = $this->db->get();
        if($query->num_rows() >0){
            return $query->row();
        }else{
            return array();
        }
    }

    // //get all notification list
    // function getAllNotifications($data,$count=0){

    //     $url_image = CDN_UPLOAD.'profile/thumb/';
    //     $url_placeholder = base_url().USER_DEFAULT_AVATAR;

    //     $where = array('nt.notification_for'=>$data['userId']);
    //     $this->db->select('nt.*,u.full_name,
    //         @http:= SUBSTR(u.profile_photo, 1, 4) as http ,
    //         @https:=SUBSTR(profile_photo, 1, 5) as https,
    //         (case when( u.profile_photo = "" OR u.profile_photo IS NULL) 
    //         THEN "'.$url_placeholder.'"
    //         when( @http = "http" OR @https = "https") 
    //         THEN u.profile_photo
    //         ELSE
    //         concat("'.$url_image.'",u.profile_photo) 
    //         END ) as profile_photo');
    //     $this->db->from(NOTIFICATIONS.' as nt');
    //     $this->db->join(USERS.' as u','u.userId = nt.notification_for');
    //     $this->db->where($where);

    //     if($count==0){
    //     $this->db->limit($data['limit'],$data['offset']);
    //     $this->db->order_by('nt.notificationId','DESC');
    //     }

    //     $query = $this->db->get();
    //     if($query->num_rows() >0){
    //         return $query->result();
    //     }
    // }

    function getAllNotificationsCount($data){
        $where = array('nt.notification_for'=>$data['userId'],'nt.is_read'=>"0");
        $this->db->select('count(nt.notificationID) AS count');
        $this->db->from(NOTIFICATIONS.' as nt');
        $this->db->group_by('nt.notification_for');
        $this->db->where($where);
        $query = $this->db->get();
        if($query->num_rows() >0){
            return $query->row();
        }
    }

    //function for get notification
    function get_notification($data){
        $this->db->select('*');
        $this->db->from(NOTIFICATIONS);
        $this->db->where(array('notification_for'=>$data['userId']));
        $this->db->order_by("notificationID", "desc");
        $sql = $this->db->get();
        if(!$sql){
            $this->output_db_error();
        }
        $returnData = $sql->result();
       
        if(!empty($returnData)){
            foreach ($returnData as $key => $value) {
                $returnData[$key]->current_time =  datetime();
                $returnData[$key]->product_image =  $this->get_product_image($value->reference_id);
            }
        }
        return $returnData;
    }//end of function

    //function for get product feature image
    function get_product_image($order_id){
        $url = getenv('AWS_CDN_PRODUCT_IMG_PATH');
        $default = getenv('AWS_CDN_USER_PLACEHOLDER_IMG');
        $this->db->select('CASE
            WHEN product.feature_image = " "  
            THEN "'.$default.'"
            ELSE concat("'.$url.'",product.feature_image)
            END as product_image');
        $this->db->from(ORDER_ITEMS.' as order_item');
        $this->db->join(PRODUCTS.' as product', "order_item.product_id = product.productID",'left');
         $this->db->where('order_item.order_id',$order_id);
         $this->db->limit(1,0);
        return $this->db->get()->row()->product_image;
    }//end of function

}