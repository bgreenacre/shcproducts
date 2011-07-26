<?php defined('SHCP_PATH') OR die('No direct script access.');
/**
 * shcproducts
 *
 * @author Brian Greenacre and Kyla Klein
 * @email bgreenacre42@gmail.com
 * @version $Id$
 * @since Wed 15 Jun 2011 07:32:09 PM
 */

// ----------------------------------------------------------------------------

/**
 * Controller_Widget_Products
 *
 */
class Controller_Widget_Products extends SHCP_Controller_Widget {

    /**
     * @access  protected
     * @var string
     */
    protected $content = 'widget/products/list';

    public function form($values = NULL)
    {
        $data = array(
            'ids'       => array(
                'title'     => $this->get_field_id('title'),
                'keyword'   => $this->get_field_id('keyword'),
                'limit'     => $this->get_field_id('limit'),
            ),
            'names'       => array(
                'title'     => $this->get_field_name('title'),
                'keyword'   => $this->get_field_name('keyword'),
                'limit'     => $this->get_field_name('limit'),
            ),
            'values'    => array_merge(SHCP::config('widget/products.options'), $values),
        );

        echo SHCP::view('widget/products/form', $data);
    }

    public function update($new, $old)
    {
        $data = array_merge($old, $new);
        $data['title'] = strip_tags($data['title']);

        $search = Library_Sears_Api::factory('search')
            ->keyword(SHCP::get($data, 'keyword'));

        if (count($search) > 0)
        {
            $search->reset();

            while ($search->valid())
            {
                $product = new Model_Product();

                $product->param('partnumber', $search->partNumber);

                if ( ! $product->loaded())
                {

                }

                $search->next();
            }
        }

        return $data;
    }

}
