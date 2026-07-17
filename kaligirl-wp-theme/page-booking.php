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
 * Autofill (name/email/phone on the Bookings widget) is NOT implemented
 * here — two attempts at guessing Zoho's undocumented query-param prefill
 * mechanism both broke the widget outright ("nothing found"), and their
 * help docs blocked every fetch attempt this session, so there was no way
 * to verify the correct approach rather than keep guessing against a live
 * page. Reverted to the plain, known-working embed URL. $_GET still reads
 * ?name=/?email=/?phone= off this page's own URL below (harmless, doesn't
 * touch the embed) so that data isn't lost once the real mechanism is
 * confirmed — see README "Get Started flow" for how to find it (Zoho
 * Bookings' own widget-customization screen in your dashboard is the
 * fastest path, since it's account-specific and not blocked the way their
 * public docs were).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$kg_token       = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
$kg_token_valid = kaligirl_validate_gate_token( $kg_token );

// Captured but not yet used (see file header) — kept so nothing needs to
// change on the Zoho Forms redirect-URL side once prefill is figured out.
$kg_prefill_name  = isset( $_GET['name'] ) ? sanitize_text_field( wp_unslash( $_GET['name'] ) ) : '';
$kg_prefill_email = isset( $_GET['email'] ) ? sanitize_email( wp_unslash( $_GET['email'] ) ) : '';
$kg_prefill_phone = isset( $_GET['phone'] ) ? sanitize_text_field( wp_unslash( $_GET['phone'] ) ) : '';

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
	    url: "https://kaligirlfinancialservices.zohobookings.com/portal-embed#/4946279000000039045",
	    parent: "#inline-container",
	    height: "600px"
	  });
	};
	</script>
<?php endif; ?>
</main>
<?php get_footer(); ?>
