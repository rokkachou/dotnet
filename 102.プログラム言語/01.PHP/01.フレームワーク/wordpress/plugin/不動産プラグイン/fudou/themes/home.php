<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query. 
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress3.6
 * @subpackage Fudousan Plugin
 */


get_header(); ?>
<div id="top_fbox" class="site-content">
		<div id="container">
			<div id="content" role="main">

					<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('top_widgets') ) : ?>
					<?php endif; ?>

			</div><!-- #content -->
		</div><!-- #container -->
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
