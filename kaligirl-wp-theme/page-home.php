<?php
/**
 * Template Name: Home
 *
 * Public. Also used automatically for the site's front page — see
 * front-page.php, which includes this same markup.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
require KALIGIRL_DIR . '/template-parts/content-home.php';
get_footer();
