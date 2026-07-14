<?php
/**
 * Kaligirl Financial Services theme bootstrap.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KALIGIRL_VERSION', '1.0.0' );
define( 'KALIGIRL_DIR', get_template_directory() );
define( 'KALIGIRL_URI', get_template_directory_uri() );

/**
 * Theme setup.
 */
function kaligirl_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'align-wide' );
}
add_action( 'after_setup_theme', 'kaligirl_setup' );

/**
 * Styles & scripts.
 */
function kaligirl_assets() {
	wp_enqueue_style(
		'kaligirl-fonts',
		'https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'kaligirl-style', get_stylesheet_uri(), array(), KALIGIRL_VERSION );
	wp_enqueue_script( 'kaligirl-main', KALIGIRL_URI . '/js/main.js', array(), KALIGIRL_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'kaligirl_assets' );

/**
 * Includes.
 */
require KALIGIRL_DIR . '/inc/template-tags.php';
require KALIGIRL_DIR . '/inc/membership.php';
require KALIGIRL_DIR . '/inc/security.php';

/**
 * Page templates registered by this theme (Appearance > Page Attributes > Template).
 * Physical files live at the theme root using the `page-{slug}.php` naming
 * convention, which WordPress also auto-matches to pages with that slug —
 * the explicit "Template Name" headers below just make them selectable from
 * any page slug in wp-admin as well.
 */
