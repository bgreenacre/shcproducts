<?php

/*
	Unit tests for the SHC products plugin.
	
*/

require_once(dirname(__FILE__) . '/../shcproducts.php');


class ApiTest extends WP_UnitTestCase {

	function setUp() {
 		parent::setUp();
// 		require_once('../../../../wordpress/wp-load.php');
// 		require_once('../shcproducts.php');
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

}