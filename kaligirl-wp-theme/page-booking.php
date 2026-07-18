<?php
/**
 * Template Name: Booking
 *
 * Route Two destination (Zoho Forms "Schedule an Introductory
 * Consultation"). Public URL, but gated: validates ?token= server-side
 * against Zoho Creator's intake-token-gate datastore (inc/zoho.php)
 * *before* rendering anything — nobody reaches the real Zoho Bookings
 * embed by guessing this URL, only by completing that form first.
 *
 * Needs a real Zoho Bookings "post-booking redirect" setting pointed at a
 * /thank-you page on this domain, configured in Zoho Bookings itself —
 * confirm this is actually set during testing, don't assume it already is.
 *
 * That redirect (if the widget uses an iframe at all — unconfirmed; the
 * embed's own name, "inlineEmbed," suggests it may render same-origin DOM
 * content instead) would otherwise fire *inside whatever iframe the
 * Bookings widget creates* rather than the top-level page, so /thank-you
 * would render nested inside this small embed. js/main.js runs two
 * mechanisms in parallel to cover either case:
 *   1. kaligirlWatchEmbedContainerForIframes() — watches #inline-container
 *      for any iframe the widget injects and breaks out on same-origin
 *      load, same trick as the Get Started forms.
 *   2. kaligirlListenForBookingComplete() — listens for a
 *      window.postMessage() from Zoho's domain (the standard way an
 *      embedded widget notifies its host page of an event, whether or not
 *      it uses an iframe) and redirects on a best-effort match. Also logs
 *      every such message to the console — if this is still nested after
 *      a real test, check the console output from that test to see the
 *      actual message shape and tighten the match condition.
 *
 * Autofill (Name/Email/Phone on the Bookings widget): reads ?Name=,
 * ?Email=, ?Phone= off this page's own URL (same redirect URL Zoho Forms
 * already sends people to, alongside ?token=) and appends them onto the
 * Zoho Bookings widget's own URL as a query string placed AFTER the
 * #/{booking id} hash fragment — confirmed by testing that placing it
 * before the hash does NOT prefill the widget, so don't move it there
 * again. Zoho Bookings maps Phone to a field literally named
 * "Contact Number" (with a space) — kept as-is since that's the field
 * name that pre-fills correctly.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$kg_token       = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
$kg_token_valid = kaligirl_validate_gate_token( $kg_token );

$kg_prefill = array();

if ( ! empty( $_GET['Name'] ) ) {
	$kg_prefill['Name'] = sanitize_text_field(
		wp_unslash( $_GET['Name'] )
	);
}

if ( ! empty( $_GET['Email'] ) ) {
	$kg_email = sanitize_email(
		wp_unslash( $_GET['Email'] )
	);

	if ( is_email( $kg_email ) ) {
		$kg_prefill['Email'] = $kg_email;
	}
}

if ( ! empty( $_GET['Phone'] ) ) {
	$kg_prefill['Contact Number'] = sanitize_text_field(
		wp_unslash( $_GET['Phone'] )
	);
}

$kg_booking_url = 'https://kaligirlfinancialservices.zohobookings.com/portal-embed#/4946279000000039045';

if ( ! empty( $kg_prefill ) ) {
	$kg_booking_query = http_build_query(
		$kg_prefill,
		'',
		'&',
		PHP_QUERY_RFC3986
	);

	$kg_booking_query = str_replace(
		array( '%40', '%20' ),
		array( '@', '%20' ),
		$kg_booking_query
	);

	$kg_booking_url .= '?' . $kg_booking_query;
}

get_header();
?>
<main>
<?php if ( ! $kg_token_valid ) : ?>
	<section class="kg-section--no-border">
		<div class="kg-container resource-hero">
			<p class="kg-eyebrow" style="justify-content:center;">Get started</p>
			<h1>Let's start with an introduction.</h1>
			<p>This booking link isn't valid, or has already been used — head back to Get Started and book your introductory consultation from there.</p>
			<p style="margin-top:2rem;"><a href="<?php echo esc_url( kaligirl_url( 'get-started' ) ); ?>" class="btn-pill">Back to Get Started</a></p>
		</div>
	</section>
<?php else : ?>
	<section class="kg-section--no-border">
		<div class="kg-container hero hero--narrow">
			<div>
				<p class="kg-eyebrow" style="margin-bottom:1rem;">Get started</p>
				<h1>Book your introductory consultation.</h1>
				<p class="lede">Pick whatever time works best — you'll get a confirmation by email.</p>
			</div>
		</div>
	</section>
	<section>
		<div class="kg-container" style="padding-top:0;padding-bottom:clamp(2rem,5vh,3rem);">
			<div class="embed-frame">
				<div id="inline-container"></div>
			</div>
		</div>
	</section>
	<script src="https://bookings.nimbuspop.com/assets/embed.js"></script>
	<script>
	window.onload = function() {
	  Bookings.inlineEmbed({
	    url: <?php echo wp_json_encode( $kg_booking_url ); ?>,
	    parent: "#inline-container",
	    height: "600px"
	  });
	};
	</script>
<?php endif; ?>
</main>
<?php get_footer(); ?>
