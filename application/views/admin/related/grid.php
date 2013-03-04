<div id="shcp_pager"><?php echo paginate_links($pager); ?></div>
<ul<?php echo (isset($id)) ? ' id="'.$id.'"' : NULL; ?>>
<?php foreach ($products as $product): ?>
<li class="shcp_product" data-post_id="<?php echo $product->ID; ?>" id="post_id_<?php echo $product->ID; ?>">
<?php if (isset($related_tank)): ?>
<a href="#" class="shcp_trash"><img src="<?php echo SHCP_IMAGES; ?>/trash.png" alt="Remove Product" height="22px" width="20px" /></a>
<input type="hidden" name="shcp_related_products[]" value="<?php echo $product->ID; ?>" />
<?php endif; ?>
<img src="<?php echo $products->imageid; ?>?hei=100&wid=100&op_sharpen=1" style="width: 100px;" alt="<?php echo $products->imageid; ?>" />
<p>
<b><?php echo $product->post_title; ?>:</b>
<?php if ($product->cutprice): ?>
<del>$<?php echo $products->cutprice; ?></del>
<b>$<?php echo $products->displayprice; ?></b>
<?php else: ?>
$<?php echo $products->displayprice; ?>
<?php endif; ?>
</p>
</li>
<?php endforeach; ?>
</ul>
<div style="clear:both;">&nbsp;</div>
