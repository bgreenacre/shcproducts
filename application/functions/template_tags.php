<?php

/*
Template Tags functions.
Based on functions that were originally contained in their own "SHC Products Template Tags" plugin.
*/


// ============== HARDLINES / CORE PRODUCT TEMPLATE TAGS ==================//

/**
 * Retrieves meta data fields from sears products in the WordPress database.
 *
 * @param string $meta_key [required] Key of the meta data to be retrieved
 * @param bool $echo [optiona] If set to false, the requested meta data will be returned, otherwise it will be echoed [default: true]
 * @param int $index [optional] Index of the meta value to be retrived
 */
function product_meta($meta_key, $echo = true, $index = 0) {
    $meta_value = get_post_meta(get_the_ID(), $meta_key, false);

    if($echo){ echo $meta_value[$index]; }
    else { return $meta_value[$index]; }
}




/**
 * Returns the entire contents of the 'detail' metadata fields in a product. The initial parameter
 * $product is simply the first level of the detail array. If echo is true, and the resulting value
 * is still an array, the function will print_r via the print_pre function for debugging purposes.
 *
 * This function also scours all of the content returned for missing colons in urls, as well as
 * performs decoding of special html entities
 *
 * @staticvar object $detail Contains the entire details object of the current product.
 *
 * @param string $property [optional] The string of the first-level property to be retreived from the details meta fields object. If not set, returns the entire object.
 * @param bool $echo [optional]. If true will echo the results (unless is array), if false it will be returned.
 * @param int $post_id [optional] A last minute addition - this accepts a specified post id.
 *
 * @return string|print_r Depening on the value of $echo and if the returned value is an string.
 */
function product_detail($property = false, $echo = true, $post_id = false) {

    $post_id = ($post_id) ? $post_id : get_the_ID();
    if (!isset($detail)) {

        static $detail = null;
        $detail = get_post_meta($post_id, 'detail', true);

        if (is_string($detail)) $detail = unserialize($detail);
    }

	if (is_object($detail)) {
		// The following line doesn't appear to be necessary in this context,
		// and has been found to cause problems in certain rare cases.
		//$detail = $detail->current(); 
		$detail = (!empty($property)) ? $detail->$property : $detail;

		//Whoo, logic!
		if ($echo) {
			if (!is_string($detail)) { 
				//print_pre($detail); // Enable for debugging.
				return false;
			} else { 
				echo colon_dangit( htmlspecialchars_decode(html_entity_decode($detail) ) ); 
			}
		} else {  
			return $detail; 
		}
    } else {
    	return false;
    }
}



/**
 * Returns a description depending on the preference passed. Looks for the best available match.
 *
 * @param string $preference [required]
 * @param bool $echo [optional]
 * @param int $force [optional]
 */
function product_description($preference, $max_lenth = null, $force = false, $echo = true){

    $other = ($preference = 'long') ? 'short' : $preference;

    //Use the prefered description if it exists, otherwise use the other one
    $desc = (product_detail($preference.'description', false)) ? product_detail($preference.'description', false) : product_detail($other.'description', false);
    $desc = ($force) ? product_detail($preference.'description', false) : $desc;

    if($echo){
        echo $desc;
    } else {
        return $desc;
    }
}


function product_url(){
    
    $detail = product_detail(false, false);
    $options = get_option('shcp_options');
    $store = strtolower($options['store']);
    $base_url =  'http://www.' . $store .'.com/shc/s/p_';

    $catalogid = $detail->catalogid;
    $partnumber = $detail->partnumber;
    $storeid = $detail->storeid;
    $url = $base_url.$storeid.'_'.$catalogid.'_'.$partnumber;

    return $url;
}




/**
 * Retrieves the monstrocity that is the specifications data from a product, and
 * turns it into a simple, easy to manage associative array
 *
 * @return array Multidimensional assiciative array.
 */
function product_specs() {

    $specifications = product_detail('specifications', false);
    $specifications = $specifications->specification[1];

    if(!empty($specifications)){
        foreach ($specifications as $attribute) {

            $heading = $attribute->label;
            $specs = $attribute->attribute[1];

            foreach ($specs as $spec) {

                preg_match_all('/(.*?[a-hj-z\))])+([A-Z0-9\-])/', $spec->value, $label);

                //print_pre($label);
                $label = rtrim($label[0][0], $label[2][0]);

                $value = str_replace($label, '', $spec->value);

                $values[$label] = colon_dangit($value);
            }

            $attributes[] = array(
                'heading' => $heading,
                'values' => $values
            );
        }
    }

    return $attributes;
}


/**
 * Retrieve the product specifications as a multidimensional array.
   Intended return format:
		array(
			'Header 1' => array(
				'Specification Name' => 'Specification Value',
				'Specification Name' => 'Specification Value'
			),
			'Header 2' => array( 
				// etc. etc. etc.
		)
 *
 * @return array Multidimensional assiciative array.
 */
