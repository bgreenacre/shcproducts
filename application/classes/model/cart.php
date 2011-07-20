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

        if ( ! $this->cart)
        {
            if ($session = SHCP::get($_COOKIE, 'shcp_cart_session_key'))
            {
                self::session($session);

                $this->cart = get_option('cart_'.md5($session), (object) SHCP::config('cart.defaults'));
                //$this->cart = (object) SHCP::config('cart.defaults');
            }
            else
            {
                $this->cart = (object) SHCP::config('cart.defaults');
            }
        }
    }

    protected function _load()
    {
        if ( ! $this->method())
            $this->view();
        
        parent::_load();

        if (self::session() != SHCP::get($_COOKIE, 'shcp_cart_session_key'))
        {
            setcookie('shcp_cart_session_key', self::session(), time()+3600, '/');
        }

        $this->update_cart();
    }
    
    public function update_cart()
    {
        $this->cart = (object) $this->cart;
        $this->cart->order_id = (string) $this->OrderId;
        $this->cart->session = self::session();
        $this->cart->catalog_id = (string) $this->CatalogId;
        $this->cart->total_item_price = (double) preg_replace('/[^0-9\.]+/', '', (string) $this->Summary->SubTotal);
        $this->cart->total_price = (double) preg_replace('/[^0-9\.]+/', '', (string) $this->Summary->EstimatedPreTaxTotal);
        $this->cart->total_discounts = (double) preg_replace('/[^0-9\.]+/', '', (string) $this->Summary->TotalSavings);

        $this->cart->item_count = 0;
        $this->cart->items = array();
        
        if ( ! empty($this->OrderItems->OrderItem))
        {
            foreach ($this->OrderItems->OrderItem as $item)
            {
                $product = new Model_Products();
                $product->meta('partnumber', '=', $item->PartNo)->load();
                
                if ($product->loaded())
                {
                    $name = $product->post_title;
                }
                else
                {
                    $name = NULL;
                }
                
                $this->cart->items[] = (object) array(
                    'id'            => (string) $item->OrderItemID,
                    'name'          => $name,
                    'image'         => (string) $item->ImageURL,
                    'partnumber'    => (string) $item->PartNo,
                    'display_partnumber'    => (string) $item->DisplayPartNumber,
                    'catentryid'    => (string) $item->CatEntryId,
                    'quantity'      => (int) $item->Qty,
                    'price_each'    => (double) (($item->SalePrice) ? $item->SalePrice : $item->RegularPrice),
                    'price'         => (double) preg_replace('/[^0-9\.]/', '', (string) $item->ItemTotal),
                    'options'       => array(),
                );
                
                unset($product);
                
                ++$this->cart->item_count;
            }
        }

        update_option('cart_'.md5(self::session()), $this->cart);
        
        return $this;
    }

}
