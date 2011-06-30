<ul>
<?php foreach ($products as $product): ?>
<li class="shcp_product" data-post_id="<?php echo $product->ID; ?>">
<img src="<?php echo $product->product_image; ?>" alt="<?php echo __('Product Image'); ?>" />
<p><b><?php echo $product->post_title; ?>:</b> <?php echo $product->Price; ?></p>
</li>
<?php endforeach; ?>
</ul>
