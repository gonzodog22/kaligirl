<?php
/**
 * Template Name: Account
 *
 * MemberPress-gated. Protect this page's URL with a MemberPress Rule in
 * wp-admin (primary control) — the kaligirl_require_login() call below is
 * the template-level defense-in-depth backup the migration spec calls for,
 * in case a rule is missing or misconfigured.
 *
 * Documents/Messages/Plan below are placeholders for the real
 * MemberPress/Moxo-linked dashboard content, per the design handoff.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

kaligirl_require_login();

get_header();
$kg_user = wp_get_current_user();
?>
<main>
	<section class="kg-section--no-border account-hero">
		<div class="kg-container hero hero--tight">
			<div>
				<p class="kg-eyebrow">Client account</p>
				<h1 style="font-size:clamp(2rem,1.7rem + 2vw,2.8rem);">Welcome back<?php echo $kg_user->display_name ? ', ' . esc_html( $kg_user->display_name ) : ''; ?>.</h1>
				<p class="lede" style="font-size:1.05rem;">Your documents, messages, and plan live in the client portal. [This panel becomes your MemberPress account dashboard once on WordPress.]</p>
				<a href="<?php echo esc_url( kaligirl_logout_url() ); ?>" class="btn-outline">Log out</a>
			</div>
		</div>
	</section>

	<section class="kg-section">
		<div class="kg-container section-pad--sm">
			<div class="card-grid">
				<div class="info-card">
					<h3>Documents</h3>
					<p>Secure files shared between you and your advisor.</p>
				</div>
				<div class="info-card">
					<h3>Messages</h3>
					<p>Direct line to your advisor between meetings.</p>
				</div>
				<div class="info-card">
					<h3>Plan</h3>
					<p>Your current financial plan and next steps.</p>
				</div>
			</div>
		</div>
	</section>
</main>
<?php get_footer(); ?>
