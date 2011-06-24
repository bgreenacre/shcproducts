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
 */
if ( ! function_exists('SHCP_autoload'))
{
    function SHCP_autoload($class)
    {

        try
        {
            $file = str_replace('_', '/', strtolower($class));
            $fullpath = SHCP_CLASS . '/' . $file . '.php';

            if (is_file($fullpath))
            {
                require $fullpath;
                return TRUE;
            }

            return FALSE;
        }
        catch(Exception $e)
        {
            throw Exception($e);
            die;
        }
    }
    
    spl_autoload_register('SHCP_autoload');
}

SHCP::init();
