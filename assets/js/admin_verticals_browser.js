jQuery(document).ready(function($) {

	console.log('admin_verticals_browser ready');
	
	$('#verticals').change(function(){
		var search_keyword = $('#verticals option:selected').val();
		console.log(search_keyword);
		
		jQuery.post(
			shcp_ajax.ajaxurl,
			{
				action        : 'get_verticals_category',
				search_keyword : search_keyword
			},
			function(response) {
				jQuery('#category_holder').html(response);
				jQuery('#subcategory_holder').html('');
				callback();
			}
		);
	});

});



function callback() {
	jQuery('#categories').change(function(){
		var category = jQuery('#categories option:selected').val();
		var vertical = jQuery('#verticals option:selected').val();
		
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
				category_search : category_search
			},
			function(response) {
				jQuery('#subcategory_holder').html(response);
			}
		);
	});
}