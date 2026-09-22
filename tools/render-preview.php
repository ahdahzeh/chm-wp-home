<?php
/**
 * A throwaway WordPress stand-in: just enough of core to render the
 * theme's templates and diff the output against the verified preview.
 * Not shipped with the theme.
 */
define( 'ABSPATH', __DIR__ . '/' );
define( 'STR_PAD_LEFT_OK', true );

$GLOBALS['chm_stub_theme'] = getenv( 'CHM_THEME' );

function get_template_directory()     { return $GLOBALS['chm_stub_theme']; }
function get_template_directory_uri() { return '..'; }
function home_url( $p = '/' )         { return $p; }
function __( $s, $d = '' )            { return $s; }
function esc_html__( $s, $d = '' )    { return $s; }
function esc_attr__( $s, $d = '' )    { return $s; }
function _n( $s, $p, $n, $d = '' )    { return 1 === (int) $n ? $s : $p; }
function esc_html( $s )               { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $s )               { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function esc_url( $s )                { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function esc_html_e( $s, $d = '' )    { echo esc_html( $s ); }
function esc_attr_e( $s, $d = '' )    { echo esc_attr( $s ); }
function wp_kses( $s, $allowed )      { return $s; }
function language_attributes()        { echo 'lang="en"'; }
function bloginfo( $k )               { echo 'utf-8' === $k || 'charset' === $k ? 'utf-8' : 'CHM'; }
function body_class()                 { echo 'class="home"'; }
function wp_body_open()               {}
function number_format_i18n( $n )     { return number_format( $n ); }
function wp_date( $f )                { return gmdate( $f ); }
function is_wp_error( $t )            { return false; }
function taxonomy_exists( $t )        { return in_array( $t, array( 'format', 'category', 'series' ), true ); }
function add_theme_support()          {}
function add_action()                 {}
function add_filter()                 {}
function register_nav_menus()         {}
function add_image_size()             {}
function wp_enqueue_style()           {}
function wp_enqueue_script()          {}
function get_theme_mod( $k, $d = '' ) { return $d; }
function wp_list_pluck( $a, $k )      { return array_column( $a, $k ); }
function get_term_by( $f, $s, $t )    { return (object) array( 'count' => 0 ); }

// The head and foot the stub emits in place of WordPress's own, so the
// rendered page loads the same fonts and stylesheets as the preview.
function wp_head() {
	echo '<title>CHM. Expert oncology education for clinicians</title>' . "\n";
	echo '<link rel="stylesheet" href="https://api.fontshare.com/v2/css?f[]=chillax@400,500,600&display=swap">' . "\n";
	echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geist:wght@400;450;500&family=Geist+Mono:wght@500&display=swap">' . "\n";
	// python3 -m http.server sends no cache headers, so a stamp is the
	// only thing stopping the browser serving a stale stylesheet.
	$v = time();
	echo '<link rel="stylesheet" href="../assets/css/tokens.css?v=' . $v . '">' . "\n";
	echo '<link rel="stylesheet" href="../assets/css/home.css?v=' . $v . '">' . "\n";
}
function wp_footer() { echo '<script src="../assets/js/hero.js" defer></script>' . "\n"; }

function wp_nav_menu( $args ) { call_user_func( $args['fallback_cb'] ); }

// Twelve fixture posts, mirroring the preview's works.
$GLOBALS['chm_stub_posts'] = array();
$chm_fixtures = array(
	array( 'ild-tdxd', 'Managing ILD risk with early-stage T-DXd', 'Full session', '24:18', 'thumb-ild.jpg', 'breast' ),
	array( 'db09-1l', 'DB-09: redefining 1L metastatic HER2+ therapy', 'Full session', '31:02', 'thumb-db09.jpg', 'breast' ),
	array( 'db05-practice', 'Understanding DB05 in clinical practice', 'Clip', '4:12', 'thumb-patina.jpg', 'breast' ),
	array( 'adc-second-line', 'ADCs in the second line, and what follows', 'Audio cut', '34:02', 'thumb-cleopatra.jpg', 'lung' ),
	array( 'brca-adc', 'Do BRCA patients benefit from ADCs?', 'Clip', '4:02', 'thumb-db09.jpg', 'breast' ),
	array( 'neratinib', 'Neratinib revisited: time to reconsider?', 'Perspective', '6 min', 'thumb-ild.jpg', 'breast' ),
	array( 'post-adc-sequencing', 'Sequencing after progression on an ADC', 'Perspective', '8 min', 'thumb-cleopatra.jpg', 'gi' ),
	array( 'residual-disease', 'Residual disease, and what it predicts', 'Editorial', '6 min', 'thumb-patina.jpg', 'breast' ),
	array( 'her2-low-threshold', 'HER2-low: where the threshold actually sits', 'Clip', '5:20', 'thumb-ild.jpg', 'breast' ),
	array( 'perioperative-io', 'Perioperative immunotherapy, worked through', 'Full session', '28:40', 'thumb-db09.jpg', 'lung' ),
	array( 'step-up-dosing', 'Step-up dosing in practice', 'Audio cut', '22:06', 'thumb-patina.jpg', 'hematology' ),
	array( 'parp-maintenance', 'PARP maintenance after first line', 'Clip', '3:48', 'thumb-cleopatra.jpg', 'gynecology' ),
);
foreach ( $chm_fixtures as $i => $f ) {
	$GLOBALS['chm_stub_posts'][] = (object) array(
		'ID' => $i + 1, 'slug' => $f[0], 'title' => $f[1],
		'format' => $f[2], 'duration' => $f[3], 'thumb' => $f[4], 'area' => $f[5],
	);
}

function get_posts( $a = array() )    { return $GLOBALS['chm_stub_posts']; }
function get_permalink( $p = null )   { $p = $p ?: $GLOBALS['chm_stub_posts'][0]; return '/catalog/clip/' . $p->slug; }
function get_the_title( $p = null )   { $p = $p ?: $GLOBALS['chm_stub_posts'][0]; return $p->title; }
function get_the_date( $f, $p )       { return '1 Sep 2026'; }
function has_post_thumbnail( $p = null ) { return true; }
function get_the_post_thumbnail_url( $p, $s = '' ) { return '../assets/img/' . $p->thumb; }
function get_the_post_thumbnail( $p, $s = '', $attr = array() ) {
	return '<img src="../assets/img/' . $p->thumb . '" alt="" loading="lazy">';
}
function get_post_meta( $id, $key, $single = false ) {
	foreach ( $GLOBALS['chm_stub_posts'] as $p ) {
		if ( $p->ID === $id ) {
			if ( 'duration' === $key ) { return $p->duration; }
			if ( 'channel' === $key )  { return 'YouTube'; }
		}
	}
	return '';
}
function get_the_terms( $p, $tax ) {
	return 'format' === $tax ? array( (object) array( 'name' => $p->format ) ) : array();
}
function get_the_category( $id = null ) {
	$id = $id ?: $GLOBALS['chm_stub_posts'][0]->ID;
	foreach ( $GLOBALS['chm_stub_posts'] as $p ) {
		if ( $p->ID === $id ) { return array( (object) array( 'slug' => $p->area, 'name' => ucfirst( $p->area ) ) ); }
	}
	return array();
}

class WP_Query {
	public $posts;
	public function __construct( $args = array() ) {
		$n = isset( $args['posts_per_page'] ) ? (int) $args['posts_per_page'] : 4;
		// A format-filtered query returns the posts carrying that format.
		if ( ! empty( $args['tax_query'][0]['taxonomy'] ) && 'format' === $args['tax_query'][0]['taxonomy'] ) {
			$want = (array) $args['tax_query'][0]['terms'];
			$this->posts = array_slice( array_values( array_filter(
				$GLOBALS['chm_stub_posts'],
				function ( $p ) use ( $want ) {
					return in_array( strtolower( str_replace( ' ', '', $p->format ) ), array_map(
						function ( $w ) { return str_replace( array( 'video', 'podcast', 'editorial' ), array( 'fullsession', 'audiocut', 'editorial' ), $w ); },
						$want
					), true );
				}
			) ), 0, $n );
			return;
		}
		if ( ! empty( $args['tax_query'] ) ) { $this->posts = array(); return; }
		$this->posts = array_slice( $GLOBALS['chm_stub_posts'], 0, $n );
	}
}

/* ── enough of the loop and the conditionals for the secondary
      templates: archive, search, single, page and 404. ───────── */

function wp_parse_args( $a, $d = array() ) { return array_merge( $d, (array) $a ); }

$GLOBALS['chm_stub_ctx'] = getenv( 'CHM_CTX' ) ?: 'archive';
$GLOBALS['chm_stub_i']   = -1;

class CHM_Stub_Query {
	public $posts;
	public $found_posts;
	public function __construct( $posts ) { $this->posts = $posts; $this->found_posts = count( $posts ); }
}
$GLOBALS['wp_query'] = new CHM_Stub_Query( array_slice( $GLOBALS['chm_stub_posts'], 0, 8 ) );

function is_category() { return 'archive' === $GLOBALS['chm_stub_ctx']; }
function is_archive()  { return 'archive' === $GLOBALS['chm_stub_ctx']; }
function get_queried_object() { return (object) array( 'slug' => 'breast', 'name' => 'Breast cancer' ); }
function get_the_archive_title() { return 'Breast cancer'; }
function get_search_query() { return 'HER2-low'; }
function has_nav_menu( $l = '' ) { return false; }
function wp_strip_all_tags( $s ) { return strip_tags( $s ); }

function have_posts() { return $GLOBALS['chm_stub_i'] < 0; }
function the_post() { $GLOBALS['chm_stub_i']++; $GLOBALS['post'] = $GLOBALS['chm_stub_posts'][0]; }
function get_post() { return $GLOBALS['chm_stub_posts'][0]; }
function get_the_ID() { return $GLOBALS['chm_stub_posts'][0]->ID; }
function the_title() { echo esc_html( $GLOBALS['chm_stub_posts'][0]->title ); }
function has_excerpt() { return true; }
function get_the_excerpt() { return 'A short standfirst that sets up what the page covers, in one sentence.'; }
function the_post_thumbnail( $size = '', $attr = array() ) {
	echo '<img src="../assets/img/' . $GLOBALS['chm_stub_posts'][0]->thumb . '" alt="">';
}
function the_content() {
	echo '<p>Recurrence remains a clinically important challenge in high-risk HER2-positive early breast cancer, and the registrational data has moved twice in three years.</p>';
	echo '<h2>What the data says</h2><p>Trastuzumab-based therapy improved long-term survival, but a meaningful share of patients still recur after standard adjuvant treatment. <a href="/catalog">Browse the sessions</a> that work through it.</p>';
	echo '<ul><li>Extended adjuvant therapy targets exactly that gap.</li><li>The benefit concentrates in those who begin within a year.</li></ul>';
	echo '<h3>Where the guidelines lag</h3><p>Guidance has not yet caught up with the second readout.</p>';
	echo '<blockquote><p>The question is no longer whether to treat, but for how long.</p></blockquote>';
}
function the_posts_pagination( $a = array() ) {
	echo '<nav class="pagination"><div class="nav-links"><span class="current">1</span><a href="#">2</a><a href="#">3</a><span class="dots">&hellip;</span><a href="#">9</a><a href="#">Next</a></div></nav>';
}

function get_header() { require $GLOBALS['chm_stub_theme'] . '/header.php'; }
function get_footer() { require $GLOBALS['chm_stub_theme'] . '/footer.php'; }

require $GLOBALS['chm_stub_theme'] . '/functions.php';
$chm_tpl = getenv( 'CHM_TPL' ) ?: 'front-page.php';
require $GLOBALS['chm_stub_theme'] . '/' . $chm_tpl;
