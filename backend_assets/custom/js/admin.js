var base_url = $('#base_url').val();
var unknown_error_msg = 'Something went wrong. Please try again.';
function show_loader(){
    $('.preloader').show();
}

function hide_loader(){
    $('.preloader').hide();
}

$('#loginButton').on('click',function(){
    event.preventDefault();
    var actionUrl=$('#login_form').attr('action');
    formData = new FormData(document.getElementById('login_form'));
    $.ajax({
      type: "POST",
      url: actionUrl,
      dataType:'json',
      data: formData, //only input
      processData: false,
      contentType: false,
      beforeSend: function (){
        show_loader()
      },
      success: function(data){ 
        hide_loader();
        toastr.remove();
        if(data.status==1){
          setTimeout(function(){
            window.location.href=data.url
          },1000);
          toastr.success(data.message)
        }else{
          toastr.remove();
          toastr.error(data.message);
        }
      }
    });
});//end of login function

$(function(){

    var category_table = $("#category_table");
    var table_post = $('#category_table').DataTable({ 
      "processing": true, //Feature control the processing indicator.
      "serverSide": true, //Feature control DataTables' servermside processing mode.
      "order": [], //Initial no order.

      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,    
      "blengthChange": false,
      "iDisplayLength" :10,

      "bPaginate": true,
      "bInfo": true,
      "bFilter": false,
      "language": {                
      "infoFiltered": ""
      },

      // Load data for the table's content from an Ajax source
      "ajax": {
      "url": base_url + "admin/category/category_list_ajax",
      // "url": base_url + "admin/users_list_ajax",
      "type": "POST",
      "dataType": "json",
      data:function(d) {
        var csrf_key = category_table.attr('data-keys');
        var csrf_hash = category_table.attr('data-values');
        d[csrf_key] = csrf_hash;
      },
      beforeSend: function(){
        show_loader()
      },
      dataSrc: function (jsonData) {
        hide_loader();
        // $("#iframeloading").hide();
        $('#total').html(jsonData.recordsFiltered);
        if(jsonData.status==-1){
          location.reload();
        }else{
        category_table.attr('data-values',jsonData.csrf);
        return jsonData.data;
        }
      }
      },
      //Set column definition initialisation properties.
      "columnDefs": [
      { orderable: false, targets: -1 },

      ]

    });
});//category table end

//buyer table 
$(function(){

    var user_table = $("#user_table");
    var table_post = $('#user_table').DataTable({ 
      "processing": true, //Feature control the processing indicator.
      "serverSide": true, //Feature control DataTables' servermside processing mode.
      "order": [], //Initial no order.

      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,    
      "blengthChange": false,
      "iDisplayLength" :10,

      "bPaginate": true,
      "bInfo": true,
      "bFilter": false,
      "language": {                
      "infoFiltered": ""
      },

      // Load data for the table's content from an Ajax source
      "ajax": {
      "url": base_url + "admin/user/user_list_ajax",
      // "url": base_url + "admin/users_list_ajax",
      "type": "POST",
      "dataType": "json",
      data:function(d) {
        var csrf_key = user_table.attr('data-keys');
        var csrf_hash = user_table.attr('data-values');
        d[csrf_key] = csrf_hash;
      },
      beforeSend: function(){
        show_loader()
      },
      dataSrc: function (jsonData) {
        hide_loader();
        // $("#iframeloading").hide();
        $('#total').html(jsonData.recordsFiltered);
        if(jsonData.status==-1){
          location.reload();
        }else{
        user_table.attr('data-values',jsonData.csrf);
        return jsonData.data;
        }
      }
      },
      //Set column definition initialisation properties.
      "columnDefs": [
      { orderable: false, targets: -1 },

      ]

    });
});//buyer table end

//seller table 
$(function(){

    var seller_table = $("#seller_table");
    var table_post = $('#seller_table').DataTable({ 
      "processing": true, //Feature control the processing indicator.
      "serverSide": true, //Feature control DataTables' servermside processing mode.
      "order": [], //Initial no order.

      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,    
      "blengthChange": false,
      "iDisplayLength" :10,

      "bPaginate": true,
      "bInfo": true,
      "bFilter": false,
      "language": {                
      "infoFiltered": ""
      },

      // Load data for the table's content from an Ajax source
      "ajax": {
      "url": base_url + "admin/seller/seller_list_ajax",
      "type": "POST",
      "dataType": "json",
      data:function(d) {
        var csrf_key = seller_table.attr('data-keys');
        var csrf_hash = seller_table.attr('data-values');
        d[csrf_key] = csrf_hash;
      },
      beforeSend: function(){
        show_loader()
      },
      dataSrc: function (jsonData) {
        hide_loader();
        // $("#iframeloading").hide();
        $('#total').html(jsonData.recordsFiltered);
        if(jsonData.status==-1){
          location.reload();
        }else{
        seller_table.attr('data-values',jsonData.csrf);
        return jsonData.data;
        }
      }
      },
      //Set column definition initialisation properties.
      "columnDefs": [
      { orderable: false, targets: -1 },

      ]

    });
});//seller table end

//add sub category table
$(function(){

    var sub_category_table = $("#sub_category_table");
    var table_post = $('#sub_category_table').DataTable({ 
      "processing": true, //Feature control the processing indicator.
      "serverSide": true, //Feature control DataTables' servermside processing mode.
      "order": [], //Initial no order.

      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,    
      "blengthChange": false,
      "iDisplayLength" :10,

      "bPaginate": true,
      "bInfo": true,
      "bFilter": false,
      "language": {                
      "infoFiltered": ""
      },

      // Load data for the table's content from an Ajax source
      "ajax": {
      "url": base_url + "admin/sub_category/sub_category_list_ajax",
      // "url": base_url + "admin/users_list_ajax",
      "type": "POST",
      "dataType": "json",
      data:function(d) {
        var csrf_key = sub_category_table.attr('data-keys');
        var csrf_hash = sub_category_table.attr('data-values');
        d[csrf_key] = csrf_hash;
      },
      beforeSend: function(){
        show_loader()
      },
      dataSrc: function (jsonData) {
        hide_loader();
        // $("#iframeloading").hide();
        $('#total').html(jsonData.recordsFiltered);
        if(jsonData.status==-1){
          location.reload();
        }else{
        sub_category_table.attr('data-values',jsonData.csrf);
        return jsonData.data;
        }
      }
      },
      //Set column definition initialisation properties.
      "columnDefs": [
      { orderable: false, targets: -1 },

      ]

    });
});//sub category table end


