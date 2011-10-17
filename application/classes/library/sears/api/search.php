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
 * Library_Sears_Api_Search
 *
 */
class Library_Sears_Api_Search extends Library_Sears_Api {

    /**
     * _load 
     * 
     * @access protected
     * @return void
     */
    protected function _load()
    {
        parent::_load();

        if (isset($this->_object->mercadoresult->products->product[1]))
        {
            $this->_total_rows = (int) count($this->_object->mercadoresult->products->product[1]);
            $this->_data =& $this->_object->mercadoresult->products->product[1];
        }
        elseif (isset($this->_object->mercadoresult->navgroups->navgroup[1][0]->shopbycategories->shopbycategory[1]))
        {
          $this->_total_rows = (int) count($this->_object->mercadoresult->navgroups->navgroup[1][0]->shopbycategories->shopbycategory[1]);
          $this->_data =& $this->_object->mercadoresult->navgroups->navgroup[1][0]->shopbycategories->shopbycategory[1];
        }
    }

    /**
     * keyword 
     * 
     * @param mixed $q 
     * @access public
     * @return void
     */
    public function keyword($q)
    {
        $this->method('productsearch')
            ->param('searchType', 'keyword')
            ->param('keyword', $q);

        return $this;
    }

    /**
     * category 
     * 
     * @param mixed $vertical 
     * @param mixed $category 
     * @param mixed $sub_category 
     * @access public
     * @return void
     */
    public function category($vertical, $category, $sub_category = NULL)
    {
      
        $this->method('productsearch')
          ->param('verticalName', $vertical)
          ->param('categoryName', $category);

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

    /**
     * vertical 
     * 
     * @param mixed $vertical 
     * @access public
     * @return void
     */
    public function vertical($vertical)
    {
        $this->method('productsearch')
            ->param('verticalName', $vertical)
            ->param('searchType', 'vertical');

        return $this;
    }

    /**
     * filter 
     * 
     * @param mixed $filter 
     * @access public
     * @return void
     */
    public function filter($filter)
    {
        $this->param('filter', $filter);
        return $this;
    }

    /**
     * part_numbers 
     * 
     * @param array $numbers 
     * @access public
     * @return void
     */
    public function part_numbers(array $numbers)
    {
        if ($numbers)
        {
            $this->param('partNumber', implode(',', $numbers));
        }

        return $this;
    }

}
