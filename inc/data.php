<?php
/**
 * The fixed content the homepage carries: disease states, shows and
 * the clinicians.
 *
 * These are editorial sets, not post queries. They change when the
 * business changes, not when someone publishes, so they live in code
 * where they can be reviewed rather than in the database where they
 * would drift silently. Counts, episodes and thumbnails still come
 * from WordPress.
 *
 * @package CHM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The seven disease states.
 *
 * `slug` must match the existing category slug, the catalog URLs are
 * pinned to these, so changing one breaks inbound links and the
 * ContentHub join. Confirm against wp-admin before editing.
 *
 * `hue` tints nothing on its own now: the anatomy drawing carries the
 * colour and the well behind it stays neutral. It is kept because the
 * card hover and the focus ring still derive from it.
 */
function chm_disease_areas() {
	return array(
		array(
			'slug'  => 'breast',
			'label' => __( 'Breast cancer', 'chm' ),
			'note'  => __( 'Video, podcast, editorial and live', 'chm' ),
			'hue'   => 'var(--cerebral-pink)',
			'ink'   => 'var(--ink-pink)',
			'art'   => 'breast',
		),
		array(
			'slug'  => 'weight-loss',
			'label' => __( 'Weight Loss', 'chm' ),
			'note'  => __( 'In production', 'chm' ),
			'hue'   => 'var(--cerebral-coral)',
			'ink'   => 'var(--ink-coral)',
			'art'   => 'weight-loss',
		),
		array(
			'slug'  => 'lung',
			'label' => __( 'Lung Cancer', 'chm' ),
			'note'  => __( 'In production', 'chm' ),
			'hue'   => 'var(--cerebral-cyan)',
			'ink'   => 'var(--ink-cyan)',
			'art'   => 'lung',
		),
		array(
			'slug'  => 'hematology',
			'label' => __( 'Hematology', 'chm' ),
			'note'  => __( 'In production', 'chm' ),
			'hue'   => 'var(--cerebral-green)',
			'ink'   => 'var(--ink-green)',
			'art'   => 'hematology',
		),
		array(
			'slug'  => 'gi',
			'label' => __( 'GI', 'chm' ),
			'note'  => __( 'In production', 'chm' ),
			'hue'   => 'var(--cerebral-amber)',
			'ink'   => 'var(--ink-amber)',
			'art'   => 'gi',
		),
		array(
			'slug'  => 'gu',
			'label' => __( 'GU', 'chm' ),
			'note'  => __( 'In production', 'chm' ),
			'hue'   => 'var(--cerebral-blue)',
			'ink'   => 'var(--primary)',
			'art'   => 'gu',
		),
		array(
			'slug'  => 'gynecology',
			'label' => __( 'Gynecologic', 'chm' ),
			'note'  => __( 'In production', 'chm' ),
			'hue'   => 'var(--cerebral-purple)',
			'ink'   => 'var(--ink-purple)',
			'art'   => 'gynecology',
		),
	);
}

/**
 * The CHM podcast network.
 *
 * Ported from `frontend/src/data/podcastsCatalog.ts` on origin/main,
 * which is where the real taglines live, the homepage previously
 * carried the same placeholder line on all four, which read as
 * unfinished copy.
 *
 * `ground` is a deep surface carrying white text, not a spectrum hue,
 * which is why these sit far darker than the disease set.
 *
 * `lang` is set where the show is not in English. TeTalks is in
 * Spanish and its copy stays in Spanish; the attribute is what stops
 * a screen reader reading it with an English voice.
 */