function get_product_specs() {

	// Grab the specification data:
	$specifications = product_detail('specifications', false);
    $specifications = $specifications->specification[1];

	$rval = array(); // We'll return this later.

    if(!empty($specifications) && is_array($specifications)){
    	// Got our data, so far so good.
    	// Now it's time to drill down through the many layers of objects and arrays.
    	
    	// Each item in $specifications is effectively a section - it has two relevant attributes:
    	//	-- 'label' - a section header, which should be a string
    	//	-- 'attribute' - an array of features, wrapped in another array just for fun
        foreach ($specifications as $attribute) {        	
        	
        	if(isset($attribute->attribute[1]) && is_array($attribute->attribute[1])) {
        		$features = $attribute->attribute[1];
        		
        		// The headers are given to us with colons at the end, 
        		// which may not necessarily be wanted.
        		$header = trim($attribute->label,':');
        		
        		$rval[$header] = array();
        		
        		foreach($features as $feature) {
        			// Each individual feature is given to us as a string,
        			// with feature name and feature value separated by a colon.
        			// We'll explode these and add them to our multi-demensional array.
        			$exploded_feature = explode(':',$feature->value);
        			
        			// Check that the exlosion did what we expected it to do, just in case:
        			if(!empty($exploded_feature) && count($exploded_feature) == 2) {
        				// Seems legit. Let's go ahead and add it to our array:
						list($feature_name, $feature_value) = $exploded_feature;
						$rval[$header][$feature_name] = $feature_value;
        			}
        		}
        	}
        }
    }
    return $rval;
}



/**
 * Will either return an array of image urls for each "star" for the rating, or will
 * directly output those images as html. Attention must be paid to the path used for
 *
 * The first example will assign an array of star image urls to the $star variable.
 * The images would have to be in /<yourtheme>/images folder.
 *
 * The second example would simply output all the images as html.
 *
 * @example $stars = product_rating_images('images/star-filled.gif', 'images/star.gif', '', false);
 * @example product_rating_images('images/star-filled.gif', 'images/star.gif');
 *
 * @uses product_meta();
 *
 * @param string $filled [required] Path and name of image to be used for filled images. Path must be relative to theme and can not have a leading slash.
 * @param string $unfilled [required] Path and name of image to be used for unfilled images. Path must be relative to theme and can not have a leading slash.
 * @param string $url_base [optional] Base path both the filled and unfilled images. Can be passed as an empty string if necessary. Default is get_template_directory_uri().
 * @param bool $echo_as_html [optional] If true, the passed images will be directly output within img tags. If false it will return an array of the image urls.
 * @return html|array Either an array of image urls, or it will directly output html.
 */
function product_rating_images($filled, $unfilled, $partial, $url_base = null, $echo_as_html = true){

    $url_base = (empty ($url_base)) ? get_template_directory_uri() : $url_base;

    $filled_image = $url_base . "/" . $filled;
    $unfilled_image = $url_base . "/" . $unfilled;
    $partial_image = $url_base . "/" . $partial;

    $stars = array();

    $round_rating = floor(product_meta('rating', false));

    if(!empty($round_rating)){
        for($star = 1; $star <= $round_rating; $star++){
            $stars[] = $filled_image;

        }

        if((product_meta('rating', false)-$round_rating)>0){
            $stars[] = $partial_image;
        }

        $filled_star_count = count($stars);
        if($filled_star_count < 5){
            for($star = $filled_star_count+1; $star <= 5; $star++){
                $stars[] = $unfilled_image;
            }
        }
    }

    if($echo_as_html){
        foreach($stars as $star){
        ?>
            <img src="<?php echo $star; ?>" alt="star-image"/>

       <?php
       }

    } else { return $stars; }
}



/**
 * Retreives the entire post object of the a single image that is categorized
 * with the category ID that is passed or the current category if none is passed.
 *
 * = Usage =
 *  * Kmart Fashion Lookbook
 *
 * @param int $category_id [optional] A category ID, defaults to the current category ID.
 * @param bool $echo [depricated since 1.1] Can't echo the entire post object.
 *
 * @return object
 */
function get_category_image($category_id = null, $echo = false, $args = array()){

    $category_id = (empty($category_id)) ? get_query_var('cat') : $category_id;
    $img_args = array(
        'numberposts' => 1,
        'post_type' => 'attachment',
        'tax_query' => array (
            'relation' => 'AND',
            array(
                'taxonomy' => 'category',
                'field' => 'id',
                'terms' => $category_id,
                'include_children' => false,
                'operator' => 'IN'
            ),
            array(
                'taxonomy' => 'category',
                'field' => 'slug',
                'terms' => 'thumbnails',
                'operator' => 'NOT IN'
            )
         )
    );

    $image = get_posts(array_merge($img_args, $args));

    if(isset($image[0])) {
        $image = $image[0];

		if ($echo) {
			echo $image;
    	} else {
    		return $image;
    	}
    } else {
    	return false;
    }
}


/**
 * Gets the url of the first categorized image for a particular category.
 *
 * @param int $category_id [optional] If set, it will use the given id to search for an image. If not set, it will use the current category from get_query_var('cat)
 * @param bool $echo [optional] If true, the url is echoed out, if false, it is returned. Default: true/
 * @return string The url of the image.
 */
