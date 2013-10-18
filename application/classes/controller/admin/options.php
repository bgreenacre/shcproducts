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
 * Options controller.
 *
 * @package     shcproducts
 * @subpackage  Controller
 * @since       0.1
 * @author      Kyla Klein
 */
class Controller_Admin_Options {

    /**
     * The field name to use for api key. It's used in more then one area.
     *
     * @access  public
     * @var     string
     */
    public $api_key_field_name;

    /**
     * Name of the store field.
     *
     * @access  public
     * @var     string
     */
    public $store_field_name;

    /**
     * Name of the app ID field.
     *
     * @access  public
     * @var     string
     */
    public $app_id_field_name;

    /**
     * Name of the auth ID field.
     *
     * @access  public
     * @var     string
     */
    public $auth_id_field_name;

    /**
     * Name of the widgets checkbox array field.
     *
     * @access  public
     * @var     string
     */
    public $widgets_field_name;

    public $cart_field_name;
    
    public $force_update_field_name;

    /**
     * Initialize the class. Add menu and admin wordpress init.
     *
     * @access  public
     * @param   array   Array of possible class properties.
     * @return  void
     */
    public function __construct(array $params = NULL)
    {
        $this->api_key_field_name = SHCP::config('plugin.options.api_key.name');
        $this->store_field_name = SHCP::config('plugin.options.store.name');
        $this->app_id_field_name = SHCP::config('plugin.options.app_id.name');
        $this->auth_id_field_name = SHCP::config('plugin.options.auth_id.name');
        $this->widgets_field_name = SHCP::config('plugin.options.widgets.name');
        $this->cart_field_name = SHCP::config('plugin.options.cart.name');
        $this->force_update_field_name = SHCP::config('plugin.options.force_update.name');
        $this->force_update_override_field_name = SHCP::config('plugin.options.force_update_override.name');
        add_action('admin_menu', array(&$this, 'menu'));
        add_action('admin_init', array(&$this, 'init'));
        
        add_action('wp_ajax_update_single_product', array(&$this, 'ajax_update_single_product'));
    }

    /**
     * init - Register the settings form and every section and field.
     *
     * @access  public
     * @return  void
     */
    public function init()
    {
        register_setting(SHCP::prefix('options'), SHCP::prefix('options'), array(&$this, 'action_settings_save' ));

        // Display the options.
        add_settings_section('action_product_section', SHCP::lang('plugin', 'form.product.title'), array(&$this, 'action_product_section'), __CLASS__);
        add_settings_section('action_cart_section', SHCP::lang('plugin', 'form.cart.title'), array(&$this, 'action_cart_section') , __CLASS__);
        add_settings_section('action_widgets_section', SHCP::lang('plugin', 'form.widgets.title'), array(&$this, 'action_widgets_section') , __CLASS__);
        add_settings_field($this->api_key_field_name, '', array(&$this, 'action_api_key_field'), __CLASS__, 'action_product_section');
        add_settings_field($this->store_field_name, '', array(&$this, 'action_store_field'), __CLASS__, 'action_product_section');
        add_settings_field($this->app_id_field_name, '', array(&$this, 'action_app_id_field'), __CLASS__, 'action_cart_section');
        add_settings_field($this->auth_id_field_name, '', array(&$this, 'action_auth_id_field'), __CLASS__, 'action_cart_section');
        add_settings_field($this->widgets_field_name, '', array(&$this, 'action_widgets_field'), __CLASS__, 'action_widgets_section');
        add_settings_field($this->cart_field_name, '', array(&$this, 'action_cart_field'), __CLASS__, 'action_cart_section');
       // add_settings_field($this->force_update_field_name, '', array(&$this, 'action_forceupdate_field'), __CLASS__, 'action_widgets_section');
      //  add_settings_field($this->force_update_override_field_name, '', array(&$this, 'action_forceupdate_override_field'), __CLASS__, 'action_widgets_section');
    }

    /**
     * add_menu - Add a menu item for the admin setting form.
     *
     * @access  public
     * @return  void
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
     * @access  public
     * @return  void
     */
    public function action_option_page()
    {
        $data = array(
            'classname' => __CLASS__,
            'lang'      => SHCP::lang('plugin', 'options')
            );
		if(isset($_GET['force_update_all']) && $_GET['force_update_all'] == 'yes') {
			global $wpdb;
    		$sql = "SELECT ID FROM wp_posts WHERE post_type = 'shcproduct' AND (post_status = 'publish' OR post_status = 'draft') ORDER BY post_modified ASC";
    		$products = $wpdb->get_results($sql);
    		
    		$data['products'] = json_encode($products);
    		
        	echo SHCP::view('admin/options/force_update', $data);
        } else {
       		echo SHCP::view('admin/options/page', $data);
       	}
    }

    /**
     * action_product_section - Display the top of the product api section of the form.
     *
     * @access  public
     * @return  void
     */
    public function action_product_section()
    {
        echo SHCP::view('admin/options/product');
    }

    /**
     * action_cart_section - Display the top of the cart api section of the form.
     *
     * @access  public
     * @return  void
     */
    public function action_cart_section()
    {
        echo SHCP::view('admin/options/cart');
    }

    /**
     * action_widgets_section - Widgets section of the options form.
     *
     * @access  public
     * @return  void
     */
    public function action_widgets_section()
    {
        echo SHCP::view('admin/options/widgets');
    }

    /**
     * action_api_key_field - Display the api_key text field.
     *
     * @access  public
     * @return  void
     */
    public function action_api_key_field()
    {
        $data = array(
            'id'        => $this->api_key_field_name,
            'name'  => SHCP::prefix('options['.$this->api_key_field_name.']'),
            'value' => SHCP::get_option($this->api_key_field_name, SHCP::config('plugin.options.api_key.default')),
            'lang'  => SHCP::lang('plugin', 'options.'.$this->api_key_field_name)
            );

        echo SHCP::view('admin/options/fields/api_key', $data);
    }

