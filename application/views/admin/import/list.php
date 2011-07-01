<form action="/wp-admin/admin-ajax.php?action=action_save" method="post">
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
        </td>
        <td class="image">
          <img src="http://s.shld.net/is/image/Sears/<?php echo $result->current()->image; ?>?hei=100&amp;wid=100" style="width: 100px;" alt="<?php echo $result->current()->image; ?>" />
        </td>
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