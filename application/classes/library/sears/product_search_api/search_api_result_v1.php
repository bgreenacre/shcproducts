<?php defined('SHCP_PATH') OR die('No direct script access.');




class Search_Api_Result_V1 extends Search_Api_Result_Base implements Search_Api_Result {
	
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
	
		// Standardize categories:
		$this->_standardize_categories();
				
		// Standardize product count:
		$this->_standardize_product_count();
		
		// Standardize filters:
		$this->_standardize_filters();
		
		// Get rid of the raw response:
		 unset($this->raw_response);
	}
	
	
	/**
	* _standardize_verticals 
	*
	* @return void
	*/
	function _standardize_verticals(){
		// Not applicable to V1 of the API.
	}
	
	
	/**
	* Standardize Categories 
	*
	* @return void
	*/
	function _standardize_categories(){
		$r = $this->raw_response;
		if(isset($r->mercadoresult->navgroups->navgroup[1][0]->shopbycategories->shopbycategory[1])) {
			$categories = $r->mercadoresult->navgroups->navgroup[1][0]->shopbycategories->shopbycategory[1];
			if(is_array($categories)) {
				foreach($categories as $category) {
					if(isset($category->subcategory)) {
						$this->categories[$category->subcategory] = array(
							'category_name' => $category->subcategory,
							'product_count' => $category->aggcount,
							'group_id' => '' // Not returned by V1 of the API.
						);
					} else if(isset($category->category)) {
						$this->categories[$category->category] = array(
							'category_name' => $category->category,
							'product_count' => $category->aggcount,
							'group_id' => '' // Not returned by V1 of the API.
						);
					}
				}
			}
		}
	}
	
	
	/**
	* _standardize_product_count 
	*
	* @return void
	*/
	function _standardize_product_count(){
		$r = $this->raw_response;
		if(isset($r->mercadoresult->productcount)) {
			$this->product_count = $r->mercadoresult->productcount;
		}
	}
	
	
	
	/**
	* _standardize_filters 
	*
	* @return void
	*/
	function _standardize_filters() {
		$r = $this->raw_response;
		if(isset($r->mercadoresult->filterproducts->filterproduct[1])) {
			$f = $r->mercadoresult->filterproducts->filterproduct[1];
			if(is_array($f)) {
				foreach($f as $filter) {
					$filter_name = (isset($filter->name)) ? $filter->name : '';
					if(!in_array($filter_name, $this->ignore_filters)) {
						$filter_values = array();
						if(isset($filter->filtervalues->filtervalue[1])) {
							$f_values = $filter->filtervalues->filtervalue[1];
							if(is_array($f_values)) {
								foreach($f_values as $f_value) {
									if(isset($f_value->name) && isset($f_value->contentcount)) {
										$filter_values[$f_value->name] = $f_value->contentcount;
									} 
								}
							}
						}
						if(!empty($filter_values)) {
							$this->available_filters[$filter_name] = $filter_values;
						}
					}
				}
			}
		}
	}
	
}