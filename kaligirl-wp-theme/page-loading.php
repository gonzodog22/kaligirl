<?php
/**
 * Template Name: Loading
 *
 * Minimal, chrome-free transition page — deliberately does NOT call
 * get_header()/get_footer() or enqueue the theme's normal assets, so it's
 * as light and fast as possible. Point each Zoho Bookings service's
 * "redirect after booking" setting here instead of directly at
 * /get-started (same base URL swap, Zoho still appends its own customer_*
 * and service_uuid/service_name merge fields automatically either way —
 * no merge-tag reconfiguration needed).
 *
 * Purpose: when the booking widget embedded on /get-started completes and
 * Zoho's redirect starts landing on our domain, this page (not the much
 * heavier full /get-started page) is what's briefly visible nested inside
 * the small widget before js/main.js's kaligirlBreakoutIframeOnSameOrigin()
 * detects it and breaks out to the top level. A plain branded "Loading..."
 * message here reads as an intentional transition; the full page (header,
 * nav, hero, both Route Two forms) rendering in that same tiny nested
 * space read as broken.
 *
 * Reads the same params /get-started itself reads, and forwards to
 * /get-started with them intact after a short, deliberate pause — once
 * there, page-get-started.php's existing $kg_from_booking logic takes
 * over completely unchanged.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$kg_forward_params = array();
foreach ( array( 'customer_name', 'customer_first_name', 'customer_last_name', 'customer_contact_no', 'customer_email', 'service_uuid', 'service_name' ) as $kg_field ) {
	if ( ! empty( $_GET[ $kg_field ] ) ) {
		$kg_forward_params[ $kg_field ] = sanitize_text_field( wp_unslash( $_GET[ $kg_field ] ) );
	}
}

$kg_forward_url = kaligirl_url( 'get-started' );
if ( ! empty( $kg_forward_params ) ) {
	$kg_forward_url = add_query_arg( $kg_forward_params, $kg_forward_url );
}
$kg_forward_url = esc_url_raw( $kg_forward_url );
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php bloginfo( 'name' ); ?></title>
<style>
	html, body { height: 100%; margin: 0; }
	body {
		display: flex;
		align-items: center;
		justify-content: center;
		background: #f5ead9;
		font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
		color: #2b2b2b;
	}
	.kg-loading { text-align: center; }
	.kg-loading__spinner {
		width: 2.5rem;
		height: 2.5rem;
		margin: 0 auto 1.25rem;
		border: 3px solid #e6e9ef;
		border-top-color: #2f6b64;
		border-radius: 50%;
		animation: kg-spin 0.8s linear infinite;
	}
	.kg-loading p { font-size: 1.05rem; margin: 0; }
	@keyframes kg-spin { to { transform: rotate(360deg); } }
</style>
</head>
<body>
	<div class="kg-loading">
		<div class="kg-loading__spinner" aria-hidden="true"></div>
		<p>Just a moment &mdash; pulling up your details&hellip;</p>
	</div>
	<script>
	setTimeout( function () {
		window.location.replace( <?php echo wp_json_encode( $kg_forward_url ); ?> );
	}, 1500 );
	</script>
</body>
</html>
