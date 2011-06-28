<?php wp_nonce_field('shcproducts_related', 'shcproducts_noncename'); ?>
<div id="shcp_products">
<div id="shcp_filter_products">
<input type="radio" value="keyword" name="type" checked="checked" /> Keyword
<input type="radio" value="vertical" name="type" /> Vertical
<input type="radio" value="category" name="type" /> Category<br />
<input type="text" value="" name="keyword" id="keyword" />
<input type="button" value="<?php echo __('Submit'); ?>" name="filter" id="filter" />
</div>
<?php foreach ($products as $product): ?>
<div class="shcp_product" data-post_id="<?php echo $product->ID; ?>">
</div>
<?php endforeach; ?>
</div>
<div id="shcp_related">
<h4><?php echo __('Current Related Products'); ?></h4>
<div id="shcp_related_tank">
<?php foreach ($related as $product): ?>
<div class="shcp_product">
</div>
<?php endforeach; ?>
</div>
<h2><?php echo __('Drag Products Here to Relate Them'); ?></h2>
</div>
<hr />
