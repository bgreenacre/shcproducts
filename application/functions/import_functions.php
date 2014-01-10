<?php defined('SHCP_PATH') OR die('No direct script access.');

/*
* Register ajax functions.
*/
add_action('wp_ajax_import_single_product', 'ajax_import_single_product');


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