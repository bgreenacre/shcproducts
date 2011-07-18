<form action="<?php echo bloginfo('url').'/cart/add?catentryid='.$product->catentryid; ?>" method="GET" class="shcp-quickview">
<div class="shcp-image-tank">
<div class="shcp-current-image"><?php echo Helper_Products::image($product->imageid); ?></div>
<?php if ($product->detail->imageurls): ?>
<?php foreach ($product->detail->imageurls->imageurl[1] as $image): ?>
<a href="#" class="shcp-image-thumbnail"><?php echo Helper_Products::image($image, array('height' => 30, 'width' => 30), TRUE); ?></a>
<?php endforeach; ?>
<?php endif; ?>
</div>
<div class="shcp-detail">
<p><?php echo $product->post_title; ?></p>
<p>
<?php if ($product->cutprice): ?>
<b>$<?php echo $product->displayprice; ?></b>
<del>$<?php echo $product->cutprice; ?></del>
<?php else: ?>
$<?php echo $product->displayprice; ?>
<?php endif; ?>
</p>
<p><a href="<?php echo get_permalink($product->ID); ?>">Product Detail</a></p>
</div>
</form>
