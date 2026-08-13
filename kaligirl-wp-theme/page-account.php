<?php
/**
 * Template Name: Account
 *
 * Not currently assigned to any page. Account now goes through Paid
 * Memberships Pro's own Membership Account page ([pmpro_account]
 * shortcode) instead of this custom one — see the long comment in
 * inc/membership.php for why. Kept here only as a safe stub: if this
 * template is ever assigned to a page again, it just forwards to PMP's
 * real Account page rather than rendering a second, differently-gated
 * account view.
 *
 * The original pixel-matched account-hero markup (Welcome back /
 * Documents / Messages / Plan cards) is preserved in git history.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_safe_redirect( kaligirl_account_url() );
exit;
