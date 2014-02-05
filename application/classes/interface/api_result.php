<?php defined('SHCP_PATH') OR die('No direct script access.');

interface Api_Result {
	
	function standardize_data();
	
}



interface Search_Api_Result extends Api_Result {
	
	function _standardize_verticals();
	function _standardize_categories();
	function _standardize_product_count();
	function _standardize_products();
	
}


interface Details_Api_Result extends Api_Result {
	
	function is_valid_product();
	function is_softline();
	
}