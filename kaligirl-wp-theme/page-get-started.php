<?php
/**
 * Template Name: Get Started
 *
 * Fork page per the Get Started migration handoff (supersedes the earlier
 * Moxo iframe entirely — do not reintroduce it). A Personal/Business
 * sliding toggle controls which of two buttons are visible; each reveals
 * an already-built Zoho Forms embed (not custom) on this same page, gated
 * by a one-time token generated client-side (js/main.js) and appended to
 * the iframe's src.
 *
 * - "Schedule an Introductory Consultation" (Route Two) — always visible,
 *   both toggle states — submits to /booking?token=... on completion.
 * - "Find the Right Plan" (Route One) — visible only in "Personal
 *   Advising" mode — submits to /payment?token=... on completion.
 *
 * The actual security boundary is NOT this page or the token generator —
 * it's the server-side check in /booking and /payment (page-booking.php,
 * page-payment.php via inc/zoho.php), which requires a matching token
 * record in Zoho Creator with status "used". That status only gets set by
 * a real Zoho Forms submission (on Zoho's side), so guessing or
 * hand-crafting a token client-side doesn't get anyone in — there's
 * nothing for an attacker to reach without a genuine submission having
 * already happened. This page's job is only to generate the token and get
 * it into the iframe URL and the fallback "Continue" link.
 *
 * Zoho's own "Redirect URL on Submission" navigates *inside the form's own
 * iframe* — left alone, that would render the full /booking or /payment
 * page (header, footer, and all) nested inside this small form iframe
 * instead of taking over the tab. js/main.js watches for the iframe
 * landing on our own domain (same-origin access starts working at that
 * exact moment, having thrown until then) and forces a real top-level
 * navigation to that same URL — see the iframe `load` listener in
 * loadRoute().
 *
 * TWO THINGS NEED MANUAL CONFIRMATION (see README + PR notes):
 * 1. Whether each Zoho Form's own "Redirect URL on Submission" setting can
 *    carry `gated_token` forward dynamically to /payment or /booking. If
 *    yes, prefer configuring that in Zoho Forms directly — this page's
 *    "Continue" links below are the fallback for if it can't.
 * 2. The literal Zoho referrer-tracking `<script>` that ships with each
 *    embed code isn't reproduced here (not available at build time) — see
 *    the HTML comments marking exactly where to paste it, unmodified.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$kg_steps = array(
	array(
		'n'     => '01',
		'title' => 'Reach out',
		'body'  => "Tell us a little about your situation and what you're hoping to sort out.",
	),
	array(
		'n'     => '02',
		'title' => 'Introductory meeting',
		'body'  => "A no-obligation conversation to understand your goals and confirm we're a good fit.",
	),
	array(
		'n'     => '03',
		'title' => 'Secure onboarding',
		'body'  => 'Agreements and documents are exchanged and tracked through your private client portal.',
	),
);
?>
<main>
	<section class="kg-section--no-border">
		<div class="kg-container hero hero--narrow">
			<div>
				<p class="kg-eyebrow" style="margin-bottom:1rem;">Get started</p>
				<h1>Book your introduction right here.</h1>
				<p class="lede">Tell us which kind of advising you're looking for, and we'll point you the right way.</p>
			</div>
		</div>
	</section>

	<section>
		<div class="kg-container" style="padding-top:0;padding-bottom:clamp(2rem,5vh,3rem);">

			<div data-kg-view="fork">
				<div class="advising-toggle" role="radiogroup" aria-label="Type of advising">
					<input type="radio" id="kg-mode-personal" name="kg-advising-mode" value="personal" checked>
					<label for="kg-mode-personal">Personal Advising</label>
					<input type="radio" id="kg-mode-business" name="kg-advising-mode" value="business">
					<label for="kg-mode-business">Business Advising</label>
					<span class="advising-toggle__thumb" aria-hidden="true"></span>
				</div>

				<div class="route-buttons">
					<button type="button" class="btn-pill" data-kg-route-button="two">Schedule an Introductory Consultation</button>
					<button type="button" class="btn-outline" data-kg-route-button="one" data-kg-personal-only>Find the Right Plan</button>
				</div>
			</div>

			<div data-kg-view="route-one" hidden>
				<button type="button" class="back-link" data-kg-back-to-fork>&larr; Back</button>
				<h2 class="route-view__title">Find the Right Plan</h2>
				<p class="route-view__lede">Fill this out and we'll follow up with a plan that fits.</p>
				<div class="embed-frame">
					<iframe id="ziframe_296885" aria-label="Let's review your finances together" frameborder="0" style="height:500px;width:99%;border:none;" data-kg-form-src="https://forms.zohopublic.com/ryankaligirlfina1/form/Letsreviewyourfinancestogether/formperma/LKaR5Cbaj_hr11k288Ew9580SoVc_aBzz1IY0RzZiDs"></iframe>
				</div>
				<!--
					TODO: paste the standard Zoho referrer-tracking <script> block
					that ships with this form's embed code here, unmodified.
				-->
				<p class="route-view__continue">Already submitted the form above? <a href="#" data-kg-continue-link data-kg-destination="/payment">Continue &rarr;</a></p>
			</div>

			<div data-kg-view="route-two" hidden>
				<button type="button" class="back-link" data-kg-back-to-fork>&larr; Back</button>
				<h2 class="route-view__title">Schedule an Introductory Consultation</h2>
				<p class="route-view__lede">A no-obligation conversation to see if we're a good fit.</p>
				<div class="embed-frame">
					<iframe id="ziframe_314925" aria-label="Let's review your finances together" frameborder="0" style="height:500px;width:99%;border:none;" data-kg-form-src="https://forms.zohopublic.com/ryankaligirlfina1/form/GetStarted/formperma/IA70KZBAwznXrIPD3dBzx_Y26wUDWEnX78U0u80ivMQ"></iframe>
				</div>
				<!--
					TODO: paste the standard Zoho referrer-tracking <script> block
					that ships with this form's embed code here, unmodified.
				-->
				<p class="route-view__continue">Already submitted the form above? <a href="#" data-kg-continue-link data-kg-destination="/booking">Continue &rarr;</a></p>
			</div>

		</div>
	</section>

	<section class="kg-section">
		<div class="kg-container" style="padding:clamp(2.5rem,6vh,3.5rem) 0;">
			<?php kaligirl_principle_list( $kg_steps ); ?>
		</div>
	</section>
</main>
<?php get_footer(); ?>
