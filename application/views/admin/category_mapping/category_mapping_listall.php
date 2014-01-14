<div class="wrap shcp_wrap">
  <h2>All Category Associations <span class="view_all_button"><a href="<?php echo admin_url('edit.php?post_type=shcproduct&page=categorymapping'); ?>" class="button">&larr; Back</a> <a href="<?php echo admin_url('edit.php?post_type=shcproduct&page=categorymapping&view=all&validate=yes'); ?>" class="button">Check All</a></span></h2> 
  <p>This page provides a list of the existing associations between WordPress categories and Sears API categories for tracking and syncing purposes.</p>
  
  <table class="category_mapping widefat">
  	<tr>
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
	$args = array(
		'type'                     => 'post',
		'child_of'                 => 0,
		'parent'                   => (int)$cat_obj->cat_ID,
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
	//error_log('Args = '.print_r($args,true));
	$children = get_categories($args);
	if(!empty($children)) {
		foreach($children as $child) {
			$name = $parent_string.' &rarr; '.$child->cat_name;
			cat_mapping_display_row($name, $child);
		}
		//error_log('Children not empty = '.print_r($children,true));
	}
	$shc_category = get_option('shcproducts_category_'.$cat_obj->cat_ID);
	if( !empty($shc_category) && isset($_GET['validate']) && $_GET['validate'] == 'yes') {
		$result = get_api_category_by_json($shc_category);
		if(is_object($result) && isset($result->product_count) && $result->product_count != 0) {
			$val_class = ' class="ok"';
			$s = ($result->product_count == 1) ? '' : 's';
			$val_content = $result->product_count.'&nbsp;product'.$s;
		} else {
			$val_class = ' class="error"';
			$val_content = 'Invalid Sears API category detected';
		}
		error_log('$result = '.print_r($result,true));
	} else {
		$val_class = '';
		$val_content = '';
	}
	if(!empty($shc_category)) {
		$jcat = json_decode($shc_category);
		if($jcat) {
			//error_log('$jcat = '.print_r($jcat,true));
			$shc_category = 'Vertical: '.$jcat->vertical.' &rarr; Category: '.$jcat->category.' &rarr; Subcategory: '.$jcat->subcategory;
			if(!empty($jcat->filter_name) && !empty($jcat->filter_value)) {
				$shc_category .= ' &rarr; Filter: '.$jcat->filter_name.' = '.$jcat->filter_value;
			}
		}
	}
	echo '
	<tr>
		<td>'.$parent_string.'</td>
		<td>'.$shc_category.'</td>
		<td'.$val_class.'>'.$val_content.'</td>
	</tr>';
}

?>

	</table>