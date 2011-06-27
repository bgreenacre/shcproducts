<?php
echo $before_widget;
if ($title)
	echo $before_title . $title . $after_title;
echo $content->render();
echo $after_widget;
?>
