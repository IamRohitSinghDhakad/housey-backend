<?php
/**
* Web service offer model
* Handles web service get offer request
*/
class Offer_model extends MY_Model {

    function getOfferItems($user_id,$product_id,$variants){

        $this->db->select('offer_items.offerItemID, offer_items.buyer_id, offer_items.product_id, offer_items.variant_value_id, offer_items.quantity, offer_items.product_name, offer_items.category_id, offer_items.seller_id');
        $this->db->from(OFFER_ITEMS.' as offer_items');    
        $this->db->where(array('buyer_id'=>$user_id, 'product_id'=>$product_id,'variant_value_id'=>$variants));
        
        $query = $this->db->get(); 
        $offerItem = $query->row();
        return $offerItem;
    }

    function offerItemList($user_id, $offer_id){
        $this->db->select('offer_item.offerItemID, offer_item.product_id, offer_item.product_name, offer_item.feature_image, offer_item.quantity, offer_item.category_id,offer_item.category_name, offer_item.buyer_id, offer_item.seller_id, offer_item.created_at, offer_item.updated_at, offer_item.variant_value_id,
            offer_item.product_sale_price, offer_item.product_regular_price, offer_item.product_offer_price');

        $this->db->from(OFFER_ITEMS.' as offer_item'); 
        $this->db->where('offer_item.offerItemID', $offer_id);
        $sql = $this->db->get();
        $offer_list = $sql->row();

        $offer_list->variant = $this->getOfferItemVariants($offer_list);

        $pricing_detail = $this->offerItemPricingDetail($user_id);
        $tax_detail = $this->offerItemTaxDetail($pricing_detail);
        return array('offer_item_list'=>$offer_list,'pricing_detail' => $pricing_detail, 'tax_detail' => $tax_detail);
    }

    function offerItemTaxDetail($pricing_detail){ //For Tax calcualtion
        $tax_detail = array();
        $tax_detail['tax_percent'] = $this->common_model->get_field_value(SETTING_OPTIONS, array('option_name' => 'commission_percent'), 'option_value');

        $tax_detail['tax_amount'] = number_format(($pricing_detail->subtotal * $tax_detail['tax_percent'])/100, 2,'.', '');
        return $tax_detail;
    }

    function offerItemPricingDetail($user_id){
        $currency_symbol = getenv('CURRENCY_SYMB');
        $currency_code = getenv('CURRENCY_CODE');
        $this->db->select('IF(SUM(offer_item.quantity) IS NOT NULL,SUM(offer_item.quantity),0) AS total_item, offer_item.product_id,

            "'.$currency_code.'" AS currency_code,"'.$currency_symbol.'" AS currency_symbol,

            SUM(offer_item.product_offer_price*offer_item.quantity) AS subtotal');
        $this->db->from(OFFER_ITEMS.' as offer_item'); 
        $this->db->where(array('offer_item.buyer_id' => $user_id));
        $sql = $this->db->get();
        $pricing_detail = $sql->row();
       
        $pricing_detail->shipping_charge = $this->shippingDetail($pricing_detail->product_id);

        $tax_percent = $this->common_model->get_field_value(SETTING_OPTIONS, array('option_name' => 'commission_percent'), 'option_value');

        $tax_amount = number_format(($pricing_detail->subtotal * $tax_percent)/100, 2,'.', '');

        $pricing_detail->total_amount = number_format($pricing_detail->subtotal + $pricing_detail->shipping_charge + $tax_amount ,2,'.', '');//convert into 2

        return $pricing_detail;
    }

    function shippingDetail($product_id){
        $query = $this->db->query('SELECT shipping_charge FROM products WHERE productID = '.$product_id.' ') ;
        $shipping_charge = $query->row(); 
        return $shipping_charge->shipping_charge;
    }

    function getOfferItemVariants($value){
        $this->db->select('variants.variantID,variants.name');
        $this->db->from(VARIANTS.' as variants');    
        $query = $this->db->get(); 
        $variants  = $query->result();

        foreach ($variants as $key => $value1) {

            $this->db->select('variant.variantValueID,
            variant.variant_value');
            $this->db->from(OFFER_ITEMS. ' as offer_item');
            $this->db->join(VARIANT_VALUES. ' as variant', "FIND_IN_SET(variant.variantValueID,offer_item.variant_value_id) > 0");
            $this->db->where(array('offerItemID'=>$value->offerItemID,'variant_id'=>$value1->variantID));
            $query = $this->db->get();
            $variants[$key]->variant_value = $query->row();

        }
        return $variants;
    }
} //End Class