<?php
/**
 * Template Name: Podcast network
 *
 * The public hub at /podcast-network. In-app listening stays under
 * /app/podcast-network; this page lists the shows and the places to
 * listen, and nothing here needs a login.
 *
 * Ported from `pages/public/PodcastNetwork.tsx` on origin/main.
 *
 * @package CHM
 */

get_header();

$chm_shows     = chm_shows();
$chm_platforms = chm_podcast_platforms();
?>

<main id="main">
	<?php /* The hero is the covers. They are the thing a listener
	         recognises, and four of them fanned reads as a network in
	         a way a headline alone does not. The stack drifts on a
	         long loop rather than sliding, so it is ambient and never
	         asks to be watched; under prefers-reduced-motion it simply
	         sits still. */ ?>
	<section class="pod-hero">
		<span class="pod-hero__glow" aria-hidden="true"></span>

		<div class="rail pod-hero__in">
			<div class="pod-hero__copy">
				<p class="eyebrow"><?php esc_html_e( 'Podcast network', 'chm' ); ?></p>
				<h1><?php esc_html_e( 'Four shows, one network', 'chm' ); ?></h1>
				<p class="pod-hero__lede"><?php esc_html_e( 'Expert-led conversations in oncology and breast cancer, for clinicians, patients and caregivers. Pick a show, then listen on your platform of choice.', 'chm' ); ?></p>

				<div class="getstarted__actions network__actions">
					<a class="btn btn-primary" href="<?php echo esc_url( home_url( '/join' ) ); ?>">
						<?php esc_html_e( 'Join CHM', 'chm' ); ?> <?php chm_arrow(); ?>
					</a>
					<a class="btn btn-outline" href="<?php echo esc_url( home_url( '/kol-network' ) ); ?>">
						<?php esc_html_e( 'Browse the KOL directory', 'chm' ); ?>
					</a>
				</div>
			</div>

			<ul class="pod-fan" aria-hidden="true">
				<?php foreach ( $chm_shows as $chm_i => $chm_s ) : ?>
					<li style="--i:<?php echo (int) $chm_i; ?>">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/' . $chm_s['cover'] ); ?>"
							alt="" width="1200" height="1200" loading="eager" decoding="async">
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<section class="band rail" aria-labelledby="shows-heading">
		<div class="band__head">
			<h2 id="shows-heading"><?php esc_html_e( 'Shows', 'chm' ); ?></h2>
		</div>

		<ul class="shows">
			<?php foreach ( $chm_shows as $chm_show ) :
				$chm_lang   = $chm_show['lang'] ? ' lang="' . esc_attr( $chm_show['lang'] ) . '"' : '';
				$chm_series = home_url( '/podcast-network/' . $chm_show['slug'] );
				$chm_listen = $chm_show['listen'][0];
				?>
				<li><div class="show show--static" style="--show:<?php echo esc_attr( $chm_show['ground'] ); ?>">
					<span class="eyebrow"><?php echo esc_html( $chm_show['category'] ); ?></span>
					<h3><a class="show__title" href="<?php echo esc_url( $chm_series ); ?>"><?php echo esc_html( $chm_show['label'] ); ?></a></h3>
					<p class="show__tagline"<?php echo $chm_lang; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>><?php echo esc_html( $chm_show['tagline'] ); ?></p>

					<span class="wave" aria-hidden="true"><?php foreach ( chm_wave_bars( $chm_show['slug'] ) as $chm_bar ) : ?><span style="height:<?php echo esc_attr( $chm_bar['height'] ); ?>%;animation-delay:<?php echo esc_attr( $chm_bar['delay'] ); ?>s"></span><?php endforeach; ?></span>

					<span class="show__foot">
						<a class="show__cta" href="<?php echo esc_url( $chm_series ); ?>"><?php esc_html_e( 'View series', 'chm' ); ?> <?php chm_arrow(); ?></a>
						<?php /* Opens the show's own hub, off-site, so it is marked as such. */ ?>
						<a class="show__listen" href="<?php echo esc_url( $chm_listen[1] ); ?>" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Listen', 'chm' ); ?>
							<span class="sr-only"><?php esc_html_e( '(opens in a new tab)', 'chm' ); ?></span>
						</a>
						<span class="eyebrow"<?php echo $chm_lang; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>><?php echo esc_html( $chm_show['update'] ); ?></span>
					</span>
				</div></li>
			<?php endforeach; ?>
		</ul>
	</section>

	<section class="band rail network__platforms" aria-labelledby="listen-heading">
		<div class="band__head">
			<h2 id="listen-heading"><?php esc_html_e( 'Listen on any platform', 'chm' ); ?></h2>
			<p><?php esc_html_e( 'The CHM umbrella feed covers the network. Each series also has its own listen destinations.', 'chm' ); ?></p>
		</div>
		<ul class="platforms">
			<?php foreach ( $chm_platforms as $chm_p ) : ?>
				<li><a href="<?php echo esc_url( $chm_p[1] ); ?>" target="_blank" rel="noopener noreferrer">
					<?php echo esc_html( $chm_p[0] ); ?>
					<span class="sr-only"><?php esc_html_e( '(opens in a new tab)', 'chm' ); ?></span>
				</a></li>
			<?php endforeach; ?>
		</ul>
	</section>
</main>

<?php get_footer(); ?>
