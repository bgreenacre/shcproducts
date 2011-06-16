<?php defined('SHCP_PATH') OR die('No direct script access.');

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

