<ul id="shcp_items">
<?php if (count($products) > 0): ?>
<?php while ($products->valid()): ?>
<li class="shcp-item">
<div>
<?php /*var_dump($products->detail->current());*/ ?>
<?php echo Helper_Products::image($products->imageid); ?>
<p>
<?php echo $products->post_title; ?>
</p>
<p class="shcp-rating">
<span class="shcp-rating-<?php echo $products->detail->rating; ?>"><?php echo $products->detail->rating; ?></span>
</p>
<p class="shcp-item-price">
<?php if ($product->cutprice): ?>
<b>$<?php echo $products->displayprice; ?></b>
<del>$<?php echo $products->cutprice; ?></del>
<?php else: ?>
$<?php echo $products->displayprice; ?>
<?php endif; ?>
</p>
<p><a href="<?php echo get_permalink($products->ID); ?>">Product Detail</a></p>
<p>
<?php if ($products->detail->webstatus): ?>
Buy Online
<?php endif; ?>
<?php if ($products->detail->storepickupeligible): ?>
- Pickup in Store Eligible
<?php endif; ?>
</p>
</div>
</li>
<?php $products->next(); endwhile; ?>
<?php endif; ?>
</ul>
<div style="clear:both;">&nbsp;</div>
