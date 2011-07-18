<div id="shcp_product-detail">
<?php echo Helper_Products::image($product->imageid); ?>
<p><?php echo $product->post_title; ?></p>
<p class="shcp-item-price">
<?php if ($product->cutprice): ?>
<b>$<?php echo $product->displayprice; ?></b>
<del>$<?php echo $product->cutprice; ?></del>
<?php else: ?>
$<?php echo $product->displayprice; ?>
<?php endif; ?>
</p>
<p><?php echo $product->detail->longdescription; ?></p>
<p><a href="<?php echo bloginfo('url').'/cart/add?catentryid='.$product->catentryid; ?>" class="addtocart">Add To Cart</a></p>
</div>
