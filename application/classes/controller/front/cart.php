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
 *      SHCP_Controller::factory('front_cart')->action_view();
 *  }
 *
 * @package     shcproducts
 * @subpackage  Controller
 * @since       0.1
 * @author      Brian Greenacre
 */
class Controller_Front_Cart {

    /**
     * Cart object
     *
     * @var object
     */
    public $cart;

    public $ajax_response = TRUE;

    public function __construct()
    {
        add_action('wp_ajax_cartaction_mini', array(&$this, 'action_mini'));
        add_action('wp_ajax_nopriv_cartaction_mini', array(&$this, 'action_mini'));
        add_action('wp_ajax_cartaction_view', array(&$this, 'action_view'));
        add_action('wp_ajax_nopriv_cartaction_view', array(&$this, 'action_view'));
        add_action('wp_ajax_cartaction_add', array(&$this, 'action_add'));
        add_action('wp_ajax_nopriv_cartaction_add', array(&$this, 'action_add'));
        add_action('wp_ajax_cartaction_remove', array(&$this, 'action_remove'));
        add_action('wp_ajax_nopriv_cartaction_remove', array(&$this, 'action_remove'));
        add_action('wp_ajax_cartaction_update', array(&$this, 'action_update'));
        add_action('wp_ajax_nopriv_cartaction_update', array(&$this, 'action_update'));
        add_action('wp_ajax_cartaction_empty', array(&$this, 'action_empty'));
        add_action('wp_ajax_nopriv_cartaction_empty', array(&$this, 'action_empty'));
        add_shortcode('shcp_cart', array(&$this, 'action_view'));
        add_shortcode('shcp_minicart', array(&$this, 'action_mini'));

        $this->cart = SHCP::$global_cart;
        SHCP::bind_global('cart', $this->cart);
    }

    /**
     * action_mini 
     * 
     * @access public
     * @return void
     */
    public function action_mini()
    {
        $this->cart->view()->load();
        echo SHCP::view('front/cart/mini', array('simple_cart' => $this->cart->cart));
    }

    /**
     * action_view 
     * 
     * @access public
     * @return void
     */
    public function action_view()
    {
        $this->cart->view()->load();
        echo SHCP::view('front/cart/view', array('simple_cart' => $this->cart->cart));
    }
    
    
    /*
    * add_single
    *
    * @return boolean
    */
    public function action_add_single($catentry_id) {
    	// Library_Sears_Api_Cart
    	// add($quantity = 1, $catalog_id = NULL, $catentry_id = NULL)
    	error_log('action_add_single $catentry_id - '.print_r($catentry_id,true));
    	$this->cart->method('AddtoCart');
    	$this->cart->add(1, 12605, $catentry_id);
		try {
			$this->cart->load(); //var_dump($this->cart);
			//$this->cart->view()->load();
		}
		catch(Exception $e) {
			throw new Exception($e);
		}
    }

    /**
     * action_add 
     * 
     * @access public
     * @return void
     */
    public function action_add()
    {
        if ($catentryid = (array) SHCP::get($_POST, 'catentryid', SHCP::get($_GET, 'catentryid')))
        {
            $quantity = (array) SHCP::get($_POST, 'quantity', SHCP::get($_GET, 'quantity'));
            $catalog_id = (array) SHCP::get($_POST, 'catalog_id', SHCP::get($_GET, 'catalog_id'));

            foreach ($catentryid as $key => $cid)
            {
                $this->cart->add(
                    (int) SHCP::get($quantity, $key, 1),
                    SHCP::get($catalog_id, $key, 12605),
                    $cid
                    );
            }
        }

        try
        {
            $this->cart->load(); //var_dump($this->cart);
            $this->cart->view()->load();
        }
        catch(Exception $e)
        {
            throw new Exception($e);
        }

        if ($this->ajax_response)
        {
            $this->ajax_response();
        }
    }

    /**
     * action_remove 
     * 
     * @access public
     * @return void
     */
    public function action_remove()
    {
        if ($ids = (array) SHCP::get($_POST, 'id', SHCP::get($_GET, 'id')))
        {
            foreach ($ids as $id)
            {
                $this->cart
                    ->remove($id, $this->cart->cart->order_id, $this->cart->cart->catalog_id)
                    ->load();
            }
        }

        if ($this->ajax_response)
        {
            $this->ajax_response();
        }
    }

    /**
     * action_update 
     * 
     * @access public
     * @return void
     */
    public function action_update()
    {
        $this
            ->cart
            ->load();

        $quantity = (array) SHCP::get($_GET, 'quantity');

        foreach ((array) SHCP::get($_GET, 'item_id') as $key => $item_id)
        {
            $this->cart
                ->update($item_id, (int) SHCP::get($quantity, $key))
                ->load();
        }

        $this->cart->view()->load();

        if ($this->ajax_response)
        {
            $this->ajax_response();
        }
    }

    /**
     * action_empty 
     * 
     * @access public
     * @return void
     */
    public function action_empty()
    {
        $this->cart
            ->update_cart()
            ->clear($this->cart->cart->order_id, $this->cart->cart->catalog_id)
            ->load()
            ->view()
            ->load();

        if ($this->ajax_response)
        {
            $this->ajax_response();
        }
    }

    /**
     * ajax_response 
     * 
     * @access public
     * @return void
     */
    public function ajax_response()
    {
        if ( ! SHCP::$is_ajax)
            return;

        $response = json_encode($this->cart->cart);

        // Send headers to not cache this result.
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

        // Send header for json mimetpye and length of the response json string.
        header('Content-Type: text/plain');
        header('Content-Length: '.strlen($response)+1);

        echo $response;
        exit;
    }

}
