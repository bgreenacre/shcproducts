<?php defined('SHCP_PATH') OR die('No direct script access.');

/*
* Register ajax functions.
*/
add_action('wp_ajax_import_single_product', 'ajax_import_single_product');
add_action('wp_ajax_save_category_mapping', 'ajax_save_category_mapping');
add_action('wp_ajax_remove_category_mapping', 'ajax_remove_category_mapping');
add_action('wp_ajax_get_current_category_mapping', 'ajax_get_current_category_mapping');
add_action('wp_ajax_get_category_shc_products', 'ajax_get_category_shc_products');
add_action('wp_ajax_update_single_shc_product', 'ajax_update_single_shc_product');


function import_single_product($part_number) {
	$prod_obj = new Product_Model($part_number); 
	return $prod_obj->import_product();
}


function ajax_import_single_product() {
	$part_number = $_POST['part_number'];
	$outcome = import_single_product($part_number);
	if($outcome == true) {
		echo 1;
	} else {
		echo $outcome;
	}
	die();
}


function ajax_save_category_mapping() {
	$shc_category = stripslashes($_POST['shc_category_json']);
	$wp_category_id = $_POST['wp_category_id'];
	if(is_numeric($wp_category_id)) {
		$json_shc_category = json_decode($shc_category, true);
		$new_option = array($json_shc_category);
		if( !empty($json_shc_category) ) {
			$option_key = 'shcproducts_category_'.$wp_category_id;
			// Merge with current if applicable:
			$current_option = get_option($option_key);
			if(is_array($current_option)) {
				foreach($current_option as $option) {
					if(json_encode($option) != $json_shc_category) {
						$new_option[] = $option;
					}
				}
			}
			
			$outcome = update_option( $option_key, $new_option );
			if($outcome) {
				echo 1;
			} else {
				echo 'Option not changed or update failed.';
			}
		} else {
			echo 'Error - invalid json received for Sears API category.';
		}
	} else {
		echo 'Error - invalid WordPress category id.';
	}
	
	die();
}


function ajax_remove_category_mapping() {
	$wp_category_id = $_POST['wp_category_id'];
	$option_index = $_POST['option_index']; // If -1, we will delete all.
	$option_key = 'shcproducts_category_'.$wp_category_id;
	$current_option = get_option($option_key);
	if($option_index != -1) {
		if(isset($current_option[$option_index])) {
			unset($current_option[$option_index]);
		} else {
			echo 'Error - option not found.';
		}
	}
	if(empty($current_option) || $option_index == -1) {
		$outcome = delete_option( $option_key );
	} else {
		$outcome = update_option( $option_key, $current_option );
	}
	if($outcome) {
		echo 1;
	} else {
		echo 'Error - failed to remove option.';
	}
	die();
}



function ajax_get_current_category_mapping() {
	$wp_category_id = $_POST['wp_category_id'];
	$option_key = 'shcproducts_category_'.$wp_category_id;
	$shc_category = get_option($option_key);
	if($shc_category) {
		echo json_encode($shc_category);
	}
	die();
}


function get_api_category_by_array($cat_array, $start_end=false) {
	if(!empty($cat_array)) {
		$vertical = html_entity_decode($cat_array['vertical']);
		$category = html_entity_decode($cat_array['category']);
		$subcategory = html_entity_decode($cat_array['subcategory']);
		$filter_name = html_entity_decode($cat_array['filter_name']);
		$filter_value = html_entity_decode($cat_array['filter_value']);
		$filter = array($filter_name => $filter_value);
		$search_obj = new Product_Search_Api();
		$result = $search_obj->get_products($vertical, $category, $subcategory, $filter, $start_end);
		return $result;
	}
	return false;
}



function get_all_products_in_category($cat_array) {
	$api_result = get_api_category_by_array($cat_array);
	if(is_object($api_result) && isset($api_result->product_count)) {
		$start_end = array(
			'start_index' => 1,
			'end_index' => $api_result->product_count
		);
		$api_result2 = get_api_category_by_array($cat_array, $start_end);
		return $api_result2;
	}
}


function get_full_categories() {
	$args = array(
		'type'                     => 'post',
		'child_of'                 => 0,
		'parent'                   => 0,
		'orderby'                  => 'name',
		'order'                    => 'ASC',
		'hide_empty'               => 0,
		'hierarchical'             => 1,
		'exclude'                  => '',
		'include'                  => '',
		'number'                   => '',
		'taxonomy'                 => 'category',
		'pad_counts'               => false 

	);
	$categories = get_categories( $args ); 
	
	$r_categories = array();

	foreach($categories as $category) {
		$r_categories = get_full_categories_recurse($category->cat_name, $category, $r_categories);
	}
	
	return $r_categories;
}

function get_full_categories_recurse($parent_string, $cat_obj, $r_categories) {
	$r_categories[$cat_obj->cat_ID] = $parent_string;

	$args = array(
		'type'                     => 'post',
		'child_of'                 => 0,
		'parent'                   => (int)$cat_obj->cat_ID,
		'orderby'                  => 'name',
		'order'                    => 'ASC',
		'hide_empty'               => 0,
		'hierarchical'             => 1,
		'exclude'                  => '',
		'include'                  => '',
		'number'                   => '',
		'taxonomy'                 => 'category',
		'pad_counts'               => false 
	);
	$children = get_categories($args);
	if(!empty($children)) {
		foreach($children as $child) {
			$name = $parent_string.' &rarr; '.$child->cat_name;
			$r_categories = get_full_categories_recurse($name, $child, $r_categories);
		}
	}
	
	return $r_categories;
}



function ajax_get_category_shc_products() {
	$category_id = $_POST['category_id'];
	$obj = new Product_Category_Model($category_id);
	$obj->build_sync_todo_list();
	$output = array(
		'products' => $obj->sync_todo_list
	);
	echo json_encode($output);
	die();
}


function ajax_update_single_shc_product() {
	$part_number = $_POST['part_number'];
	$category_id = $_POST['category_id'];

	$obj = new Product_Category_Model($category_id, false);
	$msg = $obj->sync_single($part_number);

	$output = array(
		'msg' => 'Part number '.$part_number.' - '.$msg,
		'imported' => $obj->last_imported,
		'added_to_category' => $obj->last_added_to_category
	);
	
	echo json_encode($output);

	die();
}
