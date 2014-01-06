<?php defined('SHCP_PATH') OR die('No direct script access.');




class Product_Model {

	/**
	* Part Number
	*
	* @var string
	*/
	public $part_number = '';
	
	/**
	* Post ID
	*
	* @var string
	*/
	public $post_id = '';
	
	
	/**
	* Post
	*
	* @var WP_Post Object
	*/
	public $post = '';
	
	
	/**
	* Post Array - for storing data prior to insert / update
	*
	* @var array
	*/
	public $post_array = array();
	
	
	/**
	* Already Imported
	*
	* @var boolean
	*/
	public $already_imported = false;
	
	
	/**
	* Product
	*
	* @var array
	*/
	public $product = array();


	/**
	* __construct 
	*
	* @return void
	*/
	function __construct($part_number){
		$this->part_number = $part_number;
		$this->initialize_product();
	}
	
	
	/**
	* initialize_product 
	*
	* @return void
	*/
	function initialize_product(){
		// If the product has already been imported, this will initialize the post.
		$query_args = array(
			'post_type' => 'shcproduct',
			'meta_key' => 'partnumber',
			'meta_value' => $this->part_number
		);
		$init_query = new WP_Query($query_args);
		
		$this->post = $init_query->post;
		
		if( empty($this->post) ) {
			$this->already_imported = false;
			$this->look_up_product();
		} else {
			$this->already_imported = true;
			$this->product = get_post_meta($this->post->ID, 'product_detail', true);
		}
	}
	
	
	/**
	* look_up_product
	*
	* @return void
	*/
	function look_up_product() {
		// Look up the product using the API.
		$obj = new Product_Details_Api();
		$r = $obj->get_product($this->part_number);
		$this->product = $r->product;
	}
	
	
	
	/**
	* convert_product_to_post
	*
	* @return void
	*/
	function convert_product_to_post(){
		$this->post_array = array();
		$this->post_array['post_title'] = $this->product[''];
		$this->post_array['post_content'] = $this->product['short_description'];
		if(!empty($this->product['long_description'])) {
			$this->post_array['post_content'] .= $this->product['long_description'];
		}
	}
	
	
	/**
	* import_product
	*
	* @return void
	*/
	function import_product() {
		// Import the product (save it as a new post)
	}
	
	
	/**
	* already_imported 
	*
	* @return void
	*/
	function already_imported(){
		return $this->already_imported;
	}

}