//add product table
$(function(){
    var product_table = $("#product_table");
    var table_post = $('#product_table').DataTable({ 
      "processing": true, //Feature control the processing indicator.
      "serverSide": true, //Feature control DataTables' servermside processing mode.
      "order": [], //Initial no order.

      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,    
      "blengthChange": false,
      "iDisplayLength" :10,

      "bPaginate": true,
      "bInfo": true,
      "bFilter": false,
      "language": {                
      "infoFiltered": ""
      },

      // Load data for the table's content from an Ajax source
      "ajax": {
      "url": base_url + "admin/product/product_list_ajax",
      "type": "POST",
      "dataType": "json",
      data:function(d) {
        var csrf_key = product_table.attr('data-keys');
        var csrf_hash = product_table.attr('data-values');
        d[csrf_key] = csrf_hash;
      },
      beforeSend: function(){
        show_loader()
      },
      dataSrc: function (jsonData) {
        hide_loader();
        // $("#iframeloading").hide();
        $('#total').html(jsonData.recordsFiltered);
        if(jsonData.status==-1){
          location.reload();
        }else{
        product_table.attr('data-values',jsonData.csrf);
        return jsonData.data;
        }
      }
      },
      //Set column definition initialisation properties.
      "columnDefs": [
      { orderable: false, targets: -1 },

      ]

    });
});//product table end

//seller product list table
$(function(){
    var seller_product_table = $("#seller_product_table");
    var table_post = $('#seller_product_table').DataTable({ 
      "processing": true, //Feature control the processing indicator.
      "serverSide": true, //Feature control DataTables' servermside processing mode.
      "order": [], //Initial no order.

      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,    
      "blengthChange": false,
      "iDisplayLength" :10,

      "bPaginate": true,
      "bInfo": true,
      "bFilter": false,
      "language": {                
      "infoFiltered": ""
      },

      // Load data for the table's content from an Ajax source
      "ajax": {
      "url": base_url + "admin/seller/seller_product_list_ajax",
      // "url": base_url + "admin/users_list_ajax",
      "type": "POST",
      "dataType": "json",
      data:function(d) {
        var csrf_key = seller_product_table.attr('data-keys');
        var csrf_hash = seller_product_table.attr('data-values');
        d[csrf_key] = csrf_hash;
        var userId = seller_product_table.attr('userId');
        d[csrf_key] = csrf_hash;
        d['userId'] = userId;
      },
      beforeSend: function(){
        show_loader()
      },
      dataSrc: function (jsonData) {
        hide_loader();
        // $("#iframeloading").hide();
        $('#total').html(jsonData.recordsFiltered);
        if(jsonData.status==-1){
          location.reload();
        }else{
        seller_product_table.attr('data-values',jsonData.csrf);
        return jsonData.data;
        }
      }
      },
      //Set column definition initialisation properties.
      "columnDefs": [
      { orderable: false, targets: -1 },

      ]

    });
});//seller product list table end

//seller new order list table
$(function(){
    var seller_new_order = $("#seller_new_order");
    var table_post = $('#seller_new_order').DataTable({ 
      "processing": true, //Feature control the processing indicator.
      "serverSide": true, //Feature control DataTables' servermside processing mode.
      "order": [], //Initial no order.

      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,    
      "blengthChange": false,
      "iDisplayLength" :10,

      "bPaginate": true,
      "bInfo": true,
      "bFilter": false,
      "language": {                
      "infoFiltered": ""
      },

      // Load data for the table's content from an Ajax source
      "ajax": {
      "url": base_url + "admin/seller/seller_new_order_ajax",
      
      "type": "POST",
      "dataType": "json",
      data:function(d) {
        var csrf_key = seller_new_order.attr('data-keys');
        var csrf_hash = seller_new_order.attr('data-values');
        d[csrf_key] = csrf_hash;
        var userId = seller_new_order.attr('userId');
        d[csrf_key] = csrf_hash;
        d['userId'] = userId;
      },
      beforeSend: function(){
        show_loader()
      },
      dataSrc: function (jsonData) {
        hide_loader();
        $('#total').html(jsonData.recordsFiltered);
        if(jsonData.status==-1){
          location.reload();
        }else{
          seller_new_order.attr('data-values',jsonData.csrf);
          return jsonData.data;
        }
      }
      },
      //Set column definition initialisation properties.
      "columnDefs": [
      { orderable: false, targets: -1 },

      ]

    });
});//seller new order list table end


//seller my order list table
$(function(){
    var seller_my_order = $("#seller_my_order");
    var table_post = $('#seller_my_order').DataTable({ 
      "processing": true, //Feature control the processing indicator.
      "serverSide": true, //Feature control DataTables' servermside processing mode.
      "order": [], //Initial no order.

      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,    
      "blengthChange": false,
      "iDisplayLength" :10,

      "bPaginate": true,
      "bInfo": true,
      "bFilter": false,
      "language": {                
      "infoFiltered": ""
      },

      // Load data for the table's content from an Ajax source
      "ajax": {
      "url": base_url + "admin/seller/seller_my_order_ajax",
      
      "type": "POST",
      "dataType": "json",
      data:function(d) {
        var csrf_key = seller_my_order.attr('data-keys');
        var csrf_hash = seller_my_order.attr('data-values');
        d[csrf_key] = csrf_hash;
        var userId = seller_my_order.attr('userId');
        d[csrf_key] = csrf_hash;
        d['userId'] = userId;
      },
      beforeSend: function(){
        show_loader()
      },
      dataSrc: function (jsonData) {
        hide_loader();
        $('#total').html(jsonData.recordsFiltered);
        if(jsonData.status==-1){
          location.reload();
        }else{
          seller_my_order.attr('data-values',jsonData.csrf);
          return jsonData.data;
        }
      }
      },
      //Set column definition initialisation properties.
      "columnDefs": [
      { orderable: false, targets: -1 },

      ]

    });
});//seller my order list table end


