<?php
/**
 * Fallback template for any page that isn't one of the theme's named
 * templates (page-home.php, page-services.php, etc). Renders the page's
 * own title/content through the standard WP editor so an admin can add
 * ad-hoc pages without needing a bespoke template for each one.
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
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<h1><?php the_title(); ?></h1>
					<div class="lede"><?php the_content(); ?></div>
					<?php
				endwhile;
				?>
			</div>
		</div>
	</section>
</main>
<?php get_footer(); ?>
