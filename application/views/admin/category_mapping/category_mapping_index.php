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
					'id'            => 'shcp_category',
					'orderby'		=> 'NAME'
					);
			wp_dropdown_categories($dropdown_args); ?>
			
			<div id="current_shc_category">
			</div>
			
			<a href="javascript:void(0);" id="mapping_button" style="display:none;" class="button-primary">
				Link Categories
			</a>

	</div>


	<div id="ajax_loading_overlay">
		<div id="ajax_loading"></div>
	</div>
  
</div>
