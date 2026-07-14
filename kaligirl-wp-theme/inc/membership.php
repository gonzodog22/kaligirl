<?php
/**
 * Membership integration helpers (Paid Memberships Pro).
 *
 * The theme never implements its own auth, session, or membership logic —
 * all login/registration/gating is delegated to Paid Memberships Pro (PMP),
 * a free-core membership plugin. Login and Account both point at PMP's own
 * built-in Membership Account page ([pmpro_account]), which shows a login
 * form to logged-out visitors and the account dashboard to members from
 * the same URL. This is deliberate: a separate custom login page whose
 * "already logged in" check doesn't exactly match the account page's
 * membership check is a redirect-loop waiting to happen. The theme's own
 * pixel-matched Login/Account templates (page-login.php / page-account.php)
 * are kept in the theme but unused for now — see the note in each file for
 * how to reconnect them later. PMP owns membership levels (Entry/Grow/
 * Exceed) and page-level access restriction (the "Require Membership" box
 * on each protected page in wp-admin).
 *
 * These helpers just adapt that logged-in/member state to the theme's
 * markup so header/footer/page templates don't need to know PMP's
 * internals directly.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * True when a user session is active. Wraps core WP so the rest of the
 * theme has one call site to change if a plugin-specific session check is
 * ever preferred instead.
 */
function kaligirl_is_logged_in() {
	return is_user_logged_in();
}

/**
 * True when the current user is logged in AND holds an active PMP
 * membership level. Falls back to plain login state if PMP isn't active
 * yet (e.g. local theme preview before plugin setup).
 */
function kaligirl_has_membership() {
	if ( function_exists( 'pmpro_hasMembershipLevel' ) ) {
		return (bool) pmpro_hasMembershipLevel();
	}
	return is_user_logged_in();
}

/**
 * PMP's own Membership Account page — auto-created on activation, uses the
 * [pmpro_account] shortcode, and (unlike a separate custom login page)
 * shows a login form to guests and the account dashboard to members from
 * the exact same URL. That single-URL behavior is what avoids the
 * redirect-loop failure mode a second, separately-gated login page creates.
 */
function kaligirl_account_url() {
	if ( function_exists( 'pmpro_url' ) ) {
		$url = pmpro_url( 'account' );
		if ( $url ) {
			return $url;
		}
	}
	return home_url( '/account/' );
}

/**
 * Login and Account are the same PMP page for now (see file header note).
 */
function kaligirl_login_url() {
	return kaligirl_account_url();
}

function kaligirl_logout_url() {
	return wp_logout_url( home_url( '/' ) );
}

/**
 * Template-level gate for Account/Library/Lessons/Tools. PMP's own
 * "Require Membership" setting on each page (configured in wp-admin) is
 * the primary access control — this is the defense-in-depth check the
 * spec calls for "at the template level, not just by hiding a nav link",
 * in case that setting is missing or misconfigured.
 *
 * Call at the very top of a gated template, before any output.
 */
function kaligirl_require_login() {
	if ( kaligirl_has_membership() ) {
		return;
	}
	wp_safe_redirect( kaligirl_login_url() );
	exit;
}
