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
 * Library_Sears_Api_Product
 *
 */
class Library_Sears_Api_Product extends Library_Sears_Api {

    protected function _load()
    {
        parent::_load();

        if ($this->_object->productdetail->softhardproductdetails)
        {
            $this->_data = $this->_object->productdetail->softhardproductdetails[1];
            $this->_total_rows = 1;
        }
    }

    public function get($id = NULL)
    {
        if ($id === NULL AND $this->_parent)
        {
            $id = $this->_parent->partnumber;
        }

        $this
            ->method('productdetails')
            ->param('partNumber', $id);

        return $this;
    }

    public function compare()
    {
        $count = func_num_args();

        if ($count > 0)
        {
            $product_numbers = array();

            if ($this->count() > 0)
            {
                $product_numbers[] = $this->current()->productnumber;
            }

            foreach (func_get_args() as $product)
            {
                if ($product instanceof Library_Sears_Api)
                {
                    $product_numbers[] = $product->current()->partnumber;
                }
                else
                {
                    $product_numbers[] = $product;
                }
            }

            $this
                ->method('CompareProducts')
                ->param('prodCount', count($product_numbers))
                ->param('partNumber', implode(',', $product_numbers));
        }

        return $this;
    }

    public function add_to_cart()
    {
        return Library_Sears_Api_Cart::factory('cart', $this->_group, $this->current())
            ->add()
            ->load();
    }

}
