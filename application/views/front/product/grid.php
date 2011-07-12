<ul>
<?php if (count($products) > 0): ?>
<?php while ($products->valid()): ?>
<li class="shcp-item">
<?php echo Helper_Products::image($products->imageid); ?>
<?php echo $products->post_title; ?>
</li>
<?php $products->next(); endwhile; ?>
<?php endif; ?>
</ul>
