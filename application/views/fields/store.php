<tr>
  <th><label><?php echo $lang['label']; ?></label></th>		
  <td>
    <input id="store_sears" name="<?php echo $name; ?>" type="radio" value="Sears" <?php echo ($value == "Sears") ? ' checked="checked"' : ''; ?> />
    <label for="store_sears">Sears</label>  		  
    <input id="store_kmart" name="<?php echo $name; ?>" type="radio" value="Kmart" <?php echo ($value == "Kmart") ? ' checked="checked"' : ''; ?> />
    <label for="store_kmart">Kmart</label>    
    <input id="store_mygofer" name="<?php echo $name; ?>" type="radio" value="MyGofer" <?php echo ($value == "MyGofer") ? ' checked="checked"' : ''; ?> />
    <label for="store_mygofer">MyGofer</label>    		  
  </td>
</tr>