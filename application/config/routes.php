<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;


//Route for pre flight CORS permission request
function option_route(){
	header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Device-Id,Device-Type,Device-Timezone,Authorization");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE, PATCH");
    $method = $_SERVER['REQUEST_METHOD'];
    if($method == "OPTIONS") {
        http_response_code(200); exit;
    }
    
}

//Route for pre flight CORS permission request
//$route['api/v1/(:any)']['options'] = option_route();
$route['api/v1/(:any)/(:any)']['options'] = option_route();


//Auth Routing
$route['api/v1/auth/signup'] = 'api_v1/auth/signup';
$route['api/v1/auth/login'] = 'api_v1/auth/login';
$route['api/v1/auth/social-signup'] = 'api_v1/auth/social';
$route['api/v1/auth/check-social-signup'] = 'api_v1/auth/check_social_signup';
$route['api/v1/auth/reset-password'] = 'api_v1/auth/reset_password';
$route['api/v1/auth/logout'] = 'api_v1/auth/logout';
$route['api/v1/user/change-password'] = 'api_v1/auth/change_password';
$route['api/v1/auth/content'] = 'api_v1/auth/content'; //Terms & condition AND Privacy Policy

//Option Routing
$route['api/v1/option/banner']['get'] = 'api_v1/option/banner';
$route['api/v1/option/deal']['get'] = 'api_v1/option/deal';

//Seller Routing
$route['api/v1/seller/biz-info'] = 'api_v1/seller/business_info';
$route['api/v1/seller/biz-info/(:num)'] = 'api_v1/seller/business_info/$1';
$route['api/v1/seller/my-profile'] = 'api_v1/seller/my_profile';
$route['api/v1/seller/profile'] = 'api_v1/seller/update_profile';
$route['api/v1/seller/update-avatar'] = 'api_v1/seller/update_avatar';
$route['api/v1/seller/seller-info'] = 'api_v1/seller/seller_info';

//Seller Product Routing
$route['api/v1/product']['post'] = 'api_v1/product/product';
$route['api/v1/category'] = 'api_v1/product/category_list';
$route['api/v1/variant'] = 'api_v1/product/variant_list';
$route['api/v1/product/(:num)/attachments']['post'] = 'api_v1/product/attachments/$1';
$route['api/v1/product/attachments/(:num)']['delete']= 'api_v1/product/attachments/$1';
$route['api/v1/product/(:num)']['delete'] = 'api_v1/product/index/$1';
$route['api/v1/product/update'] = 'api_v1/product/update';
$route['api/v1/product/my-product'] = 'api_v1/product/list';

//Seller Order routing
$route['api/v1/seller/new-order']['get'] = 'api_v1/seller/new_order';
$route['api/v1/seller/my-order']['get'] = 'api_v1/seller/my_order';
$route['api/v1/order/accept-reject/(:num)']['patch'] = 'api_v1/order/accept_reject/$1';
$route['api/v1/order/order-detail/(:num)']['get'] = 'api_v1/order/detail/$1';
$route['api/v1/seller/change-status/(:num)']['patch'] = 'api_v1/seller/change_status/$1';




//Buyer Product Routing
$route['api/v1/product']['get'] = 'api_v1/product/index'; //Product list
$route['api/v1/product/(:num)']['get'] = 'api_v1/product/index/$1'; //Detail of product
$route['api/v1/product/featured']['get'] = 'api_v1/product/featured'; //Featured product

//Buyer User Routing
$route['api/v1/user/my-profile'] = 'api_v1/user/my_profile';
$route['api/v1/user/profile'] = 'api_v1/user/update_profile';
$route['api/v1/user/update-avatar'] = 'api_v1/user/update_avatar';
$route['api/v1/user/feedback'] = 'api_v1/user/feedback';
$route['api/v1/user/wishlist']['put'] = 'api_v1/user/wishlist';  //Add and remove wishlist
$route['api/v1/user/wishlist']['delete'] = 'api_v1/user/clear_wishlist'; //Clear All Wishlist
$route['api/v1/user/wishlist-list']['get'] = 'api_v1/user/wishlist_list'; //Wishlist list
$route['api/v1/user/user-rating']['post']= 'api_v1/user/rating_review'; //rating and review


$route['api/v1/cart']['post'] = 'api_v1/cart/cart';  //Add to cart
$route['api/v1/cart']['get'] = 'api_v1/cart/list';  //Cart list
$route['api/v1/cart/(:num)']['delete']= 'api_v1/cart/cart/$1'; //Cart delete
$route['api/v1/cart']['delete']= 'api_v1/cart/cart'; //Clear cart
$route['api/v1/cart/alter/(:num)']['put']= 'api_v1/cart/alter/$1'; //Increment Decrement cart
$route['api/v1/cart/count']['get']= 'api_v1/cart/cart_count'; //cart count
$route['api/v1/offer/make-offer']['post']= 'api_v1/offer/offer_item'; //Make an offer
$route['api/v1/offer/offer-list']['get']= 'api_v1/offer/list'; //Make an offer item list

//Buyer Address Routing
$route['api/v1/user/address']['post']= 'api_v1/user/address';
$route['api/v1/user/address']['get'] = 'api_v1/user/address';
$route['api/v1/user/address/(:num)']['delete'] = 'api_v1/user/address/$1';
$route['api/v1/user/address/(:num)']['put'] = 'api_v1/user/address/$1';

//Buyer Payment Routing
$route['api/v1/card']['post']= 'api_v1/payment/card'; //save card in DB
$route['api/v1/create-customer']['put']= 'api_v1/payment/create_customer'; //create customer on stripe
$route['api/v1/add-card']['post']= 'api_v1/payment/add_card'; //add card by website
$route['api/v1/card-list']['get']= 'api_v1/payment/card'; //card list
$route['api/v1/card-delete/(:any)']['delete']= 'api_v1/payment/card/$1'; //card delete
$route['api/v1/card/default/(:any)']['patch']= 'api_v1/payment/card/$1'; //make default
$route['api/v1/payment']['post']= 'api_v1/payment/payment'; //Make Payment

//Buyer Order Routing
$route['api/v1/user/my-order']['get']= 'api_v1/user/my_order'; //user order list
$route['api/v1/order/buyer-order-detail/(:num)']['get'] = 'api_v1/order/buyer_order_detail/$1';

$route['api/v1/notification/push_alert_status']['patch'] = 'api_v1/notification/push_alert_status';
$route['api/v1/notification/web-push']['patch'] = 'api_v1/notification/webPushNotifiction';
$route['api/v1/notification/notification-list']['get'] = 'api_v1/notification/notificationList';
$route['api/v1/notification/read-notification']['post'] = 'api_v1/notification/readNotification';