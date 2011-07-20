<div id="shcp_product-detail">
  <?php // var_dump($product->detail->current()); ?>
  <?php echo Helper_Products::image($product->imageid, array('height' => '248', 'width' => '248')); ?>
  <h1><?php echo $product->post_title; ?></h1>
  <p class="shcp-item-shortdesc"><?php echo $product->detail->shortdescription; ?></p>
  <p class="shcp-item-price">
    <span>$<?php echo $product->detail->saleprice; ?></span>
<?php if ($product->detail->regularprice): ?>
    <span class="price-savings">A savings of $<?php echo number_format(abs((float)($product->detail->saleprice - $product->detail->regularprice)), 2); ?></span>
<?php endif; ?>
  </p>
  <p><a href="<?php echo bloginfo('url').'/cart/add?catentryid='.$product->catentryid; ?>" class="addtocart">Add To Cart</a></p>  
  <div class="shcp-item-longdesc"><?php echo $product->detail->longdescription; ?></div>

</div>
