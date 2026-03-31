<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AISCP_SEO
 *
 * Saves AI-generated SEO data to the correct meta fields for all major SEO plugins.
 *
 * Supported plugins:
 *  - Yoast SEO (Free & Premium)
 *  - Rank Math
 *  - All in One SEO (AIOSEO)
 *  - SEOPress
 *  - Slim SEO
 *  - The SEO Framework
 *  - Squirrly SEO
 *  - Math Rank (alias for Rank Math)
 */
class AISCP_SEO {

	/**
	 * Save all SEO data for a post.
	 *
	 * @param int   $post_id   The WordPress post ID.
	 * @param array $seo_data  Array with keys: title, description, focus_keyword, canonical, robots_noindex, og_title, og_description, twitter_title, twitter_description, schema_type
	 */
	public static function save( $post_id, $seo_data ) {
		if ( empty( $post_id ) || empty( $seo_data ) ) return;

		$title       = sanitize_text_field( $seo_data['title'] ?? $seo_data['seo_title'] ?? '' );
		$description = sanitize_text_field( $seo_data['description'] ?? $seo_data['seo_description'] ?? $seo_data['metadesc'] ?? '' );
		$focus_kw    = sanitize_text_field( $seo_data['focus_keyword'] ?? '' );
		$og_title    = sanitize_text_field( $seo_data['og_title'] ?? $title );
		$og_desc     = sanitize_text_field( $seo_data['og_description'] ?? $description );
		$tw_title    = sanitize_text_field( $seo_data['twitter_title'] ?? $title );
		$tw_desc     = sanitize_text_field( $seo_data['twitter_description'] ?? $description );
		$schema_type = sanitize_text_field( $seo_data['schema_type'] ?? 'Article' );

		self::save_yoast( $post_id, $title, $description, $focus_kw, $og_title, $og_desc, $tw_title, $tw_desc );
		self::save_rankmath( $post_id, $title, $description, $focus_kw, $og_title, $og_desc, $tw_title, $tw_desc, $schema_type );
		self::save_aioseo( $post_id, $title, $description, $focus_kw, $og_title, $og_desc, $tw_title, $tw_desc );
		self::save_seopress( $post_id, $title, $description, $focus_kw, $og_title, $og_desc, $tw_title, $tw_desc );
		self::save_slim_seo( $post_id, $title, $description, $og_title, $og_desc, $tw_title, $tw_desc );
		self::save_the_seo_framework( $post_id, $title, $description, $og_title, $og_desc, $tw_title, $tw_desc );
		self::save_squirrly( $post_id, $title, $description, $focus_kw );
	}

	// ---------------------------------------------------------------
	// Yoast SEO (yoast/wordpress-seo)
	// Meta keys: _yoast_wpseo_*
	// ---------------------------------------------------------------
	private static function save_yoast( $post_id, $title, $desc, $focus_kw, $og_title, $og_desc, $tw_title, $tw_desc ) {
		if ( ! defined( 'WPSEO_VERSION' ) ) return;

		$fields = array(
			'_yoast_wpseo_title'                => $title,
			'_yoast_wpseo_metadesc'             => $desc,
			'_yoast_wpseo_focuskw'              => $focus_kw,
			'_yoast_wpseo_opengraph-title'      => $og_title,
			'_yoast_wpseo_opengraph-description'=> $og_desc,
			'_yoast_wpseo_twitter-title'        => $tw_title,
			'_yoast_wpseo_twitter-description'  => $tw_desc,
		);

		foreach ( $fields as $key => $value ) {
			if ( ! empty( $value ) ) {
				update_post_meta( $post_id, $key, $value );
			}
		}
	}

	// ---------------------------------------------------------------
	// Rank Math (rankmath/rank-math-seo)
	// Meta keys: rank_math_*
	// ---------------------------------------------------------------
	private static function save_rankmath( $post_id, $title, $desc, $focus_kw, $og_title, $og_desc, $tw_title, $tw_desc, $schema_type ) {
		if ( ! defined( 'RANK_MATH_VERSION' ) ) return;

		$fields = array(
			'rank_math_title'                => $title,
			'rank_math_description'          => $desc,
			'rank_math_focus_keyword'        => $focus_kw,
			'rank_math_og_title'             => $og_title,
			'rank_math_og_description'       => $og_desc,
			'rank_math_twitter_title'        => $tw_title,
			'rank_math_twitter_description'  => $tw_desc,
			'rank_math_schema_type'          => $schema_type,
			// Rank Math SEO score — set to a neutral value
			'rank_math_seo_score'            => '',
		);

		foreach ( $fields as $key => $value ) {
			if ( $value !== '' ) {
				update_post_meta( $post_id, $key, $value );
			}
		}
	}

