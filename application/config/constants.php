<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code


/* Application specific constants */

//keys (session, third party API keys like google, stripe etc)  

/* Firebase API key for notifications */
//define('NOTIFICATION_KEY','AAAARhoraBA:APA91bF60FScjvb-3LkAo5zyMMn24xfNRIPLA1Pc0rYI-ijRz0a1ITij6keKaz37SjVP1D5qq9Kl5FXwXaHj2fXs1qo9wkDEA4NjyXxApXFcQkqrQ7n1luy-HUElSOYTJZ8LU8w_1Vlz'); 

/* session keys */
define('USER_SESS_KEY', 'app_user_sess'); 
define('ADMIN_USER_SESS_KEY', 'app_admin_user_sess');

//DB tables
define('ADMIN', 'admin');
define('USERS', 'users');
define('USER_DEVICES', 'user_devices');


//uploads path
define('USER_AVATAR_PATH', 'uploads/user_avatar/'); //user avatar
define('USER_DEFAULT_AVATAR', 'uploads/placeholder/user_placeholder.png'); //user placeholder image
define('CATEGORY_DEFAULT_AVATAR', 'uploads/placeholder/user_placeholder.png'); //category placeholder image

//Title, Site name, Copyright etc
define('SITE_NAME','Housey'); //your project name
define('COPYRIGHT','&copy; ' . date('Y') . ', Housey. All rights reserved.');
define('INFO_EMAIL','info@project.com'); //your project name


//common messages
define('UNKNOWN_ERR', 'Something went wrong. Please try again');
define('FAIL', 'fail');
define('SUCCESS', 'success');
define('BAD_REQUEST',400);
define('NOT_FOUND',404);
define('OK',200);
define('HTTP_OK',200);
define('INTERNAL_SERVER_ERROR',	500);
define('INVALID_PARAM_VALUE', 'INVALID_PARAM_VALUE');
define('EMAIL_EXIST',	'EMAIL_EXIST');
define('PARAM_MISSING',	'PARAM_MISSING');
define('ACCOUNT_INACTIVE',	'ACCOUNT_INACTIVE');
define('HEADERS_MISSING',	'HEADERS_MISSING');
define('INVALID_HEADER_VALUE',	'INVALID_HEADER_VALUE');
define('SESSION_EXPIRED',	'SESSION_EXPIRED');
define('INVALID_TOKEN',	'INVALID_TOKEN');
define('USER_NOT_FOUND',	'USER_NOT_FOUND');
define('SERVER_ERROR',	'INTERNAL_SERVER_ERROR');


//Asset path (In place of "APP_" you can use your own project specific prefix)
/* Admin */
define('HOUSEY_LOGO', 'backend_assets/logo/');

//Backend(ADMIN) Assets
define('CDN_BACK_ASSETS', 'backend_assets/'); //ADMIN THEME
define('CDN_BACK_DIST_CSS', CDN_BACK_ASSETS.'css/');
define('CDN_BACK_DIST_JS', CDN_BACK_ASSETS.'js/');
define('CDN_BACK_DIST_IMG', CDN_BACK_ASSETS.'img/');
define('CDN_BACK_BUILD', CDN_BACK_ASSETS.'build/');
define('CDN_BACK_BOOTSTRAP_CSS', CDN_BACK_ASSETS.'bootstrap/css/');
define('CDN_BACK_BOOTSTRAP_JS', CDN_BACK_ASSETS.'bootstrap/js/');
define('CDN_BACK_BOOTSTRAP_FONTS', CDN_BACK_ASSETS.'bootstrap/fonts/');
define('CDN_BACK_PLUGINS', CDN_BACK_ASSETS.'plugins/');
define('APP_ADMIN_ASSETS_CUSTOM_CSS', CDN_BACK_ASSETS.'custom/css/');
define('APP_ADMIN_ASSETS_CUSTOM_JS', CDN_BACK_ASSETS.'custom/js/');