<?php
/**
 * Sticky site header: logo, primary nav (logged-out), account nav
 * (logged-in + Resources dropdown), and the mobile menu.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$kg_logged_in = kaligirl_is_logged_in();
// Per spec: clicking the logo always goes home; if logged in, it also logs
// the user out (mirrors the prototype's goHomeAndLogout behavior).
$kg_logo_href = $kg_logged_in ? kaligirl_logout_url() : kaligirl_url( 'home' );
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
	<div class="kg-container site-header__row">
		<div class="site-header__left">
			<a href="<?php echo esc_url( $kg_logo_href ); ?>" class="site-logo">
				<img src="<?php echo esc_url( KALIGIRL_URI . '/assets/kaligirl-logo.png' ); ?>" alt="<?php bloginfo( 'name' ); ?>">
			</a>

			<?php if ( ! $kg_logged_in ) : ?>
				<nav aria-label="Primary" class="nav-primary">
					<?php
					kaligirl_nav_link( 'services', 'Services' );
					kaligirl_nav_link( 'about', 'About' );
					kaligirl_nav_link( 'contact', 'Contact' );
					?>
				</nav>
			<?php endif; ?>
		</div>

		<?php if ( ! $kg_logged_in ) : ?>
			<nav aria-label="Account" class="nav-account">
				<?php kaligirl_nav_link( 'login', 'Login', false, 'btn-login' ); ?>
				<a href="<?php echo esc_url( kaligirl_url( 'get-started' ) ); ?>" class="btn-pill">Get started</a>
			</nav>
		<?php else : ?>
			<nav aria-label="Account" class="nav-account">
				<?php kaligirl_nav_link( 'account', 'Account' ); ?>
				<div class="resources">
					<button type="button" class="resources-toggle" data-kg-resources-toggle aria-expanded="false" aria-haspopup="true">
						Resources
						<span class="resources-caret" aria-hidden="true"></span>
					</button>
					<div class="resources-menu" data-kg-resources-menu>
						<a href="<?php echo esc_url( kaligirl_url( 'library' ) ); ?>">Library</a>
						<a href="<?php echo esc_url( kaligirl_url( 'lessons' ) ); ?>">Lessons</a>
						<a href="<?php echo esc_url( kaligirl_url( 'tools' ) ); ?>">Tools</a>
					</div>
				</div>
			</nav>
		<?php endif; ?>

		<button type="button" class="mobile-toggle" data-kg-mobile-toggle aria-label="Menu" aria-expanded="false">
			<span></span><span></span><span></span>
		</button>
	</div>

	<div class="mobile-menu" data-kg-mobile-menu>
		<?php if ( ! $kg_logged_in ) : ?>
			<?php
			kaligirl_nav_link( 'services', 'Services', false );
			kaligirl_nav_link( 'about', 'About', false );
			kaligirl_nav_link( 'contact', 'Contact', false );
			kaligirl_nav_link( 'login', 'Login', false );
			?>
			<a href="<?php echo esc_url( kaligirl_url( 'get-started' ) ); ?>" class="btn-pill">Get started</a>
		<?php else : ?>
			<?php
			kaligirl_nav_link( 'account', 'Account', false );
			kaligirl_nav_link( 'library', 'Library', false );
			kaligirl_nav_link( 'lessons', 'Lessons', false );
			kaligirl_nav_link( 'tools', 'Tools', false );
			?>
		<?php endif; ?>
	</div>
</header>
