<?php
/**
 * Security hardening.
 *
 * Per the migration spec's security requirements section, the *primary*
 * controls for rate limiting and automated-challenge (CAPTCHA) enforcement
 * belong at the hosting/plugin level (Wordfence, Cloudflare, Paid
 * Memberships Pro's reCAPTCHA settings) — not hand-rolled in theme PHP.
 * What's below is the defense-in-depth layer that legitimately belongs in
 * code: input sanitization helpers, a honeypot mechanism for the core
 * login form and PMP's checkout/registration form, a lightweight fallback
 * rate limiter for sites without an edge/WAF layer, upload restrictions,
 * secrets-from-environment helpers, and audit logging.
 *
 * See kaligirl-wp-theme/README.md for the plugin/hosting-level setup this
 * assumes (Wordfence rules, PMP reCAPTCHA, wp-config secrets).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Baseline hardening
 * ---------------------------------------------------------------------- */

// Don't advertise the exact WordPress version in markup/headers.
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

// XML-RPC is a common brute-force/amplification vector this site has no use for.
add_filter( 'xmlrpc_enabled', '__return_false' );

// Disable the in-dashboard theme/plugin file editor.
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

// Baseline security headers. X-Frame-Options here governs whether *other*
// sites can iframe kaligirlfinancialservices.com — it has no effect on this
// site embedding the Moxo iframe on Get Started, which is an outbound embed.
add_action( 'send_headers', function () {
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );
} );

/* -------------------------------------------------------------------------
 * Secrets management
 * ---------------------------------------------------------------------- */

/**
 * Read a secret (API key, etc.) from the environment or a wp-config.php
 * constant — never from a value committed to this repo. Returns $default
 * if neither is set, so missing configuration fails closed (features that
 * need the secret should treat $default as "disabled") rather than falling
 * back to a hardcoded value.
 */
function kaligirl_secret( $name, $default = '' ) {
	$env = getenv( $name );
	if ( false !== $env && '' !== $env ) {
		return $env;
	}
	if ( defined( $name ) ) {
		return constant( $name );
	}
	return $default;
}

/* -------------------------------------------------------------------------
 * Audit logging
 * ---------------------------------------------------------------------- */

function kaligirl_security_log_dir() {
	$upload_dir = wp_upload_dir();
	return trailingslashit( $upload_dir['basedir'] ) . 'kaligirl-security';
}

/**
 * Create the log directory with a deny-all .htaccess + a blank index.php,
 * mirroring how WordPress protects wp-content/uploads subfolders it doesn't
 * want served directly.
 */
function kaligirl_security_log_bootstrap() {
	$dir = kaligirl_security_log_dir();
	if ( ! file_exists( $dir ) ) {
		wp_mkdir_p( $dir );
	}
	$htaccess = $dir . '/.htaccess';
	if ( ! file_exists( $htaccess ) ) {
		file_put_contents( $htaccess, "Require all denied\nDeny from all\n" );
	}
	$index = $dir . '/index.php';
	if ( ! file_exists( $index ) ) {
		file_put_contents( $index, "<?php // Silence is golden.\n" );
	}
}
add_action( 'init', 'kaligirl_security_log_bootstrap' );

/**
 * Append one line to the audit log: failed logins, honeypot triggers, rate
 * limit blocks, repeated 404s. Kept as plain-text, append-only, outside the
 * theme/plugin tree, for periodic review (e.g. by whatever security plugin
 * or SIEM ingests it) per the migration spec's audit logging requirement.
 */
function kaligirl_security_log( $event, $detail = '' ) {
	$dir = kaligirl_security_log_dir();
	if ( ! file_exists( $dir ) ) {
		kaligirl_security_log_bootstrap();
	}
	$line = sprintf(
		"[%s] ip=%s event=%s detail=%s\n",
		gmdate( 'Y-m-d H:i:s' ) . ' UTC',
		kaligirl_client_ip(),
		$event,
		str_replace( array( "\r", "\n" ), ' ', (string) $detail )
	);
	error_log( $line, 3, $dir . '/security.log' );
}

function kaligirl_client_ip() {
	foreach ( array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ) as $key ) {
		if ( ! empty( $_SERVER[ $key ] ) ) {
			$ip = trim( explode( ',', wp_unslash( $_SERVER[ $key ] ) )[0] );
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}
	}
	return '0.0.0.0';
}

// Failed logins.
add_action( 'wp_login_failed', function ( $username ) {
	kaligirl_security_log( 'login_failed', $username );
} );