function get_category_image_url($category_id = null, $echo = true, $thumb = false){

    $category_id = (empty($category_id)) ? get_query_var('cat') : $category_id;
    
    // Add functionality to work with newer Categories Images plugin,
    // without breaking category images that were added the old way.
    if(function_exists('z_taxonomy_image_url')) {
		$image_url = z_taxonomy_image_url($category_id);
		if(!empty($image_url)) {
			if ($echo) { 
				echo $image_url;
				return;
			} else {
				return $image_url;
			}
		}
    }

    $image = get_category_image($category_id, false);

    if(is_object($image)){
        if (!$thumb) {
            $image_url = wp_get_attachment_url($image->ID);
        } else {
            $image_url = wp_get_attachment_thumb_url($image->ID);
        }

        if ($echo ) echo $image_url;
        else { return $image_url; }

    } else return false;

}


/**
 * Returns or echoes an image url based on the 'imageid' meta data of the current product
 *
 * @param int $height
 * @param int $width
 * @param bool $echo
 *
 * @uses /plugins/shcproducts/.../Helper_Products::image_url()
 *
 * @return
 */
function product_image($height = '220', $width = null, $echo = true){

    $width = (!$width && $height) ?  $height: $width;
    $image = Helper_Products::image_url(product_meta('imageid', false),$height,$width, FALSE);

    if($echo) { echo $image; }
    else { return $image; }
}


/**
 * Scours $text for url's that are missing colons.
 *
 * @param string [required] Any chunk of text.
 *
 * @return string Text with colons in all urls'
 */
function colon_dangit($text){
    $text = str_replace('http//', 'http://', $text);
    return  $text;
}

/**
 * Does the math for you to find the difference between displayprice and cutprice
 *
 * @uses product_meta()
 *
 * @deprecated Replaced with product_price_info()
 */
function product_savings($echo = true){
    $savings = number_format(abs((float)product_meta('displayprice', false) - (float)product_meta('cutprice', false)), 2, '.', '');

    if($echo) { echo $savings; }
    else { return $savings; }
}

/**
 * Fuckton of logic to find shit that should be easy to find.
 *
 *
 * @return type
 */
function product_price_info($catentryid = null){

	// Fetch the price info from the database:
	$actual_price = product_meta('cutprice', false);
	$actual_price2 = product_detail('saleprice', false);
	$original_crossed_out_price = product_meta('displayprice', false);
    
    // If it's a softline product and we have the catalog entry ID, 
    // look up the price for that specific variant instead:
    if (is_softline() && !empty($catentryid)) {
		$match = reset(get_variants(array('catentryid' => $catentryid)));
		$actual_price = $match['price'];
	}
    
    // Make sure $actual_price is set to something.
    if(empty($actual_price)) {
    	if(!empty($actual_price2)) {
    		$actual_price = $actual_price2;
    	} else {
    		if(!empty($original_crossed_out_price)) {
    			$actual_price = $original_crossed_out_price;
    		} else {
    			// This should never happen; products without pricing info should
    			// be already rejected as invalid during the import/update process.
    		}
    	}
    }
    
    // Format the price and set all the variables we need:
    if(is_numeric($actual_price)) {
    	// If it's numeric, format it to two decimal places all money-like:
    	$actual_price = number_format((float)$actual_price, 2, '.', '');
    	$range = false;
    	// Calculate the savings if we can:
    	if(is_numeric($original_crossed_out_price)) {
    		$savings = (float)$original_crossed_out_price - (float)$actual_price;
    		// Don't bother to show savings if it was $0.00:
    		if($savings > 0) {
    			$savings = number_format((float)$savings, 2, '.', '');
    		} else {
    			$savings = false;
    		}
    		$original_crossed_out_price = number_format((float)$original_crossed_out_price, 2, '.', '');
    	} else {
    		$savings = false;
    	}
    } else {
    	// Otherwise, catch those that are ranges, i.e. "From $16.99 to $18.99"
    	// Can't calculate savings and don't format.
    	$range = $actual_price;
    	$actual_price = false;
    	$savings = false;
    }
    
    // All done, put the data into the final format to be returned:
	$return_array = array(
        'regular' => $original_crossed_out_price,
		'range'	  => $range,
        'savings' => $savings,
        'price'   => $actual_price
    );
    
    return $return_array;
}


function montize_number($number){
    $number = number_format((float)$number, 2, '.','') ;
    return "$" . $number;
}


/**
 * @return bool
 * @todo Determine if this holds true for soflines as well.
 */
function is_in_stock($post_id = false) {
    return (product_detail('instock', false, $post_id) == 1) ? true : false;
}




######### SOFTLINES ##############

/**
 * @return bool
 * @todo WIll have to be refactored, and be much more complex, as 'hasvariant' has been found to be a completly untrustworthy indicator as to if the product _has variantss_ !!!!
 */
function is_softline($post_id = false){
	return (product_detail('productvariant', false, $post_id) == "VARIATION");
//    $vars = true;//count(product_detail('skulist', false, $postid)->sku[1]);
//    return (!empty($vars));
}


/**
 * Return 'softline' if it's a softline.
 */
function softline_class(){
    echo (is_softline()) ? 'softline' : '';
}


/**
 * @return string of sizes (?), or false if there are none.
 */
function product_sizes(){

    if ( is_softline() ) {
        $product_variants = product_detail('productvariants', false);
        $sizes = $product_variants->prodlist[1][0]->product[1][0]->prodvarlist->prodvar->attlist->attdata[1][1]->avals[1][0]->aval[1];

        return str_replace('"', '', $sizes);
    } else { return false; }
}


