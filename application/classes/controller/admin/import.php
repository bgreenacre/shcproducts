<?php defined('SHCP_PATH') OR die('No direct script access.');
/**
 * Sears and Kmart product plugin.
 * Product import controller.
 *
 * @package		shcproducts
 * @subpackage	Controller
 * @version		0.1
 * @author		Kyla Klein
 */

class Controller_Admin_Import {
	public function __construct(array $params = NULL)
	{
		add_action('wp_ajax_action_save', array(&$this, 'action_save'));
		add_action('wp_ajax_action_list', array(&$this, 'action_list'));
		add_action('admin_menu', array(&$this, 'admin_init'));
	}

	/**
	 * action_list - Displays a list of products to import via the API
	 *
	 * @access	public
	 * @return	void
	 */
  public function action_list()
  {
		$num_per_page         = 20;	
		$page_range           = 3;		
		$method               = isset($_POST['method'])         ? $_POST['method']        : 'keyword';
		$search_terms         = isset($_POST['search_terms'])   ? $_POST['search_terms']  : '';
		$subcategory          = isset($_POST['subcategory'])    ? $_POST['subcategory']   : NULL;
		$current_page         = isset($_POST['page_number'])    ? $_POST['page_number']   : 1;
		$product_count        = isset($_POST['product_count'])  ? $_POST['product_count'] : 0;
		$selected_category_id = isset($_POST['category'])       ? $_POST['category']      : 0;
	
    // $selected_category      = get_term($selected_category_id, 'product_category');
    // $selected_category_name = isset($selected_category->name) ? $selected_category->name : '';
	
		$start_index = ($current_page - 1) * $num_per_page + 1;
		$end_index = ($start_index + $num_per_page > $product_count) ? $product_count : $start_index + $num_per_page;
	    
    $result = Library_Sears_Api::factory('search')
      ->$method($search_terms, $subcategory)
      ->limit(0, 5)
      ->load(); 

    // echo "RESULT COUNT: " . $result->count();
    // echo "<pre>";
    // print_r($result->current());
    // echo "</pre>";

	  $args = array(
			'num_per_page'  => $num_per_page,
			'page_range'	  => $page_range,
			'current_page'	=> $current_page,
			'product_count'	=> $result->productcount,
			'method'	      => $method,
			'search_terms'	=> $search_terms,
			'subcategory'	  => $subcategory,
			'start_index'   => $start_index,
			'end_index'     => $end_index
			);
	  
	  $data = array_merge($args, array('result' => $result));
	    
    echo SHCP::view('admin/import/list', $data);
  
    
    die(); // have to do this in WP otherwise a zero will be appended to all responses
  }

    public function action_save()
    { 
      foreach($_POST['products'] as $product) {

        error_log("IMPORT action_save: SAVING..." . $product['post_title']);
        
        $shcproduct = new Model_Products();
        $shcproduct->values($product);
      
        if ($shcproduct->check())
        {
            $shcproduct->save();
        }
      }
    }
    
  public function action_index()
  {
    echo SHCP::view('admin/import/index');
  }
  
  public function admin_init()
  {
    add_submenu_page( 'edit.php?post_type=shcproduct', __('Import Products'), __('Import Products'), 'edit_posts', 'import', array(&$this, 'action_index'));
  }

}
