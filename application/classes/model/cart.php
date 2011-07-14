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
 * Model_SHCP
 *
 * @package		shcproducts
 * @subpackage	Model
 * @since		0.1
 * @author		Brian Greenacre
 */
class Model_Cart extends Library_Sears_Api_Cart {

    public $cart;

    protected function _initialize()
    {
        parent::_initialize();

        if ( ! $this->cart AND $session = SHCP::get($_COOKIE, 'shcp_cart_session_key'))
        {
            self::session($session);

            $this->cart = get_option('cart_'.md5($session), SHCP::config('cart.default'));
        }
    }

    protected function _load()
    {
        parent::_load();

        if (self::session() != SHCP::get($_COOKIE, 'shcp_cart_session_key'))
        {
            setcookie('shcp_cart_session_key', self::session(), '/', str_replace('http://www','',get_bloginfo('url')));
        }

        var_dump($this->current());
    }

    public function add($quantity = 1, $catalog_id = NULL, $catentry_id = NULL)
    {
        parent::add($quantity, $catalog_id, $catentry_id);
    }

    public function update($line_id, $quantity, $order_id = NULL, $catalog_id = NULL)
    {
    }

    public function clear($order_id = NULL, $catalog_id = NULL)
    {
    }

    public function remove($line_id, $order_id = NULL, $catalog_id = NULL)
    {
    }

}
