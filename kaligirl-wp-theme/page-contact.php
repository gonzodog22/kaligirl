<?php
/**
 * Template Name: Contact
 *
 * Note: the design has no native contact form on this page — "Contact"
 * routes visitors to Get Started's Moxo-embedded intake, which is where
 * actual submissions happen (and where Moxo's own security/CAPTCHA
 * applies). If a native contact form is ever added here, run its fields
 * through kaligirl_sanitize_input(), print kaligirl_honeypot_field(), and
 * verify kaligirl_verify_recaptcha() before processing — see inc/security.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main>
	<section class="kg-section--no-border">
		<div class="kg-container hero hero--tight">
			<div>
				<p class="kg-eyebrow">Contact</p>
				<h1>Get in touch.</h1>
				<p class="lede" style="max-width:48ch;">Based in Morgan Hill, California, and working with clients across the state. Reach out and we'll follow up personally.</p>
			</div>
		</div>
	</section>

	<section class="kg-section">
		<div class="kg-container section-pad--sm">
			<div class="card-grid" style="margin-bottom:2.5rem;">
				<div class="info-card">
					<h3>Email</h3>
					<p><a href="mailto:hello@kaligirlfinancialservices.com">hello@kaligirlfinancialservices.com</a></p>
				</div>
				<div class="info-card">
					<h3>Location</h3>
					<p>Morgan Hill, CA<br>By appointment</p>
				</div>
				<div class="info-card">
					<h3>Hours</h3>
					<p>Mon–Fri, 9 AM – 5 PM PT</p>
				</div>
			</div>
			<div class="contact-closing">
				<div>
					<h2>Want to get in touch directly?</h2>
					<a href="<?php echo esc_url( kaligirl_url( 'get-started' ) ); ?>" class="btn-pill btn-pill--sm">Start with an introduction</a>
				</div>
				<div class="hero-media hero-media--wide">
					<span>map / office location</span>
				</div>
			</div>
		</div>
	</section>
</main>
<?php get_footer(); ?>
