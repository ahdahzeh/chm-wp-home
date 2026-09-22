<?php
/**
 * A static page: About, For HCPs, Contact, Privacy, Terms.
 *
 * One template for all of them. They differ in words, not in shape,
 * and a template each would mean five places to change the measure.
 *
 * @package CHM
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>

	<main id="main">
		<?php
		chm_page_header(
			array(
				'title' => get_the_title(),
				'lede'  => has_excerpt() ? get_the_excerpt() : '',
			)
		);
		?>

		<div class="rail">
			<article class="prose">
				<?php the_content(); ?>
			</article>
		</div>
	</main>

	<?php
endwhile;

get_footer();
