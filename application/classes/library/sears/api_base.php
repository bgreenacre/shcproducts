<?php defined('SHCP_PATH') OR die('No direct script access.');


class Sears_Api_Base {

	// Variables for holding plugin options (api key, etc.):
	protected $api_key = '';
	protected $store = '';

	function __construct(){
		$this->init();
	}
	
	function init() {
		$options = get_option('shcp_options');
		
		// Set plugin options:
		$this->api_key = $options['apikey'];
		$this->store = $options['store'];
    	//error_log('$options = '.print_r($options,true));
	}

}