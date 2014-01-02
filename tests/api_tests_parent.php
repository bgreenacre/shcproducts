<?php

/*
	Unit tests for the SHC products plugin.
	
*/


class SHC_API_Test_Parent extends WP_UnitTestCase {

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
		//error_log('Peak memory usage: '.memory_get_peak_usage());
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
    
    
    /*
    	Randomly reduce the provided parameters to the specified number.
    	Allows a random sample to be taken.
    */
	private function _reduce_parameters($parameters_array, $max = 100) {
		if( count($parameters_array) <= $max ) return $parameters_array;
		$return_array = array();
		for($i = 0; $i < $max; $i++) {
			$chosen_key = array_rand($parameters_array);
			$return_array[] = $parameters_array[$chosen_key];
			unset($parameters_array[$chosen_key]);
		}
		return $return_array;
	}

	
	public function check_verticals($result_object) {
		// Make sure the verticals property is set as expected:
		$this->assertTrue( isset($result_object->verticals) );
		// Make sure the verticals property isn't empty:
		$this->assertFalse( empty($result_object->verticals) );
		// Make sure the verticals property is an array:
		$this->assertTrue( is_array($result_object->verticals) );
		// Loop through and check the values inside:
		foreach($result_object->verticals as $vertical) {
			// Make sure each individual vertical isn't empty:
			$this->assertFalse( empty($vertical) );
			// Make sure each individual vertical is an arary:
			$this->assertTrue( is_array($vertical) );
			// Make sure each individual vertical has the expected properties:
			$this->assertTrue( isset($vertical['vertical_name']) );
			$this->assertTrue( isset($vertical['group_id']) );
			// Make sure the vertical properties are of the expected type:
			$this->assertInternalType('string', $vertical['vertical_name']);
			$this->assertInternalType('string', $vertical['group_id']);
		}
	}
	
	
	public function check_categories($result_object) {
		// Make sure the categories property is set as expected:
		$this->assertTrue( isset($result_object->categories) );
		// Make sure the categories property is an array:
		$this->assertTrue( is_array($result_object->categories) );
		// In rare cases, there may be categories that come back as empty from the API.
		// Mark the test incomplete for these.
		if( empty($result_object->categories) ) {
			$this->markTestIncomplete('No categories were found. API URL = '.$result_object->api_url);
			return;
		}
		// Loop through and check the values inside:
		foreach($result_object->categories as $category) {
			// Make sure each individual category isn't empty:
			$this->assertFalse( empty($category) );
			// Make sure each individual category is an array:
			$this->assertTrue( is_array($category) );
			// Make sure each individual category has the expected properties:
			$this->assertTrue( isset($category['category_name']) );
			$this->assertTrue( isset($category['product_count']) );
			$this->assertTrue( isset($category['group_id']) );
			// Make sure the category properties are of the expected type:
			$this->assertInternalType('string', $category['category_name']);
			$this->assertInternalType('string', $category['product_count']);
			$this->assertInternalType('string', $category['group_id']);
		}
		//error_log('check_categories $result_object = '.print_r($result_object,true));
	}
	
	
	public function check_filters($result_object) {
		// Make sure the available_filters property is set as expected:
		$this->assertTrue( isset($result_object->available_filters) );
		// Make sure the available_filters property is an array:
		$this->assertInternalType('array', $result_object->available_filters);
		// Some categories will have no available filters --
		// this is just the nature of what we get back from the API.
		// For the purposes of unit testing, we won't consider it an error
		// and will not examine the data any further if this is the case.
		if(empty($result_object->available_filters)) {
			$this->markTestIncomplete('No filters are available for this category. API URL = '.$result_object->api_url);
			return;
		}
		// Loop through and check the values inside:
		foreach($result_object->available_filters as $filter_name => $filter_values) {
			$this->assertInternalType('string', $filter_name);
			$this->assertInternalType('array', $filter_values);
			$this->assertFalse( empty($filter_values) );
		}
	}
	
	
	public function check_product_search_results($result_object) {
		$message = ' API URL = '.$result_object->api_url;
		// Make sure the products property is set as expected:
		$this->assertTrue( isset($result_object->products), '$result_object->products is not set.'.$message );
		// Make sure the products property is an array:
		$this->assertInternalType('array', $result_object->products, '$result_object->products is not an array.'.$message);
		// Make sure the products property isn't empty:
		$this->assertFalse( empty($result_object->products), '$result_object->products is empty.'.$message );
		// Loop through and check the values:
		foreach($result_object->products as $key => $product) {
			// Each individual product should also be an array:
			$this->assertInternalType('array', $product);
			// All of the following things should be set:
			$this->assertTrue( isset($product['part_number']), 'Part number not set for product '.$key.'.'.$message );
			$this->assertTrue( isset($product['name']), 'Product name not set for product '.$key.'.'.$message );
			$this->assertTrue( isset($product['brand']), 'Brand not set for product '.$key.'.'.$message );
			$this->assertTrue( isset($product['image_url']), 'Image URL not set for product '.$key.'.'.$message );
			$this->assertTrue( isset($product['rating']), 'Rating not set for product '.$key.'.'.$message );
			$this->assertTrue( isset($product['review_count']), 'Review Count not set for product '.$key.'.'.$message );
			$this->assertTrue( isset($product['price']), 'Price not set for product '.$key.'.'.$message );
			$this->assertTrue( isset($product['has_variants']), 'has_variants not set for product '.$key.'.'.$message );
			// The following fields are mandatory:
			$this->assertFalse( empty($product['part_number']), 'Part number empty for product '.$key.'.'.$message );
			$this->assertFalse( empty($product['name']), 'Product name empty for product '.$key.'.'.$message );
			$this->assertFalse( empty($product['image_url']), 'Image URL empty for product '.$key.'.'.$message );
			$this->assertFalse( empty($product['price']), 'Price empty for product '.$key.'.'.$message );
			// The array key should match the part number field:
			$this->assertEquals( $key, $product['part_number'], 'Array key does not match part number.'.$message );
		}
	}
	
		

	
	/*
	*	Return a list of vertical names that can be passed as input to other tests.
	*/
	private function _generate_verticals() {
		if(isset($this->verticals) && !empty($this->verticals)) return $this->verticals;
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
    	$this->verticals = $return_array;
    	//error_log('verticals = '.var_export($return_array,true));
    	return $return_array;
	}
	
	
	
