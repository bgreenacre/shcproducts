<?php
/**
 * shcproducts.php
 *
 * @author Brian Greenacre and Kyla Klein
 * @email bgreenacre42@gmail.com
 * @version $Id$
 * @since Wed 15 Jun 2011 07:32:09 PM
 */
/*
Plugin Name: SHC Products
Plugin URI: http://wordpress.org/#
Description: Loads a list of products from Sears.com API
Author: Brian Greenacre and Kyla Klein
Version: 0.1
*/

define('SHCP_PATH', WP_PLUGIN_DIR.'/shcproducts');
define('SHCP_CONFIG', SHCP_PATH.'/application/config');
define('SHCP_CLASS', SHCP_PATH.'/application/classes');
define('SHCP_VIEW', SHCP_PATH.'/application/views');
define('SHCP_FUNCTIONS', SHCP_PATH.'/application/functions');
define('SHCP_LANG', SHCP_PATH . '/application/messages');
define('SHCP_URL', plugins_url('/shcproducts'));
define('SHCP_CSS', SHCP_URL . '/assets/css');
define('SHCP_IMAGES', SHCP_URL . '/assets/images');
define('SHCP_JS', SHCP_URL . '/assets/js');

/**
 * Define the start time of the plugin.
 */
if ( ! defined('SHCP_START_TIME'))
{
	define('SHCP_START_TIME', microtime(TRUE));
}

/**
 * Define the memory usage at the start of the plugin load.
 */
if ( ! defined('SHCP_START_MEMORY'))
{
	define('SHCP_START_MEMORY', memory_get_usage());
}

require_once SHCP_PATH . '/application/bootstrap.php';
