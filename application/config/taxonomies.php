<?php defined('SHCP_PATH') OR die('No direct script access.');

return array(
    'product_category'  => array(
        'type'  => 'shcproduct',
        'hierarchical' => true,
        'public' => true,
        'show_in_nav_menus' => true,
        'label' => 'Product Categories',
        'query_var' => true,
        'rewrite' => true,
        'show_ui'=>true,
    ),
);