    /**
     * action_store_field - Display the store radio button field.
     *
     * @access  public
     * @return  void
     */
    public function action_store_field()
    {
        $data = array(
            'id'      => $this->store_field_name,
            'name'  => SHCP::prefix('options['.$this->store_field_name.']'),
            'value' => SHCP::get_option($this->store_field_name, SHCP::config('plugin.options.store.default')),
            'lang'  => SHCP::lang('plugin', 'options.'.$this->store_field_name)
            );

        echo SHCP::view('admin/options/fields/store', $data);
    }

    /**
     * action_app_id_field - Display the app_id text field.
     *
     * @access  public
     * @return  void
     */
    public function action_app_id_field()
    {
        $data = array(
            'id'      => $this->app_id_field_name,
            'name'  => SHCP::prefix('options['.$this->app_id_field_name.']'),
            'value' => SHCP::get_option($this->app_id_field_name, SHCP::config('plugin.options.app_id.default')),
            'lang'  => SHCP::lang('plugin', 'options.'.$this->app_id_field_name)
            );

        echo SHCP::view('admin/options/fields/app_id', $data);
    }

    /**
     * action_auth_id_field - Display the auth_id text field.
     *
     * @access  public
     * @return  void
     */
    public function action_auth_id_field()
    {
        $data = array(
            'id'      => $this->auth_id_field_name,
            'name'  => SHCP::prefix('options['.$this->auth_id_field_name.']'),
            'value' => SHCP::get_option($this->auth_id_field_name, SHCP::config('plugin.options.auth_id.default')),
            'lang'  => SHCP::lang('plugin', 'options.'.$this->auth_id_field_name)
            );

        echo SHCP::view('admin/options/fields/auth_id', $data);
    }

    /**
     * action_widgets_field 
     * 
     * @access public
     * @return void
     */
    public function action_widgets_field()
    {
        $data = array(
            'id'        => $this->widgets_field_name,
            'name'      => SHCP::prefix('options['.$this->widgets_field_name.'][]'),
            'values'    => (array) SHCP::get_option($this->widgets_field_name, SHCP::config('plugin.options.widgets.default')),
            'options'   => array(
                'products'  => __('Products'),
                'related'   => __('Related Products'),
                ),
            'lang'  => SHCP::lang('plugin', 'options.'.$this->widgets_field_name)
            );

        echo SHCP::view('admin/options/fields/widgets', $data);
    }

    public function action_cart_field()
    {
        $data = array(
            'id'    => $this->cart_field_name,
            'name'  => SHCP::prefix('options['.$this->cart_field_name.']'),
            'value' => SHCP::get_option($this->cart_field_name, SHCP::config('plugin.options.cart.default')),
            'lang'  => SHCP::lang('plugin', 'options.'.$this->cart_field_name)
        );

        echo SHCP::view('admin/options/fields/cart', $data);
    }
    
    public function action_forceupdate_field()
    {
        $data = array(
            'id'    => $this->force_update_field_name,
            'name'  => SHCP::prefix('options['.$this->force_update_field_name.']'),
            'value' => SHCP::get_option($this->force_update_field_name, SHCP::config('plugins.options.force_update.default')),
            'lang'  => SHCP::lang('plugin', 'options.'.$this->force_update_field_name)
        );

        echo SHCP::view('admin/options/fields/cart', $data);
    }

    public function action_forceupdate_override_field()
    {
        $data = array(
            'id'    => $this->force_update_override_field_name,
            'name'  => SHCP::prefix('options['.$this->force_update_override_field_name.']'),
            'value' => SHCP::get_option($this->force_update_override_field_name, SHCP::config('plugins.options.force_update.default')),
            'lang'  => SHCP::lang('plugin', 'options.'.$this->force_update_override_field_name)
        );

        echo SHCP::view('admin/options/fields/cart', $data);
    }

    public function action_settings_save($settings){

        if($settings['forceupdate']){
			$update = new Controller_Crons_Products();
			$update->is_manual_update();
			$update->action_update(true);
			$settings['forceupdate'] = 0;
        }
        
        return $settings;
    }
    
    public function ajax_update_single_product() {
    	$response = array(
    		'success' => false,
    		'updated' => 0,
    		'deleted' => 0,
    		'draft' => 0,
    		'no_action' => 0
    	);
    	if(isset($_POST['post_id'])) {
    		$post_id = $_POST['post_id'];
    		$response['message'] = 'Updating product '.$post_id;
    		
    		$model_post = new Model_Products($post_id); 
			$model_post->sync_from_api($this->_profile_mode);
			
			$response['cron_msg'] = $model_post->cron_msg;
			
			if($model_post->is_updated) {
    			$response['updated'] = 1;
    		}
    		if($model_post->is_deleted) {
    			$response['deleted'] = 1;
    			$response['cron_msg'] = '<span style="color:#FF0000;"><b>'.$response['cron_msg'].'</b></span>';
    		} 
    		if($model_post->is_draft) {
    			$response['draft'] = 1;
    			$response['cron_msg'] = '<span style="color:#ff8800;"><b>'.$response['cron_msg'].'</b></span>';
    		}  
    		if($model_post->no_action) {
    			$response['no_action'] = 1;
    			$response['cron_msg'] = '<span style="color:#FF0000;background-color:#FFFF00;"><b>'.$response['cron_msg'].'</b></span>';
    		}
    		
    		$response['success'] = true;
    	} else {
    		$response['error'] = 'Post ID not found.';
    	}
    	echo json_encode($response);
    	die();
    }
}
