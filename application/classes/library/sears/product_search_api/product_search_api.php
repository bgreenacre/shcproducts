<?php defined('SHCP_PATH') OR die('No direct script access.');




class Product_Search_Api extends Sears_Api_Base {

	/**
	* Allowed Arguments (required):
	* These are the arguments that are allowed to be passed to set_up_request.
	* Additional optional args:
	*	'search_keyword' => (string)
	*
	* @var array
	*/
	protected $allowed_args = array(
		'api_version' => array(
			'v1',
			'v2.1'
		),
		'type' => array(
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
		// Merge the given args with the defaults:
		$args = array_merge($this->default_args, $args);
		
		// Validation is intended to protect against coding errors.
		// If it fails, the script will die with an error message.
		$this->_validate_args($args);
		
		// Args validated, save as property.
		$this->args = $args;
		
		// Build the URL:
		$this->build_url();
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
		
		// Initialize the result object depending on which version we're using:
		switch($this->args['api_version']) {
			case 'v1':
				// Coming soon.
				break;
			case 'v2.1':
				$this->result_object = new Search_Api_Result_V2($this->raw_response);
				break;
		}
		
		// If the result object was successfully created, standardize the response data.
		if(is_object($this->result_object) && $this->result_object instanceof Api_Result) {
			$this->result_object->standardize_data();
		}
		
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
				$this->build_url_v2point1();
				break;
		}
	}
	
	/**
	* build_url_v2point1
	* Build the URL for making v 2.1 API requests. 
	*
	* @return void
	*/
	function build_url_v2point1() {
		$url = 'http://api.developer.sears.com/v2.1/products/';
		$type = $this->args['type'];
		if($type == 'keyword') {
			$url .= 'search/keyword/';
			$url .= $this->store.'/';
			$url .= $this->args['return_type'].'?';
		} else {
			$url .= 'browse/';
			if($type == 'category') {
				$url .= 'categories/';
			} else if($type == 'product') {
				$url .= 'products/';
			}
			$url .= $this->store.'/';
			$url .= $this->args['return_type'].'?';
			$url .= 'category='.$this->args['search_keyword'];
		}
		$url .= '&apikey='.$this->api_key;
		$this->request_url = $url;
	}
	
	function build_url_v1() {
		// Coming soon.
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
	

}