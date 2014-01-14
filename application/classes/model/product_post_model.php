<?php defined('SHCP_PATH') OR die('No direct script access.');




class Product_Post_Model {

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
	* Product Model
	*
	* @var Product_Model object
	*/
	public $product_model;
	
	
	/**
	* __construct 
	*
	* @return void
	*/
	function __construct($post_id){
		$this->post_id = $post_id;
		$this->initialize_post();
	}
	
	
	/**
	* initialize_post 
	*
	* @return void
	*/
	function initialize_post(){
		$this->part_number = get_post_meta($this->post_id, 'part_number', true);
		if(!empty($this->part_number)) {
			$this->product_model = new Product_Model($this->part_number);
			return;
		}
		// For backwards compatibility:
		$this->part_number = get_post_meta($this->post_id, 'partnumber', true);
		if(!empty($this->part_number)) {
			$this->product_model = new Product_Model($this->part_number);
		}
	}
	
	
	/**
	* is_softline
	*
	* @return Boolean
	*/
	function is_softline(){
		return $this->product_model->is_softline();
	}
	
	
	/**
	* get_image_url
	*
	* @return string
	*/
	function get_image_url(){
		return $this->product_model->product['main_image_url'];
	}
	
	
	
}