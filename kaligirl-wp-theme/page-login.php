<?php
/**
 * Template Name: Login
 *
 * Public. Wraps WordPress core's own login form (Paid Memberships Pro
 * doesn't replace wp-login.php) in the design's centered card layout —
 * see style.css's "Login form overrides" section for how core's login
 * form markup gets themed to match. Assign this template to a Page with
 * the slug/path `login`.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Already-logged-in visitors have no reason to see a login form.
if ( kaligirl_is_logged_in() ) {
	wp_safe_redirect( kaligirl_account_url() );
	exit;
}

get_header();
?>
<main>
	<section class="login-wrap">
		<h1>Log in</h1>
		<p class="sub">Access your client account.</p>

		<div class="kg-login-form-wrap">
			<?php
			// wp_login_form() fires the core 'login_form' action inside its own
			// <form>, which is where inc/security.php hooks the honeypot field —
			// no separate call needed here.
			wp_login_form(
				array(
					'redirect'       => kaligirl_account_url(),
					'label_username' => 'Email',
					'label_password' => 'Password',
					'label_log_in'   => 'Log in',
					'remember'       => false,
				)
			);
			?>
		</div>

		<p class="login-footer-note">New here? <a href="<?php echo esc_url( kaligirl_url( 'get-started' ) ); ?>">Get started</a></p>
	</section>
</main>
<?php get_footer(); ?>
