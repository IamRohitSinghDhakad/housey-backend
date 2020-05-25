<?php

/**
* Options controller
* Handles options web service request
* version: 1.0 ( 19-02-2020 )
*/
class Option extends Common_Service_Controller {

	public function __construct(){
        parent::__construct();

        $this->load->model('option_model'); //Load option model handles all options related DB queries
	}

    // Home page banner slider
    function banner_get(){

        $list = $this->option_model->get_option('home_banner_image');
        if(!empty($list)){
            $bannerList = json_decode($list);
            foreach ($bannerList as $value) {
                
                $value->banner_image_url = getenv('CDN_BANNER_IMG');
            }

            $this->success_response(get_response_message(302),['banner_list' => $bannerList]);
        }

        $this->success_response('Not Found'); //error reponse
    }

    function deal_get(){
        //$this->check_service_auth();

        $getDealList = $this->option_model->getDealList();
        $getDealCount = $this->option_model->getDealCount();

        if(!$getDealList){
            //check for deal avalability
            $this->success_response('Not Found'); //error reponse
        }

        $this->success_response(get_response_message(302),['total_records'=>$getDealCount->total_record,'deal_list' => $getDealList]);
    }
} //End Class