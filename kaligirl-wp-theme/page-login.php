<?php
/**
 * Template Name: Login
 *
 * Not currently assigned to any page. Login now goes through Paid
 * Memberships Pro's own Login page ([pmpro_login] shortcode) instead of
 * this custom one — see the long comment in inc/membership.php for why.
 * Kept here only as a safe stub: if this template is ever assigned to a
 * page again, it just forwards to PMP's real Login page rather than
 * rendering a second, conflicting login form.
 *
 * IMPORTANT: don't assign this template to a page with the slug `login`.
 * PMP treats any page at that slug as its own login page (see
 * pmpro_is_login_page() in PMP's includes/login.php) regardless of which
 * template is assigned, which layers PMP's login redirect behavior on top
 * of whatever this template does — pick a different slug if this design
 * gets revived later (e.g. `client-login`).
 *
 * The original pixel-matched login-card markup (wp_login_form() themed
 * via .kg-login-form-wrap in style.css) is preserved in git history.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_safe_redirect( kaligirl_login_url() );
exit;
