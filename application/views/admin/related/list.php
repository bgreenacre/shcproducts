<?php wp_nonce_field('shcproducts_related', 'shcproducts_noncename'); ?>
<div id="shcp_products">
<div id="shcp_filter_products">
<input type="text" value="" name="shcp_keyword" id="shcp_keyword" data-label="<?php echo __('Type in keywords to filter products'); ?>" />
<img src="<?php echo SHCP_IMAGES.'/ajax-loader.gif'; ?>" id="shcp_loader" height="16px" width="16px" />
</div>
<hr />
<div id="shcp_products_tank">
<?php echo SHCP::view('admin/related/grid', array('products' => $products)); ?>
</div>
</div>
<div id="shcp_related">
<h4><?php echo __('Current Related Products'); ?></h4>
<?php echo SHCP::view('admin/related/grid', array('products' => $related, 'id' => 'shcp_related_tank', 'related_tank' => TRUE)); ?>
<h2><?php echo __('Drag Products Here to Relate Them'); ?></h2>
</div>
<hr />
