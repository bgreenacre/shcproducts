<?php defined('SHCP_PATH') OR die('No direct script access.');




class Product_Search_Api extends Sears_Api_Base {

	/**
	* Allowed Arguments (required):
	* These are the arguments that are allowed to be passed to set_up_request.
	* Additional optional args:
	*	'search_keyword' => (string) - v2 allows vertical/category/subcategory to be separated by |
	*	'category_search' => (array)
	*				'vertical' => (string),
	*				'category' => (string),
	*				'subcategory' => (string)
	*	'filter' => (array)
					(filter name) => (filter value)
	*	'start_index' => (int) - defaults to 1
	*	'end_index'	  => (int) - defaults to 25
	*
	* @var array
	*/
	protected $allowed_args = array(
		'api_version' => array(
			'v1',
			'v2.1'
		),
		'search_type' => array(
			'keyword',
			'category',
			'product'
		),
		'return_type' => array(
			'xml',
			'json'
		)
	);
	
	/**
	* Default Arguments:
	* These arguments will be used by default if none are passed to set_up_request.
	*
	* @var array
	*/
	protected $default_args = array(
		'api_version' => 'v2.1',
		'return_type' => 'json',
	);
	
	/**
	* Arguments:
	* These are the actual arguments to be used for the request.
	*
	* @var array
	*/
	protected $args = array();
	
	/**
	* Request URL
	*
	* @var string
	*/
	protected $request_url = '';


	/**
	* __construct 
	*
	* @return void
	*/
	function __construct(){
		parent::__construct();
	}
	
	/**
	* set_up_request 
	*
	* @return void
	*/
	function set_up_request($args) {
		if(!empty($this->args)) {
			// If any args are already set, merge with these instead of the defaults:
			$args = array_merge($this->args, $args);
		} else {
			// Otherwise, merge the given args with the defaults:
			$args = array_merge($this->default_args, $args);
		}
		
		// Validation is intended to protect against coding errors.
		// If it fails, the script will die with an error message.
		$this->_validate_args($args);
		
		// Args validated, save as property.
		$this->args = $args;
		
		// Build the URL:
		$this->build_url();
	}
	
	/**
	* set_start_end_index 
	*
	* @return void
	*/
	function set_start_end_index($start, $end) {
		$this->args['start_index'] = $start;
		$this->args['end_index'] = $end;
	}
	
	/**
	* make_request 
	*
	* @return void
	*/
	function make_request() {
		// The line below will make the CURL request and save the output to
		// $this->raw_response (with any xml/json already decoded)
		parent::make_request();
		
		// Initialize the result object depending on which version and response type we're using.
		if($this->args['api_version'] == 'v1') {
		
		} else if($this->args['api_version'] == 'v2.1') {
		
			if($this->args['return_type'] == 'xml') 
			{ // Version 2 XML:
				$this->result_object = new Search_Api_Result_V2xml($this->raw_response);
				
			} else if($this->args['return_type'] == 'json') 
			{ // Version 2 JSON:
				$this->result_object = new Search_Api_Result_V2json($this->raw_response);
			}
		
		}
		
		// If the result object was successfully created, standardize the response data.
		if(is_object($this->result_object) && $this->result_object instanceof Api_Result) {
			$this->result_object->standardize_data();
		}
		
		// For debugging:
		error_log('Raw Response: '.print_r($this->raw_response,true));
		error_log('Result Object: '.print_r($this->result_object,true));
		
		return $this->result_object;
	}
	
	/**
	* build_url 
	*
	* @return void
	*/
	function build_url() {
		switch($this->args['api_version']) {
			case 'v1':
				$this->build_url_v1();
				break;
			case 'v2.1':
				$this->build_url_v2();
				break;
		}
	}
	
	/**
	* Build the URL for making v 2.1 API requests. 
	*
	* @return void
	*/
	function build_url_v2() {	
		$url = 'http://api.developer.sears.com/v2.1/products/';
		$url_params = array();
		$type = $this->args['search_type'];
		if($type == 'keyword') {
			$url .= 'search/';
			$url .= $this->store.'/';
			$url .= $this->args['return_type'].'/';
			$url .= 'keyword/';
			if(isset($this->args['search_keyword']) && !empty($this->args['search_keyword'])) {
				$url .= urlencode($this->args['search_keyword']);
			}
		} else {
			$url .= 'browse/';
			if($type == 'category') {
				$url .= 'categories/';
			} else if($type == 'product') {
				$url .= 'products/';
			}
			$url .= $this->store.'/';
			$url .= $this->args['return_type'];
			// Basic URL structure complete. Begin adding query string parameters.
			if(isset($this->args['search_keyword']) && !empty($this->args['search_keyword'])) {
				$url_params['category'] = $this->args['search_keyword'];
			} else if(isset($this->args['category_search']) && !empty($this->args['category_search'])) {
				$url_params['category'] = stripslashes(implode('|',$this->args['category_search']));
			} else {
				$url_params['category'] = '';
			}
		}
		if(isset($this->args['filter']) && is_array($this->args['filter'])) {
			foreach($this->args['filter'] as $filter_name => $filter_value) {
				if(!empty($filter_name) && !empty($filter_value)) {
					$filter = $filter_name.'|'.$filter_value;
				}
				$url_params['filter'] = $filter;
			}
		}
		if(isset($this->args['start_index']) && isset($this->args['end_index'])) {
			$url_params['startIndex'] = $this->args['start_index'];
			$url_params['endIndex'] = $this->args['end_index'];
		}
		$url_params['apikey'] = $this->api_key;
		$url .= '?'.http_build_query($url_params);
		$this->request_url = $url;
	}
	
