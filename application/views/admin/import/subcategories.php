<select id="search_subcategories">
  <option id="subcat_default">Choose Subcategory</option>
<?php
  for($i = 0; $i < $result->count(); $i++) {
    ?><option id="subcat_<?php echo $i; ?>"><?php echo $result->subcategory;?> (<?php echo $result->aggcount; ?>)</option>
    <?php $result->next();
  }
?>
</select>
<div id="shcp_filter">
	Filter:<br />
	<input type="text" name="search_terms" class="search_filter_terms" id="search_terms_filter" value="" />
	<input type="button" name="submit_filter" id="submit_filter" value="Search" />
</div>