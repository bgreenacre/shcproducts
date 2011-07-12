<p>
<label for="<?php echo SHCP::get($ids, 'title'); ?>"><?php echo __('Title'); ?>:</label><br />
<input type="text" name="title" value="<?php echo SHCP::get($values, 'title'); ?>" id="<?php echo SHCP::get($ids, 'title'); ?>" class="widefat" />
</p>
<p>
<label for="<?php echo SHCP::get($ids, 'keyword'); ?>"><?php echo __('Keyword'); ?>:</label><br />
<input type="text" name="keyword" value="<?php echo SHCP::get($values, 'keyword'); ?>" id="<?php echo SHCP::get($ids, 'keyword'); ?>" class="widefat" />
</p>
<p>
<label for="<?php echo SHCP::get($ids, 'limit'); ?>"><?php echo __('Limit Products'); ?>:</label><br />
<input type="text" name="limit" value="<?php echo SHCP::get($values, 'limit'); ?>" id="<?php echo SHCP::get($ids, 'limit'); ?>" class="widefat" />
</p>
