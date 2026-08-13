<?php
/**
 * Default fallback template, required by the WordPress theme spec.
 * This design has no blog/archive views — content pages use the
 * page-{slug}.php templates instead. This just prevents a hard error if
 * WordPress ever needs a template none of those match.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main>
	<section class="kg-section--no-border">
		<div class="kg-container" style="padding:clamp(4rem,10vh,6rem) 0;">
			<?php if ( have_posts() ) : ?>
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article <?php post_class(); ?>>
						<h1><?php the_title(); ?></h1>
						<div class="lede"><?php the_content(); ?></div>
					</article>
					<?php
				endwhile;
				?>
			<?php else : ?>
				<p class="lede"><?php esc_html_e( 'Nothing found.', 'kaligirl' ); ?></p>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php get_footer(); ?>
