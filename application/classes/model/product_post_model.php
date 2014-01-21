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
	* is_updated
	*
	* @var boolean
	*/
	public $is_updated = false;
	
	/**
	* is_deleted
	*
	* @var boolean
	*/
	public $is_deleted = false;
	
	/**
	* is_draft
	*
	* @var boolean
	*/
	public $is_draft = false;
	
	/**
	* no_action
	*
	* @var boolean
	*/
	public $no_action = false;
	
	
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
	
	/**
	* sync_from_api
	*
	* @return string
	*/
	function sync_from_api(){
		// Check for duplicates:
		if($this->post_id != $this->product_model->post_id) {
			// Duplicate detected!
			$r = 'Duplicate detected for part number '.$this->part_number.' - post ID\'s '.$this->post_id.' / '.$this->product_model->post_id;
			$deleted = wp_delete_post($this->post_id, true);
			if($deleted !== false) {
				$r .= ' - Deleted '.$this->post_id;
				$this->is_deleted = true;
			} else {
				$r .= ' - Could not delete '.$this->post_id;
				$this->no_action = true;
			}
			return $r;
		}
		// Update the product:
		$update_outcome = $this->product_model->update_product();
		// Update categories:
		$this->product_model->update_categories();
		if($update_outcome === true) {
			// Update finished. Check whether we should delete product.
			if($this->product_model->should_delete()) {
				//$update_outcome .= ' Deleting...';
				$deleted = wp_delete_post($this->post_id, true);
				if($deleted !== false) {
					$update_outcome = 'Deleted - '.$this->product_model->fail_reason;
					$this->is_deleted = true;
				} else {
					$update_outcome = 'Could not delete - '.$this->product_model->fail_reason;
					$this->no_action = true;
				}
			} else if($this->product_model->should_set_draft()) {
				$update_outcome = 'Set to draft - '.$this->product_model->fail_reason;
				$this->is_draft = true;
			} else {
				$this->is_updated = true;
			}
		} else {
			if($this->product_model->should_delete()) {
				$deleted = wp_delete_post($this->post_id, true);
				if($deleted !== false) {
					$update_outcome = 'Deleted - '.$this->product_model->fail_reason;
					$this->is_deleted = true;
				} else {
					$update_outcome = 'Could not delete - '.$this->product_model->fail_reason;
					$this->no_action = true;
				}
			} else if($this->product_model->should_set_draft()) {
				$update_outcome = 'Could not set to draft - '.$this->product_model->fail_reason;
				$this->no_action = true;
			} else {
				$update_outcome = 'No action taken - '.$update_outcome;
				$this->no_action = true;
			}
		}
		return $update_outcome;
	}
	
	
}