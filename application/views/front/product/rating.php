<div class="shcp-rating">
<?php if ($rating == 0): ?>
<span class="zeroStar"></span>
<?php else: ?>
<span class="<?php echo Text::number($rating).(strpos($rating, '.5') ? '5' : '') ; ?>Star"><?php echo $rating; ?></span>
<?php endif; ?>
</div>
