<?php
/**
 * The template for displaying the home page.
 *
 * @package WordPress3.7
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */

get_header(); ?>

<div id="main-content" class="main-content">

<?php
		if ( twentyfourteen_has_featured_posts() )
			// Include the featured content template.
			get_template_part( 'featured-content' );
?>
		<div id="primary" class="content-area">
				<div id="content" class="site-content" role="main">
					<div id="top_fbox">

					<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('top_widgets') ) : ?>
					<?php endif; ?>

					</div><!-- #top_fbox -->

			</div><!-- #content -->
		</div><!-- #primary -->
		<?php get_sidebar( 'content' ); ?>
</div><!-- #main-content -->

<?php
get_sidebar();
get_footer();
