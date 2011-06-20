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
 * Library_Sears_Api_Search
 *
 */
class Library_Sears_Api_Search extends Library_Sears_Api {

    public function __get($key)
    {
        if ($key === 'product')
        {
            return Library_Sears_Api::factory('product', current($this));
        }

        return parent::__get($key);
    }

    public function _load()
    {
        parent::_load();

        if (isset($this->_object->mercadoresult->products->product[1]))
        {
            $this->_total_rows = (int) count($this->_object->mercadoresult->products->product[1]);
            $this->_data = $this->_object->mercadoresult->products->product[1];
        }
    }

    public function keyword($q)
    {
        $this
            ->method('productsearch')
            ->param('searchType', 'keyword')
            ->param('keyword', $q);

        return $this;
    }

    public function category($category, $sub_category = NULL)
    {
        $this->param('categoryName', $category);

        if ($sub_category === NULL)
        {
            $this->param('searchType', 'category');
        }
        else
        {
            $this
                ->param('searchType', 'subcategory')
                ->param('subCategoryName', $sub_category);
        }

        return $this;
    }

    public function vertical($vertical)
    {
        $this
            ->param('verticalName', $vertical)
            ->param('searchType', 'vertical');

        return $this;
    }

    public function filter($filter)
    {
        $this->param('filter', $filter);
        return $this;
    }

    public function part_numbers(array $numbers)
    {
        if ($numbers)
        {
            $this->param('partNumber', implode(',', $numbers));
        }

        return $this;
    }

}