//seller rating list table
$(function(){
    var seller_rating = $("#seller_rating");
    var table_post = $('#seller_rating').DataTable({ 
      "processing": true, //Feature control the processing indicator.
      "serverSide": true, //Feature control DataTables' servermside processing mode.
      "order": [], //Initial no order.

      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": false,
      "info": true,
      "autoWidth": false,    
      "blengthChange": false,
      "iDisplayLength" :10,

      "bPaginate": true,
      "bInfo": true,
      "bFilter": false,
      "language": {                
      "infoFiltered": ""
      },

      // Load data for the table's content from an Ajax source
      "ajax": {
      "url": base_url + "admin/seller/seller_rating_ajax",
      
      "type": "POST",
      "dataType": "json",
      data:function(d) {
        var csrf_key = seller_rating.attr('data-keys');
        var csrf_hash = seller_rating.attr('data-values');
        d[csrf_key] = csrf_hash;
        var userId = seller_rating.attr('userId');
        d[csrf_key] = csrf_hash;
        d['userId'] = userId;
      },
      beforeSend: function(){
        show_loader()
      },
      dataSrc: function (jsonData) {
        hide_loader();
        $('#total').html(jsonData.recordsFiltered);
        if(jsonData.status==-1){
          location.reload();
        }else{
          seller_rating.attr('data-values',jsonData.csrf);
          return jsonData.data;
        }
      }
      },
      //Set column definition initialisation properties.
      "columnDefs": [
      { orderable: false, targets: -1 },

      ]

    });
});//seller rating list table end


//add deal product table
$(function(){
    var dealId = $('#dealId').val();
    var deal_product_table = $("#deal_product_table");
    var table_post = $('#deal_product_table').DataTable({ 
      "processing": true, //Feature control the processing indicator.
      "serverSide": true, //Feature control DataTables' servermside processing mode.
      "order": [], //Initial no order.

      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,    
      "blengthChange": false,
      "iDisplayLength" :10,

      "bPaginate": true,
      "bInfo": true,
      "bFilter": false,
      "language": {                
      "infoFiltered": ""
      },

      // Load data for the table's content from an Ajax source
      "ajax": {
      "url": base_url + "admin/home_setting/deal_product_list_ajax",
      "type": "POST",
      "dataType": "json",
      data:function(d) {
        var csrf_key = deal_product_table.attr('data-keys');
        var csrf_hash = deal_product_table.attr('data-values');
        d[csrf_key] = csrf_hash;
        d['dealId'] = dealId;
      },
      beforeSend: function(){
        show_loader()
      },
      dataSrc: function (jsonData) {
        hide_loader();
        $('#total').html(jsonData.recordsFiltered);
        if(jsonData.status==-1){
          location.reload();
        }else{
          deal_product_table.attr('data-values',jsonData.csrf);
          return jsonData.data;
        }
      }
      },
      //Set column definition initialisation properties.
      "columnDefs": [
      { orderable: false, targets: -1 },

      ]

    });
});//product table end

//add deal table
$(function(){
    var deal_table = $("#deal_table");
    var table_post = $('#deal_table').DataTable({ 
      "processing": true, //Feature control the processing indicator.
      "serverSide": true, //Feature control DataTables' servermside processing mode.
      "order": [], //Initial no order.

      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,    
      "blengthChange": false,
      "iDisplayLength" :10,

      "bPaginate": true,
      "bInfo": true,
      "bFilter": false,
      "language": {                
      "infoFiltered": ""
      },

      // Load data for the table's content from an Ajax source
      "ajax": {
      "url": base_url + "admin/home_setting/deal_list_ajax",
      "type": "POST",
      "dataType": "json",
      data:function(d) {
        var csrf_key = deal_table.attr('data-keys');
        var csrf_hash = deal_table.attr('data-values');
        d[csrf_key] = csrf_hash;
      },
      beforeSend: function(){
        show_loader()
      },
      dataSrc: function (jsonData) {
        hide_loader();
        // $("#iframeloading").hide();
        $('#total').html(jsonData.recordsFiltered);
        if(jsonData.status==-1){
          location.reload();
        }else{
        deal_table.attr('data-values',jsonData.csrf);
        return jsonData.data;
        }
      }
      },
      //Set column definition initialisation properties.
      "columnDefs": [
      { orderable: false, targets: -1 },

      ]

    });
});//product table end

//add order table
$(function(){
    var order_table = $("#order_table");
    var table_post = $('#order_table').DataTable({ 
      "processing": true, //Feature control the processing indicator.
      "serverSide": true, //Feature control DataTables' servermside processing mode.
      "order": [], //Initial no order.

      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,    
      "blengthChange": false,
      "iDisplayLength" :10,

      "bPaginate": true,
      "bInfo": true,
      "bFilter": false,
      "language": {                
      "infoFiltered": ""
      },

      // Load data for the table's content from an Ajax source
      "ajax": {
      "url": base_url + "admin/order/order_list_ajax",
      
      "type": "POST",
      "dataType": "json",
      data:function(d) {
        var csrf_key = order_table.attr('data-keys');
        var csrf_hash = order_table.attr('data-values');
        d[csrf_key] = csrf_hash;
      },
      beforeSend: function(){
        show_loader()
      },
      dataSrc: function (jsonData) {
        hide_loader();
        // $("#iframeloading").hide();
        $('#total').html(jsonData.recordsFiltered);
        if(jsonData.status==-1){
          location.reload();
        }else{
        order_table.attr('data-values',jsonData.csrf);
        return jsonData.data;
        }
      }
      },
      //Set column definition initialisation properties.
      "columnDefs": [
      { orderable: false, targets: -1 },

      ]

    });
});//order table end

