<div class="wrap shcp_wrap">
<?php var_dump($errors); ?>
<div class="icon32 icon32-posts-shcproduct" id="icon-edit"><br /></div>
<h2><?php echo __('Import Products from File'); ?></h2>
<form action="<?php echo admin_url('edit.php?post_type=shcproduct&page=fileimport&part=upload'); ?>" method="POST" enctype="multipart/form-data">
<p>
<label for="import_file"><?php echo __('Upload file'); ?></label>
<input type="file" name="import_file" value="" id="import_file" />
</p>
<p class="media-upload-size"><?php printf(__('Maximum upload file size: %d%s'), $upload_size, $unit); ?></p>
<p>
<input type="submit" name="upload" value="<?php echo __('Upload'); ?>" />
</p>
</form>
</div>
