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
	$new_columns = array();
	foreach($columns as $key => $value) {
		$new_columns[$key] = $value;
		// Add image column after checkbox:
		if($key == 'cb') {
			$new_columns['image'] = 'Image';
		}
		// Add modified column after date:
		if($key == 'date') {
			$new_columns['modified'] = 'Last Modified';
		}
	}

	return $new_columns;
}
add_filter('manage_shcproduct_posts_columns' , 'add_shcproduct_columns');


function custom_shcproduct_column( $column, $post_id ) {
	$post = get_post($post_id);

    switch ( $column ) {
    
    	case 'image' :
    		$post_object = new Product_Post_Model($post_id);
    		if(!empty($post_object->product_model->product)) {
				$img_url = $post_object->product_model->product['main_image_url'];
				echo '<img src="'.$img_url.'?hei=140&wid=140&op_sharpen=1" width="80" height="80"/>';
    		}
    		break;

        case 'modified' :
        	$modified_date = date('Y/m/d H:i:s',strtotime($post->post_modified));
           echo $modified_date;
        	break;

    }
}
add_action( 'manage_shcproduct_posts_custom_column' , 'custom_shcproduct_column', 10, 2 );