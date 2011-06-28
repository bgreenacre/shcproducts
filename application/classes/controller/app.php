<?php defined('SHCP_PATH') OR die('No direct script access.');
/**
 * Sears and Kmart product plugin.
 * Application controller.
 *
 * @package		shcproducts
 * @subpackage	Controller
 * @version		0.1
 * @author		Kyla Klein
 */

class Controller_App {

	public function __construct(array $params = NULL)
	{
	    add_action('wp_print_scripts', array(&$this, 'load_js'));
	    add_action('admin_print_styles', array(&$this, 'load_css'));
	}

	public function load_js()
	{
	    wp_enqueue_script('shcp-admin-script', SHCP_JS.'/admin.js', array('jquery'));

	    // declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
	    wp_localize_script('shcp-admin-ajax-script', 'SHCP_ajax', array('ajaxurl' => admin_url('admin-ajax.php')));
	}

	public function load_css()
	{
	    wp_enqueue_style('shcp-admin-style', SHCP_CSS.'/admin_style.css');
	}

}
