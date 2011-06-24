<?php defined('SHCP_PATH') OR die('No direct script access.');
/**
 * HSC Products plugin.
 * Options controller.
 *
 * @package		shcproducts
 * @subpackage	Controller
 * @version		0.1
 * @author		Kyla Klein
 */

class Controller_Options {
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
		add_action('admin_menu', array(&$this, 'add_menu'));
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
		add_settings_section('product_section', SHCP::lang('plugin', 'form.product.title'), array(&$this, 'product_section'), __CLASS__);
		add_settings_section('cart_section', SHCP::lang('plugin', 'form.cart.title'), array(&$this, 'cart_section') , __CLASS__);
		add_settings_field($this->api_key_field_name, '', array(&$this, 'api_key_field'), __CLASS__, 'product_section');
		add_settings_field($this->store_field_name, '', array(&$this, 'store_field'), __CLASS__, 'product_section');
		add_settings_field($this->app_id_field_name, '', array(&$this, 'app_id_field'), __CLASS__, 'cart_section');
		add_settings_field($this->auth_id_field_name, '', array(&$this, 'auth_id_field'), __CLASS__, 'cart_section');
	}

	/**
	 * add_menu - Add a menu item for the admin setting form.
	 *
	 * @access	public
	 * @return	void
	 */
	public function add_menu()
	{
		add_options_page(SHCP::lang('plugin', 'menu.name'), SHCP::lang('plugin', 'menu.name'), 'manage_options', SHCP::prefix('options'), array(&$this, 'option_page'));
	}
	
	/**
	 * option_page - Display the option settings page.
	 *
	 * @access	public
	 * @return	void
	 */
	public function option_page()
	{
		$data = array(
			'classname'	=> __CLASS__,
			'lang'		=> SHCP::lang('plugin', 'options')
			);
		
		echo SHCP::view('option_page', $data);
	}

	/**
	 * product_section - Display the top of the product api section of the form.
	 *
	 * @access	public
	 * @return	void
	 */
	public function product_section()
	{
		echo SHCP::view('product_section');
	}

	/**
	 * cart_section - Display the top of the cart api section of the form.
	 *
	 * @access	public
	 * @return	void
	 */
	public function cart_section()
	{
		echo SHCP::view('cart_section');
	}
	
	/**
	 * api_key_field - Display the business field selector.
	 *
	 * @access	public
	 * @return	void
	 */
	public function api_key_field()
	{ 
		$data = array(
			'id'		=> $this->api_key_field_name,
			'name'	=> SHCP::prefix('options['.$this->api_key_field_name.']'),
			'value'	=> SHCP::get_option($this->api_key_field_name, SHCP::config('plugin.options.api_key.default')),
			'lang'	=> SHCP::lang('plugin', 'options.'.$this->api_key_field_name)
			);
		
		echo SHCP::view('fields/api_key', $data);
	}

	/**
	 * store_field - Display the store radio button field.
	 *
	 * @access	public
	 * @return	void
	 */
	public function store_field()
	{
		$data = array(
			'id'	  => $this->store_field_name,
			'name'	=> SHCP::prefix('options['.$this->store_field_name.']'),
			'value'	=> SHCP::get_option($this->store_field_name, SHCP::config('plugin.options.store.default')),
			'lang'	=> SHCP::lang('plugin', 'options.'.$this->store_field_name)
			);

		echo SHCP::view('fields/store', $data);
	}

	/**
	 * app_id_field - Display the app_id text field.
	 *
	 * @access	public
	 * @return	void
	 */
	public function app_id_field()
	{
		$data = array(
			'id'	  => $this->app_id_field_name,
			'name'	=> SHCP::prefix('options['.$this->app_id_field_name.']'),
			'value'	=> SHCP::get_option($this->app_id_field_name, SHCP::config('plugin.options.app_id.default')),
			'lang'	=> SHCP::lang('plugin', 'options.'.$this->app_id_field_name)
			);

		echo SHCP::view('fields/app_id', $data);
	}

	/**
	 * auth_id_field - Display the auth_id text field.
	 *
	 * @access	public
	 * @return	void
	 */
	public function auth_id_field()
	{
		$data = array(
			'id'	  => $this->auth_id_field_name,
			'name'	=> SHCP::prefix('options['.$this->auth_id_field_name.']'),
			'value'	=> SHCP::get_option($this->auth_id_field_name, SHCP::config('plugin.options.auth_id.default')),
			'lang'	=> SHCP::lang('plugin', 'options.'.$this->auth_id_field_name)
			);

		echo SHCP::view('fields/auth_id', $data);
	}

}