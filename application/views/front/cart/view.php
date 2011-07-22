<form action="<?php echo bloginfo('url').'/cart/update'; ?>" method="GET" class="cart">
  <?php if ($simple_cart->messages->information): ?>
  <div class="success"><?php echo implode('<br />', $simple_cart->messages->information); ?></div>
  <?php elseif ($simple_cart->messages->notices): ?>
  <div class="notices"><?php echo implode('<br />', $simple_cart->messages->notices); ?></div>
  <?php elseif ($simple_cart->messages->errors): ?>
  <div class="errors"><?php echo implode('<br />', $simple_cart->messages->errors); ?></div>
  <?php endif; ?>
  <a href="<?php echo get_bloginfo('url'); ?>/cart/empty?session_id=<?php echo $simple_cart->session; ?>" class="shcp-empty-cart">Empty Cart</a>
  <a href="<?php echo Library_Sears_Api::factory('cart')->checkout()->load()->url(); ?>" class="shcp-checkout">Checkout</a>
  <table width="99%">
    <thead>
      <tr>
        <th style="width: 50%;">Item</th>
        <th style="width: 25%;">Quantity</th>
        <th style="width: 25%;">Price</th>
      </tr>
    </thead>
    <tbody>
    <?php if ($simple_cart->item_count == 0): ?>
      <tr>
        <td colspan="3">There are no items currently in your cart.</td>
      </tr>
    <?php else: ?>
    <?php foreach ($simple_cart->items as $item): ?>
      <tr>
        <td class="shcp-item-description">
        <?php echo Helper_Products::image($item->image); ?>
          <p><?php echo $item->name; ?></p>
        </td>
        <td class="shcp-item-quantity">
          <input type="text" value="<?php echo $item->quantity; ?>" name="quantity[]" class="shcp-quantity" />
          <a href="<?php echo bloginfo('url').'/cart/remove?id='.$item->id; ?>" class="shcp-remove-item" title="Remove Item">[X]</a>
        </td>
        <td class="shcp-item-price"><?php echo Helper_Price::currency($item->price); ?></td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="2">Sub Total</td>
        <td><?php echo Helper_Price::currency($simple_cart->total_item_price); ?></td>
      </tr>
      <?php if ($simple_cart->total_discounts): ?>
      <tr>
        <td colspan="2">Discount Total</td>
        <td><?php echo Helper_Price::currency($simple_cart->total_discounts); ?></td>
      </tr>
      <?php endif; ?>
      <tr>
        <td colspan="2">Cart Total</td>
        <td><?php echo Helper_Price::currency($simple_cart->total_price); ?></td>
      </tr>
    </tfoot>
  </table>
</form>
