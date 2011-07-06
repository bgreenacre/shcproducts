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

function shcp_register_taxonomy()
{
    if ($taxonomies = (array) SHCP::config('taxonomies'))
    {
        foreach ($taxonomies as $tax => $params)
        {
            if ($type = SHCP::get($params, 'type'))
            {
                unset($params['type']);
            }

            register_post_type($tax, $type, $params);
        }
    }
}

add_action('init', 'shcp_register_taxonomy');

