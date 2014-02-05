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
	* Post Meta Array - for storing data prior to insert / update
	*
	* @var array
	*/
	public $post_meta_array = array();
	
	
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
	* Fail Reason
	*
	* @var string
	*/
	public $fail_reason = '';


	/**
	* Should delete - set to true if existing product should be deleted.
	*
	* @var boolean
	*/
	public $should_delete = false;


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
			'meta_key' => 'part_number',
			'meta_value' => $this->part_number,
			'post_status' => array('publish', 'draft')
		);
		$init_query = new WP_Query($query_args);
		
		$this->post = $init_query->post;
		
		if( empty($this->post) ) {
			$this->already_imported = false;
		} else {
			$this->already_imported = true;
			$this->post_id = $this->post->ID;
			$this->product = get_post_meta($this->post->ID, 'product_detail', true);
			return;
		}
		
		// If the data is detected in the old format, try to convert/update it:
		$query_args = array(
			'post_type' => 'shcproduct',
			'meta_key' => 'partnumber',
			'meta_value' => $this->part_number,
			'post_status' => array('publish', 'draft')
		);
		
		$init_query = new WP_Query($query_args);
		
		$this->post = $init_query->post;
		
		if( !empty($this->post) ) {
			$this->already_imported = true;
			$this->post_id = $this->post->ID;
			$this->update_product();
		} else {
			// Post not found...
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
		if(!$r) {
			$this->fail_reason = 'API request failed.';
			return false; // The API request failed. Nothing we can do.
		}
		if($r->is_valid_product()) {
			$this->product = $r->product;
		} else {
			$this->product = $r->product;
			$this->fail_reason = $r->error_message;
		}
		$this->should_delete = $r->should_delete;
		$this->should_set_draft = $r->should_set_draft;
	}
	
	
	
	/**
	* convert_product_to_post
	*
	* @return void
	*/
	function convert_product_to_post(){
		$this->post_array = array();
		$this->post_array['post_title'] = html_entity_decode($this->product['name']);
		$this->post_array['post_content'] = $this->product['short_description'];
		if(!empty($this->product['long_description'])) {
			$this->post_array['post_content'] .= '<br/>'.$this->product['long_description'];
		}
		$this->post_array['post_content'] = html_entity_decode($this->post_array['post_content']);
		$this->post_array['post_type'] = 'shcproduct';
		if($this->should_set_draft) {
			$this->post_array['post_status'] = 'draft';
		} else {
			$this->post_array['post_status'] = 'publish';
		}
		$this->post_array['post_modified'] = date('Y-m-d H:i:s');
		$this->post_array['post_date_gmt'] = gmdate('Y-m-d H:i:s');
		
		$this->post_meta_array['rating'] = $this->product['rating'];
		$this->post_meta_array['displayprice'] = $this->product['price'];
	}
	
	
	/**
	* import_product
	*
	* @return void
	*/
	function import_product() {
		// Import the product (save it as a new post)
		if($this->already_imported()) {
			return $this->fail_reason = 'Product has already been imported.';
		}
		$this->look_up_product();
		if(empty($this->product) || !empty($this->fail_reason) ) {
			return $this->fail_reason;
		}
		$this->convert_product_to_post();
		$this->post_id = wp_insert_post($this->post_array);
		if($this->post_id == 0) {
			return $this->fail_reason = 'Failed to insert post.';
		}
		update_post_meta($this->post_id, 'part_number', $this->part_number);
		update_post_meta($this->post_id, 'product_detail', $this->product);
		// Add any other post meta data:
		if(!empty($this->post_meta_array)) {
			foreach($this->post_meta_array as $key => $value) {
				update_post_meta($this->post_id, $key, $value);
			}
		}
		$this->update_searchable_fields();
		return true;
	}
	
	
	/**
	* update_product
	*
	* @return void
	*/
	function update_product() {
		// Update the product (update existing post)
		$this->look_up_product();
		if(empty($this->product)) {
			return 'Empty product - '.$this->fail_reason;
		}
		$this->convert_product_to_post();
		$this->post_array['ID'] = $this->post_id;
		$outcome = wp_update_post($this->post_array);
		if($outcome != 0) {
			update_post_meta($this->post_id, 'part_number', $this->part_number);
			update_post_meta($this->post_id, 'product_detail', $this->product);
			// Update any other post meta data:
			if(!empty($this->post_meta_array)) {
				foreach($this->post_meta_array as $key => $value) {
					update_post_meta($this->post_id, $key, $value);
				}
			}
			$this->update_searchable_fields();
			// Remove deprecated data:
			delete_post_meta($this->post_id, 'partnumber');
			delete_post_meta($this->post_id, 'detail');
			delete_post_meta($this->post_id, 'catentryid');
			return true;
		} else {
			return 'Failed to update product.';
		}
	}
	
	
	/**
	* update_searchable_fields
	*	For the current product, update the "searchable" fields - currently includes Size
	*
	* @return void
	*/
	function update_searchable_fields() {
		if(empty($this->product)) return false;
		if(empty($this->post_id)) return false;
		if(!isset($this->product['attribute_values']) || empty($this->product['attribute_values'])) return false;
		$size_values = array();
		if(is_array($this->product['attribute_values'])) {
			foreach($this->product['attribute_values'] as $special => $attributes) {
				if(is_array($attributes)) {
					foreach($attributes as $att_name => $att_values) {
						// Sizes:
						if (strpos(strtolower($att_name), 'size') !== false) {
							if(is_array($att_values)) {
								$size_values = array_merge($att_values, $size_values);
							}
						}
					}
				}
			}
		}
		if(!empty($size_values)) {
			$size_values_string = implode(',', $size_values);
			update_post_meta($this->post_id, 'search_size', $size_values_string);
		}
	}
	
	
	
	/**
	* add_to_category
	*	Add this product to the specified category, preserving existing categories.
	*	Also add to brand category if available / applicable.
	*
	* @return void
	*/
	function add_to_category($category_id = '') {
		if(empty($this->post_id)) return false;
		$categories = array();
		if(!empty($category_id)) $categories[] = $category_id;
		// Get existing categories:
		$existing_categories = wp_get_post_categories($this->post_id);
		if(is_array($existing_categories)) {
			$categories = array_merge($categories, $existing_categories);
		}
		// If there is a category that matches the brand name (slug) add that as a category for the product:
		$brand_slug = str_replace(' ', '-', strtolower($this->product['brand']));
		if(!empty($brand_slug)) {
			$brand = get_category_by_slug($brand_slug);
		} 
		if(isset($brand) && is_object($brand) && isset($brand->term_id)) {
			$categories[] = $brand->term_id;
		} 
		// Update the categories:
		wp_set_post_categories( $this->post_id, $categories );
	}
	
	/**
	* update_categories
	*	Updates categories - will set brand associations if applicable.
	*
	* @return void
	*/
	function update_categories() {
		$this->add_to_category('');
	}
	
	
	/**
	* already_imported 
	*
	* @return void
	*/
	function already_imported(){
		return $this->already_imported;
	}
	
	
	
	/**
	* is_softline
	*
	* @return Boolean
	*/
	function is_softline() {
		if($this->product['product_line'] == 'soft') {
			return true;
		} else {
			return false;
		}	
	}
	
	
	/**
	* should_delete
	*
	* @return Boolean
	*/
	function should_delete() {
		return $this->should_delete;
	}
	
	/**
	* should_delete
	*
	* @return Boolean
	*/
	function should_set_draft() {
		return $this->should_set_draft;
	}
	

}