//Add order table
$(function(){
    var user_order_table = $("#user_order_tables");
    var table_post = $('#user_order_tables').DataTable({ 
      "processing": true, //Feature control the processing indicator.
      "serverSide": true, //Feature control DataTables' server side processing mode.
      "order": [], //Initial no order.

      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,    
      "blengthChange": false,
      "iDisplayLength" :10,

      "bPaginate": true,
      "bInfo": true,
      "bFilter": false,
      "language": {                
      "infoFiltered": ""
      },

      // Load data for the table's content from an Ajax source
      "ajax": {
      "url": base_url + "admin/user/user_order_list_ajax",
      "type": "POST",
      "dataType": "json",
      data:function(d) {
        var csrf_key = user_order_table.attr('data-keys');
        var csrf_hash = user_order_table.attr('data-values');
        var userId = user_order_table.attr('userId');
        d[csrf_key] = csrf_hash;
        d['userId'] = userId;
      },
      beforeSend: function(){
        show_loader()
      },
      dataSrc: function (jsonData) {
        hide_loader();
        $('#total').html(jsonData.recordsFiltered);
        if(jsonData.status==-1){
          location.reload();
        }else{
        user_order_table.attr('data-values',jsonData.csrf);
        return jsonData.data;
        }
      }
      },
      //Set column definition initialisation properties.
      "columnDefs": [
      { orderable: false, targets: -1 },

      ]

    });
});//order table end

//offer Item list 
$(function(){
    var offer_item_table = $("#offer_item_table");
    var table_post = $('#offer_item_table').DataTable({ 
      "processing": true, //Feature control the processing indicator.
      "serverSide": true, //Feature control DataTables' servermside processing mode.
      "order": [], //Initial no order.

      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,    
      "blengthChange": false,
      "iDisplayLength" :10,

      "bPaginate": true,
      "bInfo": true,
      "bFilter": false,
      "language": {                
      "infoFiltered": ""
      },

      // Load data for the table's content from an Ajax source
      "ajax": {
      "url": base_url + "admin/weekly_offer/product_weekly_list_ajax",
      // "url": base_url + "admin/users_list_ajax",
      "type": "POST",
      "dataType": "json",
      data:function(d) {
        var csrf_key = offer_item_table.attr('data-keys');
        var csrf_hash = offer_item_table.attr('data-values');
        d[csrf_key] = csrf_hash;
      },
      beforeSend: function(){
        show_loader()
      },
      dataSrc: function (jsonData) {
        hide_loader();
        $("#total").text(jsonData.recordsFiltered);
        // $("#iframeloading").hide();
        if(jsonData.status==-1){
          location.reload();
        }else{
        offer_item_table.attr('data-values',jsonData.csrf);
        return jsonData.data;
        }
      }
      },
      //Set column definition initialisation properties.
      "columnDefs": [
      { orderable: false, targets: -1 },

      ]

    });
});//product table end


//add category modal start
var add_category_modal = function (controller) {

  var userType=$('.userTypeGet').data('user-type');
  $.ajax({
      url: base_url + controller + "/category/add_category_modal",
      // 
      type: 'GET',
      data:{'userType':userType},

      success: function (data, textStatus, jqXHR) {
          $('#add_category').html(data);
          $("#Modal").modal('show');

      }
  });
} //END OF ADD CATEGORY MODAL

//add plan modal start
var open_plan_modal = function (controller) {

  var userType=$('.userTypeGet').data('user-type');
  $.ajax({
      url: base_url + controller ,
      // 
      type: 'GET',
      data:{'userType':userType},

      success: function (data, textStatus, jqXHR) {
          $('#form-modal-box').html(data);
          $("#planModal").modal('show');

      }
  });
} //END OF ADD CATEGORY MODAL

//add collection modal start
var add_collection_popup = function (controller) {

  var userType=$('.userTypeGet').data('user-type');
  $.ajax({
      url: base_url + controller + "/collection_types/add_collection_modal",
      // 
      type: 'GET',
      data:{'userType':userType},

      success: function (data, textStatus, jqXHR) {
        console.log('success');
          $('#add_collction').html(data);
          $("#collectionModal").modal('show');

      }
  });
} //END OF ADD COLLECION MODAL

//add product modal start
var add_product_modal = function (controller) {

  var userType=$('.userTypeGet').data('user-type');
  $.ajax({
      url: base_url + controller + "/product/add_product_modal",
      // 
      type: 'GET',
      data:{'userType':userType},

      success: function (data, textStatus, jqXHR) {
          $('#add_product').html(data);
          $("#addProductModal").modal('show');

      }
  });
} //END OF ADD PRODUCT MODAL

//add category modal start
var add_sub_category_modal = function (controller) {

  var userType=$('.userTypeGet').data('user-type');
  $.ajax({
    url: base_url + controller + "/sub_category/add_sub_category_modal",
    type: 'GET',
    data:{'userType':userType},

    success: function (data, textStatus, jqXHR) {
      $('#add_sub_category').html(data);
      $("#Modal").modal('show');
    }
  });
} //END OF ADD SUB CATEGORY MODAL

//edit category modal start
var editCategory = function (controller,categoryId) {

  var userType=$('.userTypeGet').data('user-type');
  $.ajax({
    url: base_url + controller,
    type: 'GET',
    data:{'categoryId':categoryId},

    success: function (data, textStatus, jqXHR) {
      $('#edit_category').html(data);
      $("#editModal").modal('show');
    }
  });
} //END OF EDIT CATEGORY MODAL

//edit category modal start
var edit_plan = function (controller,id) {

  var userType=$('.userTypeGet').data('user-type');
  $.ajax({
    url: base_url + controller,
    type: 'GET',
    data:{'id':id},
    success: function (data, textStatus, jqXHR) {
      $('#edit_plan').html(data);
      $("#editModalPlan").modal('show');
    }
  });
} //END OF EDIT CATEGORY MODAL