/**
 * Returns an associative array of swatches, where the index is the name of the color, and the value is the
 * url for that swatch.
 *
 * Example fo output:
 * Array
 * (
 *    [Black]       => http://s.shld.net/is/image/Sears/04177892000?hei=50&wid=50
 *    [Navy]        => http://s.shld.net/is/image/Sears/04177893000?hei=50&wid=50
 *    [Light blue]  => http://s.shld.net/is/image/Sears/04177896000?hei=50&wid=50
 *    [White]       => http://s.shld.net/is/image/Sears/spin_prod_527275101?hei=50&wid=50
 *
 * )
 *
 * @param int $height [optional] The height of the image that should be returned. Default to 220
 * @param int $width [optional] The width of the image that should be returned. Default null. If set to null, it will simply mirror whatever the value of $height is set to.
 * @param $specificcolor [optional] If they want to return the imageurl for one color in particular they can pass in that color name. Default false.
 *
 * @return array Associative array of color names => image url
 */
function product_swatches($height = '50', $width = null, $specificcolor = false){

    $width = (!$width && $height) ?  $height: $width;

    if( is_softline() ) {
        $product_variants = product_detail('productvariants', false);
        $swatches_objs = false;
        if(isset($product_variants->prodlist[1][0]->product[1][0]->prodvarlist->colorswatchlist)) {
       		$swatches_objs = $product_variants->prodlist[1][0]->product[1][0]->prodvarlist->colorswatchlist->colorswatch[1];
        }

        if (!empty($swatches_objs)) {
            foreach ($swatches_objs as $swatch) {
                $img = Helper_Products::image_url($swatch->mainimagename, $height, $width, FALSE);
				if (!$specificcolor || $specificcolor == $swatch->colorname) {
					$swatches[$swatch->colorname] = $img;
				}
            }
        }

        //How happy it makes me when shit actually works out :)
        if(isset($swatches)) return $swatches;
    } else { return false; }
}



/**
 * Template Tag. Returns an array of product variants for the current product.
 * They can optionally made returned as an associative array, where the key of each
 * variant is one of the values of the given variant.
 *
 * @param string [optional] If set, it must be the key of one of the elements it the output of the array. The value of that element will be used as a key for the entire variant.
 */
function product_variants($assoc = false, $postid = null){


    if ( is_softline($postid) ) {
        $product_variants = product_detail('productvariants', false, $postid);
        $variants_objs = $product_variants->prodlist[1][0]->product[1][0]->prodvarlist->prodvar->skulist->sku[1];
        $variants_type_keys = $product_variants->prodlist[1][0]->product[1][0]->attnames[1][0]->attname[1];

        foreach ($variants_objs as $key => $variant){

            $variant_type_values = $variant->avals->aval[1];
            $variant_types = array_combine($variants_type_keys, $variant_type_values);

            $variants[$key] = array(
                'partnumber'    => $variant->itempno,
                'price'         => $variant->price,
                'instock'       => $variant->stk,
                'pid'           => $variant->pid,
                'catentryid'    => $variant->pid //product_detail('skulist', false, $postid)->sku[1][$key]->catentryid
            );
            $variants[$key] += $variant_types;
        }

        if($assoc) {
            foreach($variants as $variant){
                $vars[$variant[$assoc]] = $variant;
            }
            $variants = $vars;
        }

        return $variants;

    } else { return false; }
}


/**
 * Potential Args: 'Color', 'Size', 'price', 'instock', 'partnumber'
 *
 * Returns a narrowed down list of variants based on criteria, or returns valse if none are found.
 * @param type $args
 * @return type
 */
function get_variants($args = array(), $postid = null){
    $default_args = array(
        'assoc' => false
        //'limit' => 1
    );

    $options = array_merge( $default_args, $args);
    $assoc = $options['assoc'];
    //$limit = $options['limit'];
    unset($options['assoc']);
    //unset($options['limit']);

    $product_variants = product_variants($assoc, $postid);

    foreach($product_variants as $variant_key => $variant){
        foreach($options as $opt_key => $opt_val){
            if($opt_val != $variant[$opt_key] || !array_key_exists($opt_key, $variant)){
				unset($product_variants[$variant_key]);
            }
        }
    }

    return (count($product_variants)) ? $product_variants : false;
}


/**
 * @param type $postid
 * @return type 
 */
