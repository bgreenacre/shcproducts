<tr>
  <th><label><?php echo $lang['label']; ?></label></th>
  <td>
  <input id="<?php echo $id; ?>" name="<?php echo $name; ?>" type="radio" value="1" class="regular-checkbox"<?php echo ($value != FALSE) ? ' checked="checked"' : ''; ?> />
  <label>Yes</label>
  <input id="<?php echo $id; ?>" name="<?php echo $name; ?>" type="radio" value="0" class="regular-checkbox"<?php echo ($value == FALSE) ? ' checked="checked"' : ''; ?> />
  <label>No</label>
  </td>
</tr>
