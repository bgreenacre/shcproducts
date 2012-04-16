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
 * @package     shcproducts
 * @subpackage  Controller
 * @since       0.1
 * @author      Brian Greenacre
 */
class Controller_Admin_Related {

    /**
     * __construct - Setup the actions used by this controller.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('init', array(&$this, 'init'));

        if ( ! in_array('related', (array) SHCP::get_option('widgets')))
            return;

        add_action('wp_ajax_action_filter_list', array(&$this, 'action_filter_list'));
        add_action('wp_ajax_action_page_list', array(&$this, 'action_filter_list'));
        add_action('add_meta_boxes', array(&$this, 'metabox'));
        add_action('save_post', array(&$this, 'action_save'));
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
            wp_enqueue_script('shcproducts-related-scripts', SHCP_JS.'/related.js', array(
                'jquery',
                'jquery-ui-draggable',
                'jquery-ui-droppable',
                'jquery-ui-sortable',
                )
            );
        }
    }

    /**
     * metabox - Adds the large metabox to the post edit form.
     *
     * Edited by Eddie Moya to include the metabox in pages and products.
     * 
     * @return void
     */
    public function metabox()
    {
        add_meta_box('shcproducts_related', __(SHCP::lang('related', 'section.title'), 'shcproducts_related'), array(&$this, 'action_list'), 'post' );
        add_meta_box('shcproducts_related', __(SHCP::lang('related', 'section.title'), 'shcproducts_related'), array(&$this, 'action_list'), 'page' );
        add_meta_box('shcproducts_related', __(SHCP::lang('related', 'section.title'), 'shcproducts_related'), array(&$this, 'action_list'), 'shcproduct' );
    }

    /**
     * action_list - Display available products and current related products.
     *
     * @param object $post = NULL
     * @return void
     */
    public function action_list($post = NULL)
    {
        $page_format = '?action=action_filter_list&paged=%#%';

        if ($s = SHCP::get($_GET, 's', SHCP::get($_POST, 's', '')))
        {
            $page_format .= '&s='.$s;
        }

        $products = new Model_Products();
        $products->limit(20);
        $related = new Model_Products();

        $related->related($post->ID);

        $data = array(
            'products'  => $products,
            'related'   => $related,
            'pager'     => array(
                'base'      => admin_url('admin-ajax.php%_%'),
                'format'    => $page_format,
                'total'     => $products->total_pages(),
                'current'   => $products->current_page(),
                'show_all'  => TRUE,
            ),
        );

        echo SHCP::view('admin/related/list', $data);
    }

    /**
     * action_filter_list - This is an ajax action to return product posts based
     * on search terms.
     *
     * @return void
     */
    public function action_filter_list()
    {
        $page_format = '?action=action_filter_list&paged=%#%';

        if ($s = SHCP::get($_GET, 's', SHCP::get($_POST, 's', '')))
        {
            $page_format .= '&s='.$s;
        }

        $products = new Model_Products();

        // Add in the search query to filter results.
        $products->param('s', SHCP::get($_GET, 's', SHCP::get($_POST, 's', '')));

        if ($page = (int) SHCP::get($_GET, 'paged', SHCP::get($_POST, 'paged', 0)))
        {
            $products->param('paged', $page);
        }

        $data = array(
            'products' => $products,
            'pager'     => array(
                'base'      => admin_url('admin-ajax.php%_%'),
                'format'    => $page_format,
                'total'     => $products->total_pages(),
                'current'   => $products->current_page(),
                'show_all'  => TRUE,
            ),
        );

        echo SHCP::view('admin/related/grid', $data);
        exit;
    }

    /**
     * Saves related products on page and shcproduct editors, while properly handling
     * autosave which would otherwise undo changes periodically.
     * 
     * Do not call this function directly, add it to the save_post hook.
     * 
     * @author Eddie Moya
     * 
     * @param int $id Required. ID of the post (content object) being edited.
     * @param object $post
     * 
     * @return void 
     */
    function action_save($id, $post) {

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        if (!wp_verify_nonce($_POST['shcproducts_noncename'], 'shcproducts_related'))
            return;

        if ('shcproduct' == $post->post_type || 'page' == $post->post_type || 'post' == $post->post_type) 
            return;

        $related = $_POST['shcp_related_products'];
        update_post_meta($id, 'shcp_related_products', $related);
    }
}
