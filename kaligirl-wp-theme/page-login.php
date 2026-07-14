<?php
/**
 * Template Name: Login
 *
 * Public. Wraps MemberPress's real login form ([mepr-login-form]) in the
 * design's centered card layout — see style.css's "MemberPress form
 * overrides" section for how the shortcode's markup gets themed to match.
 * Assign this template to the page MemberPress auto-created for login
 * (Pages > Login) so the URL and the styled wrapper are the same page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Already-logged-in visitors have no reason to see a login form.
if ( kaligirl_is_logged_in() ) {
	wp_safe_redirect( kaligirl_mepr_account_url() );
	exit;
}

get_header();
?>
<main>
	<section class="login-wrap">
		<h1>Log in</h1>
		<p class="sub">Access your client account.</p>

		<?php if ( shortcode_exists( 'mepr-login-form' ) ) : ?>
			<div class="mepr-login-form-wrap">
				<?php echo do_shortcode( '[mepr-login-form]' ); ?>
			</div>
		<?php else : ?>
			<div class="login-card">
				<p class="hint" style="margin:0 0 0.5rem;">MemberPress isn't active yet — install and activate it to enable real login. This is a non-functional preview of the styled form only.</p>
				<label>Email
					<input type="email" placeholder="you@email.com" disabled>
				</label>
				<label>Password
					<input type="password" placeholder="••••••••" disabled>
				</label>
				<button type="button" disabled>Log in</button>
			</div>
		<?php endif; ?>

		<p class="login-footer-note">New here? <a href="<?php echo esc_url( kaligirl_url( 'get-started' ) ); ?>">Get started</a></p>
	</section>
</main>
<?php get_footer(); ?>
