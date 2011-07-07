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
 * Product import controller.
 *
 * @package		shcproducts
 * @subpackage	Controller
 * @version		0.1
 * @author		Brian Greenacre
 */

class Controller_Admin_Related {

    /**
     * __construct - Setup the actions used by this controller.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('wp_ajax_action_filter_list', array(&$this, 'action_filter_list'));
        add_action('add_meta_boxes', array(&$this, 'metabox'));
        add_action('save_post', array(&$this, 'action_save'));
        add_action('init', array(&$this, 'init'));
    }

    /**
     * init - Enqueue js files to the head.
     *
     * @return void
     */
    public function init()
    {
        if (is_admin())
        {
            wp_enqueue_script('jquery-ui-draggable');
            wp_enqueue_script('jquery-ui-droppable');
            wp_enqueue_script('jquery-ui-sortable');
        }
    }

    /**
     * metabox - Adds the large metabox to the post edit form.
     *
     * @return void
     */
    public function metabox()
    {
        add_meta_box(
            'shcproducts_related',
            __(SHCP::lang('related', 'section.title'), 'shcproducts_related'),
            array(&$this, 'action_list'),
            'post'
        );
    }

    /**
     * action_list - Display available products and current related products.
     *
     * @param object $post = NULL
     * @return void
     */
    public function action_list($post = NULL)
    {
        $products = new Model_Products();
        $related = new Model_Products();

        $related->related($post->ID);

        $data = array(
            'products'  => $products,
            'related'   => $related,
        );

        echo SHCP::view('admin/related/list', $data);
    }

    public function action_filter_list()
    {
        $products = new Model_Products();

        $products->param('s', SHCP::get($_GET, 's', SHCP::get($_POST, 's', '')));

        $data = array('products' => $products);

        echo SHCP::view('admin/related/grid', $data);
        exit;
    }

    /**
     * action_save - Save products from Sears API into posts table.
     *
     * @return void
     */
    public function action_save($post_id = NULL)
    {
        $response = SHCP::config('json', 'response');

        if ($products = (array) SHCP::get($_POST, 'shcp_related_products'))
        {
            $related = array();

            foreach ($products as $product)
            {
                $product = new Model_Products($product);

                if ($product->loaded())
                {
                    $related[] = $product->ID;
                }
            }

            if ($related)
            {
                update_post_meta($post_id, 'shcp_related_products', $related);
            }
        }
        else
        {
            $respones['success'] = FALSE;
            $response['messages']['notices'][] = __('No products were set to import.');
        }

        /*
        if (SHCP::$is_ajax)
        {
            $response = json_encode($response);

            // Send headers to not cache this result.
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

            // Send header for json mimetpye and length of the response json string.
            header('Content-Type: text/plain');
            header('Content-Length: '.strlen($response)+1);

            echo $response;
            exit;
        }
        */
    }

}
