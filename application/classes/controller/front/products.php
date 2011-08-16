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
 * @package		shcproducts
 * @subpackage	Controller
 * @since		0.1
 * @author		Brian Greenacre
 */
class Controller_Front_Products {

    public function __construct()
    {
        add_shortcode('shcp_products', array(&$this, 'action_grid'));
        add_shortcode('shcp_product', array(&$this, 'action_detail'));
        add_shortcode('shcp_quickview', array(&$this, 'action_quickview'));
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
            $path = str_replace(get_bloginfo('siteurl').'/wp-content/themes', get_theme_root(), get_stylesheet_directory_uri());
            
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
    
    public function filter_body_class($classes)
    {
        if (is_post_type_archive('shcproduct'))
        {
            $classes[] = 'page-template';
            $classes[] = ' page-template-template-page-fullwidth-php';
        }
        
        return $classes;
    }
    
    public function action_grid($attrs = NULL)
    {
        global $wp_query;
        
        $this->products = new Model_Products();
        $this->products->use_query_posts(TRUE)->merge_wp_query(TRUE);
        
        $this->parse_attrs($attrs);

        $data = array(
            'products'  => $this->products
        );

        echo SHCP::view('front/product/grid', $data);
    }

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

    public function parse_attrs($attrs = NULL)
    {
        if (isset($attrs[0]))
            unset($attrs[0]);
        
        $attrs = array_merge_recursive(array(
            'posts_per_page'   => get_option('posts_per_page', 10),
        ), (array) $attrs);

        $this->products->param($attrs);
    }

}
