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
    protected $cache = FALSE;

    public function form($values = NULL)
    {
        $data = array(
            'ids'       => array(
                'title'     => $this->get_field_id('title'),
                'keyword'   => $this->get_field_id('keyword'),
                'limit'     => $this->get_field_id('limit'),
                'randomize' => $this->get_field_id('randomize'),
            ),
            'names'       => array(
                'title'     => $this->get_field_name('title'),
                'keyword'   => $this->get_field_name('keyword'),
                'limit'     => $this->get_field_name('limit'),
                'randomize' => $this->get_field_name('randomize'),
            ),
            'values'    => array_merge(SHCP::config('widget/products.options'), $values),
        );

        echo SHCP::view('widget/products/form', $data);
    }

    public function widget($args, $instance)
    {
        $products = new Model_Products();
        
        $products
            ->param('post__in', $instance['pool'])
            ->orderby((SHCP::get($instance, 'randomize')) ? 'rand': 'none')
            ->limit(SHCP::get($instance, 'limit', 3))
            ->load();
        
        $data = array(
            'products'  => $products,
        );
        
        $this->content = SHCP::view($this->content, $data);
        
        parent::widget($args, $instance);
    }
    
    public function update($new, $old)
    {
        $data = array_merge($old, $new);
        $data['title'] = strip_tags($data['title']);
        $data['pool'] = array();

        $search = Library_Sears_Api::factory('search')
            ->keyword(SHCP::get($data, 'keyword'));

        if ($randomize = (bool) SHCP::get($data, 'randomize'))
        {
            $limit = ((int) SHCP::get($data, 'limit', 3)) * 20;
            $search->limit(0, $limit);
        }
        
        $search->load();
        
        if (count($search) > 0)
        {
            $search->rewind();

            while ($search->valid())
            {
                $product = new Model_Products();

                $product->meta('partnumber', '=', $search->partnumber);

                if ( ! $product->loaded())
                {
                    $data_to_import = array(
                        'post_title'    => $search->name,
                        'imageid'       => $search->imageid,
                        'numreview'     => $search->numreview,
                        'catentryid'    => $search->catentryid,
                        'rating'        => $search->rating,
                        'partnumber'    => $search->partnumber,
                        'cutprice'      => $search->cutprice,
                        'displayprice'  => $search->displayprice,
                    );
                    
                    $data_to_import['detail'] = Library_Sears_Api::factory('product')
                        ->get($data_to_import['partnumber'])
                        ->load();
                    
                    $product->values($data_to_import);
                    
                    if ($product->check())
                    {
                        $product->save();
                        $data['pool'][] = $product->ID;
                    }
                    else
                    {
                    }
                }
                else
                {
                    $data['pool'][] = $product->ID;
                }

                $search->next();
            }
        }

        return $data;
    }

}
