<ul<?php echo (isset($id)) ? ' id="'.$id.'"' : NULL; ?>>
<?php foreach ($products as $product): ?>
<li class="shcp_product" data-post_id="<?php echo $product->ID; ?>" id="post_id_<?php echo $product->ID; ?>">
<?php if (isset($related_tank)): ?>
<input type="hidden" name="shcp_related_products[]" value="<?php echo $product->ID; ?>" />
<?php endif; ?>
<img src="http://s.shld.net/is/image/Sears/<?php echo $products->imageid; ?>?hei=100&amp;wid=100" style="width: 100px;" alt="<?php echo $products->imageid; ?>" />
<p><b><?php echo $product->post_title; ?>:</b> $<?php echo $products->displayprice; ?></p>
</li>
<?php endforeach; ?>
</ul>
