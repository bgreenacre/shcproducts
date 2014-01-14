<?php defined('SHCP_PATH') OR die('No direct script access.');

/*
* Register ajax functions.
*/
add_action('wp_ajax_import_single_product', 'ajax_import_single_product');
add_action('wp_ajax_save_category_mapping', 'ajax_save_category_mapping');
add_action('wp_ajax_get_current_category_mapping', 'ajax_get_current_category_mapping');


function import_single_product($part_number) {
	error_log('import_single_product');
	$prod_obj = new Product_Model($part_number); 
	return $prod_obj->import_product();
}


function ajax_import_single_product() {
	$part_number = $_POST['part_number'];
	error_log('ajax_import_single_product - part number = '.$part_number);
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
		$json_shc_category = json_decode($shc_category);
		if( !empty($json_shc_category) ) {
			$option_key = 'shcproducts_category_'.$wp_category_id;
			$outcome = update_option( $option_key, $shc_category );
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



function ajax_get_current_category_mapping() {
	$wp_category_id = $_POST['wp_category_id'];
	$option_key = 'shcproducts_category_'.$wp_category_id;
	$shc_category = get_option($option_key);
	if($shc_category) {
		echo $shc_category;
	}
	die();
}


function get_api_category_by_json($json_string) {
	if(!empty($json_string)) {
		$jcat = json_decode($json_string);
		if($jcat) {
			$vertical = $jcat->vertical;
			$category = $jcat->category;
			$subcategory = $jcat->subcategory;
			$filter_name = html_entity_decode($jcat->filter_name);
			$filter_value = html_entity_decode($jcat->filter_value);
			$filter = array($filter_name => $filter_value);
			$search_obj = new Product_Search_Api();
			$result = $search_obj->get_products($vertical, $category, $subcategory, $filter);
			return $result;
		}
	}
	return false;
}