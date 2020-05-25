<?php
/**
* Web service payment model
* Handles web service handle payment related request
*/
class Payment_model extends MY_Model {

    public function __construct(){
        parent::__construct();
        $this->load->model('notification_model');
    }

    function getDefault($userID) { 
        $where = array('user_id'=>$userID);
        $this->db->select('*');
        $this->db->from(CARDS.' as cards');
        $this->db->where($where);
        $res = $this->db->get();
        if(!$res) {
            $this->output_db_error(); //500 error
        }
        $result = $res->row();
        return $result;
    } 

    function getCard($id) { 

        $where = array('stripe_card_id'=>$id);
        $this->db->select('*');
        $this->db->from(CARDS.' as cards');
        $this->db->where($where);
        $res = $this->db->get();
        if(!$res) {
            $this->output_db_error(); //500 error
        }
        $result = $res->row();
        return $result;
    }

    //Get all card by user id
    function getAllCards($user_id){
        $this->db->select('*');
        $this->db->from(CARDS.' as cards');
        $this->db->where(array('user_id' => $user_id, 'status' => '1'));
        $res = $this->db->get();
        if(!$res) {
            $this->output_db_error(); //500 error
        }
        $result = $res->result();
        return $result;
    }

    function getCardId($id) { 

        $where = array('cardID'=>$id);
        $this->db->select('*');
        $this->db->from(CARDS.' as cards');
        $this->db->where($where);
        $res = $this->db->get();
        if(!$res) {
            $this->output_db_error(); //500 error
        }
        $result = $res->row();
        return $result;
    } 

    function getAllDeviceToken($where){
        $this->db->select('device_token,user_id,device_type,push_alert_status');
        $this->db->from(USER_DEVICES);
        $this->db->join(USERS, "userID = user_id",'left');
        $this->db->where($where);
        $this->db->where(array('push_alert_status' =>'1'));
        $res = $this->db->get();
        $result = $res->result();
        return $result;
    }

    //function for notification
    function notification($dataNotifiy,$refrenID,$notif_msg){

        $this->notification_model->send_push_notification($refrenID,$notif_msg['title'],$notif_msg['body'],$notif_msg['order_id'],$notif_msg['type'],$notif_msg['badge']);
    }

    function sellerOrderCount($seller_id){
        $this->db->select('COUNT(orderID) as sellCount');
        $this->db->from(ORDERS);
        $this->db->where('seller_id', $seller_id);
        $res = $this->db->get();
        $result = $res->row();
        return $result;
    }
} //End Class