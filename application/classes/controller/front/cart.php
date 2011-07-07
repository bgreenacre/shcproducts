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
 *      Controller::factory('front_cart')->action_view();
 *  }
 *
 * @package		shcproducts
 * @subpackage	Controller
 * @version		0.1
 * @author		Brian Greenacre
 */

class Controller_Front_Cart {

    /**
     * Cart object
     *
     * @var object
     */
    public $cart;

    public function __construct()
    {
        add_shortcode('shcp_cart', array(&$this, 'action_view'));
        add_shortcode('shcp_minicart', array(&$this, 'action_mini'));

        $this->cart = Library_Sears_Api::factory('cart');
        SHCP::bind_global('cart', $this->cart);
    }

    public function action_mini()
    {
    }

    public function action_view()
    {
        $data = array(
        );
    }

    public function action_add()
    {
    }

    public function action_remove()
    {
    }

    public function action_update()
    {
    }

    public function action_empty()
    {
    }

}
