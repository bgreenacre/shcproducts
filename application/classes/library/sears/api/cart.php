<?php defined('SHCP_PATH') OR die('No direct script access.');
/**
 * shcproducts
 *
 * @author Brian Greenacre and Kyla Klein
 * @email bgreenacre42@gmail.com
 * @version $Id$
 * @since Thu 16 Jun 2011 11:34:46 AM
 */

// -----------------------------------------------------------------------------

/**
 * Library_Sears_Api_Cart
 *
 */
class Library_Sears_Api_Cart extends Library_Sears_Api {

    public $products_to_add = array(
        'catalogId'     => array(),
        'catentry_id'   => array(),
        'quantity'      => array(),
    );

    protected static $_session;

    public function __construct($group = NULL, $parent = NULL)
    {
        parent::__construct($group, $parent);

        $this->content_type = 'xml';
    }

    protected function _initialize()
    {
        parent::_initialize();

        self::$_session = NULL;
    }

    protected function _load()
    {
        parent::_load();

        if (isset($this->_object->StatusData) === TRUE)
        {
            $this->success = ($this->_object->StatusData == 0) ? TRUE : FALSE;
        }
        else
        {
            $this->success = FALSE;
        }

        if ($this->success AND $this->method() == 'AddtoCart' AND ! $this->session())
        {
            $this->session($this->_object->clientSessionkey);
        }
    }

    public function session($key = NULL)
    {
        if ($key === NULL)
        {
            return SHCP::get($_SESSION, 'cartSessionkey');
        }

        $_SESSION['cartSessionkey'] = $key;

        return $this;
    }

    public function cart()
    {
        $this->load();

        if ($this->success)
        {
            return $this->_object->Shoppingcart;
        }

        return FALSE;
    }

    public function view()
    {
        $this->method('ViewCart');

        return $this;
    }

    public function add($catalog_id = NULL, $catentry_id = NULL, $quantity = 1)
    {
        if ($catentry_id === NULL)
        {
            if ($this->_parent)
            {
                $this->products_to_add['catalogId'][] = $this->_parent->catalogid;
                $this->products_to_add['catentry_id'][] = $this->_parent->catentryid;
                $this->products_to_add['quantity'][] = $quantity;
            }
            else
            {
                throw new Exception('No product catalog ID given for add to cart');
            }
        }
        else
        {
            $this->products_to_add['catalogId'][] = $catalog_id;
            $this->products_to_add['catentry_id'][] = $catentry_id;
            $this->products_to_add['quantity'][] = $quantity;
        }

        $this->method('AddtoCart');

        return $this;
    }

    public function clear()
    {
        $this->method('EmptyCart')->load();

        return $this;
    }

    protected function _add_session()
    {
        if ($key = $this->session())
        {
            $this->param('sessionKey', $key);
        }
        elseif ($user = $this->user())
        {
            $this->param('loginId', $user->id);
        }
        else
        {
            $this->param('loginId', 'Guest');
        }
    }

    protected function _request()
    {
        if ($this->method() == 'AddtoCart')
        {
            if ($catalog_id = implode(',', $this->products_to_add['catalogId']))
            {
                $this->param('catalogId', $catalog_id);
            }

            $this
                ->param('catentryId', implode(',', $this->products_to_add['catentry_id']))
                ->param('quantity', implode(',', $this->products_to_add['quantity']));
        }

        $this->_add_session();

        return parent::_request();
    }

}
