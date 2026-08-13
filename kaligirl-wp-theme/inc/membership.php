<?php
/**
 * Membership integration helpers (Paid Memberships Pro).
 *
 * The theme never implements its own auth, session, or membership logic —
 * all login/registration/gating is delegated to Paid Memberships Pro (PMP),
 * a free-core membership plugin. Login and Account point at PMP's own
 * pages rather than the theme's custom ones, for now:
 *
 *   - Login  -> PMP's Login page ([pmpro_login] shortcode). Verified
 *     against PMP's own source: this shortcode is fully self-contained —
 *     it shows a login form to guests and a "Welcome" widget to members
 *     from the same URL, and PMP's own login_redirect filter sends people
 *     to the right place after signing in.
 *   - Account -> PMP's Membership Account page ([pmpro_account]
 *     shortcode). Verified against PMP's own source: this shortcode has
 *     NO guest-handling logic at all — it assumes you're already logged
 *     in — so it must only ever be reached by someone who's already
 *     passed through the Login page or an existing session.
 *
 * Routing both through PMP's real pages (instead of the theme's own
 * page-login.php / page-account.php) is what fixes the redirect loop that
 * showed up when those two custom pages disagreed about what counts as
 * "logged in enough": the Login page redirected away on is_user_logged_in(),
 * while the Account page's gate required an active membership level, so a
 * logged-in user with no level bounced back and forth forever. PMP's own
 * pages don't have that mismatch because a single plugin owns both ends of
 * the redirect. The theme's own pixel-matched templates are kept in the
 * theme but unused for now — see the note in each file for how to
 * reconnect them later, once that mismatch is resolved for the custom
 * design too.
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
 * PMP's Login page (configured under Memberships > Page Settings, or
 * detected by the slug `login` if a page ID hasn't been set there yet).
 * Falls back to core wp-login.php if PMP hasn't been set up with a Login
 * page at all.
 */
function kaligirl_login_url() {
	if ( function_exists( 'pmpro_url' ) ) {
		$url = pmpro_url( 'login' );
		if ( $url ) {
			return $url;
		}
	}
	return wp_login_url();
}

/**
 * PMP's Membership Account page (auto-created on activation, configured
 * under Memberships > Page Settings). Falls back to /account/ if PMP
 * isn't active yet.
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

function kaligirl_logout_url() {
	return wp_logout_url( home_url( '/' ) );
}

/**
 * Template-level gate for Library/Lessons/Tools. PMP's own "Require
 * Membership" setting on each page (configured in wp-admin) is the
 * primary access control — this is the defense-in-depth check the spec
 * calls for "at the template level, not just by hiding a nav link", in
 * case that setting is missing or misconfigured. Note: PMP's page-level
 * restriction filters `the_content`, which these custom-templated pages
 * never call — so this check is not just a backup here, it's the only
 * control that actually runs for pages built this way.
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
