<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/*
 * MY Loader
 * Extends MX_Loader class to load custom template view with header and footer
 * version: 2.0 (14-08-2018)
 * Common DB queries used in app
 */

require APPPATH."third_party/MX/Loader.php";

class MY_Loader extends MX_Loader {

    /* Load template for frontend(website)
     * Added in ver 2.0
     */
	function front_render($template_name, $vars = array(), $page_script = '') {
        
        $this->view('frontend_includes/front_header', $vars);
        $this->view($template_name, $vars);
        $this->view('frontend_includes/front_footer', $vars);

        //$this->view('front_includes/common_script', $vars);
        if (!empty($page_script)):
            $this->view($page_script, $vars);
        endif;
    }

    /* Load template for frontend(website) with different header
     * Added in ver 2.0
     */
    function front_render_minimal($template_name, $vars = array(), $page_script = ''){

        $this->view('frontend_includes/front_header_minimal', $vars);
        $this->view($template_name, $vars);
        $this->view('frontend_includes/front_footer_minimal', $vars);

        //$this->view('front_includes/common_script', $vars);
        if (!empty($page_script)):
            $this->view($page_script, $vars);
        endif;
    }

    /* Load template for backend(Admin panel)
     * Added in ver 2.0
     */
    function admin_render($template_name, $vars = array(), $page_script = '') {
        
        $this->view('backend_includes/admin_header', $vars);
        $this->view($template_name, $vars);
        $this->view('backend_includes/admin_footer', $vars);

        //$this->view('backend_includes/back_script', $vars);
        if (!empty($page_script)):
            $this->view($page_script, $vars);
        endif;
    }
}
