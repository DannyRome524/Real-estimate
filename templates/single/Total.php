<?php
/**
 * Single Property Template for Total Theme
 */

get_header();
global $post;
$author_id = $post->post_author;
$author_info = get_userdata($author_id);
$max_width = apply_filters( 'rem_max_container_width', '1170px' );
?>

	<div id="content-wrap" class="container clr">

		<?php wpex_hook_primary_before(); ?>

		<div id="primary" class="content-area clr">

			<?php wpex_hook_content_before(); ?>

			<div id="content" class="site-content clr">

				<?php wpex_hook_content_top(); ?>

				<section id="property-content" class="ich-settings-main-wrap" style="max-width: <?php echo $max_width; ?>;margin:0 auto;">

					<div class="">
						<div class="row">

							<div id="post-<?php the_ID(); ?>" <?php post_class('col-sm-8 col-md-9'); ?>>
							
							<?php if( have_posts() ){ while( have_posts() ){ the_post(); ?>

								<?php do_action( 'rem_single_property_slider', get_the_id() ); ?>
								<?php do_action( 'rem_single_property_contents', get_the_id() ); ?>
								
							<?php } } ?>
							</div>

							<div class="col-sm-4 col-md-3">
								<?php
									do_action( 'rem_single_property_agent', $author_id );
									$p_sidebar = rem_get_option('property_page_sidebar', '');
									if ( is_active_sidebar( $p_sidebar )  ) {
										dynamic_sidebar( $p_sidebar );
									}
								?>
							</div>

						</div>
					</div>
				</section>

				<?php wpex_hook_content_bottom(); ?>

			</div><!-- #content -->

			<?php wpex_hook_content_after(); ?>

		</div><!-- #primary -->

		<?php wpex_hook_primary_after(); ?>

	</div><!-- .container -->

<?php get_footer(); ?>