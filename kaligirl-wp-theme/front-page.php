<?php
/**
 * Front page (used automatically for `/` regardless of the Settings >
 * Reading front-page choice). Shares content with page-home.php so the
 * Home design renders identically whether reached as the site root or as a
 * separately assigned "Home" page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
require KALIGIRL_DIR . '/template-parts/content-home.php';
get_footer();
