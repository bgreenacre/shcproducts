<?php defined('SHCP_PATH') OR die('No direct script access.');




class Search_Api_Result_V2json extends Search_Api_Result_Base implements Search_Api_Result {
	
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
		
		// Standardize products:
		$this->_standardize_products();
		
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
						'group_id' => (string)$vertical->CatGroupId
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
					$group_id = '';
					if(isset($category->CatGroupId)) {
						$group_id = (string)$category->CatGroupId;
					} else {
						// Sometimes, CatGroupId is not included in the response.
						//error_log('$category->CatGroupId not found. $category = '.print_r($category,true));
					}
					$this->categories[$category->CategoryName] = array(
						'category_name' => $category->CategoryName,
						'product_count' => $category->AggProdCount,
						'group_id' => $group_id
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
		}
		// List of filters to ignore is no longer necessary:
		unset($this->ignore_filters);
	}
	
	
	/**
	* _standardize_products 
	*
	* @return void
	*/
	function _standardize_products() {
		$r = $this->raw_response;
		if(isset($r->SearchResults->Products)) {
			$raw_products = $r->SearchResults->Products;
			if(is_array($raw_products)) {
				foreach($raw_products as $rp) {
					$product = array();
					$product['part_number'] = (isset($rp->Id->PartNumber)) ? $rp->Id->PartNumber : '';
					$product['name'] = (isset($rp->Description->Name)) ? $rp->Description->Name : '';
					$product['brand'] = (isset($rp->Description->BrandName)) ? $rp->Description->BrandName : '';
					$product['image_url'] = (isset($rp->Description->ImageURL)) ? $rp->Description->ImageURL : '';
					$product['rating'] = (isset($rp->Description->ReviewRating->Rating)) ? $rp->Description->ReviewRating->Rating : '';
					$product['review_count'] = (isset($rp->Description->ReviewRating->NumReview)) ? $rp->Description->ReviewRating->NumReview : '';
					$product['price'] = (isset($rp->Price->DisplayPrice)) ? $rp->Price->DisplayPrice : '';
					$product['has_variants'] = 0;
					if(isset($rp->Description->PbType)) {
						if($rp->Description->PbType == 'VARIATION') {
							$product['has_variants'] = 1;
						}
					}
					// Validate the product search result -- make sure it has required fields, etc.
					// Method defined in parent class Search_Api_Result_Base.
					if($this->validate_product_search_result($product)) {
						$this->products[$product['part_number']] = $product;
					}
					//error_log('$raw_product = '.print_r($rp,true));
				}
			} 
			///error_log('FOUND');
		} else {
			//error_log('Variable not found');
		}
	}
	
	
}