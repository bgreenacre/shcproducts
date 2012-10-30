<select id="search_subcategories">
  <option id="subcat_default">Choose Subcategory</option>
<?php
  for($i = 0; $i < $result->count(); $i++) {
    ?><option id="subcat_<?php echo $i; ?>"><?php echo $result->subcategory;?> (<?php echo $result->aggcount; ?>)</option>
    <?php $result->next();
  }
?>
</select>