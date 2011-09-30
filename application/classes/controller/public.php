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
 * Public controller.
 *
 * @package     shcproducts
 * @subpackage  Controller
 * @version     0.1
 * @author      Kyla Klein
 */
class Controller_Public {

    /**
     * path 
     * 
     * @var mixed
     * @access public
     */
    public $path;

    /**
     * __construct 
     * 
     * @param array $params 
     * @access public
     * @return void
     */
    public function __construct(array $params = NULL)
    {
        $name = SHCP::config('plugin', 'options.api_key.name');
        $default = SHCP::config('plugin', 'options.api_key.default');
        $this->path = SHCP::get_option($name, $default);
    }

}
