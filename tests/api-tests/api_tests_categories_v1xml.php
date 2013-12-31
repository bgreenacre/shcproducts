<?php

/*
	Unit tests for the SHC products plugin.
	
	These tests check the plugin's ability to retrieve verticals, categories, and subcategories.
*/

ini_set('memory_limit','256M');

require_once(dirname(__FILE__) . '/../api_tests_parent.php');
require_once(dirname(__FILE__) . '/../../shcproducts.php');


class ApiTestCategories_V1xml extends SHC_API_Test_Parent {

	function setUp() {
 		parent::setUp();
 	}
 	
 	function tearDown() {
		parent::tearDown();
	}
    
    
    public function test_ApiRequest_GetVerticals() {
    	$this->markTestSkipped('Verticals cannot be retrieved from V1 of the API.');
    }


	
	/**
     * @dataProvider provider_verticals
     */
	public function test_ApiRequest_GetCategories($vertical_name) {
		$test_object = new Product_Search_Api();
		$args = array(
			'api_version' => 'v1',
			'search_type' => 'category',
			'return_type' => 'xml',
			'category_search' => array(
				'vertical' => $vertical_name
			)
		);
		$test_object->set_up_request($args);
		$result_object = $test_object->make_request();
		// Make sure the resulting object is of the expected type:
		$this->assertInstanceOf('Search_Api_Result_V1xml', $result_object);
		$this->assertInstanceOf('Search_Api_Result', $result_object);
		// Check the data of the result object:
		$this->check_categories($result_object);
	}

	
	/**
     * @dataProvider provider_categories
     */
	public function test_ApiRequest_GetSubcategories($vertical_name, $category_name) {
		$test_object = new Product_Search_Api();
		$args = array(
			'api_version' => 'v1',
			'search_type' => 'category',
			'return_type' => 'xml',
			'category_search' => array(
				'vertical' => $vertical_name,
				'category' => $category_name
			)
		);
		$test_object->set_up_request($args);
		$result_object = $test_object->make_request();
		// Make sure the resulting object is of the expected type:
		$this->assertInstanceOf('Search_Api_Result_V1xml', $result_object);
		$this->assertInstanceOf('Search_Api_Result', $result_object);
		// Check the data of the result object:
		$this->check_categories($result_object);
	}
	
	

}