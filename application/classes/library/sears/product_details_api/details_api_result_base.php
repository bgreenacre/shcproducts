<?php defined('SHCP_PATH') OR die('No direct script access.');




class Details_Api_Result_Base {

	/**
	* Raw API Response
	*
	* @var array
	*/
	protected $raw_response;
	
	
	/**
	* The API URL
	*
	* @var string
	*/
	public $api_url;
	
	
	/**
	* API Response Code
	*
	* @var string
	*/
	public $api_response_code;
	
	
	/**
	* API Response Message
	*
	* @var string
	*/
	public $api_response_message;
	
	
	/**
	* Error message
	*
	* @var string
	*/
	public $error_message = '';
	
	
	/**
	* Should delete - set to true if existing product should be deleted.
	*
	* @var boolean
	*/
	public $should_delete = false;
	
	/**
	* Should set draft - set to true if existing product should be set to draft.
	*
	* @var boolean
	*/
	public $should_set_draft = false;
	
	
	/**
	* Product - this holds the standardized product data.
	*
	* @var array
	*/
	public $product = array(
		'api_url' 			=> '',
		'part_number' 		=> '',
		'cat_entry'			=> '',
		/*	
			// Hard lines example - no variants
			cat_entry 		=> 'ABC123'
			
			// Soft lines example - has variants
			cat_entry		=> array(
				'ABC123' => array(
					'Color' => 'Red',
					'Size'	=> 'XL',
					'price' => 0.00
				),
				'XYZ123' => array(
					'Color' => 'Blue',
					'Size'	=> 'M',
					'price' => 0.00
				)
			);
		*/
		'main_image_url'	=> '',
		'all_image_urls'	=> array(),
		'name' 				=> '',
		'short_description' => '',
		'long_description' 	=> '',
		'brand'				=> '',
		'rating'			=> 0,
		'review_count'		=> 0,
		'in_stock'			=> 0,
		'web_status'		=> 0,
		'price'				=> 0.00,
		'crossed_out_price'	=> 0.00,
		'savings'			=> 0.00,
		'product_line'		=> '', 		// 'soft' or 'hard'
		'specifications' => array(),
		/*
			specifications => array(
				'Header 1' => array(
					'Specification Name' => 'Specification Value',
					'Specification Name' => 'Specification Value'
				),
				'Header 2' => array( 
					// etc. etc. etc.
			)
		*/
		// Applicable to softlines only:
		'attributes'		=> array(),	// Example: array('Size','Color')
		'attribute_values'	=> array(), 
		/*
			[Women's] => Array
                (
                    [Size] => Array
                        (
                            [0] => L
                            [1] => M
                            [2] => S
                            [3] => XL
                        )

                    [Color] => Array
                        (
                            [0] => Green
                            [1] => Pink
                            [2] => Red
                        )

                )
		*/
		'color_swatches'	=> array()
		/*
			color_swatches => array(
				'Red'	=> 'http://example.com/red-image.jpg',
				'Blue'	=> 'http://example.com/blue-image.jpg'
			)
		*/
	);
	
	
	/**
	* Is Softline
	*
	* @return boolean
	*/
	function is_softline() {
		if($this->product['product_line'] == 'soft') {
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	* Is Valid Product
	*
	* @return boolean
	*/
	function is_valid_product() {
		$msg = '';
		$is_valid = true;
		if(!empty($this->error_message)) {
			// If an error message was set elsewhere, it's not a valid product.
			$is_valid = false;
		}
		// Check whether it was even a valid API repsonse before checking any of the fields:
		if($this->api_response_code != 0) {
			$msg .= $this->api_response_message.' (API response code '.$this->api_response_code.') ';
			$is_valid = false;
			if($this->api_response_code == 1) {
				// Typically a "This product is no longer available" response.
				$this->should_delete = true;
			}
		} else {
			if( $this->product['in_stock'] == 0) {
				$msg = 'Product is not currently in stock. ';
				$is_valid = false;
				//$this->should_delete = true;
				$this->should_set_draft = true;
			}
			if( empty($this->product['main_image_url']) ) {
				$msg = 'Main image URL was missing. ';
				$is_valid = false;
				$this->should_delete = true;
			}
			if(strpos($this->product['main_image_url'], 'src=http%') !== FALSE) {
				$msg = 'Invalid src in image URL. ';
				$is_valid = false;
				$this->should_delete = true;
			}
			if(strpos($this->product['main_image_url'], 'shld.net') === FALSE) {
				$msg = 'Invalid external image URL. ';
				$is_valid = false;
				$this->should_delete = true;
			}
			if(!is_numeric($this->product['price'])) {
				// Don't enforce this for products that have a "range" of prices, e.g. "From $24.00 To $26.00"
				if (strpos($this->product['price'], 'From') === false) {
					$msg .= 'Price is not numeric. ';
					$is_valid = false;
					$this->should_delete = true;
				}
			}
			if( $this->product['price'] == '0.00') {
				$msg = 'Price cannot be 0.00. ';
				$is_valid = false;
				$this->should_delete = true;
			}
			if( empty($this->product['cat_entry']) ) {
				$msg = 'CatEntryId is empty. ';
				$is_valid = false;
				$this->should_delete = true;
			}
			if( $this->product['web_status'] == 0) {
				$msg = 'WebStatus = 0, product not available. ';
				$is_valid = false;
				$this->should_delete = true;
			}
		}
		if(!empty($msg)) $this->error_message .= $msg;
		return $is_valid;
	}


}