<div id="shcp-modal-container">
  <h2>1 item has been added to your cart</h2>
  <?php echo Helper_Products::image($product->imageid, array('height' => '120', 'width' => '120')); ?>
  <div id="shcp-modal-productinfo">
    <h1><?php echo $product->post_title; ?></h1>
    <dl>
      <dt>Price:</dt>
      <dd class="price"><?php echo Helper_Price::currency($product->detail->saleprice); ?></dd>
      <dt>Quantity:</dt>
      <dd>1</dd>
    </dl>
  </div>  
  <div id="shcp-modal-buttons">
    <a href="#" id="continue_shopping" class="close">Continue Shopping</a>
    <a href="<?php echo bloginfo('url').'/cart'; ?>" id="view_cart">View Cart and Checkout</a>
  </div>
</div>  
