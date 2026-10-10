<?php
/**
 * Minimal WordPress stubs so unit tests run without a WordPress install.
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'AISCG_VERSION', 'test' );

require_once dirname( __DIR__ ) . '/includes/class-aiscg-autoloader.php';
AISCG_Autoloader::register( dirname( __DIR__ ) . '/includes/' );

class WP_Error {
	public $code;
	public $message;
	public function __construct( $code = '', $message = '' ) {
		$this->code    = $code;
		$this->message = $message;
	}
	public function get_error_code() {
		return $this->code;
	}
	public function get_error_message() {
		return $this->message;
	}
}

$GLOBALS['aiscg_test_options'] = array();
$GLOBALS['aiscg_test_cleared'] = array();

function aiscg_test_reset() {
	$GLOBALS['aiscg_test_options'] = array();
	$GLOBALS['aiscg_test_cleared'] = array();
}

function is_wp_error( $thing ) {
	return $thing instanceof WP_Error;
}
function get_option( $name, $default = false ) {
	return array_key_exists( $name, $GLOBALS['aiscg_test_options'] ) ? $GLOBALS['aiscg_test_options'][ $name ] : $default;
}
function update_option( $name, $value ) {
	$GLOBALS['aiscg_test_options'][ $name ] = $value;
	return true;
}
function add_option( $name, $value ) {
	if ( ! array_key_exists( $name, $GLOBALS['aiscg_test_options'] ) ) {
		$GLOBALS['aiscg_test_options'][ $name ] = $value;
	}
	return true;
}
function delete_option( $name ) {
	unset( $GLOBALS['aiscg_test_options'][ $name ] );
	return true;
}
function wp_clear_scheduled_hook( $hook ) {
	$GLOBALS['aiscg_test_cleared'][] = $hook;
}
function wp_salt( $scheme = 'auth' ) {
	return 'test-salt-' . $scheme;
}
function wp_parse_url( $url ) {
	return parse_url( $url );
}
function sanitize_text_field( $s ) {
	return trim( preg_replace( '/[\r\n\t ]+/', ' ', strip_tags( (string) $s ) ) );
}
function sanitize_textarea_field( $s ) {
	return trim( strip_tags( (string) $s ) );
}
function sanitize_key( $s ) {
	return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $s ) );
}
function esc_url_raw( $url, $protocols = null ) {
	$p = parse_url( $url );
	if ( empty( $p['scheme'] ) || ( $protocols && ! in_array( $p['scheme'], $protocols, true ) ) ) {
		return '';
	}
	return $url;
}
