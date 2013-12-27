<?php

/*
	Unit tests for the SHC products plugin.
	
*/

require_once(dirname(__FILE__) . '/../shcproducts.php');


class ApiTest extends WP_UnitTestCase {

	function setUp() {
 		parent::setUp();
 		// Plugin settings:
 		$shcp_options = array(
 			'apikey' => '06749c96f1e1bfadfeca4a02b4120253',
 			'store' => 'Sears',
 			'appid' => 'FitStudio',
 			'authid' => 'nmktplc303B1614B51F9FE5340E87FD1A1CEB3C06222010'
 		);
 		update_option('shcp_options', $shcp_options);
	}
	
	function tearDown() {
		parent::tearDown();
	}

	public function test_Make_Sure_PHPUnit_Is_Working() {
		// If this fails, something is wrong with the PHPUnit setup.
        $this->assertTrue(true);
    }
    
    public function test_ClassExists__Product_Search_Api() {
    	$test = class_exists('Product_Search_Api');
    	$this->assertTrue($test);
    }
    
    public function test_ClassExists__Product_Details_Api() {
    	$test = class_exists('Product_Details_Api');
    	$this->assertTrue($test);
    }
    
    public function test_CreateObject__Product_Search_Api() {
    	$test_object = new Product_Search_Api();
    	$is_object = is_object($test_object);
    	$this->assertTrue($is_object);
    	$this->assertInstanceOf('Product_Search_Api', $test_object);
    }
    
    public function test_CreateObject__Product_Details_Api() {
    	$test_object = new Product_Details_Api();
    	$is_object = is_object($test_object);
    	$this->assertTrue($is_object);
    	$this->assertInstanceOf('Product_Details_Api', $test_object);
    }
    
    
    
    public function test_ApiRequest_GetVerticals_V2xml() {
    	$test_object = new Product_Search_Api();
    	$args = array(
			'api_version' => 'v2.1',
			'search_type' => 'category',
			'return_type' => 'xml'
		);
		$test_object->set_up_request($args);
		$result_object = $test_object->make_request();
		// Make sure the resulting object is of the expected type:
		$this->assertInstanceOf('Search_Api_Result_V2xml', $result_object);
		$this->assertInstanceOf('Search_Api_Result', $result_object);
		// Check the varying data things that are specific to verticals:
		$this->check_verticals($result_object);
    }
     
    public function test_ApiRequest_GetVerticals_V2json() {
    	$test_object = new Product_Search_Api();
    	$args = array(
			'api_version' => 'v2.1',
			'search_type' => 'category',
			'return_type' => 'json'
		);
		$test_object->set_up_request($args);
		$result_object = $test_object->make_request();
		// Make sure the resulting object is of the expected type:
		$this->assertInstanceOf('Search_Api_Result_V2json', $result_object);
		$this->assertInstanceOf('Search_Api_Result', $result_object);
		// Check the varying data things that are specific to verticals:
		$this->check_verticals($result_object);
    }
    
    
    public function test_ApiRequest_GetVerticals_Shortcut() {
    	$test_object = new Product_Search_Api();
    	$result_object = $test_object->get_verticals();
		// Make sure the resulting object is of the expected type:
		$this->assertInstanceOf('Search_Api_Result', $result_object);
		// Check the varying data things that are specific to verticals:
		$this->check_verticals($result_object);
    }


