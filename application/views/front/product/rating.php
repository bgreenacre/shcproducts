<?php $rating_string = array(0 => 'zero', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five'); ?>
<div class="shcp-rating">
<?php if ($rating == 0): ?>
<span class="zeroStar"></span>
<?php else: ?>
<span class="<?php echo $rating_string[floor((float)$rating)].(strpos($rating, '.5') ? '5' : '') ; ?>Star"><?php echo $rating; ?></span>
<?php endif; ?>
</div>
