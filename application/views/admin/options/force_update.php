<div class="wrap clearfix">
	<div class="icon32" id="icon-options-general"><br></div>
	<h2>Update Products</h2>
	
	<br/>
	
	<a href="<?php echo admin_url('options-general.php?page=SHCP_options'); ?>" class="button">&larr; Go Back</a>
	
	<span id="begin_update_button_holder">
		<a href="javascript:void(0);" onclick="toggle_begin();" class="button-primary">Begin Update</a>
	</span>
	
	<br/><br/>
	
</div>


<script type="text/javascript">
	var products = <?php echo $products; ?>;
	var current = 0;
	var paused = true;
	
	var total_updated = 0;
	var total_draft = 0;
	var total_deleted = 0;
	var total_no_action = 0;
	
	var elapsed_time = 0;
		
	function toggle_begin() 
	{ // If paused, begin (try to update the next one). If currently working, pause.
		if(paused) {
			jQuery("#begin_update_button_holder .button-primary").html('Working, Click To Pause');
			paused = false;
			ajax_update_next();
		} else {
			jQuery("#begin_update_button_holder .button-primary").html('Resume Update');
			paused = true;
		}
	}
	
	function ajax_update_next() {
	
		if(current >= products.length) {
			paused = true;
			jQuery("#begin_update_button_holder").html('Update Complete!');
			jQuery('#progress_holder .meter').addClass('nostripes');
			return;
		}
	
		var post_data = {
			action: 'update_single_product',
			post_id: products[current].ID
		};
		
		jQuery.ajax({
			url:shcp_ajax.ajaxurl,
			data:post_data,
			dataType:'json',
			type:'post',
			success:function(data){
				current++;
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
	
	}
	
	function update_screen(data) 
	{ // Update various things on the screen.
	
		// Calculate & Update some numbers:
		var complete_percent = current / products.length;
		complete_percent = complete_percent * 100;
		complete_percent = complete_percent.toPrecision(4);
		var status_bar = complete_percent;
		
		total_updated += data.updated;
		total_draft += data.draft;
		total_deleted += data.deleted;
		total_no_action += data.no_action;
		
		// Put things into various boxes on the screen:
		jQuery('#progress_percent_holder').html(complete_percent+'%');
		jQuery('#detail_holder').append('<p>'+data.cron_msg+'</p>');
		jQuery('#progress_holder .meter span').css('width', status_bar+'%');
		
		jQuery('#updated #updated_count').html(total_updated);
		jQuery('#deleted #deleted_count').html(total_deleted);
		jQuery('#draft #draft_count').html(total_draft);
		jQuery('#no_action #no_action_count').html(total_no_action);
		jQuery('#processed_count').html(current);
		
		// Add some obnoxious highlighting for error messages, etc.:
		if(total_draft > 0) {
			jQuery('#draft').css('color','#FF8800');
			jQuery('#draft').css('font-weight','bold');
		}
		if(total_deleted > 0) {
			jQuery('#deleted').css('color','#FF0000');
			jQuery('#deleted').css('font-weight','bold');
		}
		if(total_no_action > 0) {
			jQuery('#no_action').css('color','#FF0000');
			jQuery('#no_action').css('background-color','#FFFF00');
			jQuery('#no_action').css('font-weight','bold');
		}
		
		// Scroll to bottom of detail box:
		var detail_box = jQuery('#detail_holder');
		var height = detail_box[0].scrollHeight;
		detail_box.scrollTop(height);
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
			<b>Total Products: <?php echo count(json_decode($products)); ?></b>
			<br>
			<b>Processed: <span id="processed_count">0</span></b>
			<br/><br/>
			<span id="updated">Updated: <span id="updated_count">0</span> </span>
			<br/>
			<span id="draft">Set To Draft: <span id="draft_count">0</span> </span>
			<br/>
			<span id="deleted">Deleted: <span id="deleted_count">0</span> </span>
			<br/>
			<span id="no_action">No Action: <span id="no_action_count">0</span> </span>
			<br/><br/>
			Elapsed Time: <span id="elapsed_time">0</span> seconds
		</td>
		
		<td width="70%">
			<div id="detail_holder">
				
			</div>
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