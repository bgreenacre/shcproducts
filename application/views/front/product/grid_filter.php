<form action="<?php bloginfo('url'); ?>/products" id="shcp_grid_filter" name="shcp_grid_filter" method="GET">
<label for="shcp_category">Filter by</label>
<select name="category" id="shcp_category">
<option value="">---</option>
<?php foreach ($categories as $category): ?>
<?php if ($category->slug != 'uncategorized'): ?>
<option value="<?php echo $category->slug; ?>"<?php echo ($selected === $category->slug) ? ' selected="selected"' : ''; ?>><?php echo $category->name; ?></option>
<?php endif; ?>
<?php endforeach; ?>
</select>
</form>
