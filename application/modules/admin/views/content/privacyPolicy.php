
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <section class="content-header">
      <h1>
        <?php echo $title; ?>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard');?>"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li><a >Content</a></li>
        <li class="active"><?php echo $title; ?> </li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box box-info">
            <div class="box-header">
    
            </div>
            <!-- /.box-header -->
            <div class="box-body pad">
              <form id="editpolicy" action="<?php echo base_url('admin/setPolicyContent');?>" method="post">
                <div class="col-md-12">
                  <div class="form-group">
                    <textarea name="contents" id="editor3">
                       <?php if(!empty($content->option_value)){ echo $content->option_value; } ?>
                    </textarea>
                   </div>
                </div>
                <div class="col-md-12">
                  <div class="form-group pull-right">
                    <button type="button" id="savePolicy" class="btn btn-primary">Save</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col-->
      </div>
      <!-- ./row -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" ></script>

<script>
$(document).ready(function(){

  CKEDITOR.replace('editor3');

  function show_loader(){
    $('.preloader').show();
  }

  function hide_loader(){
    $('.preloader').hide();
  }
});
var unknown_error_msg = 'Something went wrong. Please try again.';
// save Term and condition js start
$("#savePolicy").on("click",function(){
  toastr.remove();
  var vContent = CKEDITOR.instances['editor3'].getData();
  var _that = $(this), 
  form = _that.closest('form'), 
  f_action = form.attr('action');
  $.ajax({
    type: "POST",
    url: f_action,
    data: {data:vContent}, //only input
    dataType: "JSON",
    beforeSend: function () { 
      show_loader(); 
    }, 
    success: function (data) {  
      if(data.status==-1){
          toastr.error(data.msg);
          window.setTimeout(function () {
            window.location.href = data.url;
          }, 1000); 
      }
      if (data.status == 1){ 
        toastr.success(data.message);

      }else{
        toastr.error(data.message);
      }
    }, 
    error: function(){
      toastr.warning(unknown_error_msg); //error
    },
    complete: function () {
      hide_loader(); 
    }
  });           
});//END update About us content js
</script>