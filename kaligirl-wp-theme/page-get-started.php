<?php
/**
 * Template Name: Get Started
 *
 * Embeds the Moxo client portal exactly as specified in the migration
 * handoff — Moxo owns onboarding, e-signature, secure document exchange,
 * and chat from here on; none of that is reimplemented natively.
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
				<p class="lede">Fill out the form below to get onboarded — it's secure, private, and goes straight to us.</p>
			</div>
		</div>
	</section>

	<section>
		<div class="kg-container" style="padding-top:0;padding-bottom:clamp(2rem,5vh,3rem);">
			<div class="moxo-embed">
				<iframe src="https://app.moxo.com/embed/de789728-f434-4be6-9a47-218400bf7d8d" width="100%" height="600" frameborder="0"></iframe>
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
