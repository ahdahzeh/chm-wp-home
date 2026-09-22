<?php
/**
 * Search results.
 *
 * The empty state repeats the query, because "no results" without the
 * term leaves the reader guessing whether the search even ran.
 *
 * @package CHM
 */

get_header();

$chm_term  = get_search_query();
$chm_found = (int) $GLOBALS['wp_query']->found_posts;
?>

<main id="main">
	<?php
	chm_page_header(
		array(
			'eyebrow' => __( 'Search', 'chm' ),
			/* translators: %s: the search term. */
			'title'   => sprintf( __( 'Results for “%s”', 'chm' ), $chm_term ),
			'count'   => $chm_found,
		)
	);
	?>

	<div class="rail">
		<form class="searchbar" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="sr-only" for="chm-s"><?php esc_html_e( 'Search the library', 'chm' ); ?></label>
			<input id="chm-s" type="search" name="s" value="<?php echo esc_attr( $chm_term ); ?>"
				placeholder="<?php esc_attr_e( 'T-DXd, HER2-low, adjuvant…', 'chm' ); ?>">
			<button class="btn btn-primary" type="submit"><?php esc_html_e( 'Search', 'chm' ); ?></button>
		</form>

		<?php
		chm_card_grid(
			$GLOBALS['wp_query']->posts,
			/* translators: %s: the search term. */
			sprintf( __( 'Nothing matched “%s”. Try a drug name, a trial or a disease state.', 'chm' ), $chm_term )
		);
		?>

		<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
	</div>
</main>

<?php get_footer(); ?>
