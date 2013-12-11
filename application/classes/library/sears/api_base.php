<?php defined('SHCP_PATH') OR die('No direct script access.');


class Sears_Api_Base {

	/**
	* Variables for holding plugin options (api key, etc.):
	*/
	protected $api_key = '';
	protected $store = '';
	
	/**
	* Array of CURL options to set for the curl request.
	*
	* @var array
	*/
    protected $curl_options = array(
        CURLOPT_RETURNTRANSFER  => 1,
        CURLOPT_CONNECTTIMEOUT => 300,          // timeout on connect 
        CURLOPT_TIMEOUT        => 300,          // timeout on response
        //CURLOPT_HTTPHEADER      => array('X-SHCMMR-Client-Id: app_ui'),
    );
    
	/**
	* Request Success - set this to false in the event that a CURL request fails.
	*
	* @var bool
	*/
    protected $request_success = true;

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
	
	
	function make_request() {
		// Init the curl resource.
        $ch = curl_init($this->request_url);

        // Set connection options
        if ( ! curl_setopt_array($ch, $this->curl_options))
        {
            throw new Exception('Failed to set CURL options, check CURL documentation.');
        }

        // Get the response body
        $this->raw_response = curl_exec($ch);

        // Get the response information
        $this->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        

        if ($this->raw_response === FALSE)
        {
            $this->error = curl_error($ch);
        }

        // Close the connection
        curl_close($ch);

        if (isset($error))
        {
            // error_log('CURL ERROR: '.$error); // Enable this to see what went wrong.
            $this->request_success = false;
            return false;
        }
        
		if($this->args['return_type'] == 'json') {
			$this->raw_response = json_decode($this->raw_response);
		} else if($this->args['return_type'] == 'xml') {
			$this->raw_response = simplexml_load_string($this->raw_response);
		}
        return true;
	}

}