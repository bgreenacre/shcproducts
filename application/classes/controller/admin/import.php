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
	}

	/**
	 * show_import_product_list - Displays a list of products to import via the API
	 *
	 * @access	public
	 * @return	void
	 */
  public function action_list()
  {

  }

    public function action_index()
    {
        echo View::factory('admin/import/index');
    }

    public function action_save()
    {
        $product = new Model_Product();
        $product->values($_POST);

        if ($product->check())
        {
            $product->save();
        }
    }

}
