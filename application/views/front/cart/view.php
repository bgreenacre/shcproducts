<form action="<?php echo bloginfo('url').'/cart/update'; ?>" method="GET" class="cart">
  <?php if ($simple_cart->messages->information): ?>
  <div class="success"><?php echo implode('<br />', $simple_cart->messages->information); ?></div>
  <?php elseif ($simple_cart->messages->notices): ?>
  <div class="notices"><?php echo implode('<br />', $simple_cart->messages->notices); ?></div>
  <?php elseif ($simple_cart->messages->errors): ?>
  <div class="errors"><?php echo implode('<br />', $simple_cart->messages->errors); ?></div>
  <?php endif; ?>
  <p class="shcp-cart-legalbar">
  <a href="#help" class="shcp-overlay" rel="#shcp_help_content">Need Help?</a> |
  <a href="#policy" class="shcp-overlay" rel="#shcp_return_policy_content">Return Policy</a> |
  <a href="#payment" class="shcp-overlay" rel="#shcp_payment_methods_content">Payment Methods</a>
  </p>
  <div id="shcp_loader"></div>
  <hr style="clear:both;" />
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
          <input type="hidden" value="<?php echo $item->id; ?>" name="item_id[]" />
          <input type="text" value="<?php echo $item->quantity; ?>" name="quantity[]" class="shcp-quantity" /><br />
          <a href="<?php echo bloginfo('url').'/cart/remove?id='.$item->id; ?>" class="shcp-update-cart" title="Remove Item">Update</a>
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
        <td colspan="2">Estimated Cart Total</td>
        <td><?php echo Helper_Price::currency($simple_cart->total_price); ?></td>
      </tr>
      <tr>
        <td colspan="2"></td>
        <td><a href="<?php echo Library_Sears_Api::factory('cart')->checkout()->load()->url(); ?>" class="shcp-checkout">Checkout</a></td>
      </tr>
    </tfoot>
  </table>
  <p>
  <a href="<?php echo get_bloginfo('url'); ?>/cart/empty?session_id=<?php echo $simple_cart->session; ?>" class="shcp-empty-cart">Empty Cart</a> | 
  <a href="<?php echo get_bloginfo('url'); ?>/products">Products</a>
  </p>
