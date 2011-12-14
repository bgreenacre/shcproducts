<form action="<?php bloginfo('url'); ?>/products" id="shcp_grid_filter" name="shcp_grid_filter" method="GET">
<input type="hidden" name="orderby" id="orderby" value="date" />
<label for="shcp_category">Filter by</label>
<select name="category" id="shcp_category">
<option value="">---</option>
<?php foreach ($categories as $category): ?>
<?php if ($category->slug != 'uncategorized'): ?>
<option value="<?php echo $category->slug; ?>"<?php echo ($selected === $category->slug) ? ' selected="selected"' : ''; ?>><?php echo $category->name; ?></option>
<?php endif; ?>
<?php endforeach; ?>
</select>
<label for="sort_by_meta">Sort by</label>
<select name="sort_by_meta" id="sort_by_meta">
    <option value="rating" data-fields="{orderby: 'meta_value', meta_key='rating'}">Top Rated</option>
    <option value="date" data-fields="{orderby: 'date', meta_key: false}">Date added to site</option>
    <option value="displayprice-htl" data-fields="{order: 'DESC', meta_key: 'displayprice', orderby: 'meta_value_num'}">Price (High to Low)</option>
    <option value="displayprice-lth" data-fields="{order: 'ASC', meta_key: 'displayprice', orderby: 'meta_value_num'}">Price (Low to High)</option>
</select>
</form>
