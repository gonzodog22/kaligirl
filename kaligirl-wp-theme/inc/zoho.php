<?php
/**
 * Zoho integration: gate-token validation for /booking and /payment.
 *
 * Per the Get Started migration handoff, Moxo is dropped and MemberPress's
 * status is unconfirmed (left untouched in this repo — see README). The
 * Get Started flow instead routes visitors through externally-hosted Zoho
 * Forms, which carry a one-time `gated_token` through to a Zoho Creator
 * "intake-token-gate" datastore. Before /booking or /payment render a real
 * Zoho Bookings/Billing embed, this file validates that token server-side
 * against Zoho Creator's REST API — nobody reaches either page's embed by
 * guessing a URL, only by completing a real form submission first.
 *
 * Credentials (ZOHO_CLIENT_ID, ZOHO_CLIENT_SECRET, ZOHO_REFRESH_TOKEN) are
 * wp-config.php constants already provisioned on the server — read via
 * kaligirl_secret() (inc/security.php), never hardcoded or read directly
 * here. This file only ever references the constant *names*.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Zoho Creator account/workspace segment of the report URL
 * (`{account-owner}` in the migration handoff). Not itself a secret, but
 * kept out of hardcoded URLs the same way — set as a wp-config.php
 * constant or environment variable once known; falls back to a visible
 * placeholder so a misconfiguration fails obviously rather than silently.
 */
function kaligirl_zoho_creator_account_owner() {
	return kaligirl_secret( 'ZOHO_CREATOR_ACCOUNT_OWNER', 'PLACEHOLDER-account-owner' );
}

/**
 * Exchange the long-lived refresh token for a short-lived access token.
 * Access tokens run ~1hr per Zoho; cached in a transient with a safety
 * margin so normal traffic doesn't re-exchange on every single request.
 */
function kaligirl_zoho_access_token() {
	$cached = get_transient( 'kaligirl_zoho_access_token' );
	if ( $cached ) {
		return $cached;
	}

	$client_id     = kaligirl_secret( 'ZOHO_CLIENT_ID' );
	$client_secret = kaligirl_secret( 'ZOHO_CLIENT_SECRET' );
	$refresh_token = kaligirl_secret( 'ZOHO_REFRESH_TOKEN' );

	if ( empty( $client_id ) || empty( $client_secret ) || empty( $refresh_token ) ) {
		kaligirl_security_log( 'zoho_config_missing', 'ZOHO_CLIENT_ID/SECRET/REFRESH_TOKEN not set' );
		return '';
	}

	$response = wp_remote_post( 'https://accounts.zoho.com/oauth/v2/token', array(
		'timeout' => 10,
		'body'    => array(
			'refresh_token' => $refresh_token,
			'client_id'     => $client_id,
			'client_secret' => $client_secret,
			'grant_type'    => 'refresh_token',
		),
	) );

	if ( is_wp_error( $response ) ) {
		kaligirl_security_log( 'zoho_oauth_error', $response->get_error_message() );
		return '';
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( empty( $body['access_token'] ) ) {
		kaligirl_security_log( 'zoho_oauth_error', 'no access_token in response' );
		return '';
	}

	// Cache for 50 minutes — under the ~60 minute expiry, with margin.
	set_transient( 'kaligirl_zoho_access_token', $body['access_token'], 50 * MINUTE_IN_SECONDS );

	return $body['access_token'];
}

/**
 * True if $token is a real, "used" record in the Zoho Creator token-gate
 * datastore. This is the only thing that gates /booking and /payment —
 * call at the very top of each template, before any output, and don't
 * render the Bookings/Billing embed unless this returns true.
 */
function kaligirl_validate_gate_token( $token ) {
	$token = is_string( $token ) ? trim( $token ) : '';
	if ( '' === $token ) {
		return false;
	}

	$access_token = kaligirl_zoho_access_token();
	if ( empty( $access_token ) ) {
		return false;
	}

	$report_url = sprintf(
		'https://creator.zoho.com/api/v2.1/%s/intake-token-gate/report/IntakeTokens_Report?criteria=(token=="%s")',
		rawurlencode( kaligirl_zoho_creator_account_owner() ),
		rawurlencode( $token )
	);

	$response = wp_remote_get( $report_url, array(
		'timeout' => 10,
		'headers' => array(
			'Authorization' => 'Zoho-oauthtoken ' . $access_token,
		),
	) );

	// A short, non-reversible fragment for log correlation without writing
	// the full single-use token into a plaintext log file.
	$token_fragment = substr( $token, 0, 8 ) . '…';

	if ( is_wp_error( $response ) ) {
		kaligirl_security_log( 'token_validation_error', $token_fragment . ' ' . $response->get_error_message() );
		return false;
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	$rows = isset( $body['data'] ) && is_array( $body['data'] ) ? $body['data'] : array();

	foreach ( $rows as $row ) {
		if ( isset( $row['status'] ) && 'used' === $row['status'] ) {
			return true;
		}
	}

	kaligirl_security_log( 'token_validation_failed', $token_fragment );
	return false;
}
