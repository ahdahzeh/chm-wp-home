<?php
/**
 * CHM homepage theme, setup, assets, and the queries the front page runs.
 *
 * Everything the homepage renders comes out of the posts already on
 * communityhealth.media. Nothing here invents content: if a section
 * looks empty, the query found nothing, and that is the honest state.
 *
 * @package CHM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CHM_VERSION', '0.4.3' );

require_once get_template_directory() . '/inc/mark.php';
require_once get_template_directory() . '/inc/data.php';
require_once get_template_directory() . '/inc/page-parts.php';

/**
 * Theme supports.
 */
function chm_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary', 'chm' ),
		)
	);

	// The hero wants a 16:9 crop it can upload straight to a WebGL
	// texture. 640 wide is enough at the size a work is drawn and
	// keeps the twelve-image payload down.
	add_image_size( 'chm-hero-work', 640, 360, true );

	// The bento poster and the moment rail run wider than the hero.
	add_image_size( 'chm-wide', 1200, 675, true );
}
add_action( 'after_setup_theme', 'chm_setup' );

/**
 * Styles and scripts.
 *
 * tokens.css carries the design system and has to load before
 * home.css, which consumes the custom properties it defines.
 *
 * Chillax is the display face and comes from Fontshare, which Google
 * Fonts does not carry; body and data are Geist and Geist Mono.
 */
function chm_assets() {
	$dir = get_template_directory_uri();

	wp_enqueue_style(
		'chm-display',
		'https://api.fontshare.com/v2/css?f[]=chillax@400,500,600&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'chm-fonts',
		'https://fonts.googleapis.com/css2?family=Geist:wght@400;450;500&family=Geist+Mono:wght@500&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'chm-tokens', $dir . '/assets/css/tokens.css', array(), CHM_VERSION );
	wp_enqueue_style( 'chm-home', $dir . '/assets/css/home.css', array( 'chm-tokens' ), CHM_VERSION );

	// Deferred, and the page is built to work without it: the hero
	// list, the nav and the chips all render server-side first.
	wp_enqueue_script( 'chm-hero', $dir . '/assets/js/hero.js', array(), CHM_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'chm_assets' );

/**
 * Preconnect to the two font hosts.
 *
 * Both stylesheets then serve a second request from another origin,
 * so without this the display face lands a full round trip late and
 * the headings visibly reflow.
 */
function chm_resource_hints( $urls, $relation ) {
	if ( 'preconnect' !== $relation ) {
		return $urls;
	}

	$urls[] = array( 'href' => 'https://api.fontshare.com', 'crossorigin' => 'anonymous' );
	$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous' );

	return $urls;
}
add_filter( 'wp_resource_hints', 'chm_resource_hints', 10, 2 );

/**
 * The separator WordPress puts in <title>.
 *
 * Core uses an en dash. The house style carries no dashes in copy, so
 * this is a middle dot instead.
 */
function chm_title_separator() {
	return "\u{00B7}"; // PHP only interprets \u{} inside double quotes.
}
add_filter( 'document_title_separator', 'chm_title_separator' );

/**
 * Recent posts, optionally narrowed to one format term.
 *
 * @param int    $count  How many to return.
 * @param string $format Format slug, or '' for everything.
 * @return WP_Post[]
 */
function chm_recent_posts( $count = 8, $format = '' ) {
	$args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $count,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);

	if ( $format ) {
		// Adjust the taxonomy name if formats live somewhere other
		// than a `format` taxonomy on this install.
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'format',
				'field'    => 'slug',
				'terms'    => $format,
			),
		);
	}

	$query = new WP_Query( $args );
	return $query->posts;
}

/**
 * The twelve works the hero draws.
 *
 * Featured-image-only, because a work with no thumbnail has nothing
 * to hang on the wall. The query asks for more than it needs and
 * trims, so a run of thumbnail-less posts cannot empty the room.
 */
function chm_hero_works( $count = 12 ) {
	$posts = get_posts(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $count * 3,
			'ignore_sticky_posts' => true,
			'meta_query'          => array(
				array(
					'key'     => '_thumbnail_id',
					'compare' => 'EXISTS',
				),
			),
		)
	);

	$works = array();
	foreach ( $posts as $post ) {
		$thumb = get_the_post_thumbnail_url( $post, 'chm-hero-work' );
		if ( ! $thumb ) {
			continue;
		}
		$works[] = array(
			'post'  => $post,
			'thumb' => $thumb,
		);
		if ( count( $works ) >= $count ) {
			break;
		}
	}
	return $works;
}

/**
 * This Moment in Medicine, the short-answer rail.
 *
 * Tries the series first, then a category of the same slug, then
 * falls back to recent posts so the row is never empty on an install
 * that files the show differently.
 */
function chm_moments( $count = 4 ) {
	foreach ( array( 'series', 'category' ) as $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}

		$query = new WP_Query(
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'posts_per_page'      => $count,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'tax_query'           => array(
					array(
						'taxonomy' => $taxonomy,
						'field'    => 'slug',
						'terms'    => 'this-moment-in-medicine',
					),
				),
			)
		);

		if ( $query->posts ) {
			return $query->posts;
		}
	}

	return chm_recent_posts( $count );
}

/**
 * A short format-and-duration line, e.g. "Full session · 24:18".
 *
 * Falls back to the post date when neither is recorded, so the line
 * never renders as a bare separator.
 */
function chm_meta_line( $post ) {
	$parts = array();

	$format = chm_format_name( $post );
	if ( $format ) {
		$parts[] = $format;
	}

	$duration = chm_duration( $post );
	if ( $duration ) {
		$parts[] = $duration;
	}

	if ( ! $parts ) {
		$parts[] = get_the_date( 'j M Y', $post );
	}

	return implode( ' · ', $parts );
}

/**
 * The post's format term name, or '' when it carries none.
 */
function chm_format_name( $post ) {
	if ( ! taxonomy_exists( 'format' ) ) {
		return '';
	}

	$terms = get_the_terms( $post, 'format' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return '';
	}

	return $terms[0]->name;
}

/**
 * Runtime, from the `duration` post meta key.
 *
 * Change the key here if the install records it elsewhere; the meta
 * line falls back to the post date, so a wrong key degrades quietly
 * rather than printing an empty slot.
 */
function chm_duration( $post ) {
	return (string) get_post_meta( $post->ID, 'duration', true );
}

/**
 * Up to two tag chips for a post card, each carrying its own colour.
 *
 * The first is the disease state it is filed under, the second the
 * channel it went out on. Both come from real terms; a post missing
 * either simply shows fewer chips.
 */
function chm_card_tags( $post ) {
	$tags  = array();
	$areas = wp_list_pluck( chm_disease_areas(), 'slug' );

	$cats = get_the_category( $post->ID );
	if ( $cats && ! is_wp_error( $cats ) ) {
		foreach ( $cats as $cat ) {
			if ( in_array( $cat->slug, $areas, true ) ) {
				$tags[] = array( 'label' => $cat->name, 'colour' => 'var(--knowledge-blue)' );
				break;
			}
		}
	}

	$channel = get_post_meta( $post->ID, 'channel', true );
	if ( $channel ) {
		$tags[] = array( 'label' => $channel, 'colour' => 'var(--destructive)' );
	}

	return array_slice( $tags, 0, 2 );
}

/**
 * How many published items sit in a disease-state category.
 */
function chm_area_count( $slug ) {
	$term = get_term_by( 'slug', $slug, 'category' );
	return $term && ! is_wp_error( $term ) ? (int) $term->count : 0;
}
