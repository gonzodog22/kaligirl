<?php
/**
 * Membership integration helpers (Paid Memberships Pro).
 *
 * The theme never implements its own auth, session, or membership logic —
 * all login/registration/gating is delegated to Paid Memberships Pro (PMP),
 * a free-core membership plugin. Login itself uses WordPress core's own
 * login form (PMP doesn't replace wp-login.php) — the theme's Login page
 * template just wraps that core form in the design's styled card. PMP owns
 * membership levels (Entry/Grow/Exceed) and page-level access restriction
 * (the "Require Membership" box on each protected page in wp-admin).
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
 * The theme's own Login page (a normal WP Page with the "Login" template
 * assigned) — not a plugin-owned URL, since PMP doesn't provide its own
 * login page.
 */
function kaligirl_login_url() {
	return home_url( '/login/' );
}

/**
 * The theme's own Account page (a normal WP Page with the "Account"
 * template assigned) — kept separate from PMP's built-in Membership
 * Account page so the design's custom Account layout is what renders here.
 */
function kaligirl_account_url() {
	return home_url( '/account/' );
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