//edit collection modal start
var editCollection = function (controller,collceionId) {

  var userType=$('.userTypeGet').data('user-type');
  $.ajax({
    url: base_url + controller, 
    type: 'GET',
    data:{'collectionTypeId':collceionId},
    success: function (data, textStatus, jqXHR) {
      $('#edit_collection').html(data);
      $("#editCollectionModal").modal('show');
    }
  });
} //END OF EDIT CATEGORY MODAL


//edit sub category modal start
var editSubCategory = function (controller,categoryId) {

  var userType=$('.userTypeGet').data('user-type');
  $.ajax({
    url: base_url + controller,
    type: 'GET',
    data:{'categoryId':categoryId},
    success: function (data, textStatus, jqXHR) {
      $('#edit_sub_category').html(data);
      $("#editSubModal").modal('show');
    }
  });
} //END OF EDIT SUB CATEGORY MODAL

//starts of user status change function
var statuChangeUser = function(ctr,id){
  $.ajax({
    url:base_url + ctr + "/statuChangeUser",
    type: 'GET',
    data:{'id':id},
    success: function(data){ 
      var res = JSON.parse(data);
      if(res.status==1){

      setTimeout(function(){
      window.location=res.url},
      2000),
      toastr.success(res.message)

      }else{
        toastr.error(res.message);
      }
    }
  });
}

//starts of user status change function
var userDelete = function(ctr,id){
  $.ajax({
    url:base_url + ctr + "/userDelete",
    type: 'GET',
    data:{'id':id},
    success: function(data){ 
      var res = JSON.parse(data);
      if(res.status==1){
        setTimeout(function(){
        window.location.reload()},
        2000),
        toastr.success(res.message)

      }else{
        toastr.error(res.message);
      }
    }
  });
}

//starts of Category status change function
var deleteCategory= function(ctr,id){
  bootbox.confirm({
    message: "Are you sure you want to delete this ?",
    buttons: {
      confirm: {
        label: 'OK',
        className: 'btn btn-primary'
      },
      cancel: {
        label: 'Cancel',
        className: 'btn-default'
      }
    },
    callback: function (result) {
      if (result){
        $.ajax({
          url:base_url + ctr ,
          type: 'GET',
          data:{'id':id},
          success: function(data){ 
            var res = JSON.parse(data);
            if(res.status==1){

            setTimeout(function(){
            window.location=res.url},
            2000),
            toastr.success(res.message)
            }else{
              toastr.error(res.message);
            }
          }

        });
      }
    }
  });
}

//starts of Category status change function
var statuChangeCategory= function(ctr,id){
  $.ajax({
    url:base_url + ctr ,
    type: 'GET',
    data:{'id':id},
    success: function(data){ 
      var res = JSON.parse(data);
      if(res.status==1){

      setTimeout(function(){
      window.location=res.url},
      2000),
      toastr.success(res.message)

      }else{
        toastr.error(res.message);

      }
    }

  });
}

//preview of image
jQuery('body').on('click', '.remove_img1', function () {
  var img = jQuery(this).data('avtar');
  jQuery('.ceo_logo img').attr('src', img);
  jQuery(this).css("display", "none");
  jQuery('#check_delete').val('1');
});

jQuery('body').on('change', '.input_img2', function () {

  var file_name = jQuery(this).val(),
  fileObj = this.files[0],
  calculatedSize = fileObj.size / (1024 * 1024),
  split_extension = file_name.substr( (file_name.lastIndexOf('.') +1) ).toLowerCase(), //this assumes that string will end with ext
  ext = ["jpg", "png", "jpeg"];
          
  if (calculatedSize > 5){
    $(this).val(fileObj.value = null);
    $('.ceo_file_error').html('File size should not be greater than 5MB');
    return false;
  }
  if (jQuery.inArray(split_extension, ext) != -1 && calculatedSize < 10){
    $('.ceo_file_error').html('');
    readURL(this);
  }

  $('.edit_img').addClass("imUpload");
});

function readURL(input) {
  var cur = input;
  if (cur.files && cur.files[0]) {
    var reader = new FileReader();
    reader.onload = function (e) {
      $(cur).hide();
      $(cur).next('span:first').hide();
      $(cur).next().next('img').attr('src', e.target.result);
      $(cur).next().next('img').css("display", "block");
      $(cur).next().next().next('span').attr('style', "");
    }
    reader.readAsDataURL(input.files[0]);
  }
}

jQuery('body').on('click', '.remove_img', function () {
  var img = jQuery(this).prev()[0];
  var span = jQuery(this).prev().prev()[0];
  var input = jQuery(this).prev().prev().prev()[0];
  jQuery(img).attr('src', '').css("display", "none");
  jQuery(span).css("display", "block");
  jQuery(input).css("display", "inline-block");
  jQuery(this).css("display", "none");
  jQuery(".image_hide").css("display", "block");
  jQuery("#file").val("");

  $('.edit_img').removeClass("imUpload");
});

