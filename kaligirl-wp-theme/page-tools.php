<?php
/**
 * Template Name: Tools
 *
 * Membership-gated placeholder. Protect via Paid Memberships Pro's
 * "Require Membership" setting in wp-admin; kaligirl_require_login() is
 * the template-level backup.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

kaligirl_require_login();

get_header();
$kg_resource_label = 'Tools';
require KALIGIRL_DIR . '/template-parts/content-resource-placeholder.php';
get_footer();
