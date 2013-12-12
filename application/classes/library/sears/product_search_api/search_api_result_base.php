<?php defined('SHCP_PATH') OR die('No direct script access.');




class Search_Api_Result_Base {

	/**
	* Raw API Response
	*
	* @var array
	*/
	protected $raw_response;
	
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
	* Product Count
	*
	* @var int
	*/
	public $product_count = 0;
	
	/**
	* Ignore Filters
	*
	* @var array
	*/
	public $ignore_filters = array(
		'clearance',
		'discount',
		'freeShipping',
		'freeDelivery',
		'has991',
		'layAway',
		'international_shipping',
		'new',
		'offer',
		'sale',
		'shipVantage',
		'spuEligible',
		'storeOrigin',
		'sears_international'
	);

}