function product_atts($postid = false) {
    $prod_vars = product_detail('productvariants', false, $postid);
    $attdata = array();
    
    if (is_object($prod_vars) && isset($prod_vars->prodlist)) {
        if (is_object($prod_vars->prodlist[1][0])) {
            if (is_object($prod_vars->prodlist[1][0]->product[1][0])) {
            	
                $product = $prod_vars->prodlist[1][0]->product[1][0];
                $attnames = $product->attnames[1][0]->attname[1];
                if (!is_object($product->prodvarlist->prodvar)) {
                    $prodvars = $product->prodvarlist->prodvar[1]; // This in preperation for more terribleness once we hash out the problem with prodvar in json
                } else {
                	if(isset($product->prodvarlist->colorswatchlist)) {
                    	$prodvars = $product->prodvarlist->colorswatchlist; // This in preperation for more terribleness once we hash out the problem with prodvar in json
                    }
                    // Do something slightly different when there are sizes but no colors:
                    if(empty($prodvars)) $prodvars = $product->prodvarlist->prodvar;
                    $otherProdvars = $product->prodvarlist->prodvar;
                }
    
               //print_pre($prodvars);
            	if(is_array($prodvars) || is_object($prodvars) ) {
					foreach($prodvars as $prodvar){
						//$varname = $prodvar->varname[1][0]
						if(isset($prodvar->varname)){
							$varname = $prodvar->varname[1][0];
							$attlist = $prodvar->attlist->attdata[1];
							foreach ((array) $attlist as $index => $attvalue) {
								$attdata[$varname][$attnames[$index]] = $attvalue->avals[1][0]->aval[1];
							}
						
						} else if (isset($otherProdvars->varname)) {
							$varname = $otherProdvars->varname[1][0];
							$attlist = $otherProdvars->attlist->attdata[1];
							foreach ((array) $attlist as $index => $attvalue) {
								$attdata[$varname][$attnames[$index]] = $attvalue->avals[1][0]->aval[1];
							}
						}
						//print_pre($varname);

					} 
                }
            }
        }
    }
    //print_pre($varnames);
    return (!empty($attdata)) ? $attdata : null;
}



/**
 * Displays a series of dropdowns containing all the options available
 * for a particular softline.
 * 
 * = Usage =
 * KmartFashion Lookbook
 *
 * @param int $postid Post ID for the product of which to get the options
 */
function product_options($postid = null, $label = null){ 
    
   $prodatts = product_atts($postid);
   
	if (!empty($prodatts)) {
        
        if(!empty($label))
            ?><span class="productLabel"><?php echo $label; ?></span><?php
            
	} else $prodatts = array();
	    
   //print_pre($prodatts);
   $disabled = " disabled='disabled'";
   $selected = " selected='selected';";
   $prodvar_keys = array_keys($prodatts);
   
   if(!empty($prodvar_keys[0])){
   		$mystery_attribute = $prodvar_keys[0]; // Let's try to guess what this is since the API doesn't tell us.
   		$att_keys = array_keys($prodatts[$prodvar_keys[0]]);
   		   		
   		if(in_array('Shoe Size', $att_keys)) {
   			// Width - for shoes
   			echo '<div class="softline-option-label">
   			<b>Width:</b> '.$mystery_attribute.'</b><br/><br/></div>';
   		} else if (in_array('Size', $att_keys) || in_array('Color', $att_keys) ) {
   			// Fit - for clothing
   			echo '<div class="softline-option-label">
   			<b>Fit:</b> '.$mystery_attribute.'</b><br/><br/></div>';
   		}
   }
   
 /*  if(!empty($prodvar_keys[0])){
    ?>
        <select class="softline-options prodvar" data-attname="varname">
            <option value="" selected="selected"<?php echo $disabled; ?>>Special Sizes</option>
            <?php foreach($prodatts as $prodvar_name => $prodvar): ?>
                <?php $prodvar_label = str_replace("'", "", trim($prodvar_name, "\"")); ?>
                <option value="<?php echo $prodvar_label;?>"<?php echo $selected;?>><?php echo $prodvar_name;?></option>
                <?php $selected=""; ?> 
            <?php endforeach; ?>
        </select>
   <?php 
   
   } */
       
   foreach($prodatts as $prodvar_name => $prodvar){
       $prodvar_label = str_replace("'", "", trim($prodvar_name, "\""));
       
       foreach((array)$prodvar as $label => $values){
      
			// We don't want the color options in a dropdown
			if (strtolower(trim($label, "\"")) != "color") {
      
				$swatches = product_swatches('220', '220', $postid);
				$fullsize = product_swatches('1800', '1800', $postid);
				$data_prodvar = ' data-prodvar="' . $prodvar_label . '"';
				?>
					<div class="softline-option-label"><?php echo $label; ?>:</div>
					<select class="softline-options" name="<?php echo trim($label, "\""); ?>" data-attname="<?php echo trim($label, "\""); ?>"<?php echo $data_prodvar; ?>>
						<?php $disabled = " disabled='disabled'"; ?>
						<option value="" selected="selected"<?php echo $disabled; ?>><?php echo 'Please select a '.strtolower($label); ?></option>
						<?php foreach($values as $value) :

								$instock = false;
								$value_clean = str_replace("'", "", trim($value, "\""));
								$matches = get_variants(array($label => $value_clean));
								//print_pre(array('varname' => $prodvar_name, $label => $value_clean));

								//This MIGHT work. Not sure, need actual out of stock variants
								foreach ((array)$matches as $variant){
									// If any of the size variants (etc.) are in stock,
									// don't disable the option. Another stock check will
									// happen via ajax once the user has selected all options.
								   if($variant['instock']==true){
									   $instock = true;
								   } 
								}
				

								//$disabled = ($instock) ? "" : $disabled;
								$disabled = ''; // Handling in stock check with ajax instead.
								$swatch = ($swatches[str_replace('"', '', $value)]) ? " data-colorswatch='" . $swatches[str_replace('"', '', $value)] . "'" : "";
								$full = ($fullsize[str_replace('"', '', $value)]) ? " data-fullimage='" . $fullsize[str_replace('"', '', $value)] . "'" : "";
							?>
							<option value="<?php echo trim($value,"\"") ?>"<?php echo $disabled . $swatch . $full; ?>  >
								<?php echo trim($value, "\"\t \n\r\0"); ?>
							</option>

						<?php endforeach; ?>
					</select>
				<?php
          } else {
				// Display the color options as swatches
				$swatches = product_swatches();
				if ($swatches) {
					// Storing the html for the color swatches in a variable to be printed after all the dropdowns product-option-disabled
					$colorhtml = '<input class="softline-options" id="input-selected-color" type="hidden" name="Color" value="" /><br />Color: <span id="span-selected-color">Select from below</span>
					<div class="softline-color-thumbnail-holder">';
					foreach ($swatches as $colorname=>$img) {
						$colorhtml .=  '<img id="' . str_replace(" ","",$colorname) . '" class="product-swatch" src="' . $img . '" title="' . $colorname . '" alt="' . $colorname . '" height="35" width="35">';
					}
					$colorhtml .= '</div>';
				}
          }

       }
  
   }
	// We want color swatches to always come last, after the dropdowns
   if(isset($colorhtml)) echo $colorhtml;
   echo '<br />
   <div class="size_guide_and_reset"><a href="';
   fitstudio_sizeguide(); // this function will echo the proper URL based on the categories
   echo '" target="_blank">Size Guide</a> | <a id="reset" href="#" onclick="return false;">Reset Selection</a></div>';


}


