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
        add_action('add_meta_boxes', array(&$this, 'metabox'));
        add_action('save_post', array(&$this, 'action_save'));
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
            'products'  => &$products,
            'related'   => &$related,
        );


        echo SHCP::view('admin/related/list', $data);
    }

    public function action_save()
    {
    }

}
