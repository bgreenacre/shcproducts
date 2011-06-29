<?php defined('SHCP_PATH') OR die('No direct script access.');
/**
 * Sears and Kmart product plugin.
 * Product import controller.
 *
 * @package		shcproducts
 * @subpackage	Controller
 * @version		0.1
 * @author		Brian Greenacre
 */

class Controller_Admin_Related {

    public function __construct()
    {
        add_action('wp_ajax_action_save_related', array(&$this, 'action_save'));
        add_action('add_meta_boxes', array(&$this, 'metabox'));
        add_action('save_post', array(&$this, 'action_save'));
        add_action('init', array(&$this, 'init'));
    }

    public function init()
    {
        if (is_admin())
        {
            wp_enqueue_script('jquery-ui-draggable');
            wp_enqueue_script('jquery-ui-droppable');
        }
    }

    public function metabox()
    {
        add_meta_box(
            'shcproducts_related',
            __(SHCP::lang('related', 'section.title'), 'shcproducts_related'),
            array(&$this, 'action_list'),
            'post'
        );
    }

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

    /**
     * action_save - Save products from Sears API into posts table.
     *
     * @return void
     */
    public function action_save()
    {
        $response = SHCP::config('json', 'response');

        if ($products = (array) SHCP::get($_POST, 'products'))
        {
            $key = 0;
            $count = count($products);
            $error = FALSE;

            while ($error === FALSE AND $key < $count)
            {
                $data['partNumber'] = $product;
                $data['ID'] = SHCP::get($_POST['product_id'], $key);
                $data['post_title'] = SHCP::get($_POST['product_title'], $key);

                $search = Library_Sears_Api::factory('search')
                    ->keyword($product)
                    ->load();

                if (count($search) > 0)
                {
                    $data = array_merge($data, $search->current());
                    $data['product_details'] = Library_Sears_Api::factory('product', NULL, $search->current())
                        ->get()
                        ->load()
                        ->current();
                }

                $product = new Model_Product($product);
                $product->values($data);

                if ($product->check())
                {
                    $product->save();
                    $response['success'] = TRUE;
                    $response['messages']['information'][] = __('Successfully imported products.');
                }
                else
                {
                    $error = TRUE;
                    $response['success'] = FALSE;
                    $response['messages']['errors'] = $product->errors();
                }
            }
        }
        else
        {
            $respones['success'] = FALSE;
            $response['messages']['notices'][] = __('No products were set to import.');
        }

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
    }

}
