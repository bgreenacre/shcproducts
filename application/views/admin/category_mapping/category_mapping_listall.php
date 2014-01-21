<div class="wrap shcp_wrap">
  <h2>All Category Associations <span class="view_all_button"><a href="<?php echo admin_url('edit.php?post_type=shcproduct&page=categorymapping'); ?>" class="button">&larr; Manage</a> 
  
  <?php if(isset($_GET['validate']) && $_GET['validate'] == 'yes') { ?>
  <a href="<?php echo admin_url('edit.php?post_type=shcproduct&page=categorymapping&view=all'); ?>" class="button">View All</a> 
  <?php } else { ?>
  <a href="<?php echo admin_url('edit.php?post_type=shcproduct&page=categorymapping&view=all&validate=yes'); ?>" class="button">Check All</a> 
  <?php } ?>
  
  <a href="<?php echo admin_url('edit.php?post_type=shcproduct&page=categorymapping&sync=yes'); ?>" class="button">Sync Now</a></span></h2> 
  <p>This page provides a list of the existing associations between WordPress categories and Sears API categories for tracking and syncing purposes.</p>
  
  <table class="category_mapping widefat">
  	<tr>
  		<th>ID</th>
  		<th>WordPress Category</th>
  		<th>Sears API Category</th>
  		<th></th>
  	</tr>
  
<?php 
$args = array(
	'type'                     => 'post',
	'child_of'                 => 0,
	'parent'                   => 0,
	'orderby'                  => 'name',
	'order'                    => 'ASC',
	'hide_empty'               => 0,
	'hierarchical'             => 1,
	'exclude'                  => '',
	'include'                  => '',
	'number'                   => '',
	'taxonomy'                 => 'category',
	'pad_counts'               => false 

);
$categories = get_categories( $args ); 

foreach($categories as $category) {
	cat_mapping_display_row($category->cat_name, $category);
}

function cat_mapping_display_row($parent_string, $cat_obj) {
	$validating = (isset($_GET['validate']) && $_GET['validate'] == 'yes') ? true : false;
	
	$shc_category = get_option('shcproducts_category_'.$cat_obj->cat_ID);

	$shc_category_output = array();
	$val_class = array();
	$val_content = array();

	if(!empty($shc_category)) {
		$count = 0;
		if(is_array($shc_category)) {
			foreach($shc_category as $index => $shc_cat) {
				$count++;
				$new = 'Vertical: '.$shc_cat['vertical'].' &rarr; Category: '.$shc_cat['category'].' &rarr; Subcategory: '.$shc_cat['subcategory'];
				if(!empty($shc_cat['filter_name']) && !empty($shc_cat['filter_value'])) {
					$new .= ' &rarr; Filter: '.$shc_cat['filter_name'].' = '.$shc_cat['filter_value'];
				}
				if($validating) {
					$result = get_api_category_by_array($shc_cat);
					if(is_object($result) && isset($result->product_count) && $result->product_count != 0) {
						$val_class[$count] = ' class="ok"';
						$s = ($result->product_count == 1) ? '' : 's';
						$val_content[$count] = $result->product_count.'&nbsp;product'.$s;
					} else {
						$val_class[$count] = ' class="error"';
						$val_content[$count] = 'Invalid Sears API category detected';
					}
				}
				$shc_category_output[$count] = $new;
			}
		}
	}
	echo '
	<tr>
		<td rowspan="'.$count.'">'.$cat_obj->cat_ID.'</td>
		<td rowspan="'.$count.'">'.$parent_string.'</td>';
		if(isset($shc_category_output) && is_array($shc_category_output) && !empty($shc_category_output)) {
			foreach($shc_category_output as $index => $single) {
				echo '<td>'.$single.'</td>
				<td'.$val_class[$index].'>'.$val_content[$index].'</td>';
				echo '</tr><tr>';
			}
		} else {
			echo '<td></td><td></td>';
		}
		
	echo '</tr>';
	
	$args = array(
		'type'                     => 'post',
		'parent'                   => (int)$cat_obj->cat_ID,
		'orderby'                  => 'name',
		'order'                    => 'ASC',
		'hide_empty'               => 0,
		'hierarchical'             => 1,
		'taxonomy'                 => 'category',
		'pad_counts'               => false 
	);
	$children = get_categories($args);
	if(!empty($children)) {
		foreach($children as $child) {
			$name = $parent_string.' &rarr; '.$child->cat_name;
			cat_mapping_display_row($name, $child);
		}
	}
}

?>

	</table>