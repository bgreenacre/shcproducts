<div class='products_found'><span class='product_count'><?php echo $data['product_count']; ?></span> products found</div>
<?php if($data['current_page'] > 1) 
{ ?>
<a class="product_page_link" href="#" data-product-count="<?php $data['product_count']; ?>" data-page-number="1">&laquo; First</a>
<a class="product_page_link" href="#" data-product-count="<?php $data['product_count']; ?>" data-page-number="<?php echo $data['previous_page']; ?> ">&laquo; Previous</a>
<?php 
} 
?>
<?php 
  for($i=($data['current_page'] - $data['page_range']); $i<=($data['current_page'] + $data['page_range']); $i++) {
// if it's a valid page number...
   if (($i > 0) && ($i <= $data['num_pages'])) {
      // if we're on current page...
      if ($i == $data['current_page']) { ?>
        <span class='current_page'><?php echo $i; ?></span>
<?php } 
      else 
      {  ?>
        <a class="product_page_link" href="#" data-product-count="<?php echo $data['product_count']; ?>" data-page-number="<?php echo $i; ?>"><?php echo $i; ?></a>
<?php } 
   } 
}
if($data['current_page'] < $data['num_pages']) 
{ ?>
  <a class="product_page_link" href="#" data-product-count="<?php echo $data['product_count']; ?>" data-page-number="<?php echo $data['next_page']; ?>">Next &raquo;</a>
  <a class="product_page_link" href="#" data-product-count="<?php echo $data['product_count']; ?>" data-page-number="<?php echo $data['num_pages']; ?>">Last &raquo;</a>
<?php 
} 
?>

<form action="" id="shcp_import_form" method="post">
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
    <tr>
      <th><input type="checkbox" name="import_all" id="import_all" /></th>
      <th colspan="7">Import All</th>
    </tr>    
  </thead>
  <tbody>
<?php 
  for($i = 0; $i < $result->count(); $i++) {
    
    // $result->detail();
    // $result->detail()->something;
    //<input type="hidden" name="longdescription" value="echo $result->detail()->longdescription;" />
    //<input type="hidden" name="shortdescription" value="echo $result->detail()->shortdescription;" />
    
    
    ?>
      <tr id="row_<?php echo $i; ?>">
        <td>
          <input type="checkbox" name="import_single[]" class="checkbox" value="<?php echo $i; ?>" />
          <input type="hidden" name="post_title[]" value="<?php echo $result->name; ?>" />
          <input type="hidden" name="imageid[]" value="<?php echo $result->imageid; ?>" />
          <input type="hidden" name="numreview[]" value="<?php echo $result->numreview; ?>" />
          <input type="hidden" name="catentryid[]" value="<?php echo $result->catentryid; ?>" />
          <input type="hidden" name="rating[]" value="<?php echo $result->rating; ?>" />
          <input type="hidden" name="partnumber[]" value="<?php echo $result->partnumber; ?>" />
          <input type="hidden" name="cutprice[]" value="<?php echo $result->cutprice; ?>" />
          <input type="hidden" name="displayprice[]" value="<?php echo $result->displayprice; ?>" />
          <input type="hidden" name="longdescription[]" value="<?php echo $result->detail()->longdescription; ?>" />
          <input type="hidden" name="shortdescription[]" value="<?php echo $result->detail()->shortdescription; ?>" />          
        </td>
        <td class="image"><?php echo Helper_Products::image($result->image); ?></td>
        <td class="name"><?php echo $result->name; ?></td>     
        <td class="partnumber"><?php echo $result->partnumber; ?></td>
        <td class="cutprice"><?php echo $result->cutprice; ?></td>
        <td class="displayprice"><?php echo $result->displayprice; ?></td> 
        <td><input type="checkbox" name="is_featured" class="checkbox" value="" /></td>
        <td><input type="checkbox" name="is_hidden" class="checkbox" value="" /></td>
      </tr>
    <?php
      $result->next();
  }
?>
  </tbody>
</table>
<br style='clear:both' />
<input type='submit' value='Save Selected Products' id='save_products' />
<br /><br />
</form>