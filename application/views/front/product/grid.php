<ul id="shcp_items">
<?php if (count($products) > 0): ?>
<?php while ($products->valid()): ?>
  <li class="shcp-item">
<?php // var_dump($products->current()); ?>      
<?php // var_dump($products->detail->current()); ?>
    <div class="shcp-quickview">
      <a href="/link-to-quickview-page" data-post_id="<?php echo $products->ID; ?>" rel="#shcp_quickview_modal">Quick View</a>
    </div>
    <p class="shcp-image">
      <a href="/link-to-detail-page"><?php echo Helper_Products::image($products->imageid, array('alt' => $products->post_title)); ?></a>
    </p>
    <p class="shcp-title">
      <a href="/link-to-detail-page"><?php echo $products->post_title; ?></a>
    </p>
    <p class="shcp-item-price">
      <span>$<?php echo $products->detail->saleprice; ?></span>
<?php if ($products->cutprice): ?>        
      <del>$<?php echo $products->detail->regularprice; ?></del>
<?php endif; ?>
    </p>
    <p class="shcp-add-to-cart">
      <a href="<?php echo bloginfo('url') . '/cart/add?catentryid=' . $products->catentryid; ?>" class="addtocart">Add To Cart</a><br />
      <a href="<?php echo get_permalink($products->ID); ?>">Product Detail</a>
    </p>
  </li>
<?php $products->next(); endwhile; ?>
<?php endif; ?>
</ul>
<div id="shcp_quickview_modal">adsfhafdiuhafdiuahfliuaflih</div>
