<div class="product_pagination">
  <div class='products_found'><span class='product_count'><?php echo $product_count; ?></span> products found</div>
<?php 
if(isset($pagination) && is_array($pagination)) {
	foreach($pagination as $page): ?>
  <?php if($page['number'] == $current_page): ?>
      <span class='current_page'><?php echo $page['number']; ?></span>
    <?php else: ?>
      <a class="product_page_link" href="#" data-product-count="<?php echo $product_count; ?>" data-page-number="<?php echo $page['number']; ?>" data-method="<?php echo $method; ?>"><?php echo $page['message']; ?></a>
<?php   endif;
      endforeach;
} ?>
</div>

<form action="" id="shcp_import_form" method="post">
<div class="shcp_assign_category">
  <label class="shcp_form_labels" for="shcp_category">Assign category:</label><?php wp_dropdown_categories($dropdown_args); ?>
</div>  

<br/><br/>

<table class="widefat" id="shcp_import_table">
  <thead>
    <tr>
      <th></th>
      <th>Product</th>
      <th>Title</th>
      <th>Part Number</th>
      <th>Cut Price</th>
      <th>Display Price</th>
      <th>Featured</th>
      <th>Hidden</th>
    </tr>
    
  </thead>
  <tbody>
<?php
if(isset($result->statusdata->respmessage)) {
	echo '<tr><td colspan="8">'.$result->statusdata->respmessage.'</td></tr>';
} else {
  for($i = 0; $i < 1; $i++) {
  	$image_url = $result->imageurls->imageurl[1][0];
  	$name = $result->brandname.' '.$result->descriptionname;
    ?>
      <tr id="row_<?php echo $i; ?>">
        <td>
          <input type="checkbox" name="import_single[]" class="checkbox" value="<?php echo $i; ?>" />
          <input type="hidden" name="post_title[]" value="<?php echo $name; ?>" />
          <input type="hidden" name="imageid[]" value="<?php echo $image_url; ?>" />
          <input type="hidden" name="numreview[]" value="<?php echo $result->numreview; ?>" />
          <input type="hidden" name="catentryid[]" value="<?php echo $result->catentryid; ?>" />
          <input type="hidden" name="rating[]" value="<?php echo $result->rating; ?>" />
          <input type="hidden" name="partnumber[]" value="<?php echo $result->partnumber; ?>" />
          <input type="hidden" name="cutprice[]" value="<?php echo $result->saleprice; ?>" />
          <input type="hidden" name="displayprice[]" value="<?php echo $result->regularprice; ?>" />
        </td>
        <td class="image"><?php echo Helper_Products::image($image_url); ?></td>
        <td class="name"><?php echo $name; ?></td>
        <td class="partnumber"><?php echo $result->partnumber; ?></td>
        <td class="cutprice"><?php echo $result->saleprice; ?></td>
        <td class="displayprice"><?php echo $result->regularprice; ?></td>
        <td><input type="checkbox" name="is_featured" class="checkbox" value="" /></td>
        <td><input type="checkbox" name="is_hidden" class="checkbox" value="" /></td>
      </tr>
    <?php
  }
}
?>
  </tbody>
</table>
<br style='clear:both' />
<input type='submit' value='Save Selected Products' id='save_products' />
<br /><br />
</form>
