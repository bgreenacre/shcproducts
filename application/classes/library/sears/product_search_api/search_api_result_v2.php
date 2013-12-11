<?php defined('SHCP_PATH') OR die('No direct script access.');




class Search_Api_Result_V2 implements Api_Result {

	/**
	* Raw API Response
	*
	* @var array
	*/
	protected $raw_response;
	
	/**
	* Verticals
	*
	* @var array
	*/
	public $verticals = array();
	
	/**
	* Categories
	*
	* @var array
	*/
	public $categories = array();
	
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
		error_log('Standardizing data...');
		
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
		
		// Get rid of the raw response:
		unset($this->raw_response);
	}
	
}