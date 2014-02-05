<?php defined('SHCP_PATH') OR die('No direct script access.');




class Product_Category_Model {

	/**
	* Category ID
	*
	* @var string
	*/
	public $category_id = '';
	
	
	/**
	* Has SHC Category?
	*
	* @var bool
	*/
	public $has_shc_category = false;
	
	/**
	* Last imported?
	*
	* @var bool
	*/
	public $last_imported = false;
	
	/**
	* Last added to category?
	*
	* @var bool
	*/
	public $last_added_to_category = false;
	
	/**
	* Valid SHC Category?
	*
	* @var bool
	*/
	public $valid_shc_category = false;
	
	
	/**
	* SHC Category
	*
	* @var Object
	*/
	public $shc_category;


	/**
	* WP Part Numbers array
	*
	* @var array
	*/
	public $wp_part_numbers = array();
	
	/**
	* WP Duplicated Products
	*
	* @var array
	*/
	public $wp_duplicated_products = array();


	/**
	* SHC Part Numbers array
	*
	* @var array
	*/
	public $shc_part_numbers = array();
	
	
	/**
	* Sync Change log
	*
	* @var array
	*/
	public $sync_change_log = array();
	
	/**
	* Sync To-Do List
	*
	* @var array
	*/
	public $sync_todo_list = array();
	
	/**
	* Notices
	*
	* @var array
	*/
	public $notices = array();
	
	/**
	* Warnings
	*
	* @var array
	*/
	public $warnings = array();

	
	/**
	* __construct 
	*
	* @return void
	*/
	function __construct($category_id, $should_init = true){
		$this->category_id = $category_id;
		if($should_init) {
			$this->initialize_category();
		}
	}
	
	
	/**
	* initialize_category 
	*
	* @return void
	*/
	function initialize_category(){
		// Set up the Sears API category setting if there is one:
		$this->setup_shc_category();
		$this->setup_wp_part_numbers();
		$this->setup_shc_part_numbers();
	}
	
	
	/**
	* setup_shc_category:
	*
	* @return void
	*/
	function setup_shc_category() {
		$option_key = 'shcproducts_category_'.$this->category_id;
		$this->shc_category = get_option($option_key);
		if( !empty($this->shc_category) ) {
			$this->has_shc_category = true;
		}
	}
	
	
	/**
	* setup_wp_part_numbers:
	*	Query the WordPress database and set up the array of part numbers that currently exist.
	*
	* @return void
	*/
	function setup_wp_part_numbers() {
		// Query posts in category
		$args = array(
			'posts_per_page' => -1,
			'category' => $this->category_id,
			'post_type' => 'shcproduct',
			'post_status' => array('publish', 'draft')
		);
		$posts = get_posts($args);
		// Loop through posts, set up array with part number => post id
		if(is_array($posts)) {
			foreach($posts as $post) {
				$post_id = (is_object($post) && isset($post->ID)) ? $post->ID : false;
				if($post_id) {
					$part_number = get_post_meta( $post_id, 'part_number', true );
					if(!empty($part_number)) {
						if(!isset($this->wp_part_numbers[$part_number])) {
							$this->wp_part_numbers[$part_number] = $post_id;
						} else {
							$this->wp_duplicated_products[$post_id] = $part_number;
						}
					}
				}
			}
		}		
	}
	
	
	/**
	* setup_shc_part_numbers 
	*	Query the Sears API and set up the array of part numbers that SHOULD be in this category.
	*
	* @return void
	*/
	function setup_shc_part_numbers() {
		if(is_array($this->shc_category) && !empty($this->shc_category)) {
			foreach($this->shc_category as $category) {
				$api_result = get_all_products_in_category($category);
				if(is_object($api_result) && isset($api_result->products) && is_array($api_result->products)) {
					//$this->valid_shc_category = true;
					foreach($api_result->products as $part_number => $product_info) {
						$this->shc_part_numbers[$part_number] = $part_number;
					}
				} else {
					$this->add_warning('Invalid Sears API category detected for category #'.$this->category_id.': '.print_r($category,true));
				}
			}
		} else {
			$this->add_notice('No Sears API categories are currently set for this WordPress category.');
		}
	}
	
	
	/**
	* sync
	*	Sync the WordPress category with the Sears API category.
	*
	* @return void
	*/
	function sync() {
		// Step 1: Go through list of SHC products and import any that WordPress dosen't have
		foreach($this->shc_part_numbers as $part_number) {
			if(array_key_exists($part_number, $this->wp_part_numbers)) {
				$this->sync_change_log[$part_number] = 'Already imported and in category.';
			} else {
				$this->sync_single($part_number);
			}
		}
		return $this->sync_change_log;
		
		// Step 2: Go through list of WordPress products and remove any missing from the API response
		// For now, this will be handled by the other part of the product update process
		// i.e. remove invalid / no longer available products based on details API response
// 		foreach($this->wp_part_numbers as $wp_part_number => $wp_post_id) {
// 			if(array_key_exists($wp_part_number, $this->shc_part_numbers)) {
//				// Should be in category, all is well.
// 			} else {
//				// Need to remove from category.
// 			}
// 		}
	}
	
	
	/**
	* sync_single
	*	Sync a single part number within this category.
	*
	* @return void
	*/
	function sync_single($part_number) {
		$prod_obj = new Product_Model($part_number); 
		$import_result = $prod_obj->import_product();
		$product_name = $prod_obj->post->post_title;
		if(empty($product_name)) $product_name = $prod_obj->post_array['post_title'];
		if($import_result === true) {
			$this->sync_change_log[$part_number] = 'Successfully imported. Post ID = '.$prod_obj->post_id;
			$prod_obj->add_to_category($this->category_id);
			$this->last_imported = true;
		} else {
			if(!empty($prod_obj->post_id)) {
				$this->sync_change_log[$part_number] = 'Already imported ('.$prod_obj->post_id.'), adding to category.';
				// Add to category:
				$prod_obj->add_to_category($this->category_id);
				$this->last_added_to_category = true;
			} else {
				$this->sync_change_log[$part_number] = 'Not imported - '.$import_result;
			}
		}
		if(!empty($product_name)) $this->sync_change_log[$part_number] .= ' -- '.$product_name;
		return $this->sync_change_log[$part_number];
	}
	
	
	
	/**
	* build_sync_todo_list
	*	Return a list of the operations needed to sync the WordPress category with the Sears API category.
	*
	* @return void
	*/
	function build_sync_todo_list() {
		// Step 1: Go through list of SHC products and import any that WordPress dosen't have
		foreach($this->shc_part_numbers as $part_number) {
			if(array_key_exists($part_number, $this->wp_part_numbers)) {
				$this->sync_todo_list[] = array(
					'part_number' => $part_number,
					'action' => 'No action - already imported.'
				);
			} else {
				$this->sync_todo_list[] = array(
					'part_number' => $part_number,
					'action' => 'IMPORT'
				);
			}
		}
	}
	
	
	/**
	* add_notice
	*
	* @return void
	*/
	function add_notice($notice_text) {
		$this->notices[] = $notice_text;
	}
	
	/**
	* add_warning
	*
	* @return void
	*/
	function add_warning($warning_text) {
		$this->warnings[] = $warning_text;
	}

}