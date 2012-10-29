<div class="wrap shcp_wrap">
<div class="icon32 icon32-posts-shcproduct" id="icon-edit"><br /></div>
<h2><?php echo __('Review Products from File'); ?></h2>
<form action="<?php echo admin_url('edit.php?post_type=shcproduct&page=fileimport&part=import&filename='.urlencode($filename)); ?>" method="POST" enctype="multipart/form-data">
<table>
<thead>
<tr>
<th><?php echo __('Column Name'); ?></th>
<?php for ($x=0; $x < $field_count; ++$x): ?>
<th><?php echo htmlspecialchars( (string) SHCP::get($cols, $x)); ?></th>
<?php endfor; ?>
</tr>
<tr>
<th><?php echo __('Column Field Map'); ?></th>
<?php for ($x=0; $x < $field_count; ++$x): ?>
<th>
<select name="col_field_map[]" id="col_field_map_<?php echo $x; ?>">
<?php foreach ($field_map as $name => $label): ?>
<option value="<?php echo $name; ?>"<?php echo (SHCP::get($cols, $x) == $name) ? ' selected="selected"' : ''; ?>><?php echo $label; ?></option>
<?php endforeach; ?>
</select>
<?php endfor; ?>
</th>
</tr>
</thead>
<tbody>
<?php foreach ($rows as $row): ?>
<tr>
<?php for ($x=0; $x < $field_count; ++$x): ?>
<td><?php echo htmlspecialchars( (string) SHCP::get($row, $x), ENT_QUOTES); ?></td>
<?php endfor; ?>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</form>
</div>
