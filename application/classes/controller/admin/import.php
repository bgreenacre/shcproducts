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
 * Product import controller.
 *
 * @package     shcproducts
 * @subpackage  Controller
 * @since       0.1
 * @author      Kyla Klein
 */
class Controller_Admin_Import {

    /**
     * __construct 
     * 
     * @param array $params 
     * @access public
     * @return void
     */
    public function __construct(array $params = NULL)
    {
        add_action('wp_ajax_action_save', array(&$this, 'action_save'));
        add_action('wp_ajax_action_save_all', array(&$this, 'action_save_all'));
        add_action('wp_ajax_action_list', array(&$this, 'action_list'));
        add_action('wp_ajax_action_categories', array(&$this, 'action_categories'));
        add_action('wp_ajax_action_subcategories', array(&$this, 'action_subcategories'));
        add_action('admin_menu', array(&$this, 'admin_init'));
    }

    /**
     * action_list - Displays a list of products to import via the API
     *
     * @access  public
     * @return  void
     */
    public function action_list()
    {
        $num_per_page       = 20;
        $page_range         = 3;
        $method             = isset($_POST['method'])             ? $_POST['method']            : 'keyword';
        $search_terms       = isset($_POST['search_terms'])       ? $_POST['search_terms']      : '';
        $vertical_terms     = isset($_POST['vertical_terms'])     ? $_POST['vertical_terms']    : '';
        $category_terms     = isset($_POST['category_terms'])     ? $_POST['category_terms']    : '';
        $subcategory_terms  = isset($_POST['subcategory_terms'])  ? $_POST['subcategory_terms'] : '';
        $current_page       = isset($_POST['page_number'])        ? $_POST['page_number']       : 1;
        $product_count      = isset($_POST['product_count'])      ? $_POST['product_count']     : 0;
        $next_page      = $current_page + 1;
        $previous_page  = $current_page - 1;
        $start_index    = ($current_page - 1) * $num_per_page + 1;
        $end_index      = (($start_index + $num_per_page) > $product_count) && ($product_count > 0) ? $product_count : $start_index + $num_per_page - 1;

        if($method == 'keyword')
        {
            $result = Library_Sears_Api::factory('search')
                ->$method($search_terms, $subcategory)
                ->limit($start_index, $end_index)
                ->load();
        }
        else
        {
            // remove product count from terms - e.g. for "Subcategory (1234)" removes the (1234) part
            $category_terms     = trim(substr($category_terms, 0, strpos($category_terms, '(')));
            $subcategory_terms  = trim(substr($subcategory_terms, 0, strpos($subcategory_terms, '(')));

            // somewhere single quotes are being escaped with a backslash.  We need to remove the backslash but not the single quote
            // for the API call to work correctly
            $category_terms     = str_replace('\\', '', $category_terms);
            $subcategory_terms  = str_replace('\\', '', $subcategory_terms);

            $result = Library_Sears_Api::factory('search')
                ->category(ucwords($vertical_terms), ucwords($category_terms), ucwords($subcategory_terms))
                ->limit($start_index, $end_index)
                ->load();
        }

        $product_count  = $result->mercadoresult->productcount;
        $num_pages      = ceil($product_count / $num_per_page);

        if($current_page > 1)
        {
            // first page link
            $pagination['first']['number'] = 1;
            $pagination['first']['message'] = '&laquo; First';

            // previous page link
            $pagination['previous']['number'] = $previous_page;
            $pagination['previous']['message'] = '&laquo; Previous';
        }

        // numbered page links
        for($i=($current_page-$page_range); $i<($current_page + $page_range); $i++)
        {
            if (($i > 0) && ($i <= $num_pages))
            {
                $pagination[$i]['number'] = $i;
                $pagination[$i]['message'] = $i;
            }
        }

        if($current_page < $num_pages) {
            // next page link
            $pagination['next']['number'] = $next_page;
            $pagination['next']['message'] = 'Next &raquo;';
            // last page link
            $pagination['last']['number'] = $num_pages;
            $pagination['last']['message'] = 'Last &raquo;';
        }

        $args = array(
            'current_page'  => $current_page,
            'product_count' => $product_count,
            'method'        => $method,
            'search_terms'  => $search_terms,
            'pagination'    => $pagination,
            'dropdown_args' => array( //settings for category dropdown
                'show_count'    => 1,
                'hide_empty'    => 0,
                'hierarchical'  => 1,
                'name'          => 'shcp_category',
                'id'            => 'shcp_category'
                )
        );

        $data = array_merge($args, array('result' => $result));

        $response = SHCP::view('admin/import/list', $data);

        // Send headers to not cache this result.
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

        // Send header for json mimetype and length of the response json string.
        header('Content-Type: text/html');
        header('Content-Length: '.strlen($response)+1);

        echo $response;
        exit;

        die(); // have to do this in WP otherwise a zero will be appended to all responses
    }


