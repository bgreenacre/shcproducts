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

// ----------------------------------------------------------------------------

/**
 * Controller_Widget_Products - This widget will provide product relation to the
 * site installed on. It can randomize displaying of product posts or a set
 * limit of products.
 *
 * @package     shcproducts
 * @category    Controller
 * @subpackage  Installer
 * @version     0.1
 * @author      Brian Greenacre
 */
class Controller_Widget_Products extends SHCP_Controller_Widget {

    /**
     * @access  protected
     * @var string
     */
    protected $content = 'widget/products/list';

    /**
     * Cache output on frontend or not.
     *
     * @access  protected
     * @var     bool
     */
    protected $cache = FALSE;

    /**
     * form - Display a settings form for the widget.
     *
     * @access  public
     * @param   array   Name value paired array of form field values.
     * @return  void
     */
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

    /**
     * widget - Display frontend of the widget. This will only show products
     * that were pulled in via the settings form keywords field. Optionally can
     * order the products output by random or none.
     *
     * @access  public
     * @param   array
     * @param   array
     * @return  void
     */
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

    /**
     * update - Update the setting for the widget. Based on the keywords entered
     * in the widget form the product API will search for products with that
     * keyword and store them as posts.
     *
     * @access  public
     * @param   array   New data from the form.
     * @param   array   Old data from the widget.
     * @return  void
     */
    public function update($new, $old)
    {
        $data = array_merge($old, $new);
        $data['title'] = strip_tags($data['title']);
        $data['pool'] = array();

        $search = Library_Sears_Api::factory('search')
            ->keyword(SHCP::get($data, 'keyword'));

        // If the widget is set to randomize product output then
        // the API will be queried for 20 times more then what they have
        // set as the limit. This is to allow actual random products
        // display instead of just reordering the 3 products limit.
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
                        'imageid'       => $search->imageurl,
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
