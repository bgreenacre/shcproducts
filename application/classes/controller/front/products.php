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
 * Sears and Kmart product plugin.
 * Product display controller. This provides generic templates to be used
 * in theme template files.
 *
 *  // Example of template usage
 *  if (defined('SHCP_PATH'))
 *  {
 *      SHCP_Controller::factory('front_products')->action_grid();
 *  }
 *
 * @package     shcproducts
 * @subpackage  Controller
 * @since       0.1
 * @author      Brian Greenacre
 */
class Controller_Front_Products {

    /**
     * __construct 
     * 
     * @access public
     * @return void
     */
    public function __construct()
    {
        add_shortcode('shcp_products', array(&$this, 'action_grid'));
        add_shortcode('shcp_product', array(&$this, 'action_detail'));
        add_shortcode('shcp_quickview', array(&$this, 'action_quickview'));
        add_action('wp_ajax_product_action_grid', array(&$this, 'action_grid'));
        add_action('wp_ajax_nopriv_product_action_grid', array(&$this, 'action_grid'));
        add_action('wp_ajax_product_action_quickview', array(&$this, 'action_quickview'));
        add_action('wp_ajax_nopriv_product_action_quickview', array(&$this, 'action_quickview'));
        add_action('wp_ajax_product_action_cartconfirm', array(&$this, 'action_cartconfirm'));
        add_action('wp_ajax_nopriv_product_action_cartconfirm', array(&$this, 'action_cartconfirm'));
        add_filter('body_class', array(&$this, 'filter_body_class'));
        add_action('template_redirect', array($this, 'template_redirect'));
    }

    /**
     * template_redirect - Used to allowed the category to be within the URI
     * of the request URL. Includes the template archive-shcproduct.php from
     * the current theme folder.
     *
     * @access  public
     * @return  void
     */
    public function template_redirect()
    {
        global $wp_query, $paged;

        $uri = $_SERVER['REQUEST_URI'];
        $uri = trim($uri, '/');

        if (preg_match('/^products(\/(category|tag)\/([^\/]+))?(\/page\/([0-9]+))?/', $uri, $matches) != FALSE)
        {
            $path = get_theme_root() . '/kmart-gamer/';

            $wp_query->query_vars['error'] = FALSE;

            if (SHCP::get($matches, 2) == 'tag')
            {
                $wp_query->query_vars['tag'] = SHCP::get($matches, 3);
            }
            else
            {
                $wp_query->query_vars['category_name'] = SHCP::get($matches, 3);
            }

            $wp_query->query_vars['post_type'] = 'shcproduct';
            $wp_query->query_vars['paged'] = $paged = (int) SHCP::get($matches, 5, 1);
            $wp_query->is_archive = TRUE;
            $wp_query->is_404 = FALSE;
            $wp_query->is_post_type_archive = TRUE;
            $wp_query->query = array(
                'post_type' => 'shcproduct',
                'paged'     => $wp_query->query_vars['paged'],
            );

            if ($wp_query->query_vars['category_name'])
            {
                $wp_query->query['category_name'] = $wp_query->query_vars['category_name'];
            }

            if ($wp_query->query_vars['tag'])
            {
                $wp_query->query['tag'] = $wp_query->query_vars['tag'];
            }

            if ($_GET)
            {
                $wp_query->query_vars = array_merge($wp_query->query_vars, $_GET);
                $wp_query->query = array_merge($wp_query->query, $_GET);
            }

            include $path.'/archive-shcproduct.php';
            exit;
        }

        return;
    }

    /**
     * filter_body_class 
     * 
     * @param mixed $classes 
     * @access public
     * @return void
     */
    public function filter_body_class($classes)
    {
        if (is_post_type_archive('shcproduct'))
        {
            $classes[] = 'page-template';
            $classes[] = ' page-template-template-page-fullwidth-php';
        }

        return $classes;
    }

    /**
     * action_grid 
     * 
     * @param mixed $attrs 
     * @access public
     * @return void
     */
    public function action_grid($attrs = NULL)
    {
        global $wp_query;

        $this->products = new Model_Products();
        $this->products->use_query_posts(TRUE)->merge_wp_query(TRUE);

        $this->parse_attrs($attrs);

        $data = array(
            'products'      => $this->products->load(),
        );

        $categories = get_categories(array('child_of' => 0, 'hide_empty' => FALSE));

        echo SHCP::view('front/product/grid_filter', array(
            'categories'    => $categories,
            'selected'      => get_query_var('category_name'),
        ));

        echo SHCP::view('front/product/grid', $data);

        if (SHCP::$is_ajax)
        {
            echo paginate_links(array(
                'total' => count($products)
            ));
            exit;
        }
    }

    /**
     * action_detail 
     * 
     * @param mixed $attrs 
     * @access public
     * @return void
     */
    public function action_detail($attrs = NULL)
    {
        $attrs = (array) $attrs;
        $attrs['name'] = get_query_var('shcproduct');
        $this->products = new Model_Products();

        $this->parse_attrs($attrs);

        $data = array(
            'product'  => $this->products
        );

        echo SHCP::view('front/product/detail', $data);
    }

    /**
     * action_quickview 
     * 
     * @param mixed $attrs 
     * @access public
     * @return void
     */
    public function action_quickview($attrs = NULL)
    {
        global $wp_query;
        $this->products = new Model_Products();

        $attrs = (array) $attrs;
        $attrs['p'] = SHCP::get($_POST, 'p');
        $this->parse_attrs($attrs);

        $data = array(
            'product'  => $this->products
        );

        echo SHCP::view('front/product/quickview', $data);

        if (SHCP::$is_ajax)
        {
            die;
        }
    }

    /**
     * action_cartconfirm 
     * 
     * @param mixed $attrs 
     * @access public
     * @return void
     */
    public function action_cartconfirm($attrs = NULL)
    {
        global $wp_query;
        $this->products = new Model_Products();

        $attrs = (array) $attrs;
        $attrs['p'] = SHCP::get($_POST, 'p');
        $this->parse_attrs($attrs);

        $data = array(
            'product'  => $this->products
        );

        echo SHCP::view('front/product/cartconfirm', $data);

        if (SHCP::$is_ajax)
        {
            die;
        }
    }

    /**
     * parse_attrs 
     * 
     * @param mixed $attrs 
     * @access public
     * @return void
     */
    public function parse_attrs($attrs = NULL)
    {
        if ( ! is_array($attrs))
        {
            $attrs = (array) $attrs;
        }

        if (isset($attrs[0]))
        {
            unset($attrs[0]);
        }

        $attrs = array_merge_recursive(array(
            'posts_per_page'   => get_option('posts_per_page', 10),
        ), (array) $attrs);

        $this->products->param($attrs);
    }
}
