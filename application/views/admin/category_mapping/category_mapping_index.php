<div class="wrap shcp_wrap">
  <h2>Category Mapping</h2> 
  <p>This page provides the controls for mapping WP categories to SHC categories for tracking and syncing purposes.</p>
</div>


<?php


// Import process:

//$part_number = '00806901000P'; // Hardline / Kenmore Elite 900 Watt Brushed Aluminum Blender
//$part_number = 'TEST12345678'; // Invalid part number
//$part_number = '076SA005000P'; // Shoe available in Medium and Wide widths
//$part_number = '076VA21776701P'; // Shoe available in Medium and Wide widths
$part_number = '076VA55548812P'; // Shoe available in Medium and Wide widths

$api_obj = new Product_Details_Api();
$api_result = $api_obj->get_product($part_number);

$prod_obj = new Product_Model($part_number); 
$import_result = $prod_obj->import_product();


echo '<pre>';

echo 'API Result = '.strip_tags(print_r($api_result,true));

echo '
------------------------

';

echo 'Standardized Product = '.strip_tags(print_r($api_result->product,true));

echo '
------------------------

';

echo '$prod_obj = '.print_r($prod_obj,true);

echo '
------------------------

';

echo 'Import result: '.print_r($import_result,true);

echo '</pre>';