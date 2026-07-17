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
 * Autofill: expects ?name=, ?email=, ?phone= alongside ?token= on the
 * redirect URL (configured in each Zoho Form's "Redirect URL on
 * Submission" setting via its own field-merge picker — same mechanism as
 * the token itself). Keys must be exactly these, lowercase, no spaces —
 * $_GET keys are case-sensitive. Appended onto the Zoho Bookings widget's
 * own URL as query params (Zoho Bookings' public booking pages are
 * documented elsewhere to accept name/email/phone this way) — NOT
 * confirmed against Zoho's own docs this session (their help pages
 * blocked every fetch attempt), so verify it actually prefills once
 * tested and adjust the param names below if it doesn't.
 *
 * The "Continue" fallback link (page-get-started.php, for when Zoho's
 * automatic redirect isn't configured) can only ever carry the token —
 * our JS never sees inside the cross-origin Zoho Forms iframe, so it has
 * no way to know the name/email/phone that was typed into it. That path
 * will land on this page correctly gated, just without autofill.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$kg_token       = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
$kg_token_valid = kaligirl_validate_gate_token( $kg_token );

// Prefill data, all optional — only ever present when the visitor arrived
// via Zoho's automatic redirect (the "Continue" fallback link can't supply
// these; see file header).
$kg_prefill = array();
if ( ! empty( $_GET['name'] ) ) {
	$kg_prefill['name'] = sanitize_text_field( wp_unslash( $_GET['name'] ) );
}
if ( ! empty( $_GET['email'] ) ) {
	$kg_prefill['email'] = sanitize_email( wp_unslash( $_GET['email'] ) );
}
if ( ! empty( $_GET['phone'] ) ) {
	$kg_prefill['phone'] = sanitize_text_field( wp_unslash( $_GET['phone'] ) );
}

// Query string must precede the #/ hash fragment, not follow it — the
// fragment is the booking page ID Zoho's own router looks up, and
// appending ?params after it (as an earlier version of this file did)
// glues them onto the ID itself, so Zoho searches for a page literally
// named "4946279000000039045?name=...&email=..." and finds nothing.
$kg_booking_url = 'https://kaligirlfinancialservices.zohobookings.com/portal-embed';
if ( ! empty( $kg_prefill ) ) {
	$kg_booking_url .= '?' . http_build_query( $kg_prefill );
}
$kg_booking_url .= '#/4946279000000039045';

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
	    url: "<?php echo esc_js( $kg_booking_url ); ?>",
	    parent: "#inline-container",
	    height: "600px"
	  });
	};
	</script>
<?php endif; ?>
</main>
<?php get_footer(); ?>
