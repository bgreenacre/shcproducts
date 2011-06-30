<?php wp_nonce_field('shcproducts_related', 'shcproducts_noncename'); ?>
<div id="shcp_products">
<div id="shcp_filter_products">
<input type="radio" value="keyword" name="type" checked="checked" /> Keyword
<input type="radio" value="vertical" name="type" /> Vertical
<input type="radio" value="category" name="type" /> Category<br />
<input type="text" value="" name="keyword" id="keyword" />
<input type="button" value="<?php echo __('Submit'); ?>" name="filter" id="filter" />
</div>
<?php echo SHCP::view('admin/related/grid', array('products' => $products)); ?>
</div>
<div id="shcp_related">
<h4><?php echo __('Current Related Products'); ?></h4>
<ul id="shcp_related_tank">
<?php foreach ($related as $product): ?>
<li class="shcp_product">
</li>
<?php endforeach; ?>
</ul>
<h2><?php echo __('Drag Products Here to Relate Them'); ?></h2>
</div>
<hr />