	// ---------------------------------------------------------------
	// All in One SEO (aioseo/all-in-one-seo-pack)
	// Uses its own DB table + post meta
	// ---------------------------------------------------------------
	private static function save_aioseo( $post_id, $title, $desc, $focus_kw, $og_title, $og_desc, $tw_title, $tw_desc ) {
		if ( ! defined( 'AIOSEO_VERSION' ) ) return;

		// AIOSEO stores data in wp_aioseo_posts table AND post meta
		$fields = array(
			'_aioseo_title'               => $title,
			'_aioseo_description'         => $desc,
			'_aioseo_keywords'            => $focus_kw,
			'_aioseo_og_title'            => $og_title,
			'_aioseo_og_description'      => $og_desc,
			'_aioseo_twitter_title'       => $tw_title,
			'_aioseo_twitter_description' => $tw_desc,
		);

		foreach ( $fields as $key => $value ) {
			if ( ! empty( $value ) ) {
				update_post_meta( $post_id, $key, $value );
			}
		}

		// Also update AIOSEO's custom table if it exists
		global $wpdb;
		$table = $wpdb->prefix . 'aioseo_posts';
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) === $table ) {
			$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE post_id = %d", $post_id ) );
			$data = array(
				'title'               => $title,
				'description'         => $desc,
				'keywords'            => $focus_kw,
				'og_title'            => $og_title,
				'og_description'      => $og_desc,
				'twitter_title'       => $tw_title,
				'twitter_description' => $tw_desc,
				'updated'             => current_time( 'mysql' ),
			);
			if ( $existing ) {
				$wpdb->update( $table, $data, array( 'post_id' => $post_id ) );
			} else {
				$data['post_id'] = $post_id;
				$data['created'] = current_time( 'mysql' );
				$wpdb->insert( $table, $data );
			}
		}
	}

	// ---------------------------------------------------------------
	// SEOPress (seopress/seopress.php)
	// Meta keys: _seopress_*
	// ---------------------------------------------------------------
	private static function save_seopress( $post_id, $title, $desc, $focus_kw, $og_title, $og_desc, $tw_title, $tw_desc ) {
		if ( ! defined( 'SEOPRESS_VERSION' ) ) return;

		$fields = array(
			'_seopress_titles_title'              => $title,
			'_seopress_titles_desc'               => $desc,
			'_seopress_analysis_target_kw'        => $focus_kw,
			'_seopress_social_fb_title'           => $og_title,
			'_seopress_social_fb_desc'            => $og_desc,
			'_seopress_social_twitter_title'      => $tw_title,
			'_seopress_social_twitter_desc'       => $tw_desc,
		);

		foreach ( $fields as $key => $value ) {
			if ( ! empty( $value ) ) {
				update_post_meta( $post_id, $key, $value );
			}
		}
	}

	// ---------------------------------------------------------------
	// Slim SEO (elightup/slim-seo)
	// Meta key: slim_seo (serialized array)
	// ---------------------------------------------------------------
	private static function save_slim_seo( $post_id, $title, $desc, $og_title, $og_desc, $tw_title, $tw_desc ) {
		if ( ! defined( 'SLIM_SEO_VER' ) ) return;

		$data = get_post_meta( $post_id, 'slim_seo', true );
		if ( ! is_array( $data ) ) $data = array();

		if ( ! empty( $title ) )   $data['title']               = $title;
		if ( ! empty( $desc ) )    $data['description']         = $desc;
		if ( ! empty( $og_title ) ) $data['facebook_title']     = $og_title;
		if ( ! empty( $og_desc ) )  $data['facebook_description']= $og_desc;
		if ( ! empty( $tw_title ) ) $data['twitter_title']      = $tw_title;
		if ( ! empty( $tw_desc ) )  $data['twitter_description']= $tw_desc;

		update_post_meta( $post_id, 'slim_seo', $data );
	}

	// ---------------------------------------------------------------
	// The SEO Framework (autodescription/autodescription.php)
	// Meta keys: _genesis_title, _genesis_description, etc.
	// ---------------------------------------------------------------
	private static function save_the_seo_framework( $post_id, $title, $desc, $og_title, $og_desc, $tw_title, $tw_desc ) {
		if ( ! function_exists( 'the_seo_framework' ) ) return;

		$fields = array(
			'_genesis_title'       => $title,
			'_genesis_description' => $desc,
			'_open_graph_title'    => $og_title,
			'_open_graph_description' => $og_desc,
			'_twitter_title'       => $tw_title,
			'_twitter_description' => $tw_desc,
		);

		foreach ( $fields as $key => $value ) {
			if ( ! empty( $value ) ) {
				update_post_meta( $post_id, $key, $value );
			}
		}
	}

	// ---------------------------------------------------------------
	// Squirrly SEO (squirrly-seo/squirrly.php)
	// Meta key: _sq_post_keyword
	// ---------------------------------------------------------------
	private static function save_squirrly( $post_id, $title, $desc, $focus_kw ) {
		if ( ! defined( 'SQ_VERSION' ) ) return;

		$sq_data = get_post_meta( $post_id, '_sq_post_keyword', true );
		if ( ! is_array( $sq_data ) ) $sq_data = array();

		if ( ! empty( $focus_kw ) ) $sq_data['keyword']     = $focus_kw;
		if ( ! empty( $title ) )    $sq_data['seo_title']   = $title;
		if ( ! empty( $desc ) )     $sq_data['description'] = $desc;

		update_post_meta( $post_id, '_sq_post_keyword', $sq_data );
		update_post_meta( $post_id, '_sq_title', $title );
		update_post_meta( $post_id, '_sq_description', $desc );
	}
}
