<?php

class User extends Common_Back_Controller {
  	public function __construct(){
      parent::__construct();
  		$this->load->model('user_model');
      $this->load->model('user_order_model');
  
  	}

  	function index(){
      $this->check_admin_user_session();
  		$data['title'] = 'Buyer List';
  		$this->load->admin_render('users/user_list',$data);
  	}//END OF INDEX FUNCTION

  	function user_list_ajax(){
  		$this->check_admin_ajax_auth();
      $no = $_POST['start'];
      $list = $this->user_model->get_list();
      $data = array();
      foreach ($list as $userData) {
  			if(!empty($userData->avatar && $userData->is_avatar_url == 1)){ 
  				$file = getenv('AWS_CDN_USER_THUMB_IMG').$userData->avatar;
  				$fileName = getenv('AWS_CDN_USER_THUMB_IMG').$userData->avatar;
  			}elseif($userData->is_avatar_url == 2){
  				$fileName = $userData->avatar;
  			}else{
  				$fileName = getenv('AWS_CDN_USER_PLACEHOLDER_IMG');
  			}
        $action ='';
        $no++;
        $row = array();
        $row[] = display_placeholder_text($no); 
        $row[] = "<img  style='width:40px; height:40px;' src='".$fileName."'class='img-circle' alt='User Image'>&nbsp; "."<span title='".$userData->full_name."'>".display_placeholder_text($userData->full_name)."</span>"; 
       
        $row[] = display_placeholder_text($userData->email); 
         
        $statuChange = "statuChangeUser('admin/user','$userData->userID');";
        $userDelete = "userDelete('admin/user','$userData->userID');";
        if($userData->status == 1) { $row[] =  '<p style="cursor: pointer;"  class="text-success">Active</p>'; } else { $row[] =  '<p style="cursor: pointer;"  class="text-danger">Inactive</p>'; } 
        	$userID = encoding($userData->userID);
        $action = '<div class="btn-group">
                      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                        <span class="caret"></span>
                      <div class="ripple-container"></div></button>
                      <ul class="dropdown-menu rightMenu">

                        <li><a style="font-size:17px;" href="user/user_detail?id='.$userID.'"  class="on-default edit-row table_action "><i class="fa fa-eye text-success"></i> Detail</i></a></li>

                        <li><a style="font-size:17px;" href="javascript:void(0)" onclick="'.$userDelete.'"class="on-default edit-row table_action "><i class="fa fa-trash text-danger"></i> Delete</i></a></li>';
                        if($userData->status == 0) {
                          $action .= '<li><a href="javascript:void(0)" onclick="'.$statuChange.'" class="on-default edit-row table_action"><i style="font-size:17px;" class="fa fa-check text-success" aria-hidden="true"></i>&nbsp;Active</a></li>';}
                        else{
                          $action .=  '<li><a style="font-size:17px;" href="javascript:void(0)"onclick="'.$statuChange.'" class="on-default edit-row table_action danger"><i style="font-size:17px;" class="fa fa-times text-danger" aria-hidden="true"></i>&nbsp;Inactive</a></li>
                        
                      </ul>
                    </div>';	}
        $row[] = $action;
        $data[] = $row;
      }

      $output = array(
        "draw" => $_POST['draw'],
        "recordsTotal" => $this->user_model->count_all(),
        "recordsFiltered" => $this->user_model->count_filtered(),
        "data" => $data,
        "csrf"=>get_csrf_token()['hash']
      );
      //output to json format
      echo json_encode($output);
  	}//END OF USER LISTING FUNCTION

  	function statuChangeUser(){
    	$this->check_admin_ajax_auth();
    	$id = $_GET['id'];
    	$where = array('userID'=>$id,'status'=>1);
    	$dataexist = $this->common_model->is_data_exists(USERS,$where);
    	if(!empty($dataexist)){
	    	$dataZero = array('status'=>0);
	    	$update = $this->common_model->updateFields(USERS,$dataZero,$where);
	    	$message = 'User inactivated successfully.';
    	}else{
    		$wheres = array('userID'=>$id);
    		$dataOne = array('status'=>1);
	    	$update = $this->common_model->updateFields(USERS,$dataOne,$wheres);
	    	$message = 'User activated successfully.';
    	}
    	if($update){
    		$data=array('status'=>1,'url'=>'','message'=>$message);
				echo json_encode($data);
    	}else{
    		$data=array('status'=>0,'message'=>'something went wrong.');
				echo json_encode($data);
    	}
    }//END OF USER STATUS UPDATE FUNCTION

	function user_detail(){
    $this->check_admin_user_session();
		$id = decoding($_GET['id']);
		$data['title'] = 'User Detail';
		$data['info'] = $this->user_model->getData($id);
    $data['shipping_address'] = $this->user_model->shippingAddress($id);
    $data['user_add'] = $this->common_model->is_data_exists(USER_ADDRESS,array('user_id'=>$id));
		$this->load->admin_render('users/user_detail',$data);

	}//End of user detail

  // Delete user
  function userDelete(){
    $this->check_admin_ajax_auth();
    $id = $_GET['id'];
    $where = array('userID'=>$id);
    $dataExist = $this->common_model->is_data_exists(USERS,$where);
    if(!empty($dataExist)){

      $delete = $this->common_model->deleteData(USERS,$where);
      $message = 'User has been deleted successfully';
      $data=array('status'=>1,'message'=> $message);
      echo json_encode($data); die;
    }else{
      $data=array('status'=>0,'message'=>'something went wrong');
      echo json_encode($data); die;
    }
  }

	function user_order_list_ajax(){
		$this->check_admin_ajax_auth();
    $userId = $_POST['userId'];
    $no = $_POST['start'];
    $lists = $this->user_order_model->set_data(array('ordered_by_user_id'=>$userId));
  	$list = $this->user_order_model->get_list();
    $data = array();
    foreach ($list as $orderDatas) {
      //check for payment mode
      if($orderDatas->payment_mode==1){

        $payment_mode = "COD"; 
        
      }else{
        $payment_mode = "Online"; 

      }

      //check for payment mode
      if($orderDatas->payment_status==0){
        $payment_status = "Pending"; 
      }elseif($orderDatas->payment_status==1){
        $payment_status = "Paid"; 
      }else{
        $payment_status = "Failed"; 
      }
    
      $action ='';
      $no++;
      $row = array();
      $row[] = display_placeholder_text('#'.$orderDatas->number); 
      $row[] = "<p class='dotted'>".'<span title="'.$orderDatas->name.'">'.ucfirst($orderDatas->name).'</span></p>'; 
      $row[] = display_placeholder_text($payment_mode); 
      $row[] = display_placeholder_text($orderDatas->grand_total); 
      $row[] = display_placeholder_text($payment_status ); 
      if($orderDatas->current_status == 0) { $row[] =  '<p style="cursor: pointer;"  class="text-success">Order Placed</p>'; } elseif($orderDatas->current_status == 1) { $row[] =  '<p style="cursor: pointer;"  class="text-success">Approved</p>'; } elseif($orderDatas->current_status == 2) { $row[] =  '<p style="cursor: pointer;"  class="text-success">Packed</p>'; } elseif($orderDatas->current_status == 3) { $row[] =  '<p style="cursor: pointer;"  class="text-success">Shipped</p>'; }elseif($orderDatas->current_status == 4) { $row[] =  '<p style="cursor: pointer;"  class="text-success">Delivered</p>'; }elseif($orderDatas->current_status == 5) { $row[] =  '<p style="cursor: pointer;"  class="text-success">Cancelled</p>'; }else{ $row[] =  '<p style="cursor: pointer;"  class="text-success">Refunded</p>'; }
      $clk_edit =  "editProduct('admin/product/edit_product_modal','$orderDatas->orderID');" ;
      $action = '<div class="btn-group">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                          <span class="caret"></span>
                        <div class="ripple-container"></div></button>
                        <ul class="dropdown-menu rightMenu">
                          <li><a style="" href="'.base_url().'admin/order/order_detail?id='.$orderDatas->orderID.'"  class="on-default edit-row table_action" ><i style="font-size:17px;" class="fa fa-eye text-success" aria-hidden="true"></i>View</a></li>';
                        '</ul>
                      </div>';  
      $row[] = $action;
      $data[] = $row;

    }

    $output = array(
      "draw" => $_POST['draw'],
      "recordsTotal" => $this->user_order_model->count_all(),
      "recordsFiltered" => $this->user_order_model->count_filtered(),
      "data" => $data,
      "csrf"=>get_csrf_token()['hash']
    );
    
    echo json_encode($output);

	}//END FUNCTION

}
