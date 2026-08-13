<?php
/**
 * Template Name: About
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
				<p class="kg-eyebrow">About</p>
				<h1 style="max-width:22ch;">Independent by design, fiduciary by standard.</h1>
				<p class="lede" style="max-width:52ch;">Kaligirl Financial Services was founded on a simple premise: advice should serve the client, not a product line. [Founder bio and firm background — replace with approved copy.]</p>
			</div>
		</div>
	</section>

	<section class="kg-section">
		<div class="kg-container section-pad--sm">
			<div class="card-grid">
				<div class="info-card">
					<h3>Fiduciary-minded</h3>
					<p>Built to put your interests first, with a fiduciary certification underway.</p>
				</div>
				<div class="info-card">
					<h3>Fee-only</h3>
					<p>No commissions, no product sales — compensation comes only from the clients we serve.</p>
				</div>
				<div class="info-card">
					<h3>Based in Morgan Hill</h3>
					<p>Serving clients across California, in person and remotely.</p>
				</div>
			</div>
			<div class="integrations">
				<span class="integrations__label">Connects with:</span>
				<span class="integration-badge">QuickBooks</span>
				<span class="integration-badge">Xero</span>
				<span class="integration-badge integration-badge--soon">Plaid <span class="tag">(coming soon)</span></span>
			</div>
		</div>
	</section>
</main>
<?php get_footer(); ?>