    /**
     * action_categories - Displays a list of categories related to the selected vertical
     *
     * @access  public
     * @return  void
     */
    public function action_categories() {
        $method         = isset($_POST['method'])         ? $_POST['method']        : 'keyword';
        $search_terms   = isset($_POST['search_terms'])   ? $_POST['search_terms']  : '';

        $result = Library_Sears_Api::factory('search')
            ->vertical(ucwords($search_terms))
            ->load();

        $args = array(
            'method'        => $method,
            'search_terms'  => $search_terms
        );

        $data = array_merge($args, array('result' => $result));

        echo SHCP::view('admin/import/categories', $data);

        die(); // have to do this in WP otherwise a zero will be appended to all responses
    }

    /**
     * action_subcategories - Displays a list of subcategories related to the selected category
     *
     * @access  public
     * @return  void
     */
    public function action_subcategories() {
        $method         = isset($_POST['method'])         ? $_POST['method']          : 'keyword';
        $vertical_terms = isset($_POST['vertical_terms']) ? $_POST['vertical_terms']  : '';
        $search_terms   = isset($_POST['search_terms'])   ? $_POST['search_terms']    : '';

        /**
        * remove product count from terms - e.g. for "Subcategory (1234)" removes the (1234) part
        */
        $search_terms = trim(substr($search_terms, 0, strpos($search_terms, '(')));

        /**
        * somewhere single quotes are being escaped with a backslash.  
        * We need to remove the backslash but not the single quote
        * for the API call to work correctly
        */
        $search_terms = str_replace('\\', '', $search_terms);    

        $result = Library_Sears_Api::factory('search')
            ->category(ucwords($vertical_terms), ucwords($search_terms))
            ->load();

        $args = array(
            'method'        => $method,
            'search_terms'  => $search_terms
        );

        $data = array_merge($args, array('result' => $result));

        echo SHCP::view('admin/import/subcategories', $data);

        die(); // have to do this in WP otherwise a zero will be appended to all responses
    }


    /**
     * action_save - Save all the selected products in the list
     *
     * @access  public
     * @return  void
     */

    public function action_save()
    {
        $product_count = count($_POST['import_single']);
        $shcp_category = $_POST['shcp_category'];

        $keys = array_keys($_POST);
        unset($keys[array_search('import_all', $keys)]);

        for($i=0; $i<$product_count; $i++)
        {
            $check = new Model_Products();
            $shcproduct = new Model_Products();
            $data = array();
            $categories = array();

            foreach($keys as $field_name)
            {
                if($field_name != 'shcp_category') {
                    $data[$field_name] = SHCP::get($_POST[$field_name], $i);
                }
            } 

            if ( ! $check->meta('partnumber', '=', $data['partnumber'])->loaded())
            {
                $data['detail'] = Library_Sears_Api::factory('product')
                    ->get($data['partnumber'])
                    ->param('showSpec', 'true')
                    ->load();

                $shcproduct->values($data);

                if ($shcproduct->check())
                {
                    $shcproduct->save();
                    
                    $categories[] = $shcp_category;

                    /**
                    * if there is a category that matches the brand name (slug) add that as a category for the product
                    */                    
                    $brand = get_category_by_slug(str_replace(' ', '-', strtolower($data['detail']->brandname)));
                    
                    if($brand) {
                        $categories[] = $brand->term_id;
                    }           

                    wp_set_post_categories($shcproduct->ID, $categories);                    
                }
                else
                { 
                }

                $errors[] = $shcproduct->errors();
            }
        }

        echo(json_encode(array('errors' => $errors)));
  
        die(); // have to do this in WP otherwise a zero will be appended to all responses          
    }

