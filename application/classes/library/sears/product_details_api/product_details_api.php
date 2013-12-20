<?php defined('SHCP_PATH') OR die('No direct script access.');




class Product_Details_Api extends Sears_Api_Base {

	/**
	* Allowed Arguments (required):
	* These are the arguments that are required to be passed to set_up_request,
	* and possible choices for those.
	*
	* Other arguments:
	*	part_number => (string)
	*
	* @var array
	*/
	protected $allowed_args = array(
		'api_version' => array(
			'v1',
			'v2.1'
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
	* make_request 
	*
	* @return void
	*/
	function make_request() {
		// The line below will make the CURL request and save the output to
		// $this->raw_response (with any xml/json already decoded)
		parent::make_request();
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
	* Build the URL for making v1 API requests. 
	*
	* @return void
	*/
	function build_url_v1() {
		$url = 'http://webservices.sears.com/shcapi/productdetails?';
		$url_params = array();
		$url_params['appID'] = $this->app_id;
		$url_params['authID'] = $this->auth_id;
		$url_params['apikey'] = $this->api_key;
		$url_params['contentType'] = $this->args['return_type'];
		$url_params['store'] = $this->store;
		$url_params['showSpec'] = true;
		$url_params['partNumber'] = $this->args['part_number'];
		$url .= http_build_query($url_params);
		$this->request_url = $url;
	}
	
	
	/**
	* Build the URL for making v 2.1 API requests. 
	*
	* @return void
	*/
	function build_url_v2() {
	
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
			error_log('Fatal error: Arg validation failed in Product_Details_Api. Reason: '.$msg);
			die();
		}
	}
	

	/**
	* get_product
	*/
	function get_product($part_number) {
		$args = array(
			'api_version' => 'v1',
			'return_type' => 'xml',
			'part_number' => $part_number
		);
		$this->set_up_request($args);
		return $this->make_request();
	}



}