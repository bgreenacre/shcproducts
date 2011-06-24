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

class Controller_Import {
	public function __construct(array $params = NULL)
	{
		add_action('wp_ajax_add_product_content', array(&$this, 'add_product_content'));
		add_action('wp_ajax_show_import_product_list', array(&$this, 'show_import_product_list'));
	}

	/**
	 * add_product_content - Saves product data to the database as custom post type.
	 *
	 * @access	public
	 * @return	void
	 */	
  public function add_product_content()
  {
    
  }
  
	/**
	 * show_import_product_list - Displays a list of products to import via the API
	 *
	 * @access	public
	 * @return	void
	 */	
  public function show_import_product_list()
  {
    
  }

}
