<?php
/**
 * The closing band: the CTA and the footer sit on one continuous
 * slate, divided by hairline rules rather than by a colour change.
 *
 * The footer columns are a single horizontal scroller at every width.
 * They do not reflow into a grid: reflowing produced a different
 * shape at each breakpoint, and one long row reads the same on a
 * phone and on a desktop.
 *
 * @package CHM
 */

$chm_foot_cols = array(
	array(
		'title' => __( 'Content', 'chm' ),
		'links' => array(
			array( '/catalog', __( 'Content library', 'chm' ) ),
			array( '/catalog', __( 'Playlists', 'chm' ) ),
			array( '/catalog?format=clip', __( 'Clips', 'chm' ) ),
		),
	),
	array(
		'title' => __( 'Disease states', 'chm' ),
		'links' => array(
			array( '/catalog/breast', __( 'Breast Cancer', 'chm' ) ),
			array( '/catalog/lung', __( 'Lung Cancer', 'chm' ) ),
			array( '/catalog/weight-loss', __( 'Weight Loss', 'chm' ) ),
		),
	),
	array(
		'title' => __( 'Shows', 'chm' ),
		'links' => array(
			array( '/podcast-network/the-breast-friends-podcast', __( 'Breast Friends', 'chm' ) ),
			array( '/podcast-network', __( 'In production', 'chm' ) ),
			array( '/podcast-network/cancer-unfiltered', __( 'Cancer Unfiltered', 'chm' ) ),
			array( '/podcast-network/big-c-energy', __( 'Big C Energy', 'chm' ) ),
			array( '/podcast-network/tetalks', __( 'TeTalks', 'chm' ) ),
		),
	),
	array(
		'title' => __( 'Company', 'chm' ),
		'links' => array(
			array( '/about', __( 'About CHM', 'chm' ) ),
			array( '/what-we-do', __( 'What we do', 'chm' ) ),
			array( '/kol-network', __( 'KOL network', 'chm' ) ),
			array( '/contact', __( 'Contact', 'chm' ) ),
		),
	),
	array(
		'title' => __( 'Get started', 'chm' ),
		'links' => array(
			array( '/for-hcps', __( 'For HCPs', 'chm' ) ),
			array( '/join', __( 'Create an account', 'chm' ) ),
			array( '/login', __( 'Log in', 'chm' ) ),
		),
	),
);

$chm_socials = array(
	'Instagram' => '<rect x="2.6" y="2.6" width="10.8" height="10.8" rx="3.2"/><circle cx="8" cy="8" r="2.6"/><circle cx="11.3" cy="4.7" r=".85"/>',
	'YouTube'   => '<rect x="1.7" y="3.7" width="12.6" height="8.6" rx="2.5"/><path d="M6.8 6.5 10 8l-3.2 1.5z"/>',
	'LinkedIn'  => '<rect x="2.2" y="2.2" width="11.6" height="11.6" rx="2.2"/><path d="M5.3 6.9v4.2M5.3 5v.1M7.9 11.1V6.9M7.9 8.5c0-1.5 2.7-1.6 2.7.2v2.4"/>',
	'Facebook'  => '<path d="M9.5 13.8V8.5h1.7l.3-2H9.5V5.2c0-.6.2-1 1-1h1V2.4A13 13 0 0 0 10 2.2C8.4 2.2 7.3 3.2 7.3 5v1.5H5.6v2h1.7v5.3z"/>',
);
?>

<section class="closing">
	<?php /* The marks fade out down their own height rather than sitting
	         as a flat tint, which is what makes the band read as lit
	         from the top instead of as a slab with shapes stamped on it. */ ?>
	<span class="closing__mark closing__mark--l" aria-hidden="true"><?php chm_mark(); ?></span>
	<span class="closing__mark closing__mark--r" aria-hidden="true"><?php chm_mark(); ?></span>

	<div class="rail getstarted__in">
		<h2><?php esc_html_e( 'Free for clinicians. Always.', 'chm' ); ?></h2>
		<p><?php esc_html_e( 'Create an account to save your place, claim credit and get one email a week.', 'chm' ); ?></p>
		<div class="getstarted__actions">
			<a class="btn btn-primary" href="<?php echo esc_url( home_url( '/join' ) ); ?>">
				<?php esc_html_e( 'Start watching free', 'chm' ); ?> <?php chm_arrow(); ?>
			</a>
			<a class="btn btn-secondary" href="<?php echo esc_url( home_url( '/for-hcps' ) ); ?>"><?php esc_html_e( 'For HCPs', 'chm' ); ?></a>
		</div>
	</div>

	<footer class="site-foot">
		<div class="rail">
			<div class="foot-top" tabindex="0" role="group" aria-label="<?php esc_attr_e( 'Site links, scroll for more', 'chm' ); ?>">
				<div class="foot-about">
					<a class="site-bar__mark" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'CHM, home', 'chm' ); ?>"><?php chm_mark( 'wordmark-mark' ); ?><?php chm_wordmark(); ?></a>
					<p><?php esc_html_e( 'Community Health Media. Peer-led oncology education, organised the way clinicians actually work.', 'chm' ); ?></p>
					<address>
						<?php echo wp_kses( __( '2471 18th St NW&nbsp; Second Floor&nbsp; Washington, DC 20009', 'chm' ), array() ); ?><br>
						<a href="mailto:info@communityhealth.media">info@communityhealth.media</a>
					</address>
					<div class="socials">
						<?php foreach ( $chm_socials as $chm_net => $chm_glyph ) : ?>
							<a href="#" aria-label="<?php echo esc_attr( $chm_net ); ?>"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><?php echo $chm_glyph; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- literal SVG geometry defined above. ?></svg></a>
						<?php endforeach; ?>
					</div>
				</div>

				<?php foreach ( $chm_foot_cols as $chm_col ) : ?>
					<div class="foot-col">
						<h4><?php echo esc_html( $chm_col['title'] ); ?></h4>
						<ul>
							<?php foreach ( $chm_col['links'] as $chm_link ) : ?>
								<li><a href="<?php echo esc_url( home_url( $chm_link[0] ) ); ?>"><?php echo esc_html( $chm_link[1] ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="foot-base">
				<a href="<?php echo esc_url( home_url( '/privacy' ) ); ?>"><?php esc_html_e( 'Privacy', 'chm' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/terms' ) ); ?>"><?php esc_html_e( 'Terms', 'chm' ); ?></a>
				<span class="foot-base__mid"><?php
					/* translators: %s: the current year. */
					printf( esc_html__( '&copy; %s Community Health Technologies, Inc. All rights reserved.', 'chm' ), esc_html( wp_date( 'Y' ) ) );
				?></span>
				<span class="foot-base__tag"><?php esc_html_e( 'Medicine moves through shared knowledge', 'chm' ); ?></span>
			</div>
		</div>
	</footer>
</section>

<?php wp_footer(); ?>
</body>
</html>
