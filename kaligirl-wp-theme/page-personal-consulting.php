<?php
/**
 * Template Name: Personal Financial Consulting
 *
 * Grid of consulting topics that expands in place into a single detail
 * card on click (no page navigation) — plain vanilla JS state toggle, see
 * kaligirlInitConsultingCards() in js/main.js.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$kg_cards = array(
	array(
		'title'  => 'Financial Planning',
		'body'   => 'A coordinated look at your income, savings, and goals, turned into a plan you can actually follow.',
		'detail' => "Financial planning starts with a clear-eyed look at your income, savings, debt, and goals — then turns that into a plan with concrete, sequenced steps. No generic templates: the plan is built around your actual numbers and what you're trying to achieve.",
	),
	array(
		'title'  => 'Financial Strategy',
		'body'   => 'Longer-term positioning for your assets and goals, adjusted as your life changes.',
		'detail' => "Financial strategy is the longer view — how your assets, income, and goals fit together over years, not months. As your circumstances shift, the strategy adjusts with you, so decisions today stay aligned with where you're headed.",
	),
	array(
		'title'  => 'Financial Coaching',
		'body'   => 'Ongoing, judgment-free guidance to build better financial habits over time.',
		'detail' => 'Coaching is about building habits, not just making a one-time plan. Regular, judgment-free check-ins help you stay accountable to your goals, adjust as life happens, and build genuine financial confidence over time.',
	),
	array(
		'title'  => 'Life Transitions',
		'body'   => 'Support through major changes — career shifts, marriage, divorce, inheritance, retirement, and more.',
		'detail' => 'Major life changes — a new job, marriage, divorce, an inheritance, retirement — all carry financial decisions that are easy to get wrong under stress. We help you navigate the financial side of these transitions clearly and without pressure.',
	),
);
?>
<main>
	<section class="kg-section--no-border hero-wash">
		<div class="kg-container hero hero--tight">
			<div>
				<p class="kg-eyebrow">Services &middot; Personal</p>
				<h1>Personal Financial Consulting</h1>
				<p class="lede" style="max-width:48ch;">Planning, investment guidance, and everyday financial decisions — built around your life, not a product line.</p>
			</div>
		</div>
	</section>

	<section class="kg-section">
		<div class="kg-container section-pad--sm">
			<div data-kg-consulting="grid">
				<div class="consulting-grid">
					<?php foreach ( $kg_cards as $i => $c ) : ?>
						<div class="consulting-card" data-kg-consulting-card="<?php echo esc_attr( $i ); ?>" role="button" tabindex="0">
							<div class="consulting-card__media"><?php echo esc_html( $c['title'] ); ?> image</div>
							<div class="consulting-card__body">
								<h3><?php echo esc_html( $c['title'] ); ?></h3>
								<p><?php echo esc_html( $c['body'] ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<a href="<?php echo esc_url( kaligirl_url( 'get-started' ) ); ?>" class="btn-pill" style="margin-top:2.5rem;display:inline-block;">Find the Right Plan</a>
			</div>

			<?php foreach ( $kg_cards as $i => $c ) : ?>
				<div class="consulting-detail" data-kg-consulting-detail="<?php echo esc_attr( $i ); ?>" hidden>
					<button type="button" class="consulting-back" data-kg-consulting-back>&larr; Back to all</button>
					<div class="consulting-detail__card">
						<div class="consulting-detail__media"><?php echo esc_html( $c['title'] ); ?> image</div>
						<div class="consulting-detail__body">
							<h2><?php echo esc_html( $c['title'] ); ?></h2>
							<p><?php echo esc_html( $c['detail'] ); ?></p>
							<a href="<?php echo esc_url( kaligirl_url( 'get-started' ) ); ?>" class="btn-pill">Find the Right Plan</a>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
</main>
<?php get_footer(); ?>
