<?php defined('SHCP_PATH') OR die('No direct script access.');




class Search_Api_Result_Base {

	/**
	* Raw API Response
	*
	* @var array
	*/
	protected $raw_response;
	
	
	/**
	* API Url
	*
	* @var string
	*/
	public $api_url;
	
	/**
	* Verticals
	*
	* @var array
	*/
	public $verticals = array();
	
	/**
	* Categories - can contain either categories or subcategories depending on what was searched.
	*
	* @var array
	*/
	public $categories = array();
	
	/**
	* Filters
	*
	* @var array
	*/
	public $available_filters = array();
	
	/**
	* Products
	*
	* @var array
	*/
	public $products = array();
	
	/**
	* Product Count
	*
	* @var int
	*/
	public $product_count = false;
	
	/**
	* Ignore Filters
	*
	* @var array
	*/
	public $ignore_filters = array(
		'clearance',
		'delivery',
		'discount',
		'Flex Spending Eligible',
		'freeDelivery',
		'freeShipping',
		'has991',
		'international_shipping',
		'layAway',
		'new',
		'offer',
		'sale',
		'sears_international',
		'shipping',
		'shipVantage',
		'spuEligible',
		'storeOrigin',
		'trustedSeller'
	);
	
	
	/**
	* Set API URL to the given input.
	*/
	public function set_api_url($api_url) {
		$this->api_url = $api_url;
	}
	
	
	
	/**
	*	Validate the given product (from search API result). 
	*	Return true if ok, false otherwise.
	*/
	public function validate_product_search_result($product) {
		if(!is_array($product)) return false;
		if(!isset($product['price']) || empty($product['price'])) return false;
		if(!isset($product['image_url']) || empty($product['image_url'])) return false;
		return true;
	}

}