// Repeated 404s (logged, not blocked here — a WAF/security plugin should
// handle IP throttling/blocking based on volume; this just feeds the trail).
add_action( 'template_redirect', function () {
	if ( is_404() ) {
		kaligirl_security_log( '404', esc_url_raw( $_SERVER['REQUEST_URI'] ?? '' ) );
	}
} );

/* -------------------------------------------------------------------------
 * Honeypot (login, registration, and any future public form)
 * ---------------------------------------------------------------------- */

/**
 * Check a honeypot field on $_POST and log+short-circuit if it's filled in.
 * Call from a form's validation/submit handler before doing anything else.
 * Returns true (safe to continue) or false (submission blocked).
 */
function kaligirl_check_honeypot( $field = 'kg_hp_field' ) {
	if ( ! empty( $_POST[ $field ] ) ) {
		kaligirl_security_log( 'honeypot_triggered', $field );
		return false;
	}
	return true;
}

// Print the honeypot field into the core login form — both wp-login.php
// directly and PMP's own Login page, which renders its login form via
// pmpro_login_form() -> wp_login_form() (which fires this same
// 'login_form' action; verified against PMP's includes/login.php).
add_action( 'login_form', 'kaligirl_honeypot_field' );

// Print it into Paid Memberships Pro's checkout/registration form too.
// Verified against PMP's pages/checkout.php: 'pmpro_checkout_boxes' fires
// inside the checkout <form>, after the account fields.
add_action( 'pmpro_checkout_boxes', 'kaligirl_honeypot_field' );

// Reject core wp-login.php authentication if the honeypot was filled.
add_filter( 'authenticate', function ( $user, $username, $password ) {
	if ( empty( $username ) && empty( $password ) ) {
		return $user; // Not a submitted login attempt.
	}
	if ( ! kaligirl_check_honeypot() ) {
		return new WP_Error( 'kg_honeypot', __( 'Something went wrong. Please try again.', 'kaligirl' ) );
	}
	return $user;
}, 30, 3 );

// Reject PMP checkout/registration if the honeypot was filled. Verified
// against PMP's includes/fields.php: 'pmpro_checkout_order_creation_checks'
// is the real filter PMP itself uses for this kind of pre-order validation
// (takes/returns a bool), and pmpro_setMessage() is PMP's own helper for
// surfacing the error on the checkout page.
add_filter( 'pmpro_checkout_order_creation_checks', function ( $okay ) {
	if ( ! kaligirl_check_honeypot() ) {
		if ( function_exists( 'pmpro_setMessage' ) ) {
			pmpro_setMessage( __( 'Something went wrong. Please try again.', 'kaligirl' ), 'pmpro_error' );
		}
		return false;
	}
	return $okay;
} );

/* -------------------------------------------------------------------------
 * reCAPTCHA v3 fallback verification
 *
 * Paid Memberships Pro has native reCAPTCHA v3 support (Memberships >
 * Settings > reCAPTCHA) — enable it there with site/secret keys pulled from
 * environment (see kaligirl_secret() above). kaligirl_verify_recaptcha()
 * below is a plain helper available to any additional public form the site
 * adds later (e.g. a future native contact form) that isn't already covered
 * by PMP's own reCAPTCHA integration.
 * ---------------------------------------------------------------------- */

function kaligirl_verify_recaptcha( $token ) {
	$secret = kaligirl_secret( 'KALIGIRL_RECAPTCHA_SECRET_KEY' );
	if ( empty( $secret ) || empty( $token ) ) {
		return false;
	}
	$response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
		'timeout' => 8,
		'body'    => array(
			'secret'   => $secret,
			'response' => $token,
			'remoteip' => kaligirl_client_ip(),
		),
	) );
	if ( is_wp_error( $response ) ) {
		kaligirl_security_log( 'recaptcha_error', $response->get_error_message() );
		return false;
	}
	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	$ok   = ! empty( $body['success'] ) && ( ! isset( $body['score'] ) || $body['score'] >= 0.5 );
	if ( ! $ok ) {
		kaligirl_security_log( 'recaptcha_failed', wp_json_encode( $body ) );
	}
	return $ok;
}

