
<style type="text/css">
.detail{
padding-left: 18px;
}
</style>
<?php 
  if($userData->avatar){
    $fileName = getenv('AWS_CDN_ADMIN_THUMB_IMG').$userData->avatar;
  }else{
    $fileName = getenv('AWS_CDN_USER_PLACEHOLDER_IMG');   
  }
?>
<div class="content-wrapper">
<!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>

    Admin Profile
    </h1>
    <ol class="breadcrumb">
      <li><a href="<?php echo site_url('admin'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Admin profile</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">

    <div class="row">
      <div class="col-md-3">
        <!-- Profile Image -->
        <div class="box box-primary"> 
          <div class="box-body box-profile">
            <img class="profile-user-img img-responsive img-circle" src="<?php echo $fileName; ?>" alt="Admin profile picture" id="previewImage">

            <h3 class="profile-username text-center"><?php echo ucfirst($userData->full_name); ?></h3>
            <center>
            <p style="margin-right: 18px;" class="text-muted detail"><?php echo $userData->email; ?></p>
            </center>
          </div>
          <!-- /.box-body -->
        </div>
        <!-- /.box -->

      </div>
      <!-- /.col -->
      <div class="col-md-9">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#profile" data-toggle="tab">Profile<div class="ripple-container"></div></a></li>
              <li><a href="#Password2" data-toggle="tab">Change Password</a></li>
            </ul>
            <div class="tab-content">
              <div class="active tab-pane" id="profile">
                <form class="form-horizontal" method="POST" name="editProfile"  id="editProfile" action="<?php echo base_url('admin/admin_update') ?>">
                  <div class="form-group">
                    <label for="inputName" class="col-sm-2 control-label">Name</label>

                    <div class="col-sm-10">
                      <input type="hidden" name="adminId" value="<?php echo $userData->adminID;?>">
                      <input type="text" class="form-control" name="full_name" id="full_name" placeholder="Name" value="<?php echo $userData->full_name;?>">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="inputEmail" class="col-sm-2 control-label">Email</label>

                    <div class="col-sm-10">
                      <input type="email" class="form-control" name="email"  placeholder="Email" value="<?php echo $userData->email;?>" readonly="">
                    </div>
                  </div>
                  <div class="form-group is-empty is-fileinput">
                    <label for="inputEmail" class="col-sm-2 control-label">Image</label>
                    <div class="col-sm-10">
                      <input class="form-control" type="text" placeholder="Browse..." readonly="">
                      <input type="file" id="image" name="image" onchange="readURL(this);">
                    </div>
                    <input type="hidden" name="exit_image" value="<?php echo $userData->avatar;?>">
                  </div>
                  <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                      <button class="btn btn-primary update_admin_profile pull-right" id="profileSubmit">Update</button>
                    </div>
                  </div>
                </form>
              </div>
              <!-- /.tab-pane -->
              <div class="tab-pane" id="Password2">
                <form class="form-horizontal" method="POST" name="editPassword" id="editPassword" action="<?php echo base_url('admin/change_password') ?>">
                  <div class="form-group is-empty">
                    <label for="inputName" class="col-sm-3 control-label" >Current Password</label>
                    <div class="col-sm-9">
                      <input type="password" class="form-control" name="password" id="password">
                    </div>
                  </div>
                  <div class="form-group is-empty">
                    <label for="inputEmail" class="col-sm-3 control-label">New Password</label>
                    <div class="col-sm-9">
                      <input type="password" class="form-control" name="npassword" id="npassword">
                    </div>
                  </div>
                  <div class="form-group is-empty">
                    <label for="inputEmail" class="col-sm-3 control-label">Confirm New Password</label>
                    <div class="col-sm-9">
                      <input type="password" class="form-control" name="rnpassword" id="inputEmail">
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                      <button type="button" id="passwordsubmit" class="btn btn-primary change_password pull-right">Update</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
            <!-- /.tab-content -->
          </div>
          <!-- /.nav-tabs-custom -->
        </div>
        <!-- /.nav-tabs-custom -->
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
  </section>
<!-- /.content -->
</div>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" ></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js" type="text/javascript"></script>
<script type="text/javascript">
  //validation script
  function show_loader(){
      $('.preloader').show();
  }

  function hide_loader(){
      $('.preloader').hide();
  }
// Show change image preview on file input
document.getElementById("image").onchange = function () {
  var reader = new FileReader();

  reader.onload = function (e) {
    // get loaded data and render thumbnail.
    document.getElementById("previewImage").src = e.target.result;
  };

  // read the image file as a data URL.
  reader.readAsDataURL(this.files[0]);
};
  </script>