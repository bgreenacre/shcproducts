<select id="search_categories">
  <option id="cat_default">Choose Category</option>
<?php
  for($i = 0; $i < $result->count(); $i++) {
    ?><option id="cat_<?php echo $i; ?>"><?php echo $result->category;?> (<?php echo $result->aggcount; ?>)</option>
    <?php $result->next();
  }
?>
</select>