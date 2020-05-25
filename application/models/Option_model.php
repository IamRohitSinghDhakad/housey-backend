<?php
if(!defined('BASEPATH')) exit('No direct script access allowed');
/**
* Manages DB operations for `options` table
*/

class Option_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    public function update_option($option_name, $option_value) {

        $option_name = trim( $option_name );
        if ( empty( $option_name ) ) {
            return false;
        }

        $where = array('option_name'=>$option_name);
        $option = $this->db->select("option_name")->from(SETTING_OPTIONS)->where($where)->limit(1)->get()->row();

        $set_data = $where;
        $set_data['option_value'] = $option_value;
        $set_data['updated_at'] = datetime();
        if(!empty($option->option_name)) {
            //Update option
            $set_where = array('option_name'=>$option->option_name);
            $this->common_model->updateFields(SETTING_OPTIONS, $set_data, $set_where);
            $option_id = $option->option_name;
        } else {
            //Insert Option
            $set_data['created_at'] = datetime();
            $option_id = $this->common_model->insertData(SETTING_OPTIONS, $set_data);
        }
        return $option_id;
    }

    public function get_option($option_name, $all=false) {

        $option_name = trim( $option_name );
        if ( empty( $option_name ) ) {
            return false;
        }

        $where = array('option_name'=>$option_name);
        $query = $this->db->select("option_value")->from(SETTING_OPTIONS)->where($where)->limit(1)->get();
        if(!$all) {
            $option = $query->row();
            return $option->option_value;
        } else {
            $option = $query->result();
            return $option; //object
        }
    }

    function getDealList(){
        $currency_symbol = getenv('CURRENCY_SYMB');
        $currency_code = getenv('CURRENCY_CODE');
        $img_path = getenv('CDN_BANNER_IMG');
        $url_placeholder = getenv('AWS_CDN_PLACEHOLDER_IMG');
        $this->db->select('dealID,deal_title,deal_sub_title,deal_image,

        (case when( deal_image = "" OR deal_image IS NULL) 
            THEN "'.$url_placeholder.'"
            ELSE
            "'.$img_path.'"
            END ) as deal_image_url,
                
        "'.$currency_code.'" AS currency_code,"'.$currency_symbol.'" AS currency_symbol');
        $this->db->from(DEALS);
        $this->db->where('status','1');
        $this->db->order_by('dealID','Desc');
        $query = $this->db->get(); 
        $dealList = $query->result();
        return $dealList;
    }
    //Deal count
    function getDealCount(){
        $this->db->select('count(dealID) AS total_record');
        $this->db->from(DEALS);
        $this->db->where('status','1');
        $query = $this->db->get(); 
        $count = $query->row();
        return $count;
    }
}