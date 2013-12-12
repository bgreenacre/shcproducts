<?php defined('SHCP_PATH') OR die('No direct script access.');

/*
* Register ajax functions.
*/
add_action('wp_ajax_get_verticals_category', 'ajax_get_verticals_category');

/*
* Return the HTML for a dropdown menu containing Verticals from the Sears API,
* with ajaxified categories / subcategories / filters that appear when a Vertical is selected.
*/
function get_verticals_dropdown() {
	$obj = new Product_Search_Api();

	$args = array(
		'type' => 'category',
		'return_type' => 'json',
		'search_keyword' => ''
	);

	$obj->set_up_request($args);
	$result = $obj->make_request();
	
	$output = '<div id="verticals_holder">';
	
	if(is_array($result->verticals)) {
		$output .= '<select name="verticals" id="verticals">';
		$output .= '<option value="">-- Select Vertical --</option>';
		foreach($result->verticals as $vertical) {
			$output .= '<option value="'.$vertical['vertical_name'].'">'.$vertical['vertical_name'].'</option>';
		}
		$output .= '</select>';
	}
	
	$output .= '</div>';
	
	$output .= '<div id="category_holder"></div>';
	$output .= '<div id="subcategory_holder"></div>';
	$output .= '<div id="filter_holder"></div>';
	
	return $output;
}



function ajax_get_verticals_category(){
	$search_keyword = $_POST['search_keyword'];
	
	$obj = new Product_Search_Api();

	$args = array(
		'type' => 'category',
		'return_type' => 'json',
		'search_keyword' => $search_keyword
	);

	$obj->set_up_request($args);
	$result = $obj->make_request();
	
	$output = '';
	
	error_log(print_r($result,true));
	
	if(is_array($result->categories) && !empty($result->categories)) {
		$output .= '<select name="categories" id="categories">';
		$output .= '<option value="">-- Select Category --</option>';
		
		foreach($result->categories as $category) {
			$output .= '<option value="'.$category['category_name'].'">'.$category['category_name'].' ('.$category['product_count'].')</option>';
		}
		$output .= '</select>';
	} else {
		// No categories were found, try loading products.
		$obj2 = new Product_Search_Api();

		$args2 = array(
			'type' => 'product',
			'return_type' => 'json',
			'search_keyword' => $search_keyword
		);

		$obj2->set_up_request($args2);
		$result2 = $obj2->make_request();
		
		if(isset($result2->product_count) && $result2->product_count > 0) {
			$output = '<b>'.$result2->product_count.'</b> items found in this category.';
		} else {
			$output = 'No categories or items found.';
		}
		
		error_log(print_r($result2,true));
		
	}
	
	echo $output;
	
	die();
}


