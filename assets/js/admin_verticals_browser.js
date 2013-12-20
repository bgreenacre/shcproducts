var working = false;
var start_index = 1;
var end_index = 25;

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
					jQuery('#products_holder').html('');
					start_index = 1;
					end_index = 25;
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
					jQuery('#products_holder').html('');
					start_index = 1;
					end_index = 25;
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
					jQuery('#products_holder').html('');
					start_index = 1;
					end_index = 25;
					callback();
					preview_products();
				}
			);
		}
	});
	
	jQuery('#filter').change(function(){
		console.log('Changed filter.');
		var selected_filter_id = jQuery('#filter option:selected').attr('data-id');
		jQuery('.single_filter').hide();
		jQuery('#'+selected_filter_id).show();
		start_index = 1;
		end_index = 25;
		console.log('Selected Filter: '+selected_filter_id);
		console.log(jQuery('#'+selected_filter_id));
	});
	
	
	jQuery('.single_filter').change(function(){
		preview_products();
	});
	
	working = false;
}




function preview_products() {
	if(!working) {
		working = true;
		jQuery('#products_holder').html('<p>Loading products...</p>');
		var category = jQuery('#category option:selected').val();
		var vertical = jQuery('#vertical option:selected').val();
		var subcategory = jQuery('#subcategory option:selected').val();
		
		var selected_filter_id = jQuery('#filter option:selected').attr('data-id');
		var filter_name = jQuery('#filter option:selected').val();
		var filter_value = jQuery('#'+selected_filter_id).val();
	
		var category_search = {
			'vertical' : vertical,
			'category' : category,
			'subcategory' : subcategory,
			'filter_name' : filter_name,
			'filter_value' : filter_value
		};
		
		console.log(category_search);
	
		jQuery.post(
			shcp_ajax.ajaxurl,
			{
				action        : 'get_verticals_products',
				category_search : category_search,
				'start_index' : start_index,
				'end_index'	  : end_index
			},
			function(response) {
				jQuery('#products_holder').html(response);
				start_index += 25;
				end_index += 25;
				console.log('Preview products success');
				console.log(response);
				callback();
			}
		);
	}
}



function view_more_products() {
	console.log('Loading more products...');
	if(!working) {
		working = true;
		jQuery('.load_more_products').html('<p>Loading more products...</p>');
		var category = jQuery('#category option:selected').val();
		var vertical = jQuery('#vertical option:selected').val();
		var subcategory = jQuery('#subcategory option:selected').val();
		
		var selected_filter_id = jQuery('#filter option:selected').attr('data-id');
		var filter_name = jQuery('#filter option:selected').val();
		var filter_value = jQuery('#'+selected_filter_id).val();
	
		var category_search = {
			'vertical' : vertical,
			'category' : category,
			'subcategory' : subcategory,
			'filter_name' : filter_name,
			'filter_value' : filter_value
		};
		
		console.log(category_search);
	
		jQuery.post(
			shcp_ajax.ajaxurl,
			{
				action        : 'get_verticals_products',
				category_search : category_search,
				'start_index' : start_index,
				'end_index'	  : end_index,
				'list_items_only' : true
			},
			function(response) {
				jQuery('#product_preview_list').append(response);
				if(response == '') {
					jQuery('.load_more_products').html('All products loaded.');
				} else {
					jQuery('.load_more_products').html('<a href="javascript:void(0)" onclick="view_more_products()" class="button">Load More Products</a>');
				}
				start_index += 25;
				end_index += 25;
				console.log('Preview products success');
				console.log(response);
				callback();
			}
		);
	}
}

