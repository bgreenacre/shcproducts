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

//$obj = new Product_Search_Api();
//$r = $obj->get_categories('Fitness & Sports');
//error_log(print_r($r,true));


// Data standardization:
$obj = new Product_Details_Api();
//$r = $obj->get_product('007VA58000212P'); // Sweatpants (soft line) - multiple sizes, 1 color
//$r = $obj->get_product('007VA65853512P'); // Athletic shirt (soft line) - multiple sizes, multiple colors
//$r = $obj->get_product('076SA005000P'); // Walking shoe (soft line) - available in medium & wide widths
//$r = $obj->get_product('00840604000P'); // Toaster (hard line)
//$r = $obj->get_product('00621999000P'); // Exercise bike (hard line)
//$r = $obj->get_product('SPM10883623415'); // misc
// $r = $obj->get_product('SPM10883623415'); // misc
// $r = $obj->get_product('00869293000P'); // misc
// error_log('Product Details = '.print_r($r,true));
// error_log('Standardized product = '.print_r($r->product,true));

// Import process:
//$prod_obj = new Product_Model('00840604000P');
//$prod_obj = new Product_Model('00840603000P');
// $prod_obj = new Product_Model('00869293000P');
// $prod_obj->import_product();
// error_log('$prod_obj = '.print_r($prod_obj,true));

// Already imported / post related:
//$p = new Product_Post_Model('12583');
//error_log('$p = '.print_r($p, true));

// Cart testing:
// $c = new Cart_Api();
// $c->add_to_cart('42972211');


echo get_verticals_dropdown();


?>