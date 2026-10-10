<?php
/**
 * One-time migration of the old client plugin's options (aiscp_*) to aiscg_*.
 *
 * API keys were never stored in the client plugin, so they cannot be migrated;
 * a notice asks the user to re-enter them.
 *
 * @package AISCG
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AISCG_Migrator {

	const DONE_FLAG   = 'aiscg_migrated_from_aiscp';
	const REKEY_FLAG  = 'aiscg_notice_rekey';
	const OLD_PREFIX  = 'aiscp_';
	const MISSING     = '__aiscg_missing__';

	/**
	 * Old option (without prefix) => [ new setting key, transform name or null ].
	 *
	 * @return array<string,array>
	 */
	public static function map() {
		return array(
			'target_keywords'       => array( 'target_keywords', null ),
			'negative_keywords'     => array( 'negative_keywords', null ),
			'writing_style'         => array( 'writing_style', null ),
			'tone'                  => array( 'tone', null ),
			'tone_examples'         => array( 'brand_voice', null ),
			'content_restrictions'  => array( 'content_restrictions', null ),
			'use_reference_posts'   => array( 'use_reference_posts', null ),
			'reference_posts_prompt' => array( 'reference_prompt', null ),
			'reference_urls'        => array( 'reference_urls', null ),
			'competitor_domains'    => array( 'competitor_domains', null ),
			'publish_mode'          => array( 'post_status', 'status' ),
			'enable_thumbnails'     => array( 'enable_thumbnails', null ),
			'enable_stock_images'   => array( 'enable_stock_images', null ),
			'internal_links'        => array( 'internal_links', null ),
			'sitemap_url'           => array( 'sitemap_url', null ),
			'content_language'      => array( 'language', 'language' ),
			'posts_per_month'       => array( 'monthly_cap', 'int' ),
			'auto_categorize'       => array( 'auto_categorize', null ),
			'create_new_categories' => array( 'create_categories', null ),
			'create_new_tags'       => array( 'create_tags', null ),
			'fact_checking'         => array( 'fact_checking', null ),
			'temperature'           => array( 'temperature', null ),
			'cron_interval'         => array( 'cron_interval', 'interval' ),
			'cron_start_time'       => array( 'cron_start_time', null ),
			'cron_posts_per_run'    => array( 'posts_per_run', 'int' ),
			'cron_enabled'          => array( 'cron_enabled', null ),
			'stat_posts_generated'  => array( 'stat_posts_generated', 'int' ),
			'stat_posts_this_month' => array( 'stat_posts_this_month', 'int' ),
			'stat_last_generated'   => array( 'stat_last_generated', null ),
		);
	}

	/**
	 * @param string $name  Transform name.
	 * @param mixed  $value Old value.
	 * @return mixed
	 */
	private static function transform( $name, $value ) {
		switch ( $name ) {
			case 'status':
				return in_array( $value, array( 'draft', 'pending', 'publish' ), true ) ? $value : 'draft';
			case 'language':
				$labels = array(
					'he' => 'Hebrew',
					'ar' => 'Arabic',
					'en' => 'English',
				);
				return isset( $labels[ $value ] ) ? $labels[ $value ] : $value;
			case 'int':
				return (int) $value;
			case 'interval':
				$ok = array( 'daily', 'every_3_days', 'weekly', 'biweekly', 'monthly' );
				return in_array( $value, $ok, true ) ? $value : 'weekly';
		}
		return $value;
	}

	/**
	 * Run the migration once.
	 *
	 * @return int Number of options migrated (0 when already done or nothing to migrate).
	 */
	public static function run() {
		if ( get_option( self::DONE_FLAG ) ) {
			return 0;
		}

		$migrated = 0;
		foreach ( self::map() as $old => $target ) {
			$value = get_option( self::OLD_PREFIX . $old, self::MISSING );
			if ( self::MISSING === $value ) {
				continue;
			}

			$new_name = AISCG_Constants::OPTION_PREFIX . $target[0];
			// Never overwrite something the user already set in the new plugin.
			if ( self::MISSING !== get_option( $new_name, self::MISSING ) ) {
				continue;
			}

			update_option( $new_name, $target[1] ? self::transform( $target[1], $value ) : $value );
			$migrated++;
		}

		// Stop the old plugin's cron events so they do not fire into missing code.
		if ( $migrated > 0 && function_exists( 'wp_clear_scheduled_hook' ) ) {
			wp_clear_scheduled_hook( 'aiscp_cron_main' );
			wp_clear_scheduled_hook( 'aiscp_cron_batch_process' );
		}

		if ( $migrated > 0 ) {
			update_option( self::REKEY_FLAG, '1' );
		}
		update_option( self::DONE_FLAG, '1' );

		return $migrated;
	}
}
