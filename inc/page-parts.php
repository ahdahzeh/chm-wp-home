<?php
/**
 * The chrome every page other than the homepage shares: a header
 * band, a post card, and the empty state.
 *
 * The homepage is its own composition. Everything else is a heading
 * over a grid, so it is one set of parts rather than one template per
 * route.
 *
 * @package CHM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The band at the top of a secondary page.
 *
 * @param array $args eyebrow, title, lede, and an optional count.
 */
function chm_page_header( $args = array() ) {
	$a = wp_parse_args(
		$args,
		array(
			'eyebrow' => '',
			'title'   => '',
			'lede'    => '',
			'count'   => null,
		)
	);
	?>
	<header class="page-head rail">
		<?php if ( $a['eyebrow'] ) : ?>
			<p class="eyebrow"><?php echo esc_html( $a['eyebrow'] ); ?></p>
		<?php endif; ?>

		<h1><?php echo esc_html( $a['title'] ); ?></h1>

		<?php if ( $a['lede'] ) : ?>
			<p class="page-head__lede"><?php echo esc_html( $a['lede'] ); ?></p>
		<?php endif; ?>

		<?php if ( null !== $a['count'] ) : ?>
			<p class="page-head__count">
				<?php
				/* translators: %s: number of items found. */
				printf( esc_html( _n( '%s item', '%s items', (int) $a['count'], 'chm' ) ), esc_html( number_format_i18n( $a['count'] ) ) );
				?>
			</p>
		<?php endif; ?>
	</header>
	<?php
}

/**
 * One card in a grid. The same markup the homepage's Latest row uses,
 * so a card looks the same wherever it is listed.
 */
function chm_post_card( $post ) {
	$tags = chm_card_tags( $post );
	$kind = chm_format_name( $post );
	?>
	<li><a class="post-card" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
		<span class="shot">
			<?php if ( has_post_thumbnail( $post ) ) : ?>
				<?php echo get_the_post_thumbnail( $post, 'chm-hero-work', array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
			<?php endif; ?>
			<span class="play"><?php chm_play(); ?></span>
		</span>
		<h3><?php echo esc_html( get_the_title( $post ) ); ?></h3>
		<?php if ( $tags ) : ?>
			<span class="tags"><?php foreach ( $tags as $t ) : ?><span style="--tag:<?php echo esc_attr( $t['colour'] ); ?>"><?php echo esc_html( $t['label'] ); ?></span><?php endforeach; ?></span>
		<?php endif; ?>
		<?php if ( $kind ) : ?><span class="kind"><?php echo esc_html( $kind ); ?></span><?php endif; ?>
	</a></li>
	<?php
}

/**
 * A grid of cards, or a sentence saying there are none.
 *
 * The empty state names what is missing and offers a way on, rather
 * than printing "No results" and stopping.
 */
function chm_card_grid( $posts, $empty = '' ) {
	if ( $posts ) {
		echo '<ul class="card-grid">';
		foreach ( $posts as $p ) {
			chm_post_card( $p );
		}
		echo '</ul>';
		return;
	}

	$empty = $empty ? $empty : __( 'Nothing published here yet.', 'chm' );
	?>
	<div class="empty">
		<p><?php echo esc_html( $empty ); ?></p>
		<a class="band__action" href="<?php echo esc_url( home_url( '/catalog' ) ); ?>"><?php esc_html_e( 'Browse the library', 'chm' ); ?> <?php chm_arrow(); ?></a>
	</div>
	<?php
}

/**
 * The disease-state chips, for pages that filter by area.
 *
 * @param string $current Slug of the active area, or '' for all.
 */
function chm_area_chips( $current = '' ) {
	?>
	<nav class="chips chips--links" aria-label="<?php esc_attr_e( 'Filter by disease state', 'chm' ); ?>">
		<a class="chip" href="<?php echo esc_url( home_url( '/catalog' ) ); ?>"
			<?php echo '' === $current ? 'aria-current="page"' : ''; ?>><?php esc_html_e( 'All', 'chm' ); ?></a>
		<?php foreach ( chm_disease_areas() as $area ) : ?>
			<a class="chip" href="<?php echo esc_url( home_url( '/catalog/' . $area['slug'] ) ); ?>"
				<?php echo $current === $area['slug'] ? 'aria-current="page"' : ''; ?>><?php echo esc_html( $area['label'] ); ?></a>
		<?php endforeach; ?>
	</nav>
	<?php
}

/**
 * The disease area a post is filed under, or null.
 *
 * Used to tint a card with the hue that owns its area, so colour on a
 * card carries the same meaning it carries everywhere else on the
 * site rather than being decoration.
 */
function chm_area_for_post( $post ) {
	$areas = chm_disease_areas();
	$by_slug = array();
	foreach ( $areas as $a ) {
		$by_slug[ $a['slug'] ] = $a;
	}

	foreach ( (array) get_the_category( $post->ID ) as $cat ) {
		if ( isset( $by_slug[ $cat->slug ] ) ) {
			return $by_slug[ $cat->slug ];
		}
	}
	return null;
}
