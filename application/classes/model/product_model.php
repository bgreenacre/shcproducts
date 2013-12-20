<?php defined('SHCP_PATH') OR die('No direct script access.');




class Product_Model {

	/**
	* Part Number
	*
	* @var string
	*/
	public $part_number = '';
	
	/**
	* Post
	*
	* @var string
	*/
	public $post = '';


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
		//$myquery = new WP_Query( "post_type=player&meta_key=player_team&meta_value=$teamname&order=ASC" );
		$query_args = array(
			'post_type' => 'shcproduct',
			'meta_key' => 'partnumber',
			'meta_value' => $this->part_number
		);
		$init_query = new WP_Query($query_args);
		
		$this->post = $init_query->post;
		
		//$this->query = $init_query;
	}
	
	
	/**
	* already_imported 
	*
	* @return void
	*/
	function already_imported(){
		if( empty($this->post) ) {
			return false;
		} else {
			return true;
		}
	}

}