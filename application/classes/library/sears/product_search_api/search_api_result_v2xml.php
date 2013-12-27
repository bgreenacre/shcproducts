<?php defined('SHCP_PATH') OR die('No direct script access.');




class Search_Api_Result_V2xml extends Search_Api_Result_Base implements Search_Api_Result {
	
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
		if(isset($r->Verticals->Vertical)) {
			foreach($r->Verticals->Vertical as $vertical) {
				$vertical_name = (string)$vertical->VerticalName;
				$group_id = (string)$vertical->CatGroupId;
				$this->verticals[$vertical_name] = array(
					'vertical_name' => $vertical_name,
					'group_id' => $group_id
				);
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
		if(isset($r->NavGroups->NavGroup[0]->ShopByCategories->ShopByCategory)) {
			foreach($r->NavGroups->NavGroup[0]->ShopByCategories->ShopByCategory as $category) {
				//error_log('$category = '.print_r($category,true));
				$category_name = (string)$category->CategoryName;
				$product_count = (string)$category->AggProdCount;
				$group_id = (string)$category->CatGroupId;
				$this->categories[$category_name] = array(
					'category_name' => $category_name,
					'product_count' => $product_count,
					'group_id' => $group_id
				);
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
					$this->products[$product['part_number']] = $product;
					//error_log('$raw_product = '.print_r($rp,true));
				}
			} 
			///error_log('FOUND');
		} else {
			//error_log('Variable not found');
		}
	}
	
	
}