/**
 * Displays the label and value for a particular variant of a softline
 *
 * @param int $catentryid // CatEntry_ID for the variant of the product of which to get the options
 */
function softline_details($catentryid, $echo = true) {
	$details_html = '<div class="shcp-item-softline-options">';
	$matches = get_variants(array('catentryid' => $catentryid));
	foreach ($matches as $variant) {
		$prices['price'] = $variant['price']; // Get the price of the variant and overwrite the default price
		foreach ($variant as $key => $value) {
			// We don't know what keys are specific to product options, so we have to eliminate ones
			// we know AREN'T product options and get the correct options via process of elimination
			if($key != "partnumber" && $key != "price" && $key != "partnumber" 
			&& $key != "instock" && $key != "pid" && $key != "catentryid"){
				$details_html.= '&nbsp;&nbsp;<nobr><span class="softline-label">' . $key . '</span>: ' . $value . '</nobr> ';
			}
		}
	}
	$details_html .= '</div>';
	if ($echo) { echo $details_html; }
	else { return details_html; }
}

/**
 * Returns or echoes an image url based on the catentryid of the variant
 * Primarily used for SOFTLINE products
 * 
 * @param int $catentryid
 * @param int $height
 * @param int $width 
 * @param bool $echo
 * 
 * @return 
 */
function variant_image($catentryid, $height = '220', $width = null, $echo = true){
    $width = (!$width && $height) ?  $height: $width;

	$matches = get_variants(array('catentryid' => $catentryid));
	foreach ($matches as $variant) {
		foreach ($variant as $key => $value) {
			if($key == "Color"){
				$img_src = product_swatches($height, $width, $value);
				if ($img_src) {
					$image = reset($img_src);
				}
			}
		}
	}
	// If there is no image, use the default product image
	if (empty($image)) { $image = product_image($height, $width, $echo); }
    if($echo) { echo $image; }
    else { return $image; }
}


function product_catentryid($postid = null, $echo = true){
    $postid = (empty($postid)) ? the_ID() : $postid;

    $product = new Model_Products($postid);
    $ced = $product->load()->get_catentryid();

    if($echo) { echo $ced; }
    else { return $ced; }
}







########### PRODUCT CART ##############


 /**
  * @return string with the amount of $$$ saved in the cart
  */
function cart_savings(){
    return number_format((float)get_cart_object()->total_price - (float)get_cart_object()->total_item_price, 2, '.', '');

}

/**
 * @return string with cart checkout link
 */
function cart_checkout_link($echo = true){
    $link = Library_Sears_Api::factory('cart')->checkout()->load()->url();

    if($echo) { echo $link; }
    else { return $link; }
}









######## TESTING ##############


/**
 * Strictly for development purposes. A tool for use in crawling through the horrible sears api
 * @return type
 */
function sears_api_crawler($toplevel = ''){
    if ( is_softline() ) {

        $product_details = product_detail($toplevel, false);
        $crap = $product_details;


        print_pre( $crap );

    } else { return false; }
}


/**
* @return ????
 */
function soft_details() {
    $productvariants = product_detail('productvariants', false);
    $productvariants = $productvariants->prodlist[1][0]->product[1][0];

    // Types of variations (i.e. size, color)
    $variant_types  = $productvariants->attnames[1][0]->attname[1];
    $variant_values = $productvariants->prodvarlist->prodvar->attlist->attdata[1];
    $skulist        = $productvariants->prodvarlist->prodvar->skulist->sku[1];
    $color_swatches = $productvariants->prodvarlist->colorswatchlist->colorswatch[1];

    // creates an array of dropdown choices
    foreach($variant_types as $key => $value) {
        $variant_selectors[$value] = $variant_values[$key]->avals[1][0]->aval[1];
    }

    // creates an array of product ids and related attributes
    foreach($skulist as $key => $value) {
        $variant[$key]['catentryid'] = $value->pid;
        $variant[$key]['price'] = $value->price;
        $variant[$key]['in_stock'] = $value->stk;

        //I see what you did there....
        foreach($variant_types as $key1 => $value1) {
            $variant[$key][$value1] = $value->avals->aval[1][$key1];
        }
    }

    echo "<pre>";
    //print_r($variant_selectors);
    //print_r($variant);
    //print_r//($skulist);
    //print_r($color_swatches);
    echo "</pre>";

}