	protected function check_verticals($result_object) {
		// Make sure the verticals property is set as expected:
		$test1 = isset($result_object->verticals);
		$this->assertTrue($test1);
		// Make sure the verticals property isn't empty:
		$test2 = empty($result_object->verticals);
		$this->assertFalse($test2);
		// Make sure the verticals property is an array:
		$test3 = is_array($result_object->verticals);
		$this->assertTrue($test3);
		// If the verticals property is an array, loop through and check the values inside:
		if(is_array($result_object->verticals)) {
			foreach($result_object->verticals as $vertical) {
				// Make sure each individual vertical isn't empty:
				$loop_test_1 = empty($vertical);
				$this->assertFalse($loop_test_1);
				// Make sure each individual vertical is an arary:
				$loop_test_2 = is_array($vertical);
				$this->assertTrue($loop_test_2);
				// Make sure each individual vertical has the expected properties:
				$loop_test_3 = isset($vertical['vertical_name']);
				$this->assertTrue($loop_test_3);
				$loop_test_4 = isset($vertical['group_id']);
				$this->assertTrue($loop_test_4);
				// Make sure the vertical properties are of the expected type:
				$this->assertInternalType('string', $vertical['vertical_name']);
				$this->assertInternalType('string', $vertical['group_id']);
			}
		}
	}
	
	
	protected function check_categories($result_object) {
		// Make sure the categories property is set as expected:
		$test1 = isset($result_object->categories);
		$this->assertTrue($test1);
		// Make sure the categories property is an array:
		$test2 = is_array($result_object->categories);
		$this->assertTrue($test2);
		// Make sure the categories property isn't empty:
		$test3 = empty($result_object->categories);
		$this->assertFalse($test3);
		// If the categories property is an array, loop through and check the values inside:
		if(is_array($result_object->categories)) {
			foreach($result_object->categories as $category) {
				// Make sure each individual category isn't empty:
				$loop_test_1 = empty($category);
				$this->assertFalse($loop_test_1);
				// Make sure each individual category is an array:
				$loop_test_2 = is_array($category);
				$this->assertTrue($loop_test_2);
				// Make sure each individual category has the expected properties:
				$this->assertTrue( isset($category['category_name']) );
				$this->assertTrue( isset($category['product_count']) );
				$this->assertTrue( isset($category['group_id']) );
				// Make sure the category properties are of the expected type:
				$this->assertInternalType('string', $category['category_name']);
				$this->assertInternalType('string', $category['product_count']);
				$this->assertInternalType('string', $category['group_id']);
			}
		}
		
		//error_log('check_categories $result_object = '.print_r($result_object,true));
	}
	
	
	/**
     * @dataProvider provider_verticals
     */
	public function test_ApiRequest_GetCategories_V1json($vertical_name) {
		$test_object = new Product_Search_Api();
		$args = array(
			'api_version' => 'v1',
			'search_type' => 'category',
			'return_type' => 'json',
			'category_search' => array(
				'vertical' => $vertical_name
			)
		);
		$test_object->set_up_request($args);
		$result_object = $test_object->make_request();
		// Make sure the resulting object is of the expected type:
		$this->assertInstanceOf('Search_Api_Result_V1json', $result_object);
		$this->assertInstanceOf('Search_Api_Result', $result_object);
		// Check the data of the result object:
		$this->check_categories($result_object);
	}
	
	
	/**
     * @dataProvider provider_verticals
     */
	public function test_ApiRequest_GetCategories_V1xml($vertical_name) {
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
     * @dataProvider provider_verticals
     */
	public function test_ApiRequest_GetCategories_V2json($vertical_name) {
		$test_object = new Product_Search_Api();
		$args = array(
			'api_version' => 'v2.1',
			'search_type' => 'category',
			'return_type' => 'json',
			'category_search' => array(
				'vertical' => $vertical_name
			)
		);
		$test_object->set_up_request($args);
		$result_object = $test_object->make_request();
		// Make sure the resulting object is of the expected type:
		$this->assertInstanceOf('Search_Api_Result_V2json', $result_object);
		$this->assertInstanceOf('Search_Api_Result', $result_object);
		// Check the data of the result object:
		$this->check_categories($result_object);
	}
	
	
	/**
     * @dataProvider provider_verticals
     */
	public function test_ApiRequest_GetCategories_V2xml($vertical_name) {
		$test_object = new Product_Search_Api();
		$args = array(
			'api_version' => 'v2.1',
			'search_type' => 'category',
			'return_type' => 'xml',
			'category_search' => array(
				'vertical' => $vertical_name
			)
		);
		$test_object->set_up_request($args);
		$result_object = $test_object->make_request();
		// Make sure the resulting object is of the expected type:
		$this->assertInstanceOf('Search_Api_Result_V2xml', $result_object);
		$this->assertInstanceOf('Search_Api_Result', $result_object);
		// Check the data of the result object:
		$this->check_categories($result_object);
	}
	
	
	/**
     * @dataProvider provider_verticals
     */
	public function test_ApiRequest_GetCategories_shortcut($vertical_name) {
		$test_object = new Product_Search_Api();
		$result_object = $test_object->get_categories($vertical_name);
		// Make sure the resulting object is of the expected type:
		$this->assertInstanceOf('Search_Api_Result', $result_object);
		// Check the data of the result object:
		$this->check_categories($result_object);
	}
	
	
	/*
	*	Return a list of vertical names that can be passed as input to other tests.
	*/
	public function provider_verticals() {
		$this->setUp();
		$test_object = new Product_Search_Api();
    	$result_object = $test_object->get_verticals();
    	// Set up a default array to return in the event that no verticals are found:
    	$return_array = array(
    		array('Appliances')
    	);
    	// Ignore the following verticals -- these are known to be invalid/empty
    	// but still returned by the Sears API for some reason.
    	$ignore_verticals = array(
    		'Local Services',
    		'PartsDirect Parts',
    		'Shipping'
    	);
    	// If we were able to look up the verticals, prepare to return it in a format
    	// that can be read as input by other tests.
    	if(isset($result_object->verticals) && is_array($result_object->verticals) ) {
    		$return_array = array();
    		foreach($result_object->verticals as $vertical) {
    			// Don't return any verticals that are in the $ignore_verticals array.
    			if(!in_array($vertical['vertical_name'], $ignore_verticals)) {
    				$return_array[] = array($vertical['vertical_name']);
    			}
    		}
    	} else {
    		// If we weren't able to look up the verticals, display an error.
    		error_log('ERROR: Failed to retrieve data in provider_verticals');
    		error_log('$result_object = '.print_r($result_object,true));
    	}
    	return $return_array;
	}

}