//start delete product
var deleteProduct = function (controller,productId) {
  bootbox.confirm({
    message: "Are you sure you want to change status ?",
    buttons: {
      confirm: {
        label: 'OK',
        className: 'btn btn-primary'
      },
      cancel: {
        label: 'Cancel',
        className: 'btn-default'
      }
    },
    callback: function (result) {
      if (result){
        var url = base_url+controller;
        $.ajax({
          method: "GET",
          url: url,
          dataType: "json",
          data: {productId: productId},
          beforeSend: function () {
            show_loader()
          },
          success: function (data){
            hide_loader();
            if(data.status==1){
               setTimeout(function(){
              location.reload();},2000
              )
              toastr.success(data.message)
            }else{
              toastr.error(data.error);
            }
          },
        });
      }
    }
  });
}


  //start offer item product
  var deleteOfferItem = function (controller,productId) {
      
      bootbox.confirm({
          message: "Are you sure you want to delete this product from weekly offer list ?",
          buttons: {
              confirm: {
                  label: 'OK',
                  className: 'btn btn-primary'
              },
              cancel: {
                  label: 'Cancel',
                  className: 'btn-default'
              }
          },
          callback: function (result) {
              if (result){
                  var url = base_url+controller;
                  $.ajax({
                      method: "GET",
                      url: url,
                      dataType: "json",
                      data: {productId: productId},
                      beforeSend: function () {
                          show_loader()
                          },

                      success: function (data){
                          hide_loader();
                          if(data.status==1){
                               setTimeout(function(){
                              location.reload();},2000
                              )
                              toastr.success(data.message)
                          }else{
                              toastr.error(data.error);
                          }
                      },
                      
                     
                  });
              }
          }
      });

  }

  //admin profile update
  $("#editProfile").validate({
    ignore: [],
    rules:{
      full_name:{
          required: true,
          maxlength: 100
      },
    },

    errorPlacement: function(error, element) 
    {
      if (element.attr("name") == "editProfile") 
      {
      error.insertAfter("#editProfile");
      } else {
      error.insertAfter(element);
      }
    }
  });
  //end of validation script
  var edit_profile = $("#editProfile");
  var proceed_err  = 'Please fill all the fields properly';

  $(document).on('click', "#profileSubmit", function (event) {
    event.preventDefault();
    toastr.remove();
    event.preventDefault();
    if(edit_profile.valid()===false){
        toastr.error(proceed_err);
        return false;
    }
    var formData = new FormData($("#editProfile")[0]);
    $.ajax({
      url : $("#editProfile").attr('action'),
      type : "post",
      data : formData, //only input
      contentType : false,
      processData : false,
      beforeSend : function(){
        show_loader();
      },
      success : function(response, textStatus, jqXHR){
        hide_loader();
        try {                        
          var data = $.parseJSON(response);
          if (data.status == 1)
          {
            toastr.success(data.message);
                    
            window.setTimeout(function () {
              window.location.href = data.url;
            }, 2000);
                      
          }else {
            toastr.error(data.message);
                   
            setTimeout(function () {
              $('#error-box').hide(800);
            }, 1000);
          }
        } 
        catch (e) {
          toastr.error(data.message);
          setTimeout(function () {
            $('#error-box').hide(800);
          }, 1000);
        }
      }
    });
  });

  //admin change password
  $("#editPassword").validate({
    ignore: [],
    rules:{
      password:{
          required: true,
      },
      npassword:{
          required: true,
      },
      rnpassword:{
          required: true,
          equalTo : "#npassword"
      },
    },

    messages: {
      password: {
        required: "Please enter your current password",
      },
      npassword: {
        required: "Please enter your new password",
      },
      rnpassword: {
        required: "Please retype your new password",
        equalTo: "Please enter the same password as above"
      }
    },

    errorPlacement: function(error, element) 
    {
      if (element.attr("name") == "editPassword") 
      {
      error.insertAfter("#editPassword");
      } else {
      error.insertAfter(element);
      }
    }
  });

  var editpwd = $("#editPassword");
  var proceed_err  = 'Please fill all the fields properly';
  $(document).on('click', "#passwordsubmit", function (event) {
    toastr.remove();
    event.preventDefault();
    if(editpwd.valid()===false){
        toastr.error(proceed_err);
        return false;
    }
    var formData = new FormData($("#editPassword")[0]);
    $.ajax({
      type: "POST",
      url: $("#editPassword").attr('action'),
      data: formData, //only input
      processData: false,
      contentType: false,
        beforeSend: function () {
        show_loader();
      },
      success: function (response, textStatus, jqXHR) {
        hide_loader();
        try {
                    
          var data = $.parseJSON(response);
          if (data.status == 1)
          {
            toastr.success(data.message);
                    
            window.setTimeout(function () {
              window.location.href = data.url;
            }, 2000);
                      
          }
          else {
            toastr.error(data.message);                  
            setTimeout(function () {
              $('#error-box').hide(800);
            }, 1000);
          }
        } 
        catch (e) {
          toastr.error(data.message);
          setTimeout(function () {
              $('#error-box').hide(800);
          }, 1000);
        }
      }
    });
  });


// color values table
$(function(){
  var color_table = $('#color_table');
  var table_post = $('#color_table').DataTable({ 
    "processing": true, //Feature control the processing indicator.
    "serverSide": true, //Feature control DataTables' servermside processing mode.
    "order": [], //Initial no order.

    "paging": true,
    "lengthChange": false,
    "searching": true,
    "ordering": false,
    "info": true,
    "autoWidth": false,    
    "blengthChange": false,
    "iDisplayLength" :10,

    "bPaginate": true,
    "bInfo": true,
    "bFilter": false,
    "language": {                
    "infoFiltered": ""
    },

    // Load data for the table's content from an Ajax source
    "ajax": {
    "url": base_url + "admin/color/color_list",
    "type": "POST",
    "dataType": "json",
    data:function(d) {
      var csrf_key = color_table.attr('data-keys');
      var csrf_hash = color_table.attr('data-values');
      d[csrf_key] = csrf_hash;
    },
    beforeSend: function(){
      show_loader()
    },
    dataSrc: function (jsonData) {
      hide_loader();
      $("#total").text(jsonData.recordsFiltered);
      if(jsonData.status==-1){
        location.reload();
      }else{
      color_table.attr('data-values',jsonData.csrf);
      return jsonData.data;
      }
    }
    },
    //Set column definition initialisation properties.
    "columnDefs": [
    { orderable: false, targets: -1 },

    ]

  });
});//color values table end

