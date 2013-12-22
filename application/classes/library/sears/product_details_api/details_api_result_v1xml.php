<?php defined('SHCP_PATH') OR die('No direct script access.');




class Details_Api_Result_V1xml implements Api_Result {

	/**
	* Raw API Response
	*
	* @var array
	*/
	protected $raw_response;
	
	
	/**
	* Product - this holds the standardized product data.
	*
	* @var array
	*/
	public $product;
	
	/**
	* __construct 
	*
	* @return void
	*/
	function __construct($raw_response){
		$this->raw_response = $raw_response;
	}
	
	
	/**
	* standardize_response 
	*
	* @return void
	*/
	function standardize_data() {
		$r = $this->raw_response;
		//$r->SoftHardProductDetails
	}
	
	
}