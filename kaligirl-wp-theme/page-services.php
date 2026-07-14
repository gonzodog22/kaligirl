<?php
/**
 * Template Name: Services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$kg_principles = array(
	array(
		'n'     => '01',
		'title' => 'Financial planning',
		'body'  => 'A full picture of where you are and where you want to go — cash flow, savings, goals, and the trade-offs between them.',
	),
	array(
		'n'     => '02',
		'title' => 'Investment guidance',
		'body'  => 'Objective advice on how your assets are structured, without commissions or product sales shaping the recommendation.',
	),
	array(
		'n'     => '03',
		'title' => 'Decision support',
		'body'  => 'An independent second opinion for major financial choices, from someone whose only stake is your outcome.',
	),
	array(
		'n'     => '04',
		'title' => 'Fractional CFO services',
		'body'  => "For small businesses, hands-on financial leadership without a full-time hire — engaged on a case-by-case basis as your needs change.",
	),
);

$kg_pricing = array(
	array(
		'name'  => 'Entry',
		'desc'  => 'For individuals just starting to build a plan and get organized.',
		'price' => 'Price TBD',
	),
	array(
		'name'  => 'Grow',
		'desc'  => 'Ongoing planning and investment guidance as your finances get more complex.',
		'price' => 'Price TBD',
	),
	array(
		'name'  => 'Exceed',
		'desc'  => 'Tailored, comprehensive planning or fractional CFO services for small businesses.',
		'price' => 'Contact us',
	),
);
?>
<main>
	<section class="kg-section--no-border">
		<div class="kg-container hero hero--tight">
			<div>
				<p class="kg-eyebrow">Services</p>
				<h1>How we work with you.</h1>
				<p class="lede">Every engagement is grounded in the fiduciary standard: advice that serves your interests, with fees and methods disclosed up front — for individuals and for small businesses that need fractional CFO support.</p>
			</div>
		</div>
	</section>

	<section class="kg-section">
		<div class="kg-container section-pad--sm">
			<?php kaligirl_principle_list( $kg_principles ); ?>
		</div>
	</section>

	<section class="kg-section" style="background:radial-gradient(circle at 85% 15%,#f3f7fd,#f8f9fb 55%);">
		<div class="kg-container section-pad--sm">
			<div class="section-head">
				<p class="kg-eyebrow">Fee structure</p>
				<h2 style="font-size:clamp(1.9rem,1.6rem + 1.5vw,2.6rem);">Pricing built around where you are.</h2>
				<p>Exact fees are confirmed in writing before any engagement begins.</p>
			</div>
			<div class="card-grid card-grid--pricing">
				<?php foreach ( $kg_pricing as $t ) : ?>
					<div class="pricing-card">
						<h3><?php echo esc_html( $t['name'] ); ?></h3>
						<p class="desc"><?php echo esc_html( $t['desc'] ); ?></p>
						<p class="price"><?php echo esc_html( $t['price'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="kg-section">
		<div class="kg-container section-pad--sm">
			<div class="cta-band cta-band--sm">
				<h2>Not sure which fits?</h2>
				<p>Start with an introduction and we'll figure it out together.</p>
				<a href="<?php echo esc_url( kaligirl_url( 'get-started' ) ); ?>" class="btn-pill btn-pill--white btn-pill--sm">Book an introduction</a>
			</div>
		</div>
	</section>
</main>
<?php get_footer(); ?>
