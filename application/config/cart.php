<?php defined('SHCP_PATH') OR die('No direct script access.');

return array(
    'defaults'  => array(
        'items' => array(),
        'discounts' => array(),
        'item_count'    => 0,
        'total_item_price'   => 0.00,
        'total_discounts'   => 0.00,
        'total_weight'  => 0.00,
        'total_price'   => 0.00,
        'session'       => NULL,
        'order_id'      => NULL,
        'coupons'       => array(),
        'custom_fiels'  => array(),
        'messages'      => array(
            'information'   => array(),
            'notices'       => array(),
            'errors'        => array(),
        ),
    ),
    'item_arr'  => array(
        'id'            => NULL,
        'name'          => NULL,
        'image'         => NULL,
        'partnumber'    => NULL,
        'display_partnumber'    => NULL,
        'catentryid'    => NULL,
        'quantity'      => NULL,
        'price_each'    => NULL,
        'price'         => NULL,
        'options'       => array(),
    ),
);
