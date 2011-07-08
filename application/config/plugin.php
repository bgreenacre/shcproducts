<?php defined('SHCP_PATH') OR die('No direct script access.');

return array(
	'stores'	=> array(
		'sears'		=> 'Sears',
		'kmart'	=> 'Kmart',
		'mygofer'	=> 'MyGofer'
		),
	'options'	=> array(
		'api_key'	=> array(
			'name'		=> 'api_key',
			'default'	=> 'api_key_default_value'
			),
		'store'	=> array(
			'name'		=> 'store',
			'default'	=> 'Sears'
			),
		'app_id'	=> array(
			'name'		=> 'app_id',
			'default'	=> 'app_id_default_value'
			),
		'auth_id'	=> array(
			'name'		=> 'auth_id',
			'default'	=> 'auth_id_default_value'
			),
        'widgets'   => array(
            'name'      => 'widgets',
            'default'   => array(
                'products',
                'related',
                ),
            ),
		)
);
