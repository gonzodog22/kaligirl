<?php
/**
 * Template Name: Business Advisory
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
		'title'  => 'Fractional CFO',
		'body'   => 'Executive-level financial guidance without the cost of a full-time hire.',
		'detail' => 'A fractional CFO gives your business senior-level financial oversight — cash flow management, forecasting, reporting, and strategic decision support — without the overhead of a full-time executive hire. Engaged on the schedule your business actually needs, from a few hours a month to ongoing weekly involvement.',
	),
	array(
		'title'  => 'Fractional COO',
		'body'   => 'Hands-on operational leadership to keep the business running smoothly as it grows.',
		'detail' => "A fractional COO steps in to run day-to-day operations, coordinate teams, and remove the bottlenecks that come with growth. This means tighter execution, clearer accountability, and a business that runs well whether or not you're in the room.",
	),
	array(
		'title'  => 'Financial Strategy',
		'body'   => 'Cash flow, reporting, and a financial structure built for where the business is headed.',
		'detail' => "Beyond bookkeeping, financial strategy means building a structure — cash flow discipline, reporting rhythms, and forecasting — that matches where your business is going, not just where it's been. It gives you the visibility to make confident decisions.",
	),
	array(
		'title'  => 'Organizational Design',
		'body'   => 'Structuring roles, teams, and reporting lines so the business scales without friction.',
		'detail' => 'As businesses grow, informal structures start to break down. Organizational design means clarifying roles, reporting lines, and decision rights so the business can add people and complexity without losing speed or accountability.',
	),
	array(
		'title'  => 'Operations Design',
		'body'   => 'Streamlined processes and systems that reduce bottlenecks and manual work.',
		'detail' => 'Operations design looks at how work actually flows through your business and removes the friction — manual handoffs, unclear ownership, redundant steps — replacing it with systems and processes that scale cleanly.',
	),
	array(
		'title'  => 'Customer Experience',
		'body'   => "Aligning the customer journey with the business's financial and operational goals.",
		'detail' => "A great customer experience should reinforce your financial and operational goals, not work against them. We look at the full customer journey and align it with how your business actually runs, so growth doesn't come at the cost of service quality.",
	),
);
?>
<main>
	<section class="kg-section--no-border hero-wash">
		<div class="kg-container hero hero--tight">
			<div>
				<p class="kg-eyebrow">Services &middot; Business</p>
				<h1>Business Advisory</h1>
				<p class="lede" style="max-width:52ch;">Strengthen your financial foundation, operating model, organization, and customer experience.</p>
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
				<a href="<?php echo esc_url( kaligirl_url( 'get-started' ) ); ?>" class="btn-pill" style="margin-top:2.5rem;display:inline-block;">Schedule a Consultation</a>
			</div>

			<?php foreach ( $kg_cards as $i => $c ) : ?>
				<div class="consulting-detail" data-kg-consulting-detail="<?php echo esc_attr( $i ); ?>" hidden>
					<button type="button" class="consulting-back" data-kg-consulting-back>&larr; Back to all</button>
					<div class="consulting-detail__card">
						<div class="consulting-detail__media"><?php echo esc_html( $c['title'] ); ?> image</div>
						<div class="consulting-detail__body">
							<h2><?php echo esc_html( $c['title'] ); ?></h2>
							<p><?php echo esc_html( $c['detail'] ); ?></p>
							<a href="<?php echo esc_url( kaligirl_url( 'get-started' ) ); ?>" class="btn-pill">Schedule a Consultation</a>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
</main>
<?php get_footer(); ?>
