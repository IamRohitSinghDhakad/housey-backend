<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/

/* 
 * Hook for website maintenance mode
 * Using 'post_controller_constructor' because we don't need to show maintenance message in API modules
 * So we get CI super global variable access at this point and we can easily use router->fetch_class(),
 * load->view() etc if needed 
 */
$hook['post_controller_constructor'][] = array(
    'class'    => 'maintenance_hook',
    'function' => 'go_offline',
    'filename' => 'maintenance_hook.php',
    'filepath' => 'hooks'
);
$hook['pre_system'] = array(
    'class'    => 'Credential_hook',
    'function' => 'load_credential',
    'filename' => 'Credential_hook.php',
    'filepath' => 'hooks'
);
