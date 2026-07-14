<?php
/**
 * Home page content. Copy and structure match the design handoff exactly.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$kg_principles = array(
	array(
		'n'     => '01',
		'title' => 'Advice, not products',
		'body'  => "We don't earn commissions for selling you anything. Our guidance is the product, so our incentives stay pointed in the same direction as yours.",
	),
	array(
		'n'     => '02',
		'title' => 'Full transparency',
		'body'  => 'Fees, methods, and any conflicts of interest are laid out plainly and documented — before you decide to work with us.',
	),
	array(
		'n'     => '03',
		'title' => 'Built around your life',
		'body'  => 'Every plan starts with your circumstances and goals, not an off-the-shelf template.',
	),
);

$kg_services = array(
	array(
		'title' => 'Financial planning',
		'body'  => 'A coordinated look at your income, savings, and goals, turned into a plan you can actually follow.',
	),
	array(
		'title' => 'Investment guidance',
		'body'  => 'Objective, fiduciary advice on how your assets are structured and managed over time.',
	),
	array(
		'title' => 'Financial decisions',
		'body'  => 'A steady second opinion when life brings a big choice — with no agenda behind it.',
	),
	array(
		'title' => 'Fractional CFO',
		'body'  => 'Hands-on financial leadership for small businesses, engaged on a case-by-case basis as you need it.',
	),
);
?>
<main>
	<section class="kg-section--no-border">
		<div class="kg-container hero">
			<div>
				<p class="kg-eyebrow"><span class="kg-eyebrow__rule"></span>Financial services &amp; fractional CFO · California</p>
				<h1>Financial advice that answers to you, and only you.</h1>
				<p class="lede">Independent, fee-based advice held to a fiduciary standard — no product sales, no hidden incentives.</p>
				<div class="btn-row">
					<a href="<?php echo esc_url( kaligirl_url( 'get-started' ) ); ?>" class="btn-pill btn-pill--sm">Book an introduction</a>
					<a href="<?php echo esc_url( kaligirl_url( 'services' ) ); ?>" class="btn-outline">See how we work</a>
				</div>
			</div>
			<div class="hero-media">
				<span>advisor portrait</span>
			</div>
		</div>
	</section>

	<section class="kg-section">
		<div class="kg-container section-pad">
			<div class="section-head">
				<p class="kg-eyebrow">The standard we hold</p>
				<h2>A fiduciary is legally bound to put your interests first.</h2>
				<p>Not every financial professional is. Here's what our obligation looks like in practice.</p>
			</div>
			<?php kaligirl_principle_list( $kg_principles ); ?>
		</div>
	</section>

	<section class="kg-section" style="background:linear-gradient(180deg,#eef4fc,#f8f9fb);">
		<div class="kg-container section-pad">
			<div class="section-head section-head--split" style="max-width:none;">
				<div class="section-head" style="margin-bottom:0;">
					<p class="kg-eyebrow">How we help</p>
					<h2 style="margin:0;">Guidance for the decisions that matter most.</h2>
				</div>
				<div class="mini-bars"><span></span><span></span><span></span></div>
			</div>
			<div class="card-grid">
				<?php foreach ( $kg_services as $s ) : ?>
					<div class="service-card">
						<div class="service-card__icon">
							<div class="service-card__icon-bars"><span></span><span></span><span></span></div>
						</div>
						<h3><?php echo esc_html( $s['title'] ); ?></h3>
						<p><?php echo esc_html( $s['body'] ); ?></p>
						<div class="service-card__bar"></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="kg-section">
		<div class="kg-container section-pad">
			<div class="cta-band">
				<p class="kg-eyebrow">Pre-launch · Now taking introductions</p>
				<h2>Let's start with a conversation.</h2>
				<p>The first meeting is a chance to understand your situation and see whether we're the right fit — no obligation, no pressure.</p>
				<a href="<?php echo esc_url( kaligirl_url( 'get-started' ) ); ?>" class="btn-pill btn-pill--white btn-pill--sm">Book an introduction</a>
			</div>
		</div>
	</section>
</main>
