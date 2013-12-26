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
	public $product = array(
		'part_number' 		=> '',
		'cat_entry'			=> array(),
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
		'short_description' => '',
		'long_description' 	=> '',
		'brand'				=> '',
		'rating'			=> 0,
		'review_count'		=> 0,
		'price'				=> 0.00,
		'crossed_out_price'	=> 0.00,
		'savings'			=> 0.00,
		'product_line'		=> '', 		// 'soft' or 'hard'
		// Applicable to softlines only:
		'attributes'		=> array(),	// Example: array('Size','Color')
		'attribute_values'	=> array(), // Example: array( 'Size' => array('S','M','L','XL') )
		'color_swatches'	=> array(),
		/*
			color_swatches => array(
				'Red'	=> 'http://example.com/red-image.jpg',
				'Blue'	=> 'http://example.com/blue-image.jpg'
			)
		*/
	);
	
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
		$r = $this->raw_response->SoftHardProductDetails;
		// Set basic info:
		$this->product['part_number'] = $r->PartNumber;
		$this->product['brand'] = $r->BrandName;
		$this->product['main_image_url'] = $r->MainImageUrl;
		$this->product['all_image_urls'] = $r->ImageURLs->ImageURL;
		// Determine whether product is hardline or softline:
		if($r->ProductVariant == 'VARIANT') {
			$this->product['product_line'] = 'soft';
		} else {
			$this->product['product_line'] = 'hard';
		}
	}
	
	
}