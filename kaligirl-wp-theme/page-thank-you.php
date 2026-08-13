<?php
/**
 * Template Name: Thank You
 *
 * Post-booking confirmation page. Zoho Bookings' own "post-booking
 * redirect" setting (configured in Zoho Bookings itself, not this repo)
 * should point here once a booking completes — see the note in
 * page-booking.php. No gating logic here: by the time anyone reaches this
 * URL they've either just booked for real, or found the URL directly,
 * and showing this message either way has no meaningful downside.
 *
 * Assign this template to a page at the URL /thank-you/, then set that
 * URL as Zoho Bookings' post-booking redirect (Zoho Bookings > Settings >
 * that service's booking page > redirect after booking).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main>
	<section class="kg-section--no-border">
		<div class="kg-container resource-hero">
			<p class="kg-eyebrow" style="justify-content:center;">Get started</p>
			<h1>Thank you for booking!</h1>
			<p>Check your email for your meeting link and confirmation.</p>
			<p style="margin-top:2rem;"><a href="<?php echo esc_url( kaligirl_url( 'home' ) ); ?>" class="btn-pill">Back to home</a></p>
		</div>
	</section>
</main>
<?php get_footer(); ?>