	/**
	* Build the URL for making v1 API requests. 
	*
	* @return void
	*/
	function build_url_v1() {
		$url = 'http://webservices.sears.com/shcapi/productsearch?';
		$url_params = array(
			'appID' => $this->app_id,
			'authID' => $this->auth_id,
			'apikey' => $this->api_key,
			'contentType' => $this->args['return_type'],
			'store' => $this->store,
		);
		if(isset($this->args['category_search']['vertical'])) {
			$url_params['verticalName'] = $this->args['category_search']['vertical'];
			$url_params['searchType'] = 'vertical';
		}
		if(isset($this->args['category_search']['category'])) {
			$url_params['categoryName'] = $this->args['category_search']['category'];
			$url_params['searchType'] = 'category';
		}
		if(isset($this->args['category_search']['subcategory'])) {
			$url_params['subCategoryName'] = $this->args['category_search']['subcategory'];
			$url_params['searchType'] = 'subcategory';
		}
		if(isset($this->args['start_index']) && isset($this->args['end_index'])) {
			$url_params['startIndex'] = $this->args['start_index'];
			$url_params['endIndex'] = $this->args['end_index'];
		}
		foreach($url_params as $key => $url_param) {
			$url_params[$key] = stripslashes($url_param);
		}
		$url .= http_build_query($url_params);
		$this->request_url = $url;
	}
	
	
	/**
	* _validate_args 
	*
	* @return void
	*/
	private function _validate_args($args) {
		$valid = true;
		$msg = '';
		if(empty($args) || !is_array($args)) {
			$msg = 'Args was empty or not an array. $args = '.print_r($args,true);
			$valid = false;
		} else {
			foreach($this->allowed_args as $arg_name => $allowed_values) {
				if(!isset($args[$arg_name])) {
					$msg .= "Required arg '$arg_name' was not set.\n";
					$valid = false;
				} else {
					if(!in_array($args[$arg_name], $allowed_values)) {
						$msg .= "Invalid arg given for '$arg_name' - valid args are ".implode(',',$allowed_values);
						$valid = false;
					}
				}
			}
		}
		if(!$valid) {
			error_log('Fatal error: Arg validation failed in Product_Search_Api. Reason: '.$msg);
			die();
		}
	}
	
	
	/***********************************
	*   Shortcut functions 
	************************************/
	
	/**
	* Get Verticals 
	*
	* @return void
	*/
	public function get_verticals() {
		$args = array(
			'api_version' => 'v2.1',
			'search_type' => 'category',
			'return_type' => 'xml'
		);

		$this->set_up_request($args);
		return $this->make_request();
	}
	

	/**
	* Get Categories 
	*
	* @return void
	*/
	public function get_categories($vertical_name) {
		$args = array(
			'api_version' => 'v2.1',
			'search_type' => 'category',
			'return_type' => 'json',
			'category_search' => array(
				'vertical' => $vertical_name
			)
		);

		$this->set_up_request($args);
		return $this->make_request();
	}
	
	
	/**
	* Get Subcategories 
	*
	* @return void
	*/
	public function get_subcategories($vertical_name, $category_name) {
		// Note - using API v1 for this because v2 seems to be unreliable
		// when it comes to delivering subcategories (sometimes).
		$args = array(
			'api_version' => 'v1',
			'search_type' => 'category',
			'return_type' => 'json',
			'category_search' => array(
				'vertical' => $vertical_name,
				'category' => $category_name
			)
		);

		$this->set_up_request($args);
		return $this->make_request();
	}
	
	/**
	* Get Available Filters 
	*
	* @return void
	*/
	public function get_available_filters($vertical_name, $category_name, $subcategory_name) {
		// To use API v1, use:
		// 		$args['api_version'] = 'v1';
		// 		$args['search_type'] = 'category';
		// To use API v2, use:
		// 		$args['api_version'] = 'v2.1';
		// 		$args['search_type'] = 'product';
		$args = array(
			'api_version' => 'v1',
			'search_type' => 'category',
			'return_type' => 'json',
			'category_search' => array(
				'vertical' => $vertical_name,
				'category' => $category_name,
				'subcategory' => $subcategory_name
			)
		);
		error_log('get_available_filters - $args = '.print_r($args,true));
		
		$this->set_up_request($args);
		return $this->make_request();
	}
	
	/**
	* Get Products 
	*
	* @return void
	*/
	public function get_products($vertical_name, $category_name, $subcategory_name, $filter=false, $start_end=false) {
		$args = array(
			'api_version' => 'v1',
			'search_type' => 'product',
			'return_type' => 'json',
			'category_search' => array(
				'vertical' => $vertical_name,
				'category' => $category_name,
				'subcategory' => $subcategory_name
			),
			'filter' => $filter
		);
		if(is_array($start_end) && isset($start_end['start_index']) && isset($start_end['end_index']) ) {
			$args['start_index'] = $start_end['start_index'];
			$args['end_index'] = $start_end['end_index'];
		}

		$this->set_up_request($args);
		return $this->make_request();
	}
	
}