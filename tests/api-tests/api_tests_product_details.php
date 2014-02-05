<?php

/*
	Unit tests for the SHC products plugin.
	
	These tests check the plugin's ability to retrieve lists of products using the search API.
*/

ini_set('memory_limit','512M');

require_once(dirname(__FILE__) . '/../api_tests_parent.php');
require_once(dirname(__FILE__) . '/../../shcproducts.php');


class ApiTestProductDetails extends SHC_API_Test_Parent {

	function setUp() {
 		parent::setUp();
 	}
 	
 	function tearDown() {
		parent::tearDown();
	}
    
	
	/**
     * @dataProvider provider_partnumbers
     */
	public function test_ApiRequest_GetProducts($part_number) {
		$test_object = new Product_Details_Api();
		$result_object = $test_object->get_product($part_number);
		// Make sure the resulting object is of the expected type:
		//$this->assertInstanceOf('Search_Api_Result', $result_object);
		// Check the data of the result object:
		$this->check_product_details($result_object);
	}
	

}