<?php
/**
 * The homepage, following Figma frame 643:6503 with the client's
 * C-variant arrangement: hero → content first → everything new →
 * disease states → this moment → in conversation → podcast network,
 * then the closing band in footer.php.
 *
 * The markup here is the markup verified in preview/index.html. If
 * you change one, change the other, or the preview stops being
 * evidence of anything.
 *
 * @package CHM
 */

get_header();

$chm_works   = chm_hero_works( 12 );
$chm_areas   = chm_disease_areas();
$chm_moments = chm_moments( 4 );
$chm_people  = chm_people_featured( 4 );
$chm_shows   = chm_shows();
$chm_img     = get_template_directory_uri() . '/assets/img/';

$chm_tabs = array(
	'all'       => array( 'label' => __( 'All', 'chm' ), 'format' => '' ),
	'video'     => array( 'label' => __( 'Video', 'chm' ), 'format' => 'video' ),
	'podcast'   => array( 'label' => __( 'Podcast', 'chm' ), 'format' => 'podcast' ),
	'editorial' => array( 'label' => __( 'Editorial', 'chm' ), 'format' => 'editorial' ),
);
?>

<main id="main">

	<?php /* ── hero ──────────────────────────────────────────────────
	         The <ul> is the real content; the canvas draws on top of
	         it. Delete hero.js and the list becomes a responsive grid,
	         which is also what a browser without WebGL2 and what a
	         crawler get. ─────────────────────────────────────────── */ ?>
	<section class="hero rail" data-hero data-gl="off"
		aria-roledescription="carousel"
		aria-label="<?php esc_attr_e( 'Featured sessions', 'chm' ); ?>">

		<canvas class="hero__field" aria-hidden="true"></canvas>
		<canvas class="hero__canvas" aria-hidden="true"></canvas>

		<div class="hero__copy hero__copy--top">
			<h1><?php
				/* Two lines, not three: the break is authored rather than
				   left to the measure, which moves with the viewport. */
				echo wp_kses(
					get_theme_mod( 'chm_hero_heading', __( 'Medicine moves through<br>shared knowledge.', 'chm' ) ),
					array( 'br' => array() )
				);
			?></h1>
		</div>

		<?php if ( $chm_works ) : ?>
			<ul class="hero__works">
				<?php foreach ( $chm_works as $chm_work ) :
					$chm_post = $chm_work['post'];
					$chm_meta = chm_meta_line( $chm_post );
					?>
					<li><a class="work-card" href="<?php echo esc_url( get_permalink( $chm_post ) ); ?>" data-work
						data-thumb="<?php echo esc_url( $chm_work['thumb'] ); ?>"
						data-title="<?php echo esc_attr( get_the_title( $chm_post ) ); ?>"
						data-meta="<?php echo esc_attr( $chm_meta ); ?>">
						<img src="<?php echo esc_url( $chm_work['thumb'] ); ?>" alt="" width="640" height="360" loading="lazy">
						<h3><?php echo esc_html( get_the_title( $chm_post ) ); ?></h3>
						<span class="eyebrow"><?php echo esc_html( $chm_meta ); ?></span>
					</a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<div class="hero__copy hero__copy--bottom">
			<p><?php echo esc_html( get_theme_mod( 'chm_hero_lede', __( 'Expert video, podcasts and editorial for oncology, organised by disease state and by format, so you can browse the way you actually work.', 'chm' ) ) ); ?></p>
		</div>
	</section>

	<?php /* ── content first: the format bento ─────────────────────
	         Ported from the app's FormatBento so the two properties
	         match. The wave and the pipeline run continuously rather
	         than on hover: a touch screen never hovers, and the point
	         of the row is seeing every format at once. ───────────── */ ?>
	<section class="band rail" aria-labelledby="content-first">
		<div class="band__head">
			<h2 id="content-first"><?php esc_html_e( 'Content first', 'chm' ); ?></h2>
			<p><?php esc_html_e( 'One conversation, recorded once and given four ways in. Take the format that fits the ten minutes you have.', 'chm' ); ?></p>
			<a class="band__action" href="<?php echo esc_url( home_url( '/catalog' ) ); ?>"><?php esc_html_e( 'Browse all', 'chm' ); ?></a>
		</div>

		<div class="bento">
			<a class="bento__card" href="<?php echo esc_url( home_url( '/catalog' ) ); ?>">
				<span class="bento__head"><span class="eyebrow"><?php esc_html_e( 'Video', 'chm' ); ?></span><span class="bento__meta">18:40</span></span>
				<span class="bento__media">
					<span class="bento__stack">
						<img src="<?php echo esc_url( $chm_img . 'thumb-cleopatra.jpg' ); ?>" alt="" loading="lazy">
						<ol class="chapters">
							<li><span class="t">00:00</span><span class="l"><?php esc_html_e( 'The case', 'chm' ); ?></span></li>
							<li><span class="t">03:12</span><span class="l"><?php esc_html_e( 'What the registrational data says', 'chm' ); ?></span></li>
							<li><span class="t">08:40</span><span class="l"><?php esc_html_e( 'Where the guidelines lag', 'chm' ); ?></span></li>
						</ol>
					</span>
				</span>
				<span><h3><?php esc_html_e( 'The long-form conversation', 'chm' ); ?></h3><p><?php esc_html_e( 'Two clinicians work a case end to end.', 'chm' ); ?></p></span>
			</a>

			<a class="bento__card" href="<?php echo esc_url( home_url( '/podcast-network' ) ); ?>">
				<span class="bento__head"><span class="eyebrow eyebrow--accent"><?php esc_html_e( 'Podcast', 'chm' ); ?></span><span class="bento__meta">34:02</span></span>
				<span class="bento__media"><canvas class="bento__wave" data-wave aria-hidden="true"></canvas></span>
				<span><h3><?php esc_html_e( 'The audio cut', 'chm' ); ?></h3><p><?php esc_html_e( 'The same conversation, for the commute.', 'chm' ); ?></p></span>
			</a>

			<a class="bento__card" href="<?php echo esc_url( home_url( '/editorial' ) ); ?>">
				<span class="bento__head"><span class="eyebrow"><?php esc_html_e( 'Editorial', 'chm' ); ?></span><span class="bento__meta">6 min</span></span>
				<span class="bento__media">
					<?php /* Two identical passes so the 22s loop has no seam. */ ?>
					<span class="bento__drift" aria-hidden="true"><div>
						<?php for ( $chm_pass = 0; $chm_pass < 2; $chm_pass++ ) : ?>
							<p><?php esc_html_e( 'Recurrence remains a clinically important challenge in high-risk HER2-positive early breast cancer.', 'chm' ); ?></p>
							<p><?php esc_html_e( 'Trastuzumab-based therapy improved long-term survival, but some patients still recur after standard adjuvant treatment.', 'chm' ); ?></p>
							<p><?php esc_html_e( 'Extended adjuvant therapy was designed for exactly that gap: sustained inhibition after standard therapy completes.', 'chm' ); ?></p>
							<p><?php esc_html_e( 'The benefit concentrates in those who begin within a year of finishing trastuzumab.', 'chm' ); ?></p>
						<?php endfor; ?>
					</div></span>
				</span>
				<span><h3><?php esc_html_e( 'The written explainer', 'chm' ); ?></h3><p><?php esc_html_e( 'What changed, and what it changes.', 'chm' ); ?></p></span>
			</a>

			<a class="bento__card bento__card--live" href="<?php echo esc_url( home_url( '/live' ) ); ?>">
				<span class="bento__head"><span class="eyebrow"><?php esc_html_e( 'Live', 'chm' ); ?></span><span class="bento__meta"><?php esc_html_e( 'Next: 4 Sep', 'chm' ); ?></span></span>
				<span class="bento__media">
					<span class="bento__live">
						<span class="bento__date"><span><span class="eyebrow"><?php esc_html_e( 'Sep', 'chm' ); ?></span><b>4</b></span></span>
						<ul class="bento__sessions">
							<li><span class="s"><?php esc_html_e( 'Implementing DESTINY-Breast11 in practice', 'chm' ); ?></span><span class="w">4:00 PM ET</span></li>
							<li><span class="s"><?php esc_html_e( 'Managing the AKT pathway in second line', 'chm' ); ?></span><span class="w">11 Sep</span></li>
						</ul>
					</span>
				</span>
				<span><h3><?php esc_html_e( 'Office Hours', 'chm' ); ?></h3><p><?php esc_html_e( 'Send the case you are stuck on. Two faculty work it live, without the answer in advance.', 'chm' ); ?></p></span>
			</a>

			<a class="bento__card bento__card--hcp" href="<?php echo esc_url( home_url( '/for-hcps' ) ); ?>">
				<span class="bento__head"><span class="eyebrow"><?php esc_html_e( 'For clinicians', 'chm' ); ?></span><span class="bento__meta"><?php esc_html_e( 'Free', 'chm' ); ?></span></span>
				<span class="bento__media"><canvas class="bento__pipeline" data-pipeline aria-hidden="true"></canvas></span>
				<span><h3><?php esc_html_e( 'The HCP platform', 'chm' ); ?></h3><p><?php esc_html_e( 'Every session, every format, filed by disease state. Free, and it stays free.', 'chm' ); ?></p></span>
			</a>
		</div>
	</section>

	<?php /* ── everything new, chipped by format ─────────────────── */ ?>
	<section class="band rail" aria-labelledby="everything-new">
		<div class="band__head">
			<h2 id="everything-new"><?php esc_html_e( 'Everything new, in every format', 'chm' ); ?></h2>
			<p><?php esc_html_e( 'One section instead of three. The format is a filter, not another scroll.', 'chm' ); ?></p>
			<a class="band__action" href="<?php echo esc_url( home_url( '/catalog' ) ); ?>"><?php esc_html_e( 'Browse the library', 'chm' ); ?> <?php chm_arrow(); ?></a>
		</div>

		<div data-tabs>
			<div class="chips" role="tablist" aria-label="<?php esc_attr_e( 'Filter by format', 'chm' ); ?>">
				<?php $chm_first = true; foreach ( $chm_tabs as $chm_key => $chm_tab ) : ?>
					<button class="chip" type="button" role="tab"
						id="tab-<?php echo esc_attr( $chm_key ); ?>"
						aria-controls="panel-<?php echo esc_attr( $chm_key ); ?>"
						aria-selected="<?php echo $chm_first ? 'true' : 'false'; ?>"
						<?php echo $chm_first ? '' : 'tabindex="-1"'; ?>><?php echo esc_html( $chm_tab['label'] ); ?></button>
				<?php $chm_first = false; endforeach; ?>
			</div>

			<?php $chm_first = true; foreach ( $chm_tabs as $chm_key => $chm_tab ) :
				$chm_posts = chm_recent_posts( 4, $chm_tab['format'] );
				?>
				<div role="tabpanel" tabindex="0"
					id="panel-<?php echo esc_attr( $chm_key ); ?>"
					aria-labelledby="tab-<?php echo esc_attr( $chm_key ); ?>"
					<?php echo $chm_first ? '' : 'hidden'; ?>>

					<?php if ( $chm_posts ) : ?>
						<ul class="card-grid">
							<?php foreach ( $chm_posts as $chm_post ) : ?>
								<li><a class="post-card" href="<?php echo esc_url( get_permalink( $chm_post ) ); ?>">
									<span class="shot">
										<?php if ( has_post_thumbnail( $chm_post ) ) : ?>
											<?php echo get_the_post_thumbnail( $chm_post, 'chm-hero-work', array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
										<?php endif; ?>
										<span class="play"><?php chm_play(); ?></span>
									</span>
									<h3><?php echo esc_html( get_the_title( $chm_post ) ); ?></h3>
									<?php $chm_card_tags = chm_card_tags( $chm_post ); ?>
									<?php if ( $chm_card_tags ) : ?>
										<span class="tags"><?php foreach ( $chm_card_tags as $chm_tag ) : ?><span style="--tag:<?php echo esc_attr( $chm_tag['colour'] ); ?>"><?php echo esc_html( $chm_tag['label'] ); ?></span><?php endforeach; ?></span>
									<?php endif; ?>
									<span class="kind"><?php echo esc_html( chm_format_name( $chm_post ) ); ?></span>
								</a></li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<p class="lede"><?php esc_html_e( 'Nothing published in this format yet.', 'chm' ); ?></p>
					<?php endif; ?>
				</div>
			<?php $chm_first = false; endforeach; ?>
		</div>
	</section>

	<?php /* ── disease states ────────────────────────────────────────
	         The drawing carries the hue and the well behind it stays
	         neutral, so the anatomy is what tells you which area you
	         are looking at. No particle field: at every strength it
	         competed with the drawing. ──────────────────────────── */ ?>
	<section class="band rail areas-band" aria-labelledby="areas-heading">
		<span class="areas-mark" aria-hidden="true"><?php chm_mark(); ?></span>
		<div class="areas-grid">
			<div class="areas-intro">
				<h2 id="areas-heading"><?php esc_html_e( 'Explore by', 'chm' ); ?><br><?php esc_html_e( 'disease state', 'chm' ); ?></h2>
				<p><?php esc_html_e( 'Each cluster is sized by what the area actually holds.', 'chm' ); ?></p>
			</div>
			<ul class="areas">
				<?php foreach ( $chm_areas as $chm_area ) :
					$chm_count = chm_area_count( $chm_area['slug'] );
					?>
					<li><a class="area" href="<?php echo esc_url( home_url( '/catalog/' . $chm_area['slug'] ) ); ?>"
						style="--cluster-hue:<?php echo esc_attr( $chm_area['hue'] ); ?>;--cluster-ink:<?php echo esc_attr( $chm_area['ink'] ); ?>">
						<span class="area__cluster"><span class="area__art area__art--<?php echo esc_attr( $chm_area['art'] ); ?>"></span></span>
						<span class="area__body">
							<h3><?php echo esc_html( $chm_area['label'] ); ?></h3>
							<p><?php
								/* translators: %s: number of published items in this disease area. */
								echo $chm_count ? esc_html( sprintf( _n( '%s item', '%s items', $chm_count, 'chm' ), number_format_i18n( $chm_count ) ) ) : esc_html( $chm_area['note'] );
							?></p>
						</span>
					</a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<?php /* ── this moment in medicine ───────────────────────────── */ ?>
	<section class="band rail band--footaction" aria-labelledby="moment-heading">
		<div class="band__head">
			<h2 id="moment-heading"><?php esc_html_e( 'This moment in medicine', 'chm' ); ?></h2>
			<p><?php esc_html_e( 'Short answers to the questions that come up between patients.', 'chm' ); ?></p>
		</div>

		<?php if ( $chm_moments ) : ?>
			<ul class="moments">
				<?php foreach ( $chm_moments as $chm_i => $chm_post ) : ?>
					<li><a class="moment" href="<?php echo esc_url( get_permalink( $chm_post ) ); ?>">
						<?php if ( has_post_thumbnail( $chm_post ) ) : ?>
							<?php echo get_the_post_thumbnail( $chm_post, 'chm-hero-work', array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
						<?php endif; ?>
						<span>
							<?php $chm_dur = chm_duration( $chm_post ); ?>
							<?php if ( $chm_dur ) : ?><span class="moment__dur"><?php echo esc_html( $chm_dur ); ?></span><?php endif; ?>
							<h3><?php echo esc_html( get_the_title( $chm_post ) ); ?></h3>
						</span>
						<span class="moment__no" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $chm_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
					</a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php /* The link concludes the row rather than being a shortcut
		         past it, so it sits under the cards, not beside the head. */ ?>
		<div class="band__foot">
			<a class="band__action" href="<?php echo esc_url( home_url( '/catalog' ) ); ?>"><?php esc_html_e( 'See all episodes', 'chm' ); ?> <?php chm_arrow(); ?></a>
		</div>
	</section>

	<?php /* ── in conversation ───────────────────────────────────── */ ?>
	<section class="band rail" aria-labelledby="people-heading">
		<div class="band__head">
			<h2 id="people-heading"><?php esc_html_e( 'In conversation', 'chm' ); ?></h2>
			<p><?php esc_html_e( 'Practising specialists who bring their own audiences.', 'chm' ); ?></p>
			<a class="band__action" href="<?php echo esc_url( home_url( '/kol-network' ) ); ?>"><?php esc_html_e( 'See all profiles', 'chm' ); ?> <?php chm_arrow(); ?></a>
		</div>
		<ul class="people">
			<?php foreach ( $chm_people as $chm_person ) : ?>
				<li><a class="person" href="<?php echo esc_url( home_url( '/kol-network' ) ); ?>">
					<span class="person__ghost" aria-hidden="true"><?php chm_mark(); ?></span>
					<span class="person__mono" aria-hidden="true"><?php echo esc_html( $chm_person['mono'] ); ?></span>
					<span class="person__label"><h3><?php echo esc_html( $chm_person['name'] ); ?></h3><p><?php echo esc_html( $chm_person['org'] ); ?></p></span>
				</a></li>
			<?php endforeach; ?>
		</ul>
	</section>

	<?php /* ── podcast network ───────────────────────────────────── */ ?>
	<section class="band rail" aria-labelledby="shows-heading">
		<div class="band__head">
			<h2 id="shows-heading"><?php esc_html_e( 'CHM Podcast network', 'chm' ); ?></h2>
			<p><?php esc_html_e( 'Four shows, each with its own voice.', 'chm' ); ?></p>
			<a class="band__action" href="<?php echo esc_url( home_url( '/podcast-network' ) ); ?>"><?php esc_html_e( 'All shows', 'chm' ); ?></a>
		</div>
		<ul class="shows">
			<?php foreach ( $chm_shows as $chm_show ) : ?>
				<li><a class="show" href="<?php echo esc_url( home_url( '/podcast-network/' . $chm_show['slug'] ) ); ?>"
					style="--show:<?php echo esc_attr( $chm_show['ground'] ); ?>">
					<span class="eyebrow"><?php echo esc_html( $chm_show['category'] ); ?></span>
					<h3><?php echo esc_html( $chm_show['label'] ); ?></h3>
					<p class="show__tagline"<?php echo $chm_show['lang'] ? ' lang="' . esc_attr( $chm_show['lang'] ) . '"' : ''; ?>><?php echo esc_html( $chm_show['tagline'] ); ?></p>
					<span class="wave" aria-hidden="true"><?php foreach ( chm_wave_bars( $chm_show['slug'] ) as $chm_bar ) : ?><span style="height:<?php echo esc_attr( $chm_bar['height'] ); ?>%;animation-delay:<?php echo esc_attr( $chm_bar['delay'] ); ?>s"></span><?php endforeach; ?></span>
					<span class="show__foot">
						<span class="show__play"><?php chm_play(); ?></span>
						<span><?php esc_html_e( 'Listen', 'chm' ); ?></span>
						<span class="eyebrow"<?php echo $chm_show['lang'] ? ' lang="' . esc_attr( $chm_show['lang'] ) . '"' : ''; ?>><?php echo esc_html( $chm_show['update'] ); ?></span>
					</span>
				</a></li>
			<?php endforeach; ?>
		</ul>
	</section>

</main>

<?php get_footer(); ?>
