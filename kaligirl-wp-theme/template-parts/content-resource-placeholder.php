<?php
/**
 * Shared markup for the Library/Lessons/Tools placeholder pages.
 * Expects $kg_resource_label to be set by the including template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<main>
	<section class="kg-section--no-border">
		<div class="kg-container resource-hero">
			<p class="kg-eyebrow" style="justify-content:center;">Client resources</p>
			<h1><?php echo esc_html( $kg_resource_label ); ?></h1>
			<p>This area is coming soon. Once live, it'll live here for logged-in clients.</p>
		</div>
	</section>
</main>