/**
 * Just a useful tool for debugging. This can be dropped anywhere, and toggled on
 * and off by setting the THEME_DEBUG constant.
 *
 * @param array $array [required] Array to be printed.
 */
function print_pre($array){

        if(constant("THEME_DEBUG")){
            echo "<pre style='z-index:1;background-color:rgba(192,192,192,0.9);left:0;top:100px;width:4000px;overflow:scroll;position:absolute;'>";
            print_r($array);
            echo "</pre>";
        }
}

/**
 * @param type $product
 * @return type
 */
// function get_cart_query() {
//
//    foreach(get_cart_products() as $item){
//        $partnumbers[] = $item->display_partnumber;
//    }
//    //print_pre($partnumbers);
//    query_posts( array(
//                'post_type' => 'shcproduct',
//                'meta_query' => array(
//                    array(
//                        'key' => 'partnumber',
//                        'value' => $partnumbers,
//                        'compare' => 'IN',
//                    )
//                )
//            ));
//    //print_pre($posts);
//    //return $posts;
//}



############ AJAX ##############
/**
 * @return ???
 */
function product_ajax(){

    global $is_cart_page, $cartid, $catentryid;

    if(isset($_POST['postid'])){
        $template = $_POST['template'];
        $id = (int) $_POST['postid'];
		if (!isset($_POST['catentryid'])) {
			// If it is a softline, we need to pull the catentryid of the variant out
			if ($_POST['is_softline']=="1") {
				$catentryid = retrieve_catentryid($_POST, $id);
			} else {
				$product = new Model_Products($id);
				$catentryid = $product->load()->get_catentryid();
			}
			$_POST['catentryid'] = $catentryid;
		}
        query_posts( array(
            'post_type' => 'shcproduct',
             'p' => $id
            ));

        while (have_posts()) : the_post();
            get_template_part('templates/' . $template);
        endwhile;

        wp_reset_query();
        exit;
    }
}

add_action('wp_ajax_product_ajax', 'product_ajax');
add_action('wp_ajax_nopriv_product_ajax', 'product_ajax');


/**
 * Ajax callback for front end quickview content.
 */
function cart_add_ajax() {
    if(isset($_POST['postid'])){
        $id = (int) $_POST['postid'];

		if (!isset($_POST['catentryid'])) {
			// If it is a softline, we need to pull the catentryid of the variant out
			if ($_POST['is_softline']=="1") {
				$_POST['catentryid'] = retrieve_catentryid($_POST, $id);
			} else {
				$product = new Model_Products($id);
				$_POST['catentryid'] = $product->load()->get_catentryid();
			}
		}

		// We're going to use $oldcart to compare the item count before and after the add
        $oldcart = new Controller_Front_Cart();
        $oldcart->ajax_response = false;
		$oldcart->cart->load(); 
		$oldcart->cart->view()->load();
		
        $cart = new Controller_Front_Cart();
        $cart->ajax_response = false;
        $cart->action_add();

		// If nothing got added then we're assuming it was a bad request and will handle it via jQuery
		if ($oldcart->cart->cart->item_count == $cart->cart->cart->item_count) {
			header("HTTP/1.0 400 Bad Request", true, 400);
		}

        exit;
    }
}
add_action('wp_ajax_cart_add_ajax', 'cart_add_ajax');
add_action('wp_ajax_nopriv_cart_add_ajax', 'cart_add_ajax');


/**
 * A simple function to grab the number of items currently in the cart.
 */
function get_cart_count_ajax() {
	$cart_prods = (array)get_cart_object()->items;
    $cart_item_count = count($cart_prods); 	
	echo $cart_item_count;
	exit;
}
add_action('wp_ajax_get_cart_count_ajax', 'get_cart_count_ajax');
add_action('wp_ajax_nopriv_get_cart_count_ajax', 'get_cart_count_ajax');


/**
 * Function to pull out catentryid from a product based on an inputed array of softline options.
 * Returns false if it doesn't match anything.
 *
 * @param array $postdata [required] $_POST array to be iterated and compared.
 * 		Must contain the softline options: Ex: [Color=red&Size=M]
 * @param int $id [required] id of the product from which we're going to extract the catentryid of the matching variant.
 */
function retrieve_catentryid($postdata, $id){
	$product_options = array();
	// Build an array of the $_POST softline options
	foreach ($postdata as $key => $value) {

		// Discard keys that aren't associated with softline options
		if ($key != "is_softline" && $key != "action" && $key != "postid" && $key != "template" && $key != "undefined") {
			$product_options[str_replace("-"," ", str_replace("_"," ",$key))] = stripslashes($value);
		}
	}
	// Get the variant from this id that match the options we have
	$prod_detail = get_variants($product_options, $id);

	// Should only be one variant returned, grab the catentryid
	if ($prod_detail) {
		foreach ($prod_detail as $variant) {
			return $variant['catentryid'];
		}
	}
	return false;
}

