<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AISCP_Settings {

	public static function get( $key, $default = '' ) {
		return get_option( 'aiscp_' . $key, $default );
	}

	public static function save( $data ) {
		$fields = array(
			'target_keywords', 'negative_keywords', 'writing_style', 'tone',
			'tone_examples', 'content_restrictions', 'competitor_domains', 'publish_mode',
			'enable_thumbnails', 'enable_stock_images', 'internal_links',
			'sitemap_url', 'content_language', 'ai_model', 'posts_per_month',
			'auto_categorize', 'fact_checking',
		);

		foreach ( $fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				update_option( 'aiscp_' . $field, sanitize_textarea_field( $data[ $field ] ) );
			}
		}

		// Checkboxes (save 0 if not present)
		$checkboxes = array( 'enable_thumbnails', 'enable_stock_images', 'auto_categorize', 'fact_checking' );
		foreach ( $checkboxes as $cb ) {
			update_option( 'aiscp_' . $cb, isset( $data[ $cb ] ) ? '1' : '0' );
		}

		return true;
	}

	public static function get_stats() {
		return array(
			'posts_generated'  => (int) get_option( 'aiscp_stat_posts_generated', 0 ),
			'posts_this_month' => (int) get_option( 'aiscp_stat_posts_this_month', 0 ),
			'posts_limit'      => (int) self::get( 'posts_per_month', 10 ),
			'last_generated'   => get_option( 'aiscp_stat_last_generated', __( 'Never', 'ai-seo-content-plugin' ) ),
			'keywords_tracked' => count( array_filter( explode( "\n", self::get( 'target_keywords', '' ) ) ) ),
		);
	}
}
