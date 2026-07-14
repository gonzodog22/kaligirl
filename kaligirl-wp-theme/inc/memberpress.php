<?php
/**
 * MemberPress integration helpers.
 *
 * The theme never implements its own auth, session, or membership logic —
 * per the migration spec, all login/registration/gating is delegated to the
 * MemberPress plugin. These helpers just adapt MemberPress's URLs and
 * logged-in state to the theme's markup so header/footer/page templates
 * don't need to know MemberPress's internals directly.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * True when a member/user session is active. Wraps core WP so the rest of
 * the theme has one call site to change if MemberPress's own session check
 * (MeprUtils / MeprUser) is ever preferred instead.
 */
function kaligirl_is_logged_in() {
	return is_user_logged_in();
}

/**
 * MemberPress auto-creates a Login page (with the [mepr-login-form]
 * shortcode) when it's activated. Prefer its configured URL so this keeps
 * working if an admin renames the page; fall back to /login/ if MemberPress
 * isn't active yet (e.g. local theme preview before plugin setup).
 */
function kaligirl_mepr_login_url() {
	if ( class_exists( 'MeprOptions' ) ) {
		$mepr_options = MeprOptions::fetch();
		if ( ! empty( $mepr_options->login_page_id ) ) {
			$url = get_permalink( $mepr_options->login_page_id );
			if ( $url ) {
				return $url;
			}
		}
	}
	return home_url( '/login/' );
}

/**
 * Same idea for MemberPress's Account page ([mepr-account-*] shortcodes).
 * This theme's page-account.php template is meant to be assigned to that
 * page so the design in the handoff and MemberPress's account shortcodes
 * can share the URL.
 */
function kaligirl_mepr_account_url() {
	if ( class_exists( 'MeprOptions' ) ) {
		$mepr_options = MeprOptions::fetch();
		if ( ! empty( $mepr_options->account_page_id ) ) {
			$url = get_permalink( $mepr_options->account_page_id );
			if ( $url ) {
				return $url;
			}
		}
	}
	return home_url( '/account/' );
}

function kaligirl_logout_url() {
	return wp_logout_url( home_url( '/' ) );
}

/**
 * Template-level gate for Account/Library/Lessons/Tools. MemberPress Rules
 * (configured in wp-admin) are the primary access control — this is the
 * defense-in-depth check the spec calls for "at the template level, not
 * just by hiding a nav link", in case a rule is misconfigured or missing.
 *
 * Call at the very top of a gated template, before any output.
 */
function kaligirl_require_login() {
	if ( kaligirl_is_logged_in() ) {
		return;
	}
	wp_safe_redirect( kaligirl_mepr_login_url() );
	exit;
}