/**
 * Ajax function to see if a particular combination of softline options is both possible and in stock
 *
 * @param array $postdata [required] $_POST array to be iterated and compared.
 * @param int $id [required] id of the product from which we're going to extract the catentryid of the matching variant.
 */
function cart_check_avail_ajax(){
	if(isset($_POST['postid'])){
        $id = (int) $_POST['postid'];
		$_POST['instock'] = "true";
		// This checks to see if it exists and is in stock
		$catentryid = retrieve_catentryid($_POST, $id);
		if ($catentryid) {
			// Success, now echo the price for this variant
			$match = array_shift(get_variants(array('catentryid' => $catentryid), $id));
			echo number_format($match['price'], 2, '.', '');
			exit;
		}
	}
	exit;
}
add_action('wp_ajax_cart_check_avail_ajax', 'cart_check_avail_ajax');
add_action('wp_ajax_nopriv_cart_check_avail_ajax', 'cart_check_avail_ajax');

/**
 * Ajax callback for updating the cart.
 */
function cart_update_ajax(){
        global $is_cart_page, $catentryid, $cartid;

        $_POST = $_GET;

        //$is_cart_page = $_GET['is_cart'];

        $cart = new Controller_Front_Cart();
        $cart->ajax_response = false;
        $cart->action_update();
        product_ajax();

        exit;
}

add_action('wp_ajax_cart_update_ajax', 'cart_update_ajax');
add_action('wp_ajax_nopriv_cart_update_ajax', 'cart_update_ajax');




/**
 * Ajax callback for removing items from the cart.
 */
function cart_remove_ajax(){

        //print_pre($_POST['item_id']);
        // print_pre($item_id);
        $cart = new Controller_Front_Cart();
        $cart->ajax_response = false;
        $cart->action_remove();

        exit;

}

add_action('wp_ajax_cart_remove_ajax', 'cart_remove_ajax');
add_action('wp_ajax_nopriv_cart_remove_ajax', 'cart_remove_ajax');

/**
 * @return cart object
 */
function get_cart_object($cart = null){
    $cart = (isset($cart)) ? $cart : new Model_Cart();

    $cart = $cart->view()->load()->cart;
    return $cart;
}


/**
 * @return type
 */
function get_cart_products($cart = null, $sortby = 'display_partnumber'){

    $items = get_cart_object($cart)->items;

    foreach($items as $item){
        $products[$item->$sortby] = $item;
    }


    return $products;
}


/**
 * @param type $echo
 * @return type 
 */
function product_cart_data($property, $cart = null, $key = null, $echo = true){
    
    $cart_product = get_cart_products($cart, 'catentryid');
    
    if ($key) {
		$data = $cart_product[$key]->$property;
	} else {
		$data = $cart_product[product_meta('partnumber', false)]->$property;
	}    
    if($echo) { echo $data; }
    else { return $data; }
}

/**
 * Originally intended to find any available shipping method, this is now known to
 * not work. Not only in Indicator A not required as previously though, but if it is set
 * to 'pickup' it would require a
 *
 * @param type $post_id
 * @return type
 */
function IndicatorA($post_id){

    return product_detail('arrivalmethods', false, $post_id)->arrivalmethod[0];
}



########### WP Query Setup #############


/**
 * General use loop function. Allows for a template to be selected. Currently 
 * defaults to product template because that is used by our themes most often.

 * 
 * @global type $wp_query
 * @param type $template [optional] Template part to be used in the loop.
 */

function loop($template = 'post'){
    global $wp_query;
    
    if (have_posts()) { 
        while (have_posts()) {

            the_post();

            get_template_part('templates/'.$template);
        }    
    }

    wp_reset_query();

}

/**
 * Wrapper for the loop() function that sets up products specific query and sets
 * the product template to product.php
 * 
 * This need only be used to create secondary loops, otherwise product queries
 * should be taking place via the request filter in functions.php of the theme
 * 
 * @uses loop()
 * 
 * @param type $query_args
 * @param type $template 
 */
function products($query_args = array(), $template = 'product'){
  
    $product_query = array(
        'post_type' => 'shcproduct',
        'posts_per_page' => 12
    );
    $new_query = array_merge($product_query, $query_args);
    
    query_posts($new_query);
    
    loop($template);
    
    wp_reset_query();
}

function get_products($query_args = array()){
    $product_query = array(
        'post_type' => 'shcproduct',
        'posts_per_page' => 12
    );
    
    return new WP_Query(array_merge($product_query, $query_args));
}

/**
 * Retreives posts related to either the current post/page/product or the post id
 * passed to it.
 *
 * @param type $number_of_products 
 */
function related_products($number_of_products, $post_id = null, $template = 'product'){
    
    $post_id = (is_null($post_id)) ? get_the_ID() : $post_id;
    $meta_key = 'shcp_related_products';
    
    $related = get_post_meta($post_id, $meta_key, false);
    
    if(!empty($related[0])){
        products(array('posts_per_page' => $number_of_products, 'post__in' => $related[0]), $template);
    }
    
}