/* -------------------------------------------------------------------------
 * Lightweight fallback rate limiting
 *
 * Real rate limiting belongs at the edge (Cloudflare rate rules) or in a
 * WAF plugin (Wordfence) per the spec — those inspect traffic before it
 * reaches PHP and can rate-limit static assets, not just page requests.
 * This transient-based limiter is a fallback for environments without
 * either, capping at the same thresholds: 10/min per IP unauthenticated,
 * 100/min per IP authenticated. It intentionally only guards
 * authentication endpoints (wp-login.php, which PMP's own Login page posts
 * through, and PMP's checkout/registration POST) rather than every page
 * load, so normal browsing is never throttled.
 * ---------------------------------------------------------------------- */

function kaligirl_is_rate_limited_request() {
	if ( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'] ) {
		return true;
	}
	// Paid Memberships Pro's checkout/registration form post (the level
	// selection + account fields all submit through the checkout page).
	// Field names verified against PMP's own pages/checkout.php.
	if ( ! empty( $_POST ) && isset( $_REQUEST['pmpro_level'] ) && isset( $_REQUEST['submit-checkout'] ) ) {
		return true;
	}
	return false;
}

add_action( 'init', function () {
	if ( ! kaligirl_is_rate_limited_request() ) {
		return;
	}

	$ip      = kaligirl_client_ip();
	$is_auth = is_user_logged_in();
	$limit   = $is_auth ? 100 : 10;
	$key     = 'kg_rl_' . ( $is_auth ? 'a_' : 'u_' ) . md5( $ip );

	$count = (int) get_transient( $key );
	if ( $count >= $limit ) {
		kaligirl_security_log( 'rate_limit_block', "limit={$limit}" );
		status_header( 429 );
		header( 'Retry-After: 60' );
		wp_die(
			esc_html__( 'Too many requests. Please wait a minute and try again.', 'kaligirl' ),
			esc_html__( 'Rate limit exceeded', 'kaligirl' ),
			array( 'response' => 429 )
		);
	}
	set_transient( $key, $count + 1, MINUTE_IN_SECONDS );
}, 1 );

/* -------------------------------------------------------------------------
 * Input sanitization helpers
 *
 * Use these at every point the theme accepts user input (there is no native
 * contact form in this design — Contact routes to Get Started's Moxo embed
 * — but any field added later, or PMP custom checkout fields, should run
 * through these rather than being trusted or concatenated into queries).
 * ---------------------------------------------------------------------- */

/**
 * Sanitize a single-line text field and reject anything containing script
 * tags, raw HTML, or characters that only make sense in a SQL fragment.
 * Returns '' (not the raw input) when the value looks like an injection
 * attempt, so callers can treat empty as "rejected".
 */
function kaligirl_sanitize_input( $value ) {
	$value = sanitize_text_field( wp_unslash( (string) $value ) );
	if ( preg_match( '/<script|<\/script|<iframe|javascript:|on\w+\s*=/i', $value ) ) {
		kaligirl_security_log( 'input_rejected_script', $value );
		return '';
	}
	if ( preg_match( '/(\bunion\b.*\bselect\b|\bselect\b.*\bfrom\b|\binsert\b.*\binto\b|\bdrop\b\s+table|--|;--|\/\*)/i', $value ) ) {
		kaligirl_security_log( 'input_rejected_sql', $value );
		return '';
	}
	return $value;
}

/**
 * Sanitize a field that's allowed a restricted set of HTML (e.g. a rich
 * text area) using WP's kses allowlist rather than trusting raw markup.
 */
function kaligirl_sanitize_rich_text( $value ) {
	return wp_kses( wp_unslash( (string) $value ), wp_kses_allowed_html( 'post' ) );
}

/* -------------------------------------------------------------------------
 * File upload restrictions
 *
 * Moxo handles the site's actual document/file exchange, so WordPress
 * itself shouldn't need a public uploader. These filters are the guardrail
 * required by the spec in case a native upload feature (e.g. a profile
 * photo) is ever added: images only, renamed on upload, capped at 1GB.
 * ---------------------------------------------------------------------- */

add_filter( 'upload_mimes', function ( $mimes ) {
	return array(
		'jpg|jpeg|jpe' => 'image/jpeg',
		'png'          => 'image/png',
		'gif'          => 'image/gif',
		'webp'         => 'image/webp',
	);
} );

add_filter( 'upload_size_limit', function () {
	return 1073741824; // 1GB, per spec.
} );

add_filter( 'wp_handle_upload_prefilter', function ( $file ) {
	$info = pathinfo( $file['name'] );
	$ext  = isset( $info['extension'] ) ? strtolower( $info['extension'] ) : '';
	$file['name'] = sprintf( 'kg-%s-%s.%s', time(), wp_generate_password( 8, false, false ), $ext );
	return $file;
} );