    /**
     * action_save_all - Save all products in the search result set (clicking the "import all [x] products" button)
     *
     * @access  public
     * @return  void
     */    
    public function action_save_all()
    {

        foreach($_POST as $key => $value) {
            $data[$key] = $value;
        }

        $data['product_count'] = $data['product_count'] > 1000 ? 1000 : $data['product_count']; // api maxes out at 1000 items

        if($data['method'] == 'keyword')
        {
            $result = Library_Sears_Api::factory('search')
                ->$method($data['keyword_terms'])
                ->limit(0, $data['product_count'])
                ->load();
        }
        else
        {
            /**
            * remove product count from terms - e.g. for "Subcategory (1234)" removes the (1234) part
            */
            $data['category_terms']     = trim(substr($data['category_terms'], 0, strpos($data['category_terms'], '(')));
            $data['subcategory_terms']  = trim(substr($data['subcategory_terms'], 0, strpos($data['subcategory_terms'], '(')));

            /**
            * somewhere single quotes are being escaped with a backslash.  
            * We need to remove the backslash but not the single quote
            * for the API call to work correctly
            */
            $data['category_terms']     = str_replace('\\', '', $data['category_terms']); 
            $data['subcategory_terms']  = str_replace('\\', '', $data['subcategory_terms']);

            $result = Library_Sears_Api::factory('search')
                ->category(ucwords($data['vertical_terms']), ucwords($data['category_terms']), ucwords($data['subcategory_terms']))
                ->limit(0, $data['product_count'])
                ->load();
        }    

        foreach($result as $product)
        {
            $check = new Model_Products();
            $shcproduct = new Model_Products();
            $product_data = array();
            $categories = array();

            $product_data['post_title']     = $product->name;
            $product_data['catentryid']     = $product->catentryid;
            $product_data['cutprice']       = $product->cutprice;
            $product_data['displayprice']   = $product->displayprice;
            $product_data['imageid']        = $product->imageid;
            $product_data['import_single']  = $product->import_single;
            $product_data['numreview']      = $product->numreview;
            $product_data['partnumber']     = $product->partnumber;
            $product_data['rating']         = $product->rating;

            if ( ! $check->meta('partnumber', '=', $product_data['partnumber'])->loaded())
            { 
                $product_data['detail'] = Library_Sears_Api::factory('product')
                    ->get($product_data['partnumber'])
                    ->param('showSpec', 'true')
                    ->load(); 

                $shcproduct->values($product_data);

                if ($shcproduct->check())
                {
                    $shcproduct->save();
                    
                    $categories[] = $data['assigned_category'];

                    /**
                    * if there is a category that matches the brand name (slug) add that as a category for the product
                    */
                    $brand = get_category_by_slug(str_replace(' ', '-', strtolower($product_data['detail']->brandname)));
                    
                    if($brand) {
                        $categories[] = $brand->term_id;
                    }           

                    wp_set_post_categories($shcproduct->ID, $categories);
                }
                else
                {
                }

                $errors[] = $shcproduct->errors();
            }
        }

        echo(json_encode(array('errors' => $errors)));
  
        die(); // have to do this in WP otherwise a zero will be appended to all responses
    }  

    /**
     * action_index - Call first view file (with nothing loaded)
     *
     * @access  public
     * @return  void
     */
    public function action_index()
    {
        echo SHCP::view('admin/import/index');
    }

    /**
     * admin_init - Init page
     *
     * @access  public
     * @return  void
     */
    public function admin_init()
    {
        add_submenu_page( 'edit.php?post_type=shcproduct', __('Import Products'), __('Import Products'), 'edit_posts', 'import', array(&$this, 'action_index'));
    }
}
