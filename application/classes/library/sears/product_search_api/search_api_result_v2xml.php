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
		$this->product_count = (int)$r->ProductCount;
	}
	
	
	/**
	* _standardize_filters 
	*
	* @return void
	*/
	function _standardize_filters() {
		$r = $this->raw_response;

		if(isset($r->FilterProducts->FilterProduct[0])) {
			foreach($r->FilterProducts->FilterProduct as $filter) {
				$filter_name = (string)$filter->FilterKey;
				$filter_values = array();
				if(!in_array($filter_name, $this->ignore_filters)) {
					if(isset($filter->FilterValues->FilterValue) && $filter->FilterValues->FilterValue instanceof SimpleXMLElement) {
						foreach($filter->FilterValues->FilterValue as $fvalue) {
							$filter_value_name = (string)$fvalue->Name;
							$filter_value_count = (string)$fvalue->ContentCount;
							$filter_values[$filter_value_name] = $filter_value_count;
						}
					}
				}
				if(!empty($filter_values)) {
					$this->available_filters[$filter_name] = $filter_values;
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
		if(isset($r->Products->Product[0])) {
			foreach($r->Products->Product as $p) {
				$product['part_number'] = (string)$p->Id->PartNumber;
				$product['name'] = (string)$p->Description->Name;
				$product['image_url'] = (string)$p->Description->ImageURL;
				$product['rating'] = (string)$p->Description->ReviewRating;
				$product['brand'] = (string)$p->Description->BrandName;
				$product['review_count'] = (string)$p->NumReview;
				$product['price'] = (string)$p->Price->DisplayPrice;
				$product['has_variants'] = 0;
				$type = (string)$p->Description->PbType;
				if($type == 'VARIATION') {
					$product['has_variants'] = 1;
				}
				// Validate the product search result -- make sure it has required fields, etc.
				// Method defined in parent class Search_Api_Result_Base.
				if($this->validate_product_search_result($product)) {
					$this->products[$product['part_number']] = $product;
				} else {
					// Decrement the product count by 1, since this result is invalid in some way.
					$this->product_count--;
				}
			}
		}
	}
	
	
}