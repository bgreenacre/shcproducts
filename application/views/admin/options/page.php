<div class="wrap clearfix">
	<div class="icon32" id="icon-options-general"><br></div>
	<h2><?php echo $lang['title']; ?></h2>
</div>

<form action="options.php" method="post" id="<?php echo SHCP::prefix('optionsForm'); ?>">
<table class="form-table">
<?php settings_fields(SHCP::prefix('options')); ?>
<?php do_settings_sections($classname); ?>
</table>
<p class="submit"><input type="submit" class="button-primary" value="<?php echo $lang['submit']; ?>" /></p>
</form>
