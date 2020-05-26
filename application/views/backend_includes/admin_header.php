<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Housey | <?php echo $title;?></title>
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
  <link rel="stylesheet" href="<?php echo base_url().APP_ADMIN_ASSETS_CUSTOM_CSS;?>admin.css">
  <link href="<?php echo base_url().CDN_BACK_DIST_CSS;?>toastr.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo base_url().CDN_BACK_PLUGINS;?>datatables/dataTables.bootstrap.css">
  <link rel="stylesheet" href="<?php echo base_url().CDN_BACK_PLUGINS;?>timepicker/bootstrap-timepicker.css">
  <link rel="stylesheet" href="<?php echo base_url().CDN_BACK_PLUGINS;?>timepicker/bootstrap-datepicker.min.css">
  <!-- MaterialAdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="<?php echo base_url().CDN_BACK_DIST_CSS;?>skins/skin-black.min.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/css/select2.min.css" rel="stylesheet" />
  <link href="<?php echo base_url().CDN_BACK_PLUGINS;?>croping/css/main.css" rel="stylesheet">
  <link href="<?php echo base_url().CDN_BACK_PLUGINS;?>croping/css/cropper.css" rel="stylesheet">
  

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body class="hold-transition skin-black sidebar-mini">
<div class="preloader" style="display: none;">
  <div class="loadinner">
    <img src="<?php echo base_url().HOUSEY_LOGO; ?>loader.png">
  </div>
</div>
<!-- Site wrapper -->
<div class="wrapper">
<input type="hidden" name="base_url" id="base_url" value="<?php echo base_url();?>">
  <header class="main-header">
    <!-- Logo -->
    <a href="" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><img src="<?php echo base_url().HOUSEY_LOGO; ?>logo.png"></span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><img src="<?php echo base_url().HOUSEY_LOGO;?>logo.png" id="logo-image"></span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <!-- Messages: style can be found in dropdown.less-->
          <!-- Notifications: style can be found in dropdown.less -->
          <!-- Tasks: style can be found in dropdown.less --> 
          <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
             
              <?php if(!empty($_SESSION[ADMIN_USER_SESS_KEY]['profile_photo'])){ ?>
                <img src="<?php echo getenv('AWS_CDN_ADMIN_THUMB_IMG').$_SESSION[ADMIN_USER_SESS_KEY]['profile_photo'];?>" class="user-image" alt="User Image">
              <?php }else{ ?>
                <img src="<?php echo getenv('AWS_CDN_USER_PLACEHOLDER_IMG');?>" class="user-image" alt="User Image">
              <?php } ?>
              <span class="hidden-xs"><?php echo $_SESSION[ADMIN_USER_SESS_KEY]['fullName'];?></span>
            </a>
            <ul class="dropdown-menu">
              <!-- User image -->
              <li class="user-header">
                <?php if(!empty($_SESSION[ADMIN_USER_SESS_KEY]['profile_photo'])){ ?>
                <img src="<?php echo getenv('AWS_CDN_ADMIN_THUMB_IMG').$_SESSION[ADMIN_USER_SESS_KEY]['profile_photo'];?>" class="img-circle" alt="User Image">
              <?php }else{ ?>
                <img src="<?php echo getenv('AWS_CDN_USER_PLACEHOLDER_IMG');?>" class="img-circle" alt="User Image">
              <?php } ?>

                <p>
                 <?php echo $_SESSION[ADMIN_USER_SESS_KEY]['email'];?>
                </p>
                <p>
                 <?php echo $_SESSION[ADMIN_USER_SESS_KEY]['fullName'];?>
                </p>
              </li>
              <!-- Menu Body -->
              <!-- Menu Footer-->
              <li class="user-footer">
                <div class="pull-left">
                  <a href="<?php echo site_url('admin/admin_profile'); ?>" class="btn btn-default btn-flat">Profile</a>
                </div>
                <div class="pull-right">
                  <a href="<?php echo base_url();?>admin/logout" class="btn btn-default btn-flat">Sign out</a>
                </div>
              </li>
            </ul>
          </li>
          <!-- Control Sidebar Toggle Button -->
        </ul>
      </div>
    </nav>
  </header>

  <!-- =============================================== -->

  <!-- Left side column. contains the sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <ul class="sidebar-menu">
        
        <li class="treeview">
          <a href="<?php echo base_url();?>admin/dashboard">
            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
          </a>
        </li>

        <li class="treeview ">
          <a href="" title="Users">
            <i class="fa fa-user" aria-hidden="true"></i>
            <span title="Users">Users</span>
            <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
          </a>

          <ul class="treeview-menu ">
            <li class="">
              <a href="<?php echo base_url();?>admin/user"><i class="ion ion-person-add" aria-hidden="true"></i>Buyer</a>
            </li>
          </ul>
          <ul class="treeview-menu">
            <li class="">
              <a href="<?php echo base_url();?>admin/seller"><i class="ion ion-person-add"></i>Seller</a>
            </li>
          </ul>
        </li>
        <li class="treeview ">
          <a href="" title="Categories">
              <i class="fa fa-list-alt" title="Categories"></i> 
              <span title="Categories">Categories</span>
              <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
          </a>

          <ul class="treeview-menu ">
              <li class="">
                  <a href="<?php echo base_url()?>admin/category"><i class="fa fa-list-alt"></i>Category</a>
              </li>
              <li class="">
                  <a href="<?php echo base_url();?>admin/sub_category"><i class="fa fa-tasks"></i>Sub-Category</a>
              </li>
          </ul>
        </li>
      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>
