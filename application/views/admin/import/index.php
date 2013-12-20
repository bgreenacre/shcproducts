<div class="wrap shcp_wrap">
  <h2>Import Products</h2> 
  <p>To see products select a search method, enter the search terms and click the Search button.</p>
  <form action="" id="partnumber_form" method="post">
    <div class="shcp_form_labels">
      <label for="search_terms">Part Number: </label>
    </div>
    <div class="shcp_form_fields">
      <input type="text" name="search_terms" class="search_terms search_terms_partnumber" id="search_terms_partnumber" value="Enter part number" />
      <input type="submit" name="submit_partnumber" id="submit_partnumber" value="Search" />
    </div>
  </form>
  <form action="" id="keyword_form" method="post">
    <div class="shcp_form_labels">
      <label for="search_terms">Keyword Search: </label>
    </div>
    <div class="shcp_form_fields">
      <input type="text" name="search_terms" class="search_terms" id="search_terms_keyword" value="Enter search terms" />
      <input type="submit" name="submit_keyword" id="submit_keyword" value="Search" />
    </div>
  </form>
  <form action="" id="vertical_form" method="post">  
    <div class="shcp_form_labels">
      <label for="search_terms">Vertical Search: </label>
    </div>
    <div class="shcp_form_fields">
      <input type="text" name="search_terms" class="search_terms" id="search_terms_vertical" value="Enter search terms" />
      <input type="submit" name="submit_vertical" id="submit_vertical" value="Search" />
    </div>
    <div class="shcp_form_fields">
      <div id="shcp_categories"></div>
      <div id="shcp_subcategories"></div>
    </div>  
  </form>  
  <div id="shcp_import_list"></div>
  <div id="ajax_loading_overlay">
    <div id="ajax_loading"></div>
  </div>
</div>

<?php

// $obj = new Product_Search_Api();
// 
// $args = array(
// 	'api_version' => 'v1',
// 	'search_type' => 'product',
// 	'return_type' => 'json',
// 	//'search_keyword' => 'Hat'
// 	'category_search' => array(
// 		'vertical' => 'Fitness & Sports',
// 		'category' => 'Treadmills',
// 		'subcategory' => 'Treadmills',
// 	),
// // 	'filter' => array(
// // 		'Sport' => 'Hiking'
// // 	)
// );
// 
// $obj->set_up_request($args);
// $result = $obj->make_request();
// error_log(print_r($result,true));



//echo get_verticals_dropdown();

//$test = new Product_Model('3ZZVA55174312P');
//$test = new Product_Model('015W001553543000P');
//error_log('New product model = '.print_r($test,true));


$test = new Product_Details_Api();
$result = $test->get_product('007VA54248712P');
error_log('Product details api = '.print_r($test,true));


?>