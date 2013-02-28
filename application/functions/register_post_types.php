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
 * shcp_register_post_types 
 * 
 * @access public
 * @return void
 */
function shcp_register_post_types()
{
    if ($types = (array) SHCP::config('post_types'))
    {
        foreach ($types as $type => $params)
        {
            register_post_type($type, $params);
        }
    }
}

add_action('init', 'shcp_register_post_types');
