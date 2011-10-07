<?php defined('SHCP_PATH') OR die('No direct script access.');

return array(
	'menu'	=> array(
		'name'	=> 'SHC Products'
		),
	'form'	=> array(
		'product'	=> array(
			'title'	=> 'SHC Product API Settings'
			),
		'cart'	=> array(
			'title'	=> 'SHC Cart API Settings'
			),
        'widgets'   => array(
            'title' => 'SHC Product Widgets',
            ),
		),
	'options'	=> array(
		'title'	=> 'SHC Product/Cart API Settings',
		'submit'	=> 'Save Settings',
		'apikey'	=> array(
			'label'	=> 'Product API Key'
			),
		'store'	=> array(
			'label'	=> 'Choose Store'
			),
		'appid'	=> array(
			'label'	=> 'Cart App ID'
			),
		'authid'	=> array(
			'label'	=> 'Cart Auth ID'
			),
		'widgets'	=> array(
			'label'	=> 'Available Widgets'
			),
		),
);
