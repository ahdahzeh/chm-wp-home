<?php
/**
 * Document head and the site bar.
 *
 * @package CHM
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="sr-only" href="#main"><?php esc_html_e( 'Skip to content', 'chm' ); ?></a>

<header class="site-bar" data-site-bar data-open="false">
	<div class="rail site-bar__in">
		<a class="site-bar__mark" href="<?php echo esc_url( home_url( '/' ) ); ?>"
			aria-label="<?php esc_attr_e( 'CHM, home', 'chm' ); ?>">
			<?php chm_mark( 'wordmark-mark' ); ?><?php chm_wordmark(); ?>
		</a>

		<button class="site-bar__toggle" type="button" aria-expanded="false" aria-controls="site-nav"
			aria-label="<?php esc_attr_e( 'Menu', 'chm' ); ?>">
			<svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
				<path d="M3 6h14M3 10h14M3 14h14" stroke-linecap="round"/>
			</svg>
		</button>

		<?php /* No <ul>/<li>: the bar lays the links out with flex, and a
		         list wrapper would fight the gap. With no menu assigned,
		         the five default destinations render instead of nothing. */ ?>
		<nav class="site-bar__links" id="site-nav" aria-label="<?php esc_attr_e( 'Primary', 'chm' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'items_wrap'     => '%3$s',
					'depth'          => 1,
					'fallback_cb'    => 'chm_default_nav',
				)
			);
			?>
		</nav>

		<div class="site-bar__right">
			<a class="quiet-link" href="<?php echo esc_url( home_url( '/login' ) ); ?>"><?php esc_html_e( 'Log in', 'chm' ); ?></a>
			<a class="btn btn-primary" href="<?php echo esc_url( home_url( '/join' ) ); ?>"><?php esc_html_e( 'Get started', 'chm' ); ?></a>
		</div>
	</div>
</header>
