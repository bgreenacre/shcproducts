<?php defined('SHCP_PATH') OR die('No direct script access.');
/**
 * Sears Holding Company Products Wordpress plugin.
 *
 * Provides the ability to import products via the Sears API and storing in
 * wordpress as custom post type.
 *
 * @author Brian Greenacre and Kyla Klein
 * @package shcproducts
 * @email bgreenacre42@gmail.com
 * @version $Id$
 * @since Wed 15 Jun 2011 07:32:09 PM
 */

// -----------------------------------------------------------------------------

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
	    if (is_admin())
	    {
            wp_enqueue_script('sears-products-admin-scripts', SHCP_JS.'/admin.js', array('jquery'));

		    // declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
            wp_localize_script('sears-products-admin-scripts', 'shcp_ajax', array('ajaxurl' => admin_url('admin-ajax.php')));
        }
	}

	public function load_css()
	{
	    wp_enqueue_style('shcp-admin-style', SHCP_CSS.'/admin_style.css');
	}

}

