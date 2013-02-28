<?php defined('SHCP_PATH') OR die('No direct script access.');

return array(
    'shcproduct'    => array(
        'labels'    => array(
            'name'                => _x('Products', 'post type general name'),
            'singular_name'       => _x('Product', 'post type singular name'),
            'add_new'             => _x('Add New', 'Product'),
            'add_new_item'        => __('Add New Product'),
            'edit_item'           => __('Edit Product'),
            'new_item'            => __('New Product'),
            'view_item'           => __('View  Product'),
            'search_items'        => __('Search Products'),
            'not_found'           => __('No Products found'),
            'not_found_in_trash'  => __('No Products found in Trash'),
            'parent_item_colon'   => '',
        ),
        'public'            => TRUE,
        'supports'          => array( 'title', 'thumbnail', 'editor' ),
        'taxonomies'        => array( 'category', 'post_tag' ),  // this is IMPORTANT // Duh!
        'capability_type'      => 'shcproduct',
        'rewrite'           => array(
            'slug'              => 'product',
            'with_front'        => TRUE,
        ),
        'has_archive'       => 'products',
    ),
);