	/*
	*	Generate an array of vertical names & category names that can be passed as input to other tests.
	*/
	private function _generate_categories($subcategories = false) {
		if($subcategories) {
			if(isset($this->subcategories) && !empty($this->subcategories)) return $this->subcategories;
		} else {
			if(isset($this->categories) && !empty($this->categories)) return $this->categories;
		}
		$return_array = array();
		$verticals_array = $this->provider_verticals();
		foreach($verticals_array as $key => $value) {
			//error_log('provider_categories value = '.print_r($value,true));
			$vertical_name = $value[0];
			$test_object = new Product_Search_Api();
			$result_object = $test_object->get_categories($vertical_name);
			if(isset($result_object->categories) && is_array($result_object->categories) && !empty($result_object->categories)) {
				foreach($result_object->categories as $category) {
					if($subcategories) {
						$sub_object = new Product_Search_Api();
						$sub_result = $sub_object->get_subcategories($vertical_name, $category['category_name']);
						if(isset($sub_result->categories) && is_array($sub_result->categories) && !empty($sub_result->categories)) {
							$subcats = $sub_result->categories;
							foreach($subcats as $subcat) {
								$return_array[] = array( $vertical_name, $category['category_name'], $subcat['category_name'] );
							}
						}
					} else {
						$return_array[] = array( $vertical_name, $category['category_name'] );
					}
				}
			} else {
				error_log('ERROR: Failed to retrieve data in provider_categories for '.$value[0]);
				error_log('$result_object = '.print_r($result_object,true));
			}
		}
		//error_log('provider_categories = '.print_r($return_array,true));
		if($subcategories) {
			$this->subcategories = $return_array;
			error_log('provider_subcategories = '.var_export($return_array,true));
		} else {
			$this->categories = $return_array;
			error_log('provider_categories = '.var_export($return_array,true));
		}
		return $return_array;
	}	


	/*
	*	Return an array of verticals to pass as input to other tests.
	*/
	public function provider_verticals() {
		// Use the code below to retrieve this input from the API:
		return $this->_generate_verticals();
	}


	/*
	*	Return an array of verticals and categories to pass as input to other tests.
	*/
	public function provider_categories() {
		include('input_categories.php');
		$categories = $this->_reduce_parameters($categories);
		return $categories;
		// Use the code below to regenerate this input from the API:
		//return $this->generate_categories(false);
	}

	/*
	*	Return an array of verticals, categories, and subcategories to pass as input to other tests.
	*/
	public function provider_subcategories() {
		include('input_subcategories.php');
		$subcategories = $this->_reduce_parameters($subcategories);
		return $subcategories;
		// Use the code below to regenerate this input from the API:
		//return $this->generate_categories(true);
	}


}

?>