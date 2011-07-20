<div id="shcp-modal-container">
  <h2>1 item has been added to your cart</h2>
  <span id="shcp-modal-closer">CLOSE</span>
  <?php echo Helper_Products::image($product->imageid, array('height' => '120', 'width' => '120')); ?>
  <div id="shcp-modal-productinfo">
    <h1><?php echo $product->post_title; ?></h1>
    <dl>
      <dd>Price:</dd>
      <dt><?php echo $product->detail->saleprice; ?></dt>
      <dd>Quantity:</dd>
      <dt>1</dt>
    </dl>
  <div id="shcp-modal-buttons">
    <a href="" class="continue_shopping">Continue Shopping</a>
    <a href="" class="view_cart">View Cart and Checkout</a>
  </div>
</div>  