var working = false;

jQuery(document).ready(function($) {

	console.log('admin_verticals_browser ready');
	
	$('#vertical').change(function(){
		if(!working) {
			working = true;
			var search_keyword = $('#vertical option:selected').val();
			console.log(search_keyword);
		
			jQuery.post(
				shcp_ajax.ajaxurl,
				{
					action        : 'get_verticals_category',
					search_keyword : search_keyword,
					type_selected : 'vertical'
				},
				function(response) {
					jQuery('#category_holder').html(response);
					jQuery('#subcategory_holder').html('');
					jQuery('#filter_holder').html('');
					callback();
				}
			);
		}
	});

});



function callback() {
	jQuery('#category').change(function(){
		if(!working) {
			working = true;
			var category = jQuery('#category option:selected').val();
			var vertical = jQuery('#vertical option:selected').val();
		
			var category_search = {
				'vertical' : vertical,
				'category' : category
			};
		
			var search_keyword = vertical+'|'+category;
			console.log(search_keyword);
		
			jQuery.post(
				shcp_ajax.ajaxurl,
				{
					action        : 'get_verticals_category',
					search_keyword : search_keyword,
					category_search : category_search,
					type_selected : 'category'
				},
				function(response) {
					jQuery('#subcategory_holder').html(response);
					jQuery('#filter_holder').html('');
					callback();
				}
			);
		}
	});
	
	jQuery('#subcategory').change(function(){
		if(!working) {
			working = true;
			console.log('changed subcategory');
			var category = jQuery('#category option:selected').val();
			var vertical = jQuery('#vertical option:selected').val();
			var subcategory = jQuery('#subcategory option:selected').val();
		
			var category_search = {
				'vertical' : vertical,
				'category' : category,
				'subcategory' : subcategory
			};
		
			jQuery.post(
				shcp_ajax.ajaxurl,
				{
					action        : 'get_verticals_filter',
					category_search : category_search,
					type_selected : 'subcategory'
				},
				function(response) {
					jQuery('#filter_holder').html(response);
					callback();
				}
			);
		}
	});
	
	jQuery('#filter').change(function(){
		console.log('Changed filter.');
		var selected_filter_id = jQuery('#filter option:selected').attr('data-id');
		jQuery('.single_filter').hide();
		jQuery('#'+selected_filter_id).show();
		console.log('Selected Filter: '+selected_filter_id);
		console.log(jQuery('#'+selected_filter_id));
	});
	
	working = false;
}