// size values table
$(function(){
  var size_table = $('#size_table');
  var table_post = $('#size_table').DataTable({ 
    "processing": true, //Feature control the processing indicator.
    "serverSide": true, //Feature control DataTables' servermside processing mode.
    "order": [], //Initial no order.

    "paging": true,
    "lengthChange": false,
    "searching": true,
    "ordering": false,
    "info": true,
    "autoWidth": false,    
    "blengthChange": false,
    "iDisplayLength" :10,

    "bPaginate": true,
    "bInfo": true,
    "bFilter": false,
    "language": {                
    "infoFiltered": ""
    },

    // Load data for the table's content from an Ajax source
    "ajax": {
    "url": base_url + "admin/size/size_list",
    "type": "POST",
    "dataType": "json",
    data:function(d) {
      var csrf_key = size_table.attr('data-keys');
      var csrf_hash = size_table.attr('data-values');
      d[csrf_key] = csrf_hash;
    },
    beforeSend: function(){
      show_loader()
    },
    dataSrc: function (jsonData) {
      hide_loader();
      $("#total").text(jsonData.recordsFiltered);
      if(jsonData.status==-1){
        location.reload();
      }else{
      size_table.attr('data-values',jsonData.csrf);
      return jsonData.data;
      }
    }
    },
    //Set column definition initialisation properties.
    "columnDefs": [
    { orderable: false, targets: -1 },

    ]

  });
});//size values table end

//add color values modal start
var add_color_modal = function (controller) {

  var userType=$('.userTypeGet').data('user-type');
  $.ajax({
    url: base_url + controller + "/color/add_color_modal",
    type: 'GET',
    data:{'userType':userType},

    success: function (data, textStatus, jqXHR) {
      $('#add_variant_values').html(data);
      $("#Modal").modal('show');
    }
  });
} //END OF ADD COLOR VALUES MODAL

//add size values modal start
var add_size_modal = function (controller) {

  var userType=$('.userTypeGet').data('user-type');
  $.ajax({
    url: base_url + controller + "/size/add_size_modal",
    type: 'GET',
    data:{'userType':userType},

    success: function (data, textStatus, jqXHR) {
      $('#add_variant_values').html(data);
      $("#Modal").modal('show');
    }
  });
} //END OF ADD SIZE VALUES MODAL

//add variant values modal start
var orderChangeStatus = function (controller,id) {

  var userType=$('.userTypeGet').data('user-type');
  $.ajax({
      url: base_url + controller,
      // 
      type: 'GET',
      data:{'userType':userType,'data':id},

      success: function (data, textStatus, jqXHR) {
          $('#show_status_change_modal').html(data);
          $("#change_status").modal('show');

      }
  });
} //END OF ADD VARIANT VALUES MODAL

//edit size values start
var editSizeValues = function (controller,variantValueId) {

  var userType=$('.userTypeGet').data('user-type');
  $.ajax({
      url: base_url + controller,
      // 
      type: 'GET',
      data:{'variantValueId':variantValueId},

      success: function (data, textStatus, jqXHR) {
          $('#edit_variant_values').html(data);
          $("#editSizeModal").modal('show');

      }
  });
} //END OF EDIT SIZE VALUES MODAL

//edit color values start
var editColorValues = function (controller,variantValueId) {

  var userType=$('.userTypeGet').data('user-type');
  $.ajax({
      url: base_url + controller,
      // 
      type: 'GET',
      data:{'variantValueId':variantValueId},

      success: function (data, textStatus, jqXHR) {
          $('#edit_variant_values').html(data);
          $("#editColorModal").modal('show');

      }
  });
} //END OF EDIT COLOR VALUES MODAL

//Transaction list table
// $(function(){
  var transaction_table = $("#transaction_table");
  var transaction_table_post = $('#transaction_table').DataTable({ 
    "processing": true, //Feature control the processing indicator.
    "serverSide": true, //Feature control DataTables' servermside processing mode.
    "order": [], //Initial no order.

    "paging": true,
    "lengthChange": false,
    "searching": true,
    "ordering": false,
    "info": true,
    "autoWidth": false,    
    "blengthChange": false,
    "iDisplayLength" :10,

    "bPaginate": true,
    "bInfo": true,
    "bFilter": false,
    "language": {                
    "infoFiltered": ""
    },

    // Load data for the table's content from an Ajax source
    "ajax": {
    "url": base_url + "admin/transaction/transaction_list",
    
    "type": "POST",
    "dataType": "json",
    data:function(d) {
      var csrf_key = transaction_table.attr('data-keys');
      var csrf_hash = transaction_table.attr('data-values');
      d[csrf_key] = csrf_hash;
      //var userId = transaction_table.attr('userId');
      d[csrf_key] = csrf_hash;
      //d['userId'] = userId;
      d.date_search = $('#datepicker').val();
    },
    beforeSend: function(){
      show_loader()
    },
    dataSrc: function (jsonData) {
      hide_loader();
      if(jsonData.status==-1){
        location.reload();
      }else{
        transaction_table.attr('data-values',jsonData.csrf);
        return jsonData.data;
      }
    }
    },
    //Set column definition initialisation properties.
    "columnDefs": [
    { orderable: false, targets: -1 },

    ]

  });
//}); //transaction list end


var date_inp = $("#datepicker");
date_inp.datepicker({
  //autoclose: true,
  keepOpen: false,
  todayHighlight: true,
  useCurrent: false,
  multidate: true,
  endDate: new Date(),
});

var selectedDates = [];
//toaster.remove();
date_inp.datepicker().on('changeDate', function (ev) {

    if (ev.dates.length <= 2) {
    // store current selections
      selectedDates = ev.dates
    } else {
      // reset dates if 3rd date selected
      date_inp.data('datepicker').setDates(selectedDates);
      toastr.warning('Can only select 2 dates'); //error
    }

    if (ev.dates.length == 2) {
    // store current selections
      selectedDates = ev.dates
      transaction_table_post.draw();
    }
});

$('#clearDate').on('click', function () {
  var d = new Date();
  date_inp.datepicker('update',d);
  date_inp.datepicker('update','');
  transaction_table_post.draw();
});

 // Stop Keyboard Wroking   
$('#datepicker').keydown(function(event) { 
    return false;
});


