<?php defined('SHCP_PATH') OR die('No direct script access.');

return array(
    'stores'    => array(
        'sears'     => 'Sears',
        'kmart' => 'Kmart',
        'mygofer'   => 'MyGofer'
        ),
    'options'   => array(
        'api_key'   => array(
            'name'      => 'apikey',
            'default'   => 'api_key_default_value'
            ),
        'store' => array(
            'name'      => 'store',
            'default'   => 'Sears'
            ),
        'app_id'    => array(
            'name'      => 'appid',
            'default'   => 'app_id_default_value'
            ),
        'auth_id'   => array(
            'name'      => 'authid',
            'default'   => 'auth_id_default_value'
        ),
        'cart'   => array(
            'name'      => 'cart_enabled',
            'default'   => TRUE,
        ),
        'force_update' => array(
            'name'      => 'forceupdate',
            'default'   => 'false'
        ),
		'force_update_override' => array(
            'name'      => 'forceupdate_override',
            'default'   => 'false'
        ),
        'widgets'   => array(
            'name'      => 'widgets',
            'default'   => array(
                'products',
                'related',
                ),
            ),
        ),
);
