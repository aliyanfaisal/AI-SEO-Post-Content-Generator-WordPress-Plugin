<?php
/**
 * Maps AISCG_Foo_Bar to includes/(providers|context)/class-aiscg-foo-bar.php
 * (or interface-aiscg-foo-bar.php).
 *
 * @package AISCG
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AISCG_Autoloader {

	/** @var string */
	private static $base = '';

	/**
	 * @param string $base Absolute path to the includes/ directory, with trailing slash.
	 */
	public static function register( $base ) {
		self::$base = $base;
		spl_autoload_register( array( __CLASS__, 'load' ) );
	}

	/**
	 * @param string $class Class or interface name.
	 */
	public static function load( $class ) {
		if ( 0 !== strpos( $class, 'AISCG_' ) ) {
			return;
		}

		$slug = str_replace( '_', '-', strtolower( $class ) );

		foreach ( array( '', 'providers/', 'context/' ) as $dir ) {
			foreach ( array( 'class-', 'interface-' ) as $type ) {
				$file = self::$base . $dir . $type . $slug . '.php';
				if ( is_readable( $file ) ) {
					require_once $file;
					return;
				}
			}
		}
	}
}
