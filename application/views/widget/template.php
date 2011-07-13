<?php
echo $before_widget;
if ($title)
	echo $before_title . $title . $after_title;
echo $content;
echo $after_widget;
?>
