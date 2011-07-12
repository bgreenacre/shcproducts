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
 * Product display controller. This provides generic templates to be used
 * in theme template files.
 *
 *  // Example of template usage
 *  if (defined('SHCP_PATH'))
 *  {
 *      Controller::factory('front_products')->action_grid();
 *  }
 *
 * @package		shcproducts
 * @subpackage	Controller
 * @since		0.1
 * @author		Brian Greenacre
 */
class Controller_Front_Products {

    public function __construct()
    {
        add_action('init', array(&$this, 'init'));
    }

    public function init()
    {
        add_shortcode('shcp_products', array(&$this, 'action_grid'));
        add_shortcode('shcp_product', array(&$this, 'action_detail'));
        add_shortcode('shcp_quickview', array(&$this, 'action_quickview'));
    }

    public function action_grid($attrs = NULL)
    {
        $this->products = new Model_Products();

        $this->parse_attrs($attrs);

        $data = array(
            'products'  => $this->products
        );

        echo SHCP::view('front/product/grid', $data);
    }

    public function action_detail($attrs = NULL)
    {
        $this->products = new Model_Products();

        $this->parse_attrs($attrs);

        $data = array(
            'products'  => $this->products
        );

        echo SHCP::view('front/product/detail', $data);
    }

    public function action_quickview($attrs = NULL)
    {
        $this->products = new Model_Products();

        $this->parse_attrs($attrs);

        $data = array(
            'products'  => $this->products
        );

        echo SHCP::view('front/product/quickview', $data);
    }

    public function parse_attrs($attrs = NULL)
    {
        $this->products->param($attrs);
    }

}
