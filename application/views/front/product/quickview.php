<form action="<?php echo bloginfo('url').'/cart/add?catentryid='.$product->get_catentryid(); ?>" method="GET" class="shcp-quickview" id="shcp_product-detail">
<div class="shcp-image-tank">
<div class="shcp-current-image"><?php echo Helper_Products::image($product->imageid, array('height' => '248', 'width' => '248')); ?></div>
<?php if ($product->detail->imageurls): ?>
<?php foreach ($product->detail->imageurls->imageurl[1] as $image): ?>
<a href="#" class="shcp-image-thumbnail"><?php echo Helper_Products::image($image, array('height' => 30, 'width' => 30), TRUE); ?></a>
<?php endforeach; ?>
<?php endif; ?>
</div>
<p><a href="<?php echo get_permalink($product->ID); ?>">Go to Product Page</a></p>
<h1><?php echo $product->post_title; ?></h1>
<p class="shcp-item-shortdesc"><?php echo $product->detail->shortdescription; ?></p>
<p class="shcp-item-price">
<span>$<?php echo $product->detail->saleprice; ?></span>
<?php if ($product->detail->regularprice): ?>
<span class="price-savings">A savings of $<?php echo number_format(abs((float)($product->detail->saleprice - $product->detail->regularprice)), 2); ?></span>
<?php endif; ?>
</p>
<a href="<?php echo bloginfo('url').'/cart/add?catentryid='.$product->get_catentryid(); ?>" class="addtocart">Add To Cart</a>
<div class="shcp-item-longdesc"><?php echo $product->detail->longdescription; ?></div>
</form>
