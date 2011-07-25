<?php
/**
 * Template Name: Full Width
 *
 * This Full Width template removes the primary and secondary asides so that content
 * can be displayed the entire width of the #content area.
 *
 */


    // calling the header.php
    get_header();

    // action hook for placing content above #container
    thematic_abovecontainer();
    ob_start();
	Controller::factory('front_products')->action_grid();
	$content = ob_get_clean();

?>

		<div id="container">
		
			<?php thematic_abovecontent(); ?>
		
			<div id="content">
			<?php
				// create the navigation above the content
		        thematic_navigation_above();
		    ?>
				<div <?php
					if (!(THEMATIC_COMPATIBLE_POST_CLASS)) {
						post_class('page type-page status-publish hentry');
						echo '>';
					} else {
						echo 'class="';
						thematic_post_class();
						echo '">';
						echo 'yo MAMAMAMAA';
					}
	                ?>
	            
					<div class="entry-content">
	
	                    <?php echo $content; ?>
	                    
					</div>
	            </div>
			<?php
				// create the navigation above the content
		        thematic_navigation_below();
		    ?>
			</div><!-- #content -->
			
			<?php thematic_belowcontent(); ?> 
			
		</div><!-- #container -->

<?php 

    // action hook for placing content below #container
    thematic_belowcontainer();
    
    // calling footer.php
    get_footer();

?>
