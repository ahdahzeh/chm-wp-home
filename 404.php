<?php
/**
 * Not found.
 *
 * Several routes the nav points at do not exist yet, so this page
 * gets real traffic. It says what happened and offers the library,
 * rather than apologising.
 *
 * @package CHM
 */

get_header();
?>

<main id="main">
	<?php
	chm_page_header(
		array(
			'eyebrow' => __( 'Not found', 'chm' ),
			'title'   => __( 'That page is not here', 'chm' ),
			'lede'    => __( 'It may have moved, or it may not be published yet. The library has everything that is.', 'chm' ),
		)
	);
	?>

	<div class="rail">
		<div class="empty">
			<a class="band__action" href="<?php echo esc_url( home_url( '/catalog' ) ); ?>"><?php esc_html_e( 'Browse the library', 'chm' ); ?> <?php chm_arrow(); ?></a>
		</div>
	</div>
</main>

<?php get_footer(); ?>
