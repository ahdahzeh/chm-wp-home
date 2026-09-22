<?php
/**
 * The fallback WordPress falls back to. Everything public has a more
 * specific template; this exists so the theme is valid and so an
 * unexpected query still renders in the right skin.
 *
 * @package CHM
 */

get_header();
?>

<main id="main">
	<?php
	chm_page_header(
		array(
			'title' => __( 'Latest', 'chm' ),
			'count' => (int) $GLOBALS['wp_query']->found_posts,
		)
	);
	?>

	<div class="rail">
		<?php chm_card_grid( $GLOBALS['wp_query']->posts ); ?>
		<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
	</div>
</main>

<?php get_footer(); ?>
