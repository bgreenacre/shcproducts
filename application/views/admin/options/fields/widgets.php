<tr>
  <th><label><?php echo $lang['label']; ?></label></th>
  <td>
  <?php foreach ($options as $value => $label): ?>
  <p class="shcp_widgets">
  <input id="<?php echo $id; ?>" name="<?php echo $name; ?>" type="checkbox" value="<?php echo $value; ?>" class="regular-checkbox"<?php echo (in_array($value, $values)) ? ' checked="checked"' : ''; ?> />
  <label for="<?php echo $id; ?>"><?php echo $label; ?></label>
  </p>
  <?php endforeach; ?>
  </td>
</tr>
