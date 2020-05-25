<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Housey | Admin Login</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="shortcut icon" href="<?php echo base_url().HOUSEY_LOGO; ?>favicon.png" />
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="<?php echo base_url().CDN_BACK_DIST_CSS;?>bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?php echo base_url().CDN_BACK_DIST_CSS;?>AdminLTE.min.css">
  <!-- Material Design -->
  <link rel="stylesheet" href="<?php echo base_url().CDN_BACK_DIST_CSS;?>bootstrap-material-design.min.css">
  <link rel="stylesheet" href="<?php echo base_url().CDN_BACK_DIST_CSS;?>ripples.min.css">
  <link rel="stylesheet" href="<?php echo base_url().CDN_BACK_DIST_CSS;?>MaterialAdminLTE.min.css">
  <link href="<?php echo base_url().CDN_BACK_DIST_CSS;?>toastr.min.css" rel="stylesheet">
  <link href="<?php echo base_url().APP_ADMIN_ASSETS_CUSTOM_CSS;?>admin.css" rel="stylesheet">
</head>
<body class="hold-transition login-page">
<div class="preloader" style="display: none;">
  <div class="loadinner">
    <img src="<?php echo base_url().HOUSEY_LOGO; ?>loader.png">
  </div>
</div>
<div class="login-box">
  <div class="login-logo">
    <a href=""><img width="100" height="100" src="<?php echo base_url().HOUSEY_LOGO;?>logo.png"></a>
  </div>
  <!-- /.login-logo -->
  <div class="login-box-body">
    <p class="login-box-msg">Sign in to start your session</p>

    <form action="<?php echo base_url();?>admin/login" method="post" id="login_form">
      <div class="form-group has-feedback">
        <input type="email" class="form-control" placeholder="Email" name="email" id="email">
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" class="form-control" placeholder="Password" name="password" id="password">
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>
      <div class="row">
        <!-- /.col -->
        <div class="col-xs-12">
          <button  class="btn btn-primary btn-raised btn-block btn-flat" style="background-color: #222d32;" id="loginButton">Sign In</button>
        </div>
        <!-- /.col -->
      </div>
    </form>
  </div>
</div>
<script src="<?php echo base_url().CDN_BACK_DIST_JS;?>jquery-2.2.3.min.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="<?php echo base_url().CDN_BACK_DIST_JS;?>bootstrap.min.js"></script>
<!-- Material Design -->
<script src="<?php echo base_url().CDN_BACK_DIST_JS;?>material.min.js"></script>
<script src="<?php echo base_url().CDN_BACK_DIST_JS;?>ripples.min.js"></script>
<script src="<?php echo base_url().APP_ADMIN_ASSETS_CUSTOM_JS;?>admin.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    $.material.init();
</script>
</body>
</html>
