<?php
/**
 * Template Name: Tools
 *
 * MemberPress-gated placeholder. Protect via a MemberPress Rule in
 * wp-admin; kaligirl_require_login() is the template-level backup.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

kaligirl_require_login();

get_header();
$kg_resource_label = 'Tools';
require KALIGIRL_DIR . '/template-parts/content-resource-placeholder.php';
get_footer();
