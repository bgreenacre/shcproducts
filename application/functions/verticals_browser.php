<?php defined('SHCP_PATH') OR die('No direct script access.');

/*
* Register ajax functions.
*/
add_action('wp_ajax_get_verticals_category', 'ajax_get_verticals_category');
add_action('wp_ajax_get_verticals_filter', 'ajax_get_verticals_filter');
/*
* Return the HTML for a dropdown menu containing Verticals from the Sears API,
* with ajaxified categories / subcategories / filters that appear when a Vertical is selected.
*/
function get_verticals_dropdown() {
	$search_obj = new Product_Search_Api();
	$result = $search_obj->get_verticals();

	$output = '<div id="verticals_holder">';
	
	if(is_array($result->verticals)) {
		$output .= '<select name="verticals" id="vertical">';
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
	$type_selected = $_POST['type_selected'];
	$search_keyword = $_POST['search_keyword'];
	$category_search = $_POST['category_search'];
	
	error_log('$category_search = '.print_r($category_search, true));
	
	$search_obj = new Product_Search_Api();
	
	// If a vertical was selected, display categories within that vertical.
	if($type_selected == 'vertical') {
		$result = $search_obj->get_categories($search_keyword);
		$what_to_select = 'Category';
	}
	
	// If a category was selected, display subcategories within that vertical.
	if($type_selected == 'category') {
		$vertical = $category_search['vertical'];
		$category = $category_search['category'];
		$result = $search_obj->get_subcategories($vertical, $category);
		$what_to_select = 'Subcategory';
	}
	
	error_log(print_r($result,true));
	
	if(is_array($result->categories) && !empty($result->categories)) {
		$output .= '<select name="'.strtolower($what_to_select).'" id="'.strtolower($what_to_select).'">';
		$output .= '<option value="">-- Select '.$what_to_select.' --</option>';
		
		foreach($result->categories as $category) {
			$output .= '<option value="'.$category['category_name'].'">'.$category['category_name'].' ('.$category['product_count'].')</option>';
		}
		$output .= '</select>';
	} else {
		echo 'No categories found.';
		
	}
	
	echo $output;
	
	die();
}



function ajax_get_verticals_filter() {
	$category_search = $_POST['category_search'];
	
	$vertical = (isset($category_search['vertical'])) ? $category_search['vertical'] : '';
	$category = (isset($category_search['category'])) ? $category_search['category'] : '';
	$subcategory = (isset($category_search['subcategory'])) ? $category_search['subcategory'] : '';
	
	
	error_log('$category_search = '.print_r($category_search,true));
	
	$search_obj = new Product_Search_Api();
	$result = $search_obj->get_available_filters($vertical, $category, $subcategory);
	
	$output = '';
	$filter_option_output = '';
	
	if(is_array($result->available_filters) && !empty($result->available_filters)) {
		$output .= '<select name="filter" id="filter">';
		$output .= '<option value="">-- Select Filter --</option>';
		foreach($result->available_filters as $filter_name => $filter_options) {
			$filter_id = strtolower(preg_replace('/[^a-zA-Z0-9]/','',$filter_name));
			$filter_name_pretty = preg_split('/(?=[A-Z][a-z])/', $filter_name);
			$filter_name_pretty = implode(' ', $filter_name_pretty);
			$filter_name_pretty = str_replace('_', ' ', $filter_name_pretty);
			$filter_name_pretty = ucwords($filter_name_pretty);
			$output .= '<option data-id="'.$filter_id.'" value="'.$filter_name.'">'.$filter_name_pretty.'</option>';
			if(is_array($filter_options) && !empty($filter_options)) {
				$single_filter = '<select name="'.$filter_id.'" id="'.$filter_id.'" class="single_filter" style="display:none;">';
				$single_filter .= '<option value="">-- Filter Products --</option>';
				foreach($filter_options as $option_value => $product_count) {
					$single_filter .= '<option value="'.$option_value.'">'.$option_value.' ('.$product_count.')</option>';
				}
				$single_filter .= '</select>';
				$filter_option_output .= $single_filter;
			}
		}
		$output .= '</select>';
	} else {
		echo 'No filters are available for this category.';
	}
	
	error_log('$result = '.print_r($result,true));
	
	echo $output;
	echo $filter_option_output;
		
	die();
}


