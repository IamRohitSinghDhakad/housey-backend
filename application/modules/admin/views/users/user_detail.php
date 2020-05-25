
<style type="text/css">
.detail{
  padding-left: 18px;
}
</style>
<?php 
if(!empty($info->avatar && $info->is_avatar_url == 1)){ 
    $file = getenv('AWS_CDN_USER_THUMB_IMG').$info->avatar;
    $fileName = getenv('AWS_CDN_USER_THUMB_IMG').$info->avatar;
  }elseif($info->is_avatar_url == 2){
    $fileName = $info->avatar;
  }else{
    $fileName = getenv('AWS_CDN_USER_PLACEHOLDER_IMG');
  }
?>
<div class="content-wrapper">
<!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>

    Buyer Detail
    </h1>
    <ol class="breadcrumb">
      <li><a href="<?php echo site_url('admin'); ?>"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li class="active">Buyer detail</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">

    <div class="row">
      <div class="col-md-3">
        <!-- Profile Image -->
        <div class="box box-primary"> 
          <div class="box-body box-profile">
            <img class="profile-user-img img-responsive img-circle" src="<?php echo $fileName; ?>" alt="User profile picture">

            <h3 class="profile-username text-center"><?php echo ucfirst($info->full_name); ?></h3>
            <center>
            <p style="margin-right: 18px;" class="text-muted detail"><?php //echo ($info->social_id)?'(facebook)':''; ?></p>
            </center>
          <!-- <p class="text-muted text-center">Software Engineer</p> -->

          </div>
        <!-- /.box-body -->
        </div>
        <!-- /.box -->

        <!-- About Me Box -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">About Me</h3>
          </div>
          <!-- /.box-header -->
          <div class="box-body">

            <strong><i class="fa fa-envelope margin-r-5"></i> Email</strong>

            <p class="text-muted detail"><?php echo $info->email; ?></p>

            <hr>

            <strong><i class="fa fa-map-marker margin-r-5"></i> Billing Address</strong>

            <p class="text-muted detail"><?php echo display_placeholder_text($info->profile_address); ?></p>

            <hr>

            <strong><i class="fa fa-map-marker margin-r-5"></i> Shipping Address</strong>

            <p class="text-muted detail"><?php if(!empty($shipping_address)){ echo $shipping_address->house_number.', '.$shipping_address->locality.', '.$shipping_address->city.', '.$shipping_address->zip_code.', '.$shipping_address->country; 
            }else {
              echo 'NA';
            } ?></p>
          </div>
        <!-- /.box-body -->
        </div>
      <!-- /.box -->
      </div>
      <!-- /.col -->
      <div class="col-md-9">
        <div class="nav-tabs-custom">
          <ul class="nav nav-tabs">
          <li class="active"><a href="#settings" data-toggle="tab">My Order</a></li>
          </ul>
          <div class="tab-content">
            <div class="active tab-pane" id="settings">
              <?php  $csrf = get_csrf_token()['hash'];?>
              <div class="table-responsive">
                <table id="user_order_tables" class="table" data-keys="<?php //echo get_csrf_token()['name'];?>" data-values="<?php //echo $csrf;?>" userId="<?php echo $info->userID; ?>">
                  <thead>
                    <th>Order.No.</th>
                    <th>Product</th>
                    <th>Payment Mode</th> 
                    <th>Total(<?php echo getenv('CURRENCY_SYMB');?>)</th> 
                    <th>Payment Status</th> 
                    <th>Status</th> 
                    <th style="width: 12%">Action</th>
                  </thead>
                  <tbody>

                  </tbody>
                  <tfoot>

                  </tfoot>
                </table>
              </div>

            <div class="row">

            <div class="box-body ">
                         

            </div>
            <!-- /.tab-pane -->
            </div>
            <!-- /.tab-content -->
            </div>
          <!-- /.nav-tabs-custom -->
          </div>
        <!-- /.col -->
        </div>
      </div>
    </div>
  <!-- /.row -->

  </section>
<!-- /.content -->
</div>
<div id='show_status_change_modal'></div>