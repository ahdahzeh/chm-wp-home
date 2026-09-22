<?php
/**
 * One session, clip, episode or written perspective.
 *
 * The featured image leads because most of this library is video and
 * the still is what the reader recognises. Below it: the meta line,
 * the body, then the rest of that disease state, a dead end at the
 * bottom of a session is the main way people leave.
 *
 * @package CHM
 */

get_header();

while ( have_posts() ) :
	the_post();

	$chm_post = get_post();
	$chm_area = '';
	foreach ( (array) get_the_category() as $chm_cat ) {
		if ( in_array( $chm_cat->slug, wp_list_pluck( chm_disease_areas(), 'slug' ), true ) ) {
			$chm_area = $chm_cat->slug;
			break;
		}
	}
	?>

	<main id="main">
		<article class="single rail">
			<header class="single__head">
				<p class="eyebrow"><?php echo esc_html( chm_meta_line( $chm_post ) ); ?></p>
				<h1><?php the_title(); ?></h1>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="single__media">
					<?php the_post_thumbnail( 'chm-wide', array( 'alt' => '' ) ); ?>
				</figure>
			<?php endif; ?>

			<div class="prose">
				<?php the_content(); ?>
			</div>

			<?php
			$chm_tags = chm_card_tags( $chm_post );
			if ( $chm_tags ) :
				?>
				<p class="tags single__tags"><?php foreach ( $chm_tags as $chm_t ) : ?><span style="--tag:<?php echo esc_attr( $chm_t['colour'] ); ?>"><?php echo esc_html( $chm_t['label'] ); ?></span><?php endforeach; ?></p>
			<?php endif; ?>
		</article>

		<?php
		$chm_more = $chm_area
			? get_posts(
				array(
					'category_name'       => $chm_area,
					'posts_per_page'      => 4,
					'post__not_in'        => array( get_the_ID() ),
					'ignore_sticky_posts' => true,
				)
			)
			: chm_recent_posts( 4 );

		if ( $chm_more ) :
			?>
			<section class="band rail" aria-labelledby="more-heading">
				<div class="band__head">
					<h2 id="more-heading"><?php esc_html_e( 'More in this area', 'chm' ); ?></h2>
				</div>
				<?php chm_card_grid( $chm_more ); ?>
			</section>
		<?php endif; ?>
	</main>

	<?php
endwhile;

get_footer();
