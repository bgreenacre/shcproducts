<?php

/*
	Unit tests for the SHC products plugin.
	
	These tests check the plugin's ability to retrieve lists of products using the search API.
*/

ini_set('memory_limit','256M');

require_once(dirname(__FILE__) . '/../api_tests_parent.php');
require_once(dirname(__FILE__) . '/../../shcproducts.php');


class ApiTestProductSearch extends SHC_API_Test_Parent {

	function setUp() {
 		parent::setUp();
 	}
 	
 	function tearDown() {
		parent::tearDown();
	}
    
	
	/**
     * @dataProvider provider_subcategories
     */
	public function test_ApiRequest_GetProducts($vertical_name, $category_name, $subcategory_name) {
		$test_object = new Product_Search_Api();
		$result_object = $test_object->get_products($vertical_name, $category_name, $subcategory_name);
		// Make sure the resulting object is of the expected type:
		$this->assertInstanceOf('Search_Api_Result', $result_object);
		// Check the data of the result object:
		$this->check_product_search_results($result_object);
	}
	

}