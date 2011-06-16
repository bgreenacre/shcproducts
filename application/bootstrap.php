<?php defined('SHCP_PATH') OR die('No direct script access.');
/**
 */

if ( ! function_exists('SHCP_autoload'))
{
    function SHCP_autoload($class)
    {
        if (strpos($class, 'SHCP') === FALSE)
        {
            return FALSE;
        }

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

