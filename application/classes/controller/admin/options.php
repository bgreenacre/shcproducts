<?php defined('SHCP_PATH') OR die('No direct script access.');
/**
 * shcproducts
 *
 * @author Brian Greenacre and Kyla Klein
 * @email bgreenacre42@gmail.com
 * @version $Id$
 * @since Wed 15 Jun 2011 07:32:09 PM
 */

// -----------------------------------------------------------------------------

/**
 * Options controller.
 *
 * @package		shcproducts
 * @subpackage	Controller
 * @version		0.1
 * @author		Kyla Klein
 */

class Controller_Admin_Options {

	/**
	 * The field name to use for api key. It's used in more then one area.
	 *
	 * @access	public
	 * @var		string
	 */
	public $api_key_field_name;

	/**
	 * Name of the store field.
	 *
	 * @access	public
	 * @var		string
	 */
	public $store_field_name;

	/**
	 * Name of the app ID field.
	 *
	 * @access	public
	 * @var		string
	 */
	public $app_id_field_name;

	/**
	 * Name of the auth ID field.
	 *
	 * @access	public
	 * @var		string
	 */
	public $auth_id_field_name;

	/**
	 * Initialize the class. Add menu and admin wordpress init.
	 *
	 * @access	public
	 * @param	array	Array of possible class properties.
	 * @return	void
	 */
	public function __construct(array $params = NULL)
	{
		$this->api_key_field_name = SHCP::config('plugin.options.api_key.name');
		$this->store_field_name = SHCP::config('plugin.options.store.name');
		$this->app_id_field_name = SHCP::config('plugin.options.app_id.name');
		$this->auth_id_field_name = SHCP::config('plugin.options.auth_id.name');
		add_action('admin_menu', array(&$this, 'menu'));
		add_action('admin_init', array(&$this, 'init'));
	}

	/**
	 * init - Register the settings form and every section and field.
	 *
	 * @access	public
	 * @return	void
	 */
	public function init()
	{
		register_setting(SHCP::prefix('options'), SHCP::prefix('options'));

		// Display the options.
		add_settings_section('action_product_section', SHCP::lang('plugin', 'form.product.title'), array(&$this, 'action_product_section'), __CLASS__);
		add_settings_section('action_cart_section', SHCP::lang('plugin', 'form.cart.title'), array(&$this, 'action_cart_section') , __CLASS__);
		add_settings_field($this->api_key_field_name, '', array(&$this, 'action_api_key_field'), __CLASS__, 'action_product_section');
		add_settings_field($this->store_field_name, '', array(&$this, 'action_store_field'), __CLASS__, 'action_product_section');
		add_settings_field($this->app_id_field_name, '', array(&$this, 'action_app_id_field'), __CLASS__, 'action_cart_section');
		add_settings_field($this->auth_id_field_name, '', array(&$this, 'action_auth_id_field'), __CLASS__, 'action_cart_section');
	}

	/**
	 * add_menu - Add a menu item for the admin setting form.
	 *
	 * @access	public
	 * @return	void
	 */
	public function menu()
	{
	    global $submenu;
		add_options_page(SHCP::lang('plugin', 'menu.name'), SHCP::lang('plugin', 'menu.name'), 'manage_options', SHCP::prefix('options'), array(&$this, 'action_option_page'));

		unset($submenu['edit.php?post_type=shcproduct'][10]);
	}

	/**
	 * action_option_page - Display the option settings page.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_option_page()
	{
		$data = array(
			'classname'	=> __CLASS__,
			'lang'		=> SHCP::lang('plugin', 'options')
			);

		echo SHCP::view('admin/options/page', $data);
	}

	/**
	 * action_product_section - Display the top of the product api section of the form.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_product_section()
	{
		echo SHCP::view('admin/options/product');
	}

	/**
	 * action_cart_section - Display the top of the cart api section of the form.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_cart_section()
	{
		echo SHCP::view('admin/options/cart');
	}

	/**
	 * action_api_key_field - Display the api_key text field.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_api_key_field()
	{
		$data = array(
			'id'		=> $this->api_key_field_name,
			'name'	=> SHCP::prefix('options['.$this->api_key_field_name.']'),
			'value'	=> SHCP::get_option($this->api_key_field_name, SHCP::config('plugin.options.api_key.default')),
			'lang'	=> SHCP::lang('plugin', 'options.'.$this->api_key_field_name)
			);

		echo SHCP::view('admin/options/fields/api_key', $data);
	}

	/**
	 * action_store_field - Display the store radio button field.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_store_field()
	{
		$data = array(
			'id'	  => $this->store_field_name,
			'name'	=> SHCP::prefix('options['.$this->store_field_name.']'),
			'value'	=> SHCP::get_option($this->store_field_name, SHCP::config('plugin.options.store.default')),
			'lang'	=> SHCP::lang('plugin', 'options.'.$this->store_field_name)
			);

		echo SHCP::view('admin/options/fields/store', $data);
	}

	/**
	 * action_app_id_field - Display the app_id text field.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_app_id_field()
	{
		$data = array(
			'id'	  => $this->app_id_field_name,
			'name'	=> SHCP::prefix('options['.$this->app_id_field_name.']'),
			'value'	=> SHCP::get_option($this->app_id_field_name, SHCP::config('plugin.options.app_id.default')),
			'lang'	=> SHCP::lang('plugin', 'options.'.$this->app_id_field_name)
			);

		echo SHCP::view('admin/options/fields/app_id', $data);
	}

	/**
	 * action_auth_id_field - Display the auth_id text field.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_auth_id_field()
	{
		$data = array(
			'id'	  => $this->auth_id_field_name,
			'name'	=> SHCP::prefix('options['.$this->auth_id_field_name.']'),
			'value'	=> SHCP::get_option($this->auth_id_field_name, SHCP::config('plugin.options.auth_id.default')),
			'lang'	=> SHCP::lang('plugin', 'options.'.$this->auth_id_field_name)
			);

		echo SHCP::view('admin/options/fields/auth_id', $data);
	}

}
