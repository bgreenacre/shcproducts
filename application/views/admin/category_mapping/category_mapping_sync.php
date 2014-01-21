<div class="wrap clearfix">
	<div class="icon32" id="icon-options-general"><br></div>
	<h2>Sync Product Categories</h2>
	
	<br/>
	
	<a href="<?php echo admin_url('edit.php?post_type=shcproduct&page=categorymapping&view=all'); ?>" class="button">&larr; View All</a>
	
	<span id="begin_update_button_holder">
		<a href="javascript:void(0);" onclick="toggle_begin();" class="button-primary">Begin Sync</a>
	</span>
	
	<br/><br/>
	
</div>



<script type="text/javascript">
//	var products = <?php echo (isset($products)) ? $products : '0'; ?>;

	var categories = <?php echo $categories; ?>;
	var products = new Array();
	var current_product = 0;
	var current_category = 0;
	var paused = true;
	
	var total_imported = 0;
	var total_products_processed = 0;
	
// 	var total_updated = 0;
// 	var total_draft = 0;
// 	var total_deleted = 0;
// 	var total_no_action = 0;
	
	var elapsed_time = 0;
		
	function toggle_begin() 
	{ // If paused, begin (try to update the next one). If currently working, pause.
		if(paused) {
			jQuery("#begin_update_button_holder .button-primary").html('Working, Click To Pause');
			paused = false;
			jQuery('.meter').removeClass('paused');
			ajax_update_next();
		} else {
			jQuery("#begin_update_button_holder .button-primary").html('Resume Update');
			jQuery('.meter').addClass('paused');
			paused = true;
		}
	}
	
	function ajax_update_next() {
		
		if(current_product >= products.length) {
			// Fetch the next batch of products to update.
			current_product = 0;
			current_category++;
			
			if(current_category < categories.length) {
				//get_category_shc_products
				var post_data = {
					action: 'get_category_shc_products',
					category_id: categories[current_category].id
				};
		
				jQuery.ajax({
					url:shcp_ajax.ajaxurl,
					data:post_data,
					dataType:'json',
					type:'post',
					success:function(data){					
						if( data.products != undefined && Object.prototype.toString.call( data.products ) === '[object Array]' ) {
							products = data.products;
						} else {
							products = new Array();
						}
						
						data.category_name = categories[current_category].name;
						
						update_screen(data);
						
						if(!paused) {
							ajax_update_next();
						}
					},
					error:function(j, t, e){
						console.log('Ajax error...');
						console.log(j);
						console.log(t);
						console.log(e);
				
						paused = true;
					}
				});
			} else {
				paused = true;
				jQuery("#begin_update_button_holder").html('Update Complete!');
				jQuery('#progress_holder .meter').addClass('nostripes');
				update_screen();
				return;
			}
		} else {
			// Update the next product:
			var product_to_update = products[current_product];
			current_product++;
			total_products_processed++;
			
			if(product_to_update.action != undefined && product_to_update.action == 'IMPORT') {
				var post_data = {
					action: 'update_single_shc_product',
					category_id: categories[current_category].id,
					part_number: product_to_update.part_number
				};
		
				jQuery.ajax({
					url:shcp_ajax.ajaxurl,
					data:post_data,
					dataType:'json',
					type:'post',
					success:function(data){						
						if(data.imported) {
							total_imported++;
						}
						
						update_screen({
							'msg' : data.msg
						});
						
						if(!paused) {
							ajax_update_next();
						}
					},
					error:function(j, t, e){
						console.log('Ajax error...');
						console.log(j);
						console.log(t);
						console.log(e);
				
						paused = true;
					}
				});
			} else {
				update_screen({
					'msg' : 'Part number '+product_to_update.part_number+' - '+product_to_update.action
				});
			
				if(!paused) {
					ajax_update_next();
				}
			}
		}	
	}
	
	function update_screen(data) 
	{ // Update various things on the screen.
	
		// Update status bar:
		var complete_percent = current_category / categories.length;
		complete_percent = complete_percent * 100;
		complete_percent = complete_percent.toPrecision(4);
		var status_bar = complete_percent;
		jQuery('#progress_holder .meter span').css('width', status_bar+'%');
		jQuery('#progress_percent_holder').html(complete_percent+'%');
		
		// Update processed count:
		jQuery('#processed_count').html(current_category);
		jQuery('#imported_count').html(total_imported);
		jQuery('#product_processed_count').html(total_products_processed);
		
		// Update current category:
		jQuery('#current_category_name').html('Current Category: <b>'+categories[current_category].name+'</b>');
		
		var msg = '';
		
		if(data != undefined && data.category_name != undefined) {
			msg = 'Beginning sync of category: '+data.category_name;
			if(data.products != undefined && Object.prototype.toString.call( data.products ) === '[object Array]') {
				var s = 's';
				if(data.products.length == 1) s = '';
				msg += ' -- '+ data.products.length +' product'+ s +' found in this category.';
			} else {
				msg += ' -- No products found in this category.';
			}
		}
		
		if(data != undefined && data.msg != undefined) {
			msg = data.msg;
		}
		
		
		// Update the console with the message:
		if(msg != '') {
			jQuery('#detail_holder').append('<p>'+msg+'</p>');
		}
		
// 		total_updated += data.updated;
// 		total_draft += data.draft;
// 		total_deleted += data.deleted;
// 		total_no_action += data.no_action;
// 		
// 		// Put things into various boxes on the screen:
// 		jQuery('#progress_percent_holder').html(complete_percent+'%');
// 		jQuery('#detail_holder').append('<p>'+data.cron_msg+'</p>');
// 		jQuery('#progress_holder .meter span').css('width', status_bar+'%');
// 		
// 		jQuery('#updated #updated_count').html(total_updated);
// 		jQuery('#deleted #deleted_count').html(total_deleted);
// 		jQuery('#draft #draft_count').html(total_draft);
// 		jQuery('#no_action #no_action_count').html(total_no_action);
// 		jQuery('#processed_count').html(current);
// 		
// 		// Add some obnoxious highlighting for error messages, etc.:
// 		if(total_draft > 0) {
// 			jQuery('#draft').css('color','#FF8800');
// 			jQuery('#draft').css('font-weight','bold');
// 		}
// 		if(total_deleted > 0) {
// 			jQuery('#deleted').css('color','#FF0000');
// 			jQuery('#deleted').css('font-weight','bold');
// 		}
// 		if(total_no_action > 0) {
// 			jQuery('#no_action').css('color','#FF0000');
// 			jQuery('#no_action').css('background-color','#FFFF00');
// 			jQuery('#no_action').css('font-weight','bold');
// 		}
		
		// Scroll to bottom of detail box:
		var detail_box = jQuery('#detail_holder');
		var height = detail_box[0].scrollHeight;
		detail_box.scrollTop(height);
	}
	
	function selectText() {
		var containerid = 'detail_holder';
		jQuery('#detail_holder').scrollTop(0);
        if (document.selection) {
            var range = document.body.createTextRange();
            range.moveToElementText(document.getElementById(containerid));
            range.select();
        } else if (window.getSelection) {
            var range = document.createRange();
            range.selectNode(document.getElementById(containerid));
            window.getSelection().addRange(range);
        }
    }
	
	// Keep track of how much time this is taking:
	jQuery(document).ready(function(){
		setInterval(function() {
			if(!paused) {
				elapsed_time += 1;
			}
			jQuery('#elapsed_time').html(elapsed_time);
		}, 1000);
	});
	
</script>


<table class="force_update_table" width="95%">

	<tr>

		<td width="30%">
			<b>Total Categories: <?php echo count(json_decode($categories)); ?></b>
			<br>
			<b>Categories Processed: <span id="processed_count">0</span></b>
			<br>
			<b>Products Processed: <span id="product_processed_count">0</span></b>
			<br/><br/>
			<span id="imported">Products Imported: <span id="imported_count">0</span> </span>
			<br/><br/>
			Elapsed Time: <span id="elapsed_time">0</span> seconds
		</td>
		
		<td width="70%">
			<div class="current_category_name"><span id="current_category_name"></span></div>
			<div id="detail_holder">
				
			</div>
			<a href="javascript:void(0);" onclick="selectText()">Select All</a>
		</td>

	</tr>
	
	
	<tr>
	
		<td colspan="2">
			<div id="progress_holder">
				<div class="meter">
					<span style="width:0%">
					</span>
				</div>
				
				<div id="progress_percent_holder">
				0%
				</div>
			</div>
		</td>
	
	</tr>

</table>