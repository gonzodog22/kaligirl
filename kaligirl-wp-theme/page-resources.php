<?php
/**
 * Template Name: Resources
 *
 * Public resources page (Articles/Videos/Calculators, linked from the
 * Resources mega menu). Content is a "coming soon" placeholder per the
 * design handoff — not to be confused with the logged-in-only
 * Library/Lessons/Tools dropdown, a separate, pre-existing feature.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main>
	<section class="kg-section--no-border hero-wash">
		<div class="kg-container hero hero--tight">
			<div>
				<p class="kg-eyebrow">Resources</p>
				<h1>Free articles &amp; videos.</h1>
				<p class="lede" style="max-width:48ch;">Practical, no-pressure financial guidance — open to everyone, client or not.</p>
			</div>
		</div>
	</section>

	<section class="kg-section">
		<div class="kg-container" style="padding:clamp(4rem,10vh,5rem) 0 clamp(5rem,12vh,6rem);text-align:center;">
			<p class="lede" style="margin:0 auto;">Content is coming soon — check back for articles and videos.</p>
		</div>
	</section>
</main>
<?php get_footer(); ?>
