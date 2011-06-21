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

    public function __construct($group = NULL)
    {
        parent::__construct($group);

        $this->content_type = 'xml';
    }

    protected function _initialize()
    {
        parent::_initialize();

        self::$_session = NULL;
    }

    public function session($key = NULL)
    {
        if ($key === NULL)
        {
            return self::$_session;
        }

        self::$_session = $key;

        return TRUE;
    }

    public function view()
    {
        return $this->method('ViewCart');
    }

    public function add($catalog_id = NULL, $catentry_id = NULL, $quantity = 1)
    {
        if ($catentry_id === NULL)
        {
            if ($this->_parent)
            {
                $this->products_to_add['catalogId'][] = $this->_parent->catalogId;
                $this->products_to_add['catentry_id'][] = $this->_parent->catentryId;
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

        $this->method('AddToCart');

        return $this;
    }

    public function clear()
    {
        $this->method('EmptyCart');

        return $this;
    }

    protected function _add_session()
    {
        if ($key = $this->session())
        {
            $this->param('sessionKey', $key);
        }
        else
        {
            $this->param('loginId', 'guest');
        }
    }

    protected function _request()
    {
        if ($this->method() == 'AddToCart')
        {
            if ($catalog_id = implode(',', $this->products_to_add['catalogId']))
            {
                $this->param('catalogId', $catalog_id);
            }

            $this
                ->param('catentryId', implode(',', $this->products_to_add['catentry_id']))
                ->param('quantity', implode(',', $this->products_to_add['quantity']));
        }

        return parent::_request();
    }

}
