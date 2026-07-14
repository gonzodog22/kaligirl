<?php
/**
 * Template Name: Get Started
 *
 * Custom intake form (replaces the earlier Moxo iframe embed). Public,
 * unauthenticated — collects contact info, segment, and intent only. Real
 * onboarding (documents, e-signature, secure file exchange) still happens
 * later, behind authentication, once a lead is qualified; this page is not
 * the place to collect SSN/DOB/account numbers/balances/income or take
 * file uploads.
 *
 * The intake form itself (fields, branching, validation, submit) lives in
 * js/main.js — see KALIGIRL_INTAKE_WEBHOOK_URL there for the one config
 * constant that needs a real value before launch.
 *
 * $kg_calendar_embed_url below is the other placeholder that needs a real
 * value before launch: a Google Calendar "Appointment schedule" page,
 * shared publicly and embedded here so either segment can book a real slot
 * (Personal or Business/CFO) without a second login or a third-party tool.
 * Create one at calendar.google.com/calendar/appointments, publish it, and
 * paste its public URL here.
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

// Placeholder — replace with a real, published Google Calendar Appointment
// Schedule URL before launch (calendar.google.com/calendar/appointments).
$kg_calendar_embed_url = 'https://calendar.google.com/calendar/appointments/schedules/PLACEHOLDER';
?>
<main>
	<section class="kg-section--no-border">
		<div class="kg-container hero hero--narrow">
			<div>
				<p class="kg-eyebrow" style="margin-bottom:1rem;">Get started</p>
				<h1>Book your introduction right here.</h1>
				<p class="lede">Tell us a bit about yourself below — it's secure, private, and goes straight to us.</p>
			</div>
		</div>
	</section>

	<section>
		<div class="kg-container" style="padding-top:0;padding-bottom:clamp(2rem,5vh,3rem);">
			<form id="kg-intake-form" class="intake-form" novalidate>
				<div class="intake-form__row">
					<div class="form-field">
						<label for="kg-first-name">First name</label>
						<input type="text" id="kg-first-name" name="firstName" autocomplete="given-name" required>
						<p class="form-field__error" id="kg-first-name-error" role="alert" hidden></p>
					</div>
					<div class="form-field">
						<label for="kg-last-name">Last name</label>
						<input type="text" id="kg-last-name" name="lastName" autocomplete="family-name" required>
						<p class="form-field__error" id="kg-last-name-error" role="alert" hidden></p>
					</div>
				</div>

				<div class="intake-form__row">
					<div class="form-field">
						<label for="kg-email">Email</label>
						<input type="email" id="kg-email" name="email" autocomplete="email" required>
						<p class="form-field__error" id="kg-email-error" role="alert" hidden></p>
					</div>
					<div class="form-field">
						<label for="kg-phone">Phone <span class="form-field__optional">(optional)</span></label>
						<input type="tel" id="kg-phone" name="phone" autocomplete="tel">
					</div>
				</div>

				<div class="form-field">
					<label for="kg-segment">What brings you here?</label>
					<select id="kg-segment" name="segment" required>
						<option value="">Select one&hellip;</option>
						<option value="personal">Personal financial planning</option>
						<option value="business">Business / CFO services</option>
					</select>
					<p class="form-field__error" id="kg-segment-error" role="alert" hidden></p>
				</div>

				<div class="intake-branch" data-kg-branch="personal" hidden>
					<div class="form-field">
						<label for="kg-personal-goal">What are you hoping to sort out?</label>
						<textarea id="kg-personal-goal" name="personalGoal" rows="4"></textarea>
						<p class="form-field__error" id="kg-personal-goal-error" role="alert" hidden></p>
					</div>
					<div class="form-field">
						<label for="kg-worked-with-advisor">Have you worked with an advisor before?</label>
						<select id="kg-worked-with-advisor" name="workedWithAdvisor">
							<option value="">Select one&hellip;</option>
							<option value="yes">Yes</option>
							<option value="no">No</option>
						</select>
						<p class="form-field__error" id="kg-worked-with-advisor-error" role="alert" hidden></p>
					</div>
					<div class="form-field">
						<label for="kg-preferred-contact">Preferred contact method</label>
						<select id="kg-preferred-contact" name="preferredContact">
							<option value="">Select one&hellip;</option>
							<option value="email">Email</option>
							<option value="phone">Phone</option>
							<option value="text">Text</option>
						</select>
						<p class="form-field__error" id="kg-preferred-contact-error" role="alert" hidden></p>
					</div>
				</div>

				<div class="intake-branch" data-kg-branch="business" hidden>
					<div class="form-field">
						<label for="kg-business-name">Business name</label>
						<input type="text" id="kg-business-name" name="businessName">
						<p class="form-field__error" id="kg-business-name-error" role="alert" hidden></p>
					</div>
					<div class="form-field">
						<label for="kg-business-what">What does the business do?</label>
						<input type="text" id="kg-business-what" name="businessWhat">
						<p class="form-field__error" id="kg-business-what-error" role="alert" hidden></p>
					</div>
					<div class="form-field">
						<label for="kg-accounting-system">Accounting system</label>
						<select id="kg-accounting-system" name="accountingSystem">
							<option value="">Select one&hellip;</option>
							<option value="quickbooks_online">QuickBooks Online</option>
							<option value="xero">Xero</option>
							<option value="other">Other</option>
							<option value="none_yet">None yet</option>
						</select>
						<p class="form-field__error" id="kg-accounting-system-error" role="alert" hidden></p>
					</div>
					<div class="form-field">
						<label for="kg-business-issue">Most pressing issue</label>
						<textarea id="kg-business-issue" name="businessIssue" rows="4"></textarea>
						<p class="form-field__error" id="kg-business-issue-error" role="alert" hidden></p>
					</div>
				</div>

				<div class="form-field">
					<label for="kg-referral">How did you hear about us? <span class="form-field__optional">(optional)</span></label>
					<input type="text" id="kg-referral" name="referral">
				</div>

				<?php kaligirl_honeypot_field( 'kg_hp_get_started' ); ?>

				<div class="form-field form-field--checkbox">
					<label for="kg-consent">
						<input type="checkbox" id="kg-consent" name="consent" required>
						<span>I consent to being contacted by Kali Girl Financial Services about the information provided above. <strong>[Placeholder consent/privacy language pending compliance review.]</strong></span>
					</label>
					<p class="form-field__error" id="kg-consent-error" role="alert" hidden></p>
				</div>

				<button type="submit" class="btn-pill" id="kg-intake-submit">Send introduction request</button>

				<div class="form-status" id="kg-intake-status" role="status" aria-live="polite" hidden></div>
			</form>
		</div>
	</section>

	<section class="kg-section">
		<div class="kg-container" style="padding:clamp(2.5rem,6vh,3.5rem) 0;">
			<p class="kg-eyebrow" style="margin-bottom:1rem;">Pick a time</p>
			<h2 style="font-family:var(--font-heading);font-weight:500;font-size:clamp(1.7rem,1.5rem+1vw,2.2rem);margin:0 0 1.25rem;">Prefer to grab a slot directly?</h2>
			<div class="calendar-embed">
				<iframe src="<?php echo esc_url( $kg_calendar_embed_url ); ?>" width="100%" height="600" frameborder="0"></iframe>
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
