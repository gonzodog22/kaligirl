<?php
/**
 * Site footer: logo, link list, and the compliance disclosure paragraph.
 *
 * The disclosure text below is final short-form copy as provided in the
 * design handoff (see README "Compliance placeholders") — reproduce
 * exactly; do not edit without compliance/counsel sign-off. The Form ADV /
 * Disclosures link is a stub pending a real destination.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	<footer class="site-footer">
		<div class="kg-container site-footer__inner">
			<div class="site-footer__top">
				<a href="<?php echo esc_url( kaligirl_url( 'home' ) ); ?>" class="site-footer__logo">
					<img src="<?php echo esc_url( KALIGIRL_URI . '/assets/kaligirl-logo.png' ); ?>" alt="<?php bloginfo( 'name' ); ?>">
				</a>
				<ul class="site-footer__links">
					<li><a href="<?php echo esc_url( kaligirl_url( 'services' ) ); ?>">Services</a></li>
					<li><a href="<?php echo esc_url( kaligirl_url( 'about' ) ); ?>">About</a></li>
					<li><a href="<?php echo esc_url( kaligirl_url( 'contact' ) ); ?>">Contact</a></li>
					<li><a href="#" data-kg-adv-stub>Form ADV / Disclosures</a></li>
				</ul>
			</div>
			<p class="site-footer__disclosure">
				<strong>Disclosure:</strong>
				Kaligirl Financial Services is a general S corporation and is not currently a registered investment advisory firm. Fiduciary advisory services are not yet being offered. This website is for informational purposes only and does not constitute investment, tax, or legal advice.
			</p>
		</div>
	</footer>

<?php wp_footer(); ?>
</body>
</html>
