<form action="<?php echo bloginfo('url').'/cart/add?catentryid='.$product->get_catentryid(); ?>" method="GET" class="shcp-quickview" id="shcp_product-detail">
  <div class="quickview_wrapper">
    <div class="shcp-image-tank">
      <div class="shcp-current-image"><?php echo Helper_Products::image($product->imageid, array('height' => '248', 'width' => '248')); ?></div>
  <?php if ($product->detail->imageurls): ?>
      <div class="shcp-more-images">
  <?php foreach ($product->detail->imageurls->imageurl[1] as $image): ?>
        <a href="" class="shcp-image-thumbnail<?php if (substr($image, strrpos($image, '/') + 1) == $product->imageid): echo " selected"; endif; ?>" data-image="<?php echo Helper_Products::image_url($image, 248, 248); ?>">
          <?php echo Helper_Products::image($image, array('height' => 40, 'width' => 40)); ?>
        </a>
  <?php endforeach; ?>
      </div>
  <?php endif; ?>
    </div>
    <h1><?php echo $product->post_title; ?></h1>
    <div class="shcp-item-shortdesc"><?php echo htmlspecialchars_decode($product->detail->shortdescription); ?></div>
    <p class="shcp-item-price">
      <span>$<?php echo $product->detail->saleprice; ?></span>
  <?php if ($product->detail->regularprice): 
          $price_savings = number_format(abs((float)($product->detail->saleprice - $product->detail->regularprice)), 2);
          if($price_savings != 0.00): ?>
      <span class="price-savings">A savings of $<?php echo $price_savings; ?></span>
          <?php endif; ?>
  <?php endif; ?>
    </p>
    <a href="<?php echo bloginfo('url').'/cart/add?catentryid='.$product->get_catentryid(); ?>" class="addtocart" data-post_id="<?php echo $product->ID; ?>">Add To Cart</a>
    <div class="shcp-item-longdesc"><?php echo htmlspecialchars_decode($product->detail->longdescription); ?></div>
  </div>
</form>
