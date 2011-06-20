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
            $this->_data = $this->_object->productdetail->softhardproductdetails[1][0];
            $this->_total_rows = 1;
        }
    }

    public function get($id = NULL)
    {
        if ($id === NULL AND $this->_parent)
        {
            $id = $this->_parent->id;
        }

        $this
            ->method('productdetails')
            ->param('partNumber', $id);

        return $this;
    }

}

