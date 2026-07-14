<?php
/**
 * Small presentation helpers used by header.php, footer.php, and page templates.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve the canonical URL for one of the theme's fixed sections.
 * Login/Account defer to MemberPress's own configured pages when available
 * (see inc/memberpress.php) so this stays correct even if an admin renames
 * the MemberPress login/account page slugs.
 */
function kaligirl_url( $key ) {
	switch ( $key ) {
		case 'home':
			return home_url( '/' );
		case 'login':
			return kaligirl_mepr_login_url();
		case 'account':
			return kaligirl_mepr_account_url();
		default:
			return home_url( '/' . $key . '/' );
	}
}

/**
 * True when the current request is the given section, for active-state styling.
 */
function kaligirl_is_current( $key ) {
	if ( 'home' === $key ) {
		return is_front_page() || is_page( 'home' );
	}
	return is_page( $key );
}

/**
 * Render one primary/account nav link with active-state + underline handling.
 *
 * @param string $key      Section key (matches a page slug).
 * @param string $label    Visible link text.
 * @param bool   $underline Whether the active state also gets the accent underline.
 * @param string $class    Extra classes.
 */
function kaligirl_nav_link( $key, $label, $underline = true, $class = 'nav-link' ) {
	$is_active = kaligirl_is_current( $key );
	$classes   = $class;
	if ( $is_active ) {
		$classes .= ' is-active';
		if ( $underline ) {
			$classes .= ' has-underline';
		}
	}
	printf(
		'<a href="%1$s" class="%2$s">%3$s</a>',
		esc_url( kaligirl_url( $key ) ),
		esc_attr( $classes ),
		esc_html( $label )
	);
}

/**
 * Render a numbered principle/step row list, shared by Home, Services, and Get Started.
 *
 * @param array $rows Each item: [ 'n' => '01', 'title' => '...', 'body' => '...' ].
 */
function kaligirl_principle_list( array $rows ) {
	echo '<div class="principle-list">';
	foreach ( $rows as $row ) {
		echo '<div class="principle-row">';
		echo '<span class="principle-row__n">' . esc_html( $row['n'] ) . '</span>';
		echo '<div>';
		echo '<h3>' . esc_html( $row['title'] ) . '</h3>';
		echo '<p>' . esc_html( $row['body'] ) . '</p>';
		echo '</div>';
		echo '</div>';
	}
	echo '<div class="principle-list-end"></div>';
	echo '</div>';
}

/**
 * A honeypot field for public forms (login, registration). Invisible to
 * humans via CSS + off-screen positioning (not display:none, which some bots
 * skip when filling fields); any non-empty value marks the submission as
 * automated. See kaligirl_check_honeypot() in inc/security.php for validation.
 */
function kaligirl_honeypot_field( $name = 'kg_hp_field' ) {
	printf(
		'<div class="kg-hp-field" aria-hidden="true"><label for="%1$s">Leave this field empty</label><input type="text" id="%1$s" name="%1$s" value="" tabindex="-1" autocomplete="off"></div>',
		esc_attr( $name )
	);
}
