<?php defined('SHCP_PATH') OR die('No direct script access.');




class Details_Api_Result_V1xml extends Details_Api_Result_Base implements Api_Result, Details_Api_Result {
	
	/**
	* __construct 
	*
	* @return void
	*/
	function __construct($raw_response){
		$this->raw_response = $raw_response;
	}
	
	
	/**
	* Standardize Response
	*
	* @return void
	*/
	function standardize_data() {
		$r = $this->raw_response->SoftHardProductDetails;
		// Set basic info:
		$this->product['part_number'] = (string)$r->PartNumber;
		$this->product['brand'] = (string)$r->BrandName;
		$this->product['main_image_url'] = (string)$r->MainImageUrl;
		$this->product['all_image_urls'] = array();
		if(isset($r->ImageURLs->ImageURL) && !empty($r->ImageURLs->ImageURL)) {
			foreach($r->ImageURLs->ImageURL as $image_url) {
				$this->product['all_image_urls'][] = (string)$image_url;
			}
		}
		$this->product['name'] = (string)$r->DescriptionName;
		// If the product name does not already contain the brand name, add it to the beginning:
		if (!empty($this->product['brand']) && strpos($this->product['name'], $this->product['brand']) === false) {
			$this->product['name'] = $this->product['brand'].' '.$this->product['name'];
		}
		
		$this->product['short_description'] = (string)$r->ShortDescription;
		$this->product['long_description'] = (string)$r->LongDescription;
		
		$this->product['rating'] = (string)$r->Rating;
		$this->product['review_count'] = (string)$r->NumReview;
		
		// Determine whether product is hardline or softline:
		$product_variant = (string)$r->ProductVariant;
		if($product_variant == 'VARIATION') {
			$this->product['product_line'] = 'soft';
			if( !isset($r->ProductVariants->prodList->product->attNames->attName) ) {
				// In rare cases, the API may return products that are listed as VARIATION, but missing actual variants.
				// To avoid errors, we will treat any such products as invalid, and not softlines.
				$this->product['product_line'] = 'hard';
				$this->error_message .= 'Product was listed as VARIATION, but no variants were found in the API response. ';
			}
		} else {
			$this->product['product_line'] = 'hard';
		}
		
		
		$this->_standardize_price();
		
		$this->_standardize_cat_entry();
		
		// Unset the raw response, as it is no longer necessary:
		//unset($this->raw_response);
	}
	
	
	
	/**
	* Standardize Price
	*
	* @return void
	*/
	function _standardize_price() {
		$r = $this->raw_response->SoftHardProductDetails;
		
		$this->product['price'] = (string)$r->SalePrice;
		$this->product['crossed_out_price'] = (string)$r->RegularPrice;
		
		// Leave this blank if the product has a "range" of prices, e.g. "From $24.00 To $26.00"
		if( !is_numeric($this->product['price']) ) {
			$this->product['crossed_out_price'] = '';
		}
		
		// Calculate savings (if applicable):
		if( is_numeric($this->product['price']) && is_numeric($this->product['crossed_out_price']) ) {
			$original_cents = floatval($this->product['crossed_out_price']) * 100;
			$actual_cents = floatval($this->product['price']) * 100;
			$savings_cents = $original_cents - $actual_cents;
			$savings_dollars = number_format( ($savings_cents / 100), 2);
			$this->product['savings'] = $savings_dollars;
		} else {
			$this->product['savings'] = '0.00';
		}
		
	}
	
	
	
	/**
	* Standardize Cat Entry ID's
	*
	* @return void
	*/
	function _standardize_cat_entry() {
		$r = $this->raw_response->SoftHardProductDetails;
		
		 
		if($this->is_softline()) {
			// Set attributes:
			$raw_atts = $r->ProductVariants->prodList->product->attNames->attName;
			$i = 0;
			foreach($raw_atts as $att) {
				$this->product['attributes'][$i] = (string)$att;
				$i++;
			}
			/* 	Set possible attribute values below.
				The goal is to produce something like:
				[Size] => Array
					(
						[0] => S
						[1] => M
						[2] => L
						[3] => XL
					)

				[Color] => Array
					(
						[0] => Black Onyx
					)
			*/
			$raw_vals = $r->ProductVariants->prodList->product->prodVarList->prodVar->attList->attData;
			$j = 0;
			foreach($raw_vals as $val) {
				$single_val = $val->aVals->aVal;
				foreach($single_val as $attribute) {
					$att_name = $this->product['attributes'][$j];
					if(!isset($this->product['attribute_values'][$att_name])) {
						$this->product['attribute_values'][$att_name] = array();
					}
					$final_attribute = (string)$attribute;
					$final_attribute = trim($final_attribute,'"'); // Remove unnecessary quotes.
					$this->product['attribute_values'][$att_name][] = $final_attribute;
				}
				$j++;
			}
			// Build color swatch list:
			$raw_swatch = $r->ProductVariants->prodList->product->prodVarList->colorSwatchList->colorSwatch;
			if(!empty($raw_swatch)) {
				foreach($raw_swatch as $swatch) {
					$color_name = (string)$swatch->colorName;
					$swatch_url = (string)$swatch->mainImageName;
					$this->product['color_swatches'][$color_name] = $swatch_url;
				}
			}
			// Set the cat entry id's array:
			
			$this->product['cat_entry'] = array();
			//$raw_cids = $r->ProductVariants->prodList->product->prodVarList->prodVar->skuList->sku;
			$raw_cids = $r->ProductVariants->prodList->product->prodVarList->prodVar;
			foreach($raw_cids as $outer_cid) {
				$outer_name = (string)$outer_cid->varName;
				$sku_list = $outer_cid->skuList->sku;
				
				$this->product['cat_entry'][$outer_name] = array();
				foreach($sku_list as $cid) {
		
		//error_log('$cid = '.print_r($cid,true));
					$price = (string)$cid->price;
					/* // Which one of these is the cat entry id?
						[pId] => 43936660
						[stk] => true
						[ksn] => 6121260
						[upc] => 886809650406
						[price] => 7.99
						[regularPrice] => 30.00
						[pI] => 0
						[itemPNO] => 00765077025
						[aVals] => SimpleXMLElement Object
							(
								[aVal] => Array
									(
										[0] => S
										[1] => Directorie Blue
									)

							)

					)
					*/
					$cat_entry = (string)$cid->pId; // ??? is it this one?
					$this->product['cat_entry'][$outer_name][$cat_entry] = array(
						'price' => $price
					);
		
					$cid_atts = $cid->aVals->aVal;
					//error_log('$cid_atts = '.print_r($cid_atts,true));
					$k = 0;
					foreach($cid_atts as $cid_att) {
						$att_name = $this->product['attributes'][$k];
						$att_value = (string)$cid_att;
						$this->product['cat_entry'][$outer_name][$cat_entry][$att_name] = $att_value;
						$k++;
					}
					//$this->product['cat_entry']
				}
			}
		} else {
			$this->product['cat_entry'] = (string)$r->CatEntryId;
		}
	}
	
	
	
	
	
	
}