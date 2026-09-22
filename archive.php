<?php
/**
 * The library, and every term archive under it.
 *
 * One template covers /catalog, a disease-state archive and a format
 * archive, because all three are the same thing: a heading, the area
 * chips, and a grid of what matched.
 *
 * @package CHM
 */

get_header();

$chm_area    = is_category() ? get_queried_object()->slug : '';
$chm_areas   = wp_list_pluck( chm_disease_areas(), 'slug' );
$chm_is_area = in_array( $chm_area, $chm_areas, true );
?>

<main id="main">
	<?php
	chm_page_header(
		array(
			'eyebrow' => $chm_is_area ? __( 'Disease state', 'chm' ) : __( 'Library', 'chm' ),
			'title'   => is_archive() ? wp_strip_all_tags( get_the_archive_title() ) : __( 'Everything', 'chm' ),
			'lede'    => $chm_is_area
				? __( 'Every session, podcast and written perspective filed under this area.', 'chm' )
				: __( 'Expert video, podcasts and editorial, filed by disease state and by format.', 'chm' ),
			'count'   => (int) $GLOBALS['wp_query']->found_posts,
		)
	);
	?>

	<div class="rail">
		<?php chm_area_chips( $chm_is_area ? $chm_area : '' ); ?>

		<?php
		chm_card_grid(
			$GLOBALS['wp_query']->posts,
			$chm_is_area
				? __( 'This area has no published sessions yet. It is in production.', 'chm' )
				: __( 'Nothing matches this filter yet.', 'chm' )
		);
		?>

		<?php
		the_posts_pagination(
			array(
				'mid_size'  => 1,
				'prev_text' => __( 'Previous', 'chm' ),
				'next_text' => __( 'Next', 'chm' ),
			)
		);
		?>
	</div>
</main>

<?php get_footer(); ?>
