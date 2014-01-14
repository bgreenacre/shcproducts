<div class="wrap shcp_wrap">
  <h2>Category Mapping <span class="view_all_button"><a href="<?php echo admin_url('edit.php?post_type=shcproduct&page=categorymapping&view=all'); ?>" class="button">View All</a></span></h2> 
  <p>This page provides the controls for mapping WordPress categories to Sears API categories for tracking and syncing purposes.</p>
  
	<div class="catmapping_shc_category">
	
		Sears API Category:

		<?php echo get_verticals_dropdown(); ?>

	</div>
	<div class="catmapping_wp_category">
	
		WordPress Category:

		<?php 
			$dropdown_args = array( 
					'show_count'    => 1,
					'hide_empty'    => 0,
					'hierarchical'  => 1,
					'name'          => 'shcp_category',
					'id'            => 'shcp_category'
					);
			wp_dropdown_categories($dropdown_args); ?>
			
			<div id="current_shc_category">
			</div>
			
			<div id="mapping_button" style="display:none;">
				Link Categories
			</div>

	</div>


	<div id="ajax_loading_overlay">
		<div id="ajax_loading"></div>
	</div>
  
</div>


<?php


// Import process:

$post_obj = new Product_Post_Model(9168);
//$prod_obj = new Product_Model('007VA54248712P');
//error_log('$prod_obj = '.print_r($prod_obj,true));

//$part_number = '00806901000P'; // Hardline / Kenmore Elite 900 Watt Brushed Aluminum Blender
//$part_number = 'TEST12345678'; // Invalid part number
//$part_number = '076SA005000P'; // Shoe available in Medium and Wide widths
//$part_number = '076VA21776701P'; // Shoe available in Medium and Wide widths
// $part_number = '076VA55548812P'; // Shoe available in Medium and Wide widths
// 
// $api_obj = new Product_Details_Api();
// $api_result = $api_obj->get_product($part_number);
// 
// $prod_obj = new Product_Model($part_number); 
// $import_result = $prod_obj->import_product();
// 
// 
// echo '<pre>';
// 
// echo 'API Result = '.strip_tags(print_r($api_result,true));
// 
// echo '
// ------------------------
// 
// ';
// 
// echo 'Standardized Product = '.strip_tags(print_r($api_result->product,true));
// 
// echo '
// ------------------------
// 
// ';
// 
// echo '$prod_obj = '.print_r($prod_obj,true);
// 
// echo '
// ------------------------
// 
// ';
// 
// echo 'Import result: '.print_r($import_result,true);
// 
// echo '</pre>';

?>

