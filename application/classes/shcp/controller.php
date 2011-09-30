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
 * Controller - Provides a factory to instantiate controller objects.
 *
 * @package shcproducts
 * @subpackage  Controller
 * @category    Controller
 * @since		0.1
 * @auther      Brian Greenacre
 */
class SHCP_Controller {

    public static function factory($class)
    {
        $class = 'Controller_' . $class;

        try
        {
            return new $class();
        }
        catch(Exception $e)
        {
            throw new Exception($e);
        }
    }

}