</form>
<div id="shcp_help_content" class="shcp_modal" style="display:none;">
<div class="shcp-modal-content">
<a class="close"></a>
<h4>Need Help? > Call Us 1-866-697-3277</h4>
<p>
Sears Holdings Corporation - How to Order
How to Order Payment Options Rain Checks Taxes Back-in-Stock E-mail Notification
</p>
<h4>How to Order</h4>
<p>
We make it easy. Throughout our site, whenever you see a product you'd like to purchase, simply click "Add to Cart" and the item will be added to your Shopping Cart. There are many ways to find what
you're looking for: search by type of product, brand name, item or manufacturer number, keyword, or by browsing categories and featured items.
Once you've clicked "Add to Cart" on an item:
</p>
<p>
If the item you have selected has additional options or accessories, the Product Options page will display. If there are no additional options or accessories, you will go straight to the Shopping Cart.
Your Shopping Cart will be displayed. From your Shopping Cart, you can continue shopping, save your Shopping Cart for purchase at a later date, or continue to check out.
Once you choose to Check Out, you'll provide your billing and shipping information.
</p>
<p>
If the item you have selected qualifies for home delivery, the Set-up, Haul-Away & Installation page will display for you to choose set-up, haul-away and installation specifics, as applicable. You
will also be able to choose a delivery date from the Delivery Scheduling page. If your selection does not require home delivery or installation, you will be taken directly to the Review Order
and Specify Payment Method screen.
</p>
<p>
You'll have a chance to review your total order, including any discounts, tax, and shipping costs. If you're satisfied, you'll provide your credit card information to complete your secure transaction.
You will then see an Order Confirmation screen which indicates we've received your order and gives you a confirmation number for your order.
</p>
<p>
We'll send an email confirmation within 24 hours to let you know your order has been processed. This email is your receipt and will include your confirmation number, verification of the items
purchased, your shipping and delivery information, and any applicable rebate forms.
You can always contact Customer Service at 1-800-349-4358 for any information regarding your order.
</p>
</div>
</div>
<div id="shcp_payment_methods_content" class="shcp_modal" style="display:none;">
<div class="shcp-modal-content">
<a class="close"></a>
<h4>Payment Options</h4>
<p>
You can purchase from Sears with a gift card, eBillmeTM, PayPal, or credit card.
We accept the following Gift Cards: Sears, the new Kmart 16-digit card and Lands' End with PIN number.
We accept cash payments from eBillmeTM Pay secure from your online bank account or walk-in location. Learn More
We accept the following Credit Cards: Sears Card® , Sears Premier Card®, Sears ChargePlus® , Sears Commercial One®, Sears Gold Mastercard®, Mastercard, VISA, American Express, or Discover.
</p>
<h4>Rain Checks</h4>
<p>
Occasionally, demand for our hottest items surpasses our supply. When this happens with items that are not discontinued, our stores may be able to issue you a rain check. This rain check allows you to
purchase the item at a later date for the original the sale price. At this time, we do not issue rain checks online.
See our store locator to find a store near you that issues rain checks. Or, try Buy Online Pick Up in Store as a more convenient option to purchase the product at the sale price. Look for the blue star for
qualifying products.
</p>
<h4>Taxes</h4>
<p>
We are required by law to collect all taxes based on where your order is being shipped or delivered. When you review your order total during the checkout process, you'll see the estimated sales tax. The
actual charge to your credit card will reflect the applicable state and local taxes.
</p>
<h4>Back-in-Stock E-mail Notification</h4>
<p>
If an item is out of stock, a "Ways to Buy" button will display beside the product. Click on the "Ways to Buy" button, enter your email address, and we will notify you when the product is back in stock.
At that time, you can decide if you want to purchase the item.
</p>
</div>
</div>
<div id="shcp_return_policy_content" class="shcp_modal" style="display:none;">
<div class="shcp-modal-content">
<a class="close"></a>
<h4>Returns & Cancellations</h4>
<p>
Satisfaction Guaranteed or Your Money Back
</p>
<p>
Our goal is that you are completely satisfied with your purchase. If for any reason you are not satisfied, simply return your purchase in its original packaging, with your receipt within 90
days of your purchase; 30 days for Home Electronics, Customized Jewelry and Mattresses for a refund or exchange. Video Games, CD, DVD must be unopened for a refund or exchange.
Return policies may vary for products sold and fulfilled by third-party merchants other than Sears and Kmart. See individual merchant profile, accessible from product detail pages, for
applicable merchant return policies. If you are not satisfied with your purchase after these time periods, please let us know. Your satisfaction is important to Sears.
Sears.com and Kmart.com both partner with marketplace merchants who sell items on our website. If your order contains an item that is not sold by Sears or Kmart, please be advised that
this item cannot be returned or exchanged at your local Sears or Kmart store. Your merchant's profile, which contains their specific return policy, is available via the product page. Please
see your merchant's profile for additional details.
</p>
<p>
Return policies may vary for products sold and fulfilled by third-party merchants other than Sears and Kmart. See individual merchant profile, accessible from product detail pages, for
applicable merchant return policies. All returns of products sold and fulfilled by a third-party merchant, including damaged and incorrect products, must be returned directly to such third-
party merchant.
</p>
<h4>Restocking/Cancellation Fee Policy</h4>
<p>
A 15% restocking fee is charged on Home Electronics returned without the original box, used, and without all of the original product packaging and accessories; Mattresses, built in Home
Appliances, and special orders on Hardware, Sporting Goods, Lawn & Garden, and Automotive merchandise. Home Electronics returned in opened boxes may, but need not be, determined
to have been used. Special orders cancelled after 24 hours of purchase are subject to a 15% order cancellation fee.
For mailable and home delivery items, see our returns options below. Shipping and handling charges are not refundable. For additional questions on sears.com returns or order inquiries,
email webcenter@customerservice.sears.com.
</p>
<h4>Hassle-free Returns & Exchanges</h4>
<p>
Wrong gift? Doesn't fit? Whatever the reason, it's no problem. Depending on the item, online merchandise can be returned in several ways. Here's how:
Shipped items
(UPS, US Postal Service, etc.)
By mail
To any Full-line Sears store*
Scheduled delivery items
(Appliances, treadmills or big-screen TV's, etc.)
By scheduled pick up
To any Full-line Sears store*
Mail-in Returns
Except Tires, all shipped items (UPS, US Postal Service etc...), including Parts and Auction merchandise, can be returned by mail using the pack slip provided. Shipping cost will be
refunded only if the product was damaged during delivery or if the wrong item was shipped.
Mail-in return limitations: Return Tires to a Sears Auto Center. Use our Store Locator to find a Sears Auto Center near you. Customized Jewelry according to packing instructions.
</p>
<h4>UPS Shipped Items</h4>
<p>
Because our automated system processes your order almost immediately after you click the "Process Order" button in Checkout, it's not possible to cancel your order before it's fulfilled.
However, when you received the shipment, it will contain specific instructions on how to return it for a refund. In addition, home delivery of larger items can be refused, if necessary.
If you make an order that you wish to cancel, you may mail the order back after you have received it or return the merchandise to your local Sears store. If you choose this last option, please
be sure to follow the correct steps on returning Sears.com merchandise.
</p>
<p>
Check your Order Status onlineOrders with a status of "Processing" cannot be canceled. When the status changes from Processing to Shipped it means that your order has left Sears
distribution facility and is on its way to the carrier's regional distribution center. Tracking numbers and other information may not be available until your shipment arrives at the carrier's
regional center.
</p>
<p>
To contact Customer Service: Send an e-mail to our Customer Order Department or call 1-800-349-4358.
</p>
<h4>Home Delivered Items</h4>
<p>
Home delivered orders can be canceled at any time prior to your scheduled delivery date by calling Customer Service at 1-800-732-7747.
Store Pickup ItemsStore pickup orders can be canceled at any time prior to actual pickup Call the store you selected for store pickup or customer service at 1-800-349-4358. For store
information, link to our Store Locator.
</p>
<p>
Policy for Purchases Made from 11/16/08 through 12/23/08:
</p>
<p>
For purchases made from 11/16/08 through 12/23/08, returns will be accepted up to 120 days from the sale date with the following exclusions: Electronics, Software (including CD, DVD,
Games), and Mattress/Foundations.
</p>
</div>
</div>
