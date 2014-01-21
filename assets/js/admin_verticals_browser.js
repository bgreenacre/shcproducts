var working = false;
var start_index = 1;
var end_index = 25;

jQuery(document).ready(function($) {
	
	$('#vertical').change(function(){
		if(!working) {
			working = true;
			var search_keyword = $('#vertical option:selected').val();
		
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
					jQuery('#category_json').html('');
					start_index = 1;
					end_index = 25;
					callback();
				}
			);
		}
	});
	
	$('#shcp_category').change(function(){
		current_mapping_display();
		mapping_button_display();
	});
	
	jQuery('#mapping_button').click(function(){
		save_category_mapping();
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
					jQuery('#category_json').html('');
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
					jQuery('#category_json').html('');
					start_index = 1;
					end_index = 25;
					callback();
					preview_products();
				}
			);
		}
	});
	
	jQuery('#filter').change(function(){
		var selected_filter_id = jQuery('#filter option:selected').attr('data-id');
		jQuery('.single_filter').hide();
		jQuery('#'+selected_filter_id).show();
		start_index = 1;
		end_index = 25;
	});
	
	
	jQuery('.single_filter').change(function(){
		preview_products();
	});
	
	jQuery('.import_button').click(function(){
		var imported = jQuery(this).attr('data-imported');
		var partnumber = jQuery(this).attr('data-partnumber');
		if(imported == '') {
			// Not yet imported, import now:
			jQuery(this).addClass('importing');
			jQuery(this).html('Importing Product...');
			import_product(partnumber, jQuery(this));
		} else {
			jQuery(this).addClass('deleting');
			jQuery(this).html('Deleting Product...');
		}
	});
	
	working = false;
	
	mapping_button_display();
}


function import_product(partnumber, j){
	jQuery.post(
		shcp_ajax.ajaxurl,
		{
			action        : 'import_single_product',
			part_number	  : partnumber
		},
		function(response) {
			if(response == 1) {
				j.removeClass('importing');
				j.addClass('already_imported');
				j.attr('data-imported','imported');
				j.html('Imported');
			} else {
				alert('Import error - ' + response);
				j.attr('data-imported','error');
				j.html('Could not import');
			}
		}
	);
}

function isNumber(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}


function mapping_button_display() {
	// Decide whether to display mapping button:
	var json_category = jQuery('#category_json').html();
	if(json_category == '') {
		jQuery('#mapping_button').hide();
	} else {
		jQuery('#mapping_button').show();
	}
}

function current_mapping_display() {
	// get_current_category_mapping
	var category_id = jQuery('#shcp_category').val();
	jQuery.post(
		shcp_ajax.ajaxurl,
		{
			action        : 'get_current_category_mapping',
			'wp_category_id' : category_id
		},
		function(response) {
			if(response != '') {
				var robj2 = jQuery.parseJSON( response );
				var rstring = '';
				for(var rindex in robj2) {
					var robj = robj2[rindex];
					rstring += '<b>Vertical: </b>'+robj.vertical+'<br/><b>Category: </b>'+robj.category+'<b><br/>Subcategory: </b>'+robj.subcategory;
					if(robj.filter_name != '' && robj.filter_value != '') {
						rstring += '<br/><b>Filter:</b> '+robj.filter_name+' = '+robj.filter_value;
					}
					var param_index = -1;
					if(isNumber(rindex)) param_index = rindex;
					rstring += '<br/><a href="javascript:void(0);" onclick="remove_category_mapping('+param_index+')" class="button">Remove</a>';
					rstring += '<br/><br/>';
				}
				jQuery('#current_shc_category').html('<h4>Current Sears API category mapping:</h4>'+rstring);
			} else {
				jQuery('#current_shc_category').html('This WordPress category is not currently associated with any Sears API category.');
			}
		}
	);
}

function save_category_mapping() {
	var json_category = jQuery('#category_json').html();
	var category_id = jQuery('#shcp_category').val();
	
	jQuery('#current_shc_category').html('Saving...');
	
	jQuery.post(
		shcp_ajax.ajaxurl,
		{
			action        : 'save_category_mapping',
			'shc_category_json' : json_category,
			'wp_category_id' : category_id
		},
		function(response) {
			if(response == 1) {
				current_mapping_display();
			} else {
				jQuery('#current_shc_category').html(response);
			}
		}
	);
}


function remove_category_mapping(option_index) {
	var category_id = jQuery('#shcp_category').val();
	
	jQuery('#current_shc_category').html('Saving...');
	
	jQuery.post(
		shcp_ajax.ajaxurl,
		{
			action        : 'remove_category_mapping',
			'wp_category_id' : category_id,
			'option_index' : option_index
		},
		function(response) {
			if(response == 1) {
				current_mapping_display();
			} else {
				jQuery('#current_shc_category').html(response);
			}
		}
	);
}



function preview_products() {
	if(!working) {
		working = true;
		start_index = 1;
		end_index = 25;
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
		
		var json_category_data = JSON.stringify(category_search);
		jQuery('#category_json').html(json_category_data);
			
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
				callback();
			}
		);
	}
}



function view_more_products() {
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
				callback();
			}
		);
	}
}

