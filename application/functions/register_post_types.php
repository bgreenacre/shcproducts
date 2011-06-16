<?php defined('SHCP_PATH') OR die('No direct script access.');

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

