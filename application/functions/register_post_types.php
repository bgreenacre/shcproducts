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



function add_shcproduct_columns($columns) {
    return array_merge($columns, 
              array('modified' => __('Last Modified') )
              );
}
add_filter('manage_shcproduct_posts_columns' , 'add_shcproduct_columns');


function custom_shcproduct_column( $column, $post_id ) {
	$post = get_post($post_id);

    switch ( $column ) {

        case 'modified' :
        	$modified_date = date('Y/m/d H:i:s',strtotime($post->post_modified));
           echo $modified_date;

    }
}
add_action( 'manage_shcproduct_posts_custom_column' , 'custom_shcproduct_column', 10, 2 );