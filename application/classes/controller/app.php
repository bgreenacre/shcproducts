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
 * Application controller. Loads javascript and controlls the widgets.
 *
 * @package     shcproducts
 * @subpackage  Controller
 * @since       0.1
 * @author      Kyla Klein
 */
class Controller_App {

    /**
     * __construct - Add the actions and enabled widgets.
     *
     * @access  public
     * @return  void
     */
    public function __construct()
    {
        add_action('wp_print_scripts', array(&$this, 'load_js'));
        add_action('wp_print_styles', array(&$this, 'load_front_css'));
        add_action('admin_print_styles', array(&$this, 'load_css'));

        if ($widgets = (array) SHCP::get_option('widgets', SHCP::config('plugin.options.widgets.default')))
        {
            foreach ($widgets as $widget)
            {
                SHCP_Controller::factory('Widget_'.ucfirst($widget));
            }
        }
    }

    /**
     * load_js - Load javascript files that are global.
     *
     * @access  public
     * @return  void
     */
    public function load_js()
    {
        if (is_admin())
        {
            wp_enqueue_script('sears-products-admin-scripts', SHCP_JS.'/admin.js', array('jquery'));

            // declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
            wp_localize_script('sears-products-admin-scripts', 'shcp_ajax', array('ajaxurl' => admin_url('admin-ajax.php'), 'imageurl' => SHCP_IMAGES));
            
             wp_enqueue_script('sears-products-admin-verticals', SHCP_JS.'/admin_verticals_browser.js', array('jquery'));
        }
        else
        {
            wp_enqueue_script('sears-products-cart-script', SHCP_JS.'/cart.js', array('jquery'));
            wp_enqueue_script('sears-products-overlay-scripts', SHCP_JS.'/jquery.tools.min.js');
            wp_enqueue_script('sears-products-front-scripts', SHCP_JS.'/front.js', array('jquery', 'sears-products-overlay-scripts', 'sears-products-cart-script'));
            wp_localize_script('sears-products-front-scripts', 'shcp_ajax', array('ajaxurl' => admin_url('admin-ajax.php'), 'imageurl' => SHCP_IMAGES));
        }
    }

    /**
     * load_css - Load style files that are global.
     *
     * @access  public
     * @return  void
     */
    public function load_css()
    {
        wp_enqueue_style('shcp-admin-style', SHCP_CSS.'/admin_style.css');
    }

    /**
     * load_front_css 
     * 
     * @access public
     * @return void
     */
    public function load_front_css()
    {
        $store = strtolower(SHCP::get_option('store', SHCP::config('plugin.options.store.default')));
        wp_enqueue_style('shcp-front-style', SHCP_CSS.'/front.css');
        wp_enqueue_style('shcp-front-'.$store, SHCP_CSS.'/'.$store.'_front.css');
    }

}
