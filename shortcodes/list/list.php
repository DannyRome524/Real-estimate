<?php
if ( $the_query->have_posts() ) {
	$masonry_class = ($masonry == 'enable') ? 'masonry-properties' : '';
	echo '<div class="ich-settings-main-wrap">'; ?>

	<?php if ($top_bar == 'enable') {
		$this->render_top_bar($style);
	} ?>
	
	<?php 
	echo '<div class="row '.$masonry_class.'">';
	while ( $the_query->have_posts() ) {
		$the_query->the_post();
		echo '<div id="property-'.get_the_id().'" class="'.join(' ', get_post_class($class)).'">';
			do_action('rem_property_box', get_the_id(), $style);
		echo '</div>';
	}
	echo '</div>';
	/* Restore original Post Data */
	wp_reset_postdata();
	if ($pagination == 'enable') {
		do_action( 'rem_pagination', $paged, $the_query->max_num_pages );
	}				
	echo '</div>';
} else {
	$msg = rem_get_option('no_results_msg', 'No Properties Found.');
	echo stripcslashes($msg);
}
?>