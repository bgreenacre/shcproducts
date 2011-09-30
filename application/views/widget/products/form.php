<p>
<label for="<?php echo SHCP::get($ids, 'title'); ?>"><?php echo __('Title'); ?>:</label><br />
<input type="text" name="<?php echo SHCP::get($names, 'title'); ?>" value="<?php echo SHCP::chars(SHCP::get($values, 'title')); ?>" id="<?php echo SHCP::get($ids, 'title'); ?>" class="widefat" />
</p>
<p>
<label for="<?php echo SHCP::get($ids, 'keyword'); ?>"><?php echo __('Keyword'); ?>:</label><br />
<input type="text" name="<?php echo SHCP::get($names, 'keyword'); ?>" value="<?php echo SHCP::chars(SHCP::get($values, 'keyword')); ?>" id="<?php echo SHCP::get($ids, 'keyword'); ?>" class="widefat" />
</p>
<p>
<label for="<?php echo SHCP::get($ids, 'limit'); ?>"><?php echo __('Limit Products'); ?>:</label><br />
<input type="text" name="<?php echo SHCP::get($names, 'limit'); ?>" value="<?php echo SHCP::chars(SHCP::get($values, 'limit')); ?>" id="<?php echo SHCP::get($ids, 'limit'); ?>" class="widefat" />
</p>
<p>
<label for="<?php echo SHCP::get($ids, 'randomize'); ?>"><?php echo __('Randomize Products List'); ?>:</label><br />
<input type="radio" name="<?php echo SHCP::get($names, 'randomize'); ?>" value="1" id="<?php echo SHCP::get($ids, 'randomize'); ?>"<?php echo (SHCP::get($values, 'randomize')) ? ' checked="checked"' : ''; ?> class="widefat" />&nbsp;Yes&nbsp;&nbsp;
<input type="radio" name="<?php echo SHCP::get($names, 'randomize'); ?>" value="0" id="<?php echo SHCP::get($ids, 'randomize'); ?>"<?php echo ( ! SHCP::get($values, 'randomize')) ? ' checked="checked"' : ''; ?> class="widefat" />&nbsp;No
</p>
