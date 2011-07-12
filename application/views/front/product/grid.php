<ul id="shcp_items">
<?php if (count($products) > 0): ?>
<?php while ($products->valid()): ?>
<li class="shcp-item">
<a href="<?php echo get_permalink($products->ID); ?>">
<?php echo Helper_Products::image($products->imageid); ?>
<p>
<?php echo $products->post_title; ?>
<span class="shcp-item-price">
<?php if ($product->cutprice): ?>
<del>$<?php echo $products->cutprice; ?></del>
<b>$<?php echo $products->displayprice; ?></b>
<?php else: ?>
$<?php echo $products->displayprice; ?>
<?php endif; ?>
</span>
</p>
</a>
</li>
<?php $products->next(); endwhile; ?>
<?php endif; ?>
</ul>