//function for adding product in deal
var addInDeal = function(controller,productId) {
  
  var oldValue = $("#productIds").val();

  var values = oldValue.split(',');

  let index = values.indexOf(productId);
  if(index=='-1'){
    var arr = oldValue === "" ? [] : oldValue.split(',');
    arr.push(productId);
    var valueAdd = arr.join(',');
  }else{

    values.splice(index, 1);
    var valueAdd = values.join(',');
  }
  $("#productIds").val(valueAdd);
}


  //Feedback list
  var BASEURL = $('#BASEURL').val();
  var order_table = $('#feedback_list').DataTable({ 

   "processing": true, //Feature control the processing indicator.
   "serverSide": true, //Feature control DataTables' servermside processing mode.
   "order": [], //Initial no order.   
   "paging": true,
   "lengthChange": false,
   "searching": true,
   "ordering": false,
   "info": true,
   "autoWidth": false, 
   "blengthChange": false,
   "iDisplayLength" :10,
   "bPaginate": true,
   "bInfo": true,
   "bFilter": false,
   
   // Load data for the table's content from an Ajax source
   "ajax": {
      "url": BASEURL+"admin/getFeedbackList",
      "type": "POST",
      "dataType": "json",
      // data: {'status': sta},
      "dataSrc": function (jsonData) {
        return jsonData.data;
      }
   },
   //Set column definition initialisation properties.
   "columnDefs":[
      { orderable: false, targets: -1 },
             
    ]
}); //END ajax Feedback list

//view feedback modal
var viewFeedback = function (ctrl,id) {
  $.ajax({
      url: base_url + ctrl ,
      type: 'POST',
      data: {'id': id},
      beforeSend: function () {
        show_loader()
      },
      success: function (data) {
        $('#form-modal-box').html(data);
        $("#viewFeedbackModel").modal('show');
      },
      error: function() {
        toastr.warning(unknown_error_msg); //error
      },
      complete: function () {
        hide_loader(); 
      }
  });
}

$("#add_category_form").validate({
  ignore: [],
  rules:{
    category_name:{
      required: true,
      maxlength: 100
    },
    category_desc:{
      required: true,
      maxlength: 200
    },
    image_base64:{
      required: true,
    },
  },

  errorPlacement: function(error, element)  {
    if (element.attr("name") == "add_category_form") {
      error.insertAfter("#add_category_form");
    } else {
      error.insertAfter(element);
    }
  }
});

//end of validation script

// $('.input_img3').click(function(){
//   $('#req').hide();
// });

var addCat = $("#add_category_form");
var proceed_err  = 'Please fill all the fields properly';

//end of add category modal
$('#add_category_button').on('click',function(){
  event.preventDefault();

  // var base64Image = $('#image_base64').val();
  // if(!base64Image && addCat.valid()===true){

  //   $('#req').show();
  //   return false;
  // }

  toastr.remove();
  if(addCat.valid()===false){
    //toastr.error(proceed_err);
    return false;
  }

  var block = $('#image_base64').val().split(";");
  // Get the content type of the image
  var contentTypes = block[0].split(":")[1];// In this case "image/png"
  // get the real base64 content of the file
  var realData = block[1].split(",")[1];

  var blob = b64toBlob(realData, contentTypes);

  var actionUrl=$('#add_category_form').attr('action');
  formData = new FormData(document.getElementById('add_category_form'));
  formData.append('category_image',blob,'category.png');

  $.ajax({
    type: "POST",
    url: actionUrl,
    dataType:'json',
    data: formData, //only input
    processData: false,
    contentType: false,
    beforeSend: function (){
      show_loader()
    },
    success: function(data){ 
      hide_loader();
      
      if(data.status==1){
        toastr.remove();
        toastr.clear();
        setTimeout(function(){
          window.location=data.url},
        2000),
        toastr.success(data.message)
      }else{
        $('#csrfs').val(data.csrf);
        toastr.remove();
        toastr.clear();
        toastr.error(data.message);
      }
    }
  });
 
});//end of login function



//destroy of cropping
function destroy(){
  $('#files').val('');
  $('#gallery_modal').cropper('destroy');
  $('#imageModal').modal('hide');
}


//show image on modal
$("#files").on("change", function(e) {

  if(this.files[0] || typeof this.files[0]!='undefined'){ // check image select

    $('#imageModal').modal({
      backdrop: 'static', 
      keyboard: false
    });

    document.getElementById('gallery_modal').src = window.URL.createObjectURL(this.files[0])
    var $image = $('#gallery_modal');

    //code for crop image
    $image.cropper({
      aspectRatio: 37 / 52 ,
      autoCropArea: true,
      dragMode: 'move',
      strict: false,
      guides: false,
      highlight: true,
      dragCrop: true,
      cropBoxResizable: false,
    });

  }

});

// upload image
if (window.File && window.FileList && window.FileReader) {

    $("#submit_preview").on("click", function(e) {
      show_loader();

      var cropper = $('#gallery_modal').data('cropper');
      cropper.getCroppedCanvas().toBlob(function (blob) {
        hide_loader();

         var reader = new FileReader();
         reader.readAsDataURL(blob); 
         reader.onloadend = function() {
            var base64data = reader.result;  
            $('#image_preview').css('display','block');
            $('#image_preview img').attr('src',base64data);
            $('#image_base64').val(base64data);
            $('#image_base64-error').html('');

            destroy();
         }
      
        $('#imageModal').modal('hide');
      }); 

    });

}else{
  toastr.error("Your browser doesn't support to File API");
}

function b64toBlob(b64Data, contentType, sliceSize) {
  contentType = contentType || '';
  sliceSize = sliceSize || 512;

  var byteCharacters = atob(b64Data);
  var byteArrays = [];

  for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
    var slice = byteCharacters.slice(offset, offset + sliceSize);

    var byteNumbers = new Array(slice.length);
    for (var i = 0; i < slice.length; i++) {
      byteNumbers[i] = slice.charCodeAt(i);
    }

    var byteArray = new Uint8Array(byteNumbers);

    byteArrays.push(byteArray);
  }

  var blob = new Blob(byteArrays, {type: contentType});
  return blob;
}