function chm_shows() {
	$platforms = chm_podcast_platforms();

	return array(
		array(
			'slug'     => 'breast-friends',
			'cover'    => 'podcasts/breast-friends.jpg',
			'label'    => 'Breast Friends',
			'category' => __( 'Clinical · Oncology', 'chm' ),
			'tagline'  => __( 'Direct, expert-led conversations about breast cancer, built for patients and clinicians. We pair first-line data with what it feels like in the exam room and at home.', 'chm' ),
			'update'   => __( 'New episodes monthly', 'chm' ),
			'lang'     => '',
			'ground'   => 'var(--show-teal)',
			'listen'   => array_merge( array( array( 'Breast Friends', 'https://linkin.bio/breastfriendspodcast/' ) ), $platforms ),
		),
		array(
			'slug'     => 'cancer-unfiltered',
			'cover'    => 'podcasts/cancer-unfiltered.jpg',
			'label'    => 'Cancer Unfiltered',
			'category' => __( 'Clinical · Oncology', 'chm' ),
			'tagline'  => __( 'Candid conversations with leading oncologists on the realities of cancer care, honest perspectives, real-world insights, and what it means for patients and clinicians.', 'chm' ),
			'update'   => __( 'New episodes monthly', 'chm' ),
			'lang'     => '',
			'ground'   => 'var(--show-blue)',
			'listen'   => array_merge( array( array( 'Cancer Unfiltered', 'https://linkin.bio/cancerunfiltered/' ) ), $platforms ),
		),
		array(
			'slug'     => 'big-c-energy',
			'cover'    => 'podcasts/big-c-energy.jpg',
			'label'    => 'Big C Energy',
			'category' => __( 'Patient · Survivorship', 'chm' ),
			'tagline'  => __( 'Cancer, from the people who lived it. Alison Haislip talks to survivors, caregivers and clinicians about what treatment is actually like once the appointment ends.', 'chm' ),
			'update'   => __( 'New episodes monthly', 'chm' ),
			'lang'     => '',
			'ground'   => 'var(--show-navy)',
			'listen'   => array_merge( array( array( 'Big C Energy', 'https://podcasts.apple.com/us/podcast/big-c-energy/id1896934572' ) ), $platforms ),
		),
		array(
			'slug'     => 'tetalks',
			'cover'    => 'podcasts/tetalks.jpg',
			'label'    => 'TeTalks',
			'category' => __( 'Clinical · En español', 'chm' ),
			// Not translated on purpose: the show is in Spanish.
			'tagline'  => 'Oncología en español. Las Dras. Marcela Mazo Canola y Ana Sandoval León repasan los datos que cambian la conversación en la consulta.',
			'update'   => 'Nuevos episodios cada mes',
			'lang'     => 'es',
			'ground'   => 'var(--show-rust)',
			'listen'   => array_merge( array( array( 'TeTalks', 'https://podcasts.apple.com/us/podcast/tetalks/id6791666042' ) ), $platforms ),
		),
	);
}

/**
 * The CHM umbrella feed's listen destinations, shared by every show.
 */
function chm_podcast_platforms() {
	return array(
		array( 'Apple Podcasts', 'https://podcasts.apple.com/us/podcast/community-health-media/id1837428248' ),
		array( 'Spotify', 'https://open.spotify.com/show/7e6ZVY93ogJny9AJJpANq4' ),
		array( 'iHeartRadio', 'https://www.iheart.com/podcast/269-community-health-media-291542082' ),
		array( 'Castbox', 'https://castbox.fm/channel/id6735434?country=us' ),
		array( 'Pocket Casts', 'https://pocketcasts.com/podcast/community-health-media/4b3c4980-695c-013e-60d1-0affd6caf14d' ),
		array( 'Goodpods', 'https://goodpods.com/profile/chm-111066' ),
	);
}

/**
 * The clinicians in the In conversation row.
 *
 * No portraits yet, so each card shows a monogram over a ghosted
 * mark. A video still is not a portrait, and using one would put the
 * wrong face against a named clinician, see the README.
 */
function chm_people() {
	return array(
		array( 'name' => 'Dr. Aditya Bardia', 'org' => 'UCLA Health', 'mono' => 'AB' ),
		array( 'name' => 'Dr. Irene Kang', 'org' => 'UCSF', 'mono' => 'IK' ),
		array( 'name' => 'Dr. Mark Pegram', 'org' => 'Stanford Medicine', 'mono' => 'MP' ),
		array( 'name' => 'Dr. Mabel Mardones', 'org' => 'Rocky Mountain Cancer Centers, US Oncology Network', 'mono' => 'MM' ),
		array( 'name' => 'Dr. Martin Dietrich', 'org' => 'Cancer Care Centers of Brevard, US Oncology Network', 'mono' => 'MD' ),
	);
}

/**
 * Twenty-eight bar heights for a show's waveform.
 *
 * Seeded from the slug rather than randomised, so a show looks the
 * same on every request and across the cache. `mt_srand` is reseeded
 * afterwards to avoid leaking a fixed sequence into anything else
 * running on the request.
 */
function chm_wave_bars( $seed ) {
	mt_srand( crc32( (string) $seed ) );

	$bars = array();
	for ( $i = 0; $i < 28; $i++ ) {
		$bars[] = array(
			'height' => mt_rand( 18, 94 ),
			'delay'  => number_format( fmod( 1.9 - ( $i * 0.07 ), 1.9 ), 2 ),
		);
	}

	mt_srand();
	return $bars;
}

/**
 * The primary nav, for an install with no menu assigned yet.
 *
 * A bar with no links reads as broken, and these five are the public
 * routes the footer already points at. Assigning a menu to the
 * Primary location replaces this entirely.
 */
function chm_default_nav() {
	// Pairs, not a keyed map: Latest and Disease states both point at
	// /catalog, and a keyed array would silently drop one of them.
	$items = array(
		array( '/catalog', __( 'Latest', 'chm' ) ),
		array( '/catalog?format=video', __( 'Videos', 'chm' ) ),
		array( '/podcast-network', __( 'Podcasts', 'chm' ) ),
		array( '/editorial', __( 'Editorial', 'chm' ) ),
		array( '/catalog', __( 'Disease states', 'chm' ) ),
	);

	foreach ( $items as $item ) {
		printf(
			'<a href="%s">%s</a>',
			esc_url( home_url( $item[0] ) ),
			esc_html( $item[1] )
		);
	}
}
