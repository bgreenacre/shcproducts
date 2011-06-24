<?php defined('SHCP_PATH') OR die('No direct script access.');
/**
 * Sears and Kmart product plugin.
 * Public controller.
 *
 * @package		shcproducts
 * @subpackage	Controller
 * @version		0.1
 * @author		Kyla Klein
 */

class Controller_Public {

	public $path;

	public function __construct(array $params = NULL)
	{
		$name = SHCP::config('plugin', 'options.api_key.name'); 
		$default = SHCP::config('plugin', 'options.api_key.default');
		$this->path = SHCP::get_option($name, $default);
		
		add_action('init', array(&$this, 'init'));
	}

	public function init()
	{
    // add_action('wp_head', array(&$this, 'assets'));
    // add_action('thematic_before', array(&$this, 'header'));
    // add_action('thematic_after', array(&$this, 'footer'));
	}

  // public function assets()
  // {
  //  if ($this->path === 'sears')
  //  {
  //    echo '<link rel="stylesheet" id="sears-header-css" href="http://a.shld.net/04270759/ue/home/CMBDZ_global_10588.css" type="text/css" media="all" />';
  //    echo '<script type="text/javascript" src="'.SK_JS_URL.'/sears/NewUtility.js"></script>';
  //    echo '<script type="text/javascript" src="'.SK_JS_URL.'/sears/SearsNavData.js"></script>';
  //    echo '<script type="text/javascript" src="'.SK_JS_URL.'/sears/CMBDZ_global.js"></script>';
  //  }
  //  elseif ($this->path === 'kmart')
  //  {
  //    echo '<link rel="stylesheet" id="sears-header-css" href="http://k.shld.net/04270759/ue/home/CMBDZ_global_10216.css" type="text/css" media="all" />';
  //    echo '<script type="text/javascript" src="'.SK_JS_URL.'/kmart/CMBDZ_global.js"></script>';
  //    echo '<script type="text/javascript" src="'.SK_JS_URL.'/kmart/foot_v9.js"></script>';
  //  }
  // }
  // 
  // public function header()
  // {
  //  $show_name = SK_Base::config('plugin', 'options.header.name');
  //  $show_default = SK_Base::config('plugin', 'options.header.default');
  //  $show = (bool) SK_Base::get_option($show_name, $show_default);
  // 
  //  if ($show)
  //  {
  //    echo SK_Base::view($this->path.'/header');
  //  }
  // }
  // 
  // public function footer()
  // {
  //  $show_name = SK_Base::config('plugin', 'options.footer.name');
  //  $show_default = SK_Base::config('plugin', 'options.footer.default');
  //  $show = (bool) SK_Base::get_option($show_name, $show_default);
  // 
  //  if ($show)
  //  {
  //    echo SK_Base::view($this->path.'/footer');
  //  }
  // }

}
