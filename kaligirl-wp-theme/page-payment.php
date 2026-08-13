<?php
/**
 * Template Name: Payment
 *
 * Route One destination (Zoho Forms "Find the Right Plan"). Scaffolded per
 * the migration handoff — Zoho Billing's hosted payment page/plan isn't
 * created yet, so $kg_zoho_billing_page_id below is a placeholder. The
 * token-gate logic is identical to /booking (page-booking.php) and already
 * fully working; only the embed itself is a placeholder pending that page.
 *
 * Deliberately a plain hand-rolled iframe once a real page id exists — not
 * the Zoho Billing WordPress plugin, WooCommerce, or the Payments Checkout
 * Widget JS SDK, per the handoff (this theme avoids plugin dependencies).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$kg_token       = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
$kg_token_valid = kaligirl_validate_gate_token( $kg_token );

// Placeholder — replace once the Zoho Billing hosted payment page/plan exists.
$kg_zoho_billing_page_id = 'PLACEHOLDER-zoho-billing-page-id';

get_header();
?>
<main>
<?php if ( ! $kg_token_valid ) : ?>
	<section class="kg-section--no-border">
		<div class="kg-container resource-hero">
			<p class="kg-eyebrow" style="justify-content:center;">Get started</p>
			<h1>Let's start with an introduction.</h1>
			<p>This payment link isn't valid, or has already been used — head back to Get Started and find the right plan from there.</p>
			<p style="margin-top:2rem;"><a href="<?php echo esc_url( kaligirl_url( 'get-started' ) ); ?>" class="btn-pill">Back to Get Started</a></p>
		</div>
	</section>
<?php else : ?>
	<section class="kg-section--no-border hero-wash">
		<div class="kg-container hero hero--narrow">
			<div>
				<p class="kg-eyebrow" style="margin-bottom:1rem;">Get started</p>
				<h1>Set up your plan.</h1>
				<p class="lede">Complete payment below to get started.</p>
			</div>
		</div>
	</section>
	<section>
		<div class="kg-container" style="padding-top:0;padding-bottom:clamp(2rem,5vh,3rem);">
			<div class="embed-frame">
				<iframe src="https://checkout.zoho.com/embed/<?php echo esc_attr( $kg_zoho_billing_page_id ); ?>" width="100%" height="600" frameborder="0"></iframe>
			</div>
		</div>
	</section>
<?php endif; ?>
</main>
<?php get_footer(); ?>
