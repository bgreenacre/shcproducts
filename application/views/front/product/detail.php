<div id="shcp_product-detail">
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
  <div class="shcp-item-shortdesc"><?php echo html_entity_decode($product->detail->shortdescription, ENT_QUOTES); ?></div>
  <?php echo SHCP::view('front/product/rating', array('rating' => $product->detail->rating)); ?>
  <p class="shcp-item-price">
    <span><?php echo Helper_Price::currency($product->detail->saleprice); ?></span>
<?php if ($product->cutprice): ?>
    <span class="price-savings">A savings of <?php echo Helper_Price::currency($product->cutprice - $product->detail->saleprice); ?></span>
<?php endif; ?>
  </p>
  <a href="<?php echo bloginfo('url').'/cart/add?catentryid='.$product->get_catentryid(); ?>" class="addtocart" rel="#shcp-cartconfirm" data-post_id="<?php echo $product->ID; ?>">Add To Cart</a>    
  <?php if ($product->detail->specifications->specification): ?>
  <div class="shcp-item-details">
  <?php endif; ?>  
    <div class="shcp-item-longdesc"><?php echo str_replace('http//', 'http://', htmlspecialchars_decode(html_entity_decode($product->detail->longdescription))); ?></div>      
  <?php if ($product->detail->specifications->specification): ?>
    <div class="shcp-item-specs">
      <ul>
        <?php foreach($product->detail->specifications->specification[1] as $specification): ?>
        <li><?php echo $specification->label; ?>
          <ul>
          <?php foreach ($specification->attribute[1] as $attribute): ?>
            <!-- <li class="<?php // echo (($i % 2) ? "odd" : "even"); ?>"><?php // echo html_entity_decode($attribute->value); ?></li> -->
            <li class="<?php echo (($i % 2) ? "odd" : "even"); ?>"><span class="description"><?php echo preg_replace("/([a-z\)])([A-Z0-9])/", '$1 :</span> <span class="value">$2', html_entity_decode($attribute->value)); ?></span></li>
            <?php $i++; ?>
          <?php endforeach;?>
          </ul>
        </li>
        <?php endforeach; ?>  
      </ul>  
    </div>
  </div>    
  <?php endif; ?>    
  <div id="shcp-cartconfirm" class="shcp_modal"></div>
</div>
