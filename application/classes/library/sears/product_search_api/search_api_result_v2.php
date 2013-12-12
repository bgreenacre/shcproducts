<?php defined('SHCP_PATH') OR die('No direct script access.');




class Search_Api_Result_V2 extends Search_Api_Result_Base implements Search_Api_Result {
	
	/**
	* __construct 
	*
	* @return void
	*/
	function __construct($raw_response){
		$this->raw_response = $raw_response;
	}
	
	/**
	* standardize_data 
	*
	* @return void
	*/
	function standardize_data(){

		// Standardize Verticals:
		$this->_standardize_verticals();
		
		// Standardize categories:
		$this->_standardize_categories();
				
		// Standardize product count:
		$this->_standardize_product_count();
		
		// Standardize available filters:
		$this->_standardize_filters();
		
		// Get rid of the raw response:
		 unset($this->raw_response);
	}
	
	/**
	* _standardize_verticals 
	*
	* @return void
	*/
	function _standardize_verticals() {
		$r = $this->raw_response;
		
		// Standardize verticals:
		if(isset($r->SearchResults->Verticals)) {
			$verticals = $r->SearchResults->Verticals;
			if(is_array($verticals) && !empty($verticals)) {
				foreach($verticals as $vertical) {
					$this->verticals[$vertical->VerticalName] = array(
						'vertical_name' => $vertical->VerticalName,
						'group_id' => $vertical->CatGroupId
					);
				}
			}
		}
	}
	
	/**
	* _standardize_categories 
	*
	* @return void
	*/
	function _standardize_categories() {
		$r = $this->raw_response;
		
		// Standardize categories:
		if(isset($r->SearchResults->NavGroups[0]->ShopByCategories)) {
			$categories = $r->SearchResults->NavGroups[0]->ShopByCategories;
			if(is_array($categories) && !empty($categories)) {
				foreach($categories as $category) {
					$this->categories[$category->CategoryName] = array(
						'category_name' => $category->CategoryName,
						'product_count' => $category->AggProdCount,
						'group_id' => $category->CatGroupId
					);
				}
			}
		}
	}
	
	
	/**
	* _standardize_product_count 
	*
	* @return void
	*/
	function _standardize_product_count() {
		$r = $this->raw_response;
		
		// Standardize product count:
		if(isset($r->SearchResults->ProductCount)){
			$this->product_count = $r->SearchResults->ProductCount;
		}
	}
	
	
	/**
	* _standardize_filters 
	*
	* @return void
	*/
	function _standardize_filters() {
		$r = $this->raw_response;
		if(isset($r->SearchResults->FilterProducts)) {
			$f = $r->SearchResults->FilterProducts;
			if(is_array($f)) {
				foreach($f as $filter) {
					$filter_name = (isset($filter->FilterKey)) ? $filter->FilterKey : '';
					if(!in_array($filter_name, $this->ignore_filters)) {
						$filter_values = array();
						if(isset($filter->FilterValues) && is_array($filter->FilterValues)) {
							foreach($filter->FilterValues as $f_value) {
								if(isset($f_value->Name) && isset($f_value->ContentCount)) {
									$filter_values[$f_value->Name] = $f_value->ContentCount;
								}
							}
						}
						if(!empty($filter_values)) {
							$this->available_filters[$filter_name] = $filter_values;
						}
					}
				}
			}
			//error_log('$f = '.print_r($f,true));
		} else {
			//error_log('not found etc. etc.');
		}
	}
	
	
}