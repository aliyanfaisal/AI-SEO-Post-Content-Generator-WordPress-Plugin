<?php
/**
 * Plugin lifecycle and bootstrap.
 *
 * @package AISCG
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AISCG_Plugin {

	/**
	 * Whether the environment meets the minimum requirements.
	 *
	 * @return bool
	 */
	public static function requirements_met() {
		global $wp_version;
		return version_compare( PHP_VERSION, AISCG_MIN_PHP, '>=' )
			&& version_compare( $wp_version, AISCG_MIN_WP, '>=' );
	}

	/**
	 * Runs on plugins_loaded.
	 */
	public static function boot() {
		load_plugin_textdomain( 'ai-seo-content-generator', false, dirname( plugin_basename( AISCG_PLUGIN_FILE ) ) . '/languages' );

		if ( ! self::requirements_met() ) {
			add_action( 'admin_notices', array( __CLASS__, 'requirements_notice' ) );
			return;
		}

		// Subsystems (settings, cron, admin, CLI) are wired up here in later steps.
	}

	public static function requirements_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: 1: required PHP version, 2: required WordPress version. */
					__( 'AI SEO Content Generator requires PHP %1$s+ and WordPress %2$s+.', 'ai-seo-content-generator' ),
					AISCG_MIN_PHP,
					AISCG_MIN_WP
				)
			)
		);
	}

	public static function activate() {
		if ( ! self::requirements_met() ) {
			deactivate_plugins( plugin_basename( AISCG_PLUGIN_FILE ) );
			wp_die(
				esc_html(
					sprintf(
						/* translators: 1: required PHP version, 2: required WordPress version. */
						__( 'AI SEO Content Generator requires PHP %1$s+ and WordPress %2$s+.', 'ai-seo-content-generator' ),
						AISCG_MIN_PHP,
						AISCG_MIN_WP
					)
				)
			);
		}
		// Defaults, option migration and scheduling are added in later steps.
	}

	public static function deactivate() {
		// Cron events are cleared here once AISCG_Cron exists.
	}
}
