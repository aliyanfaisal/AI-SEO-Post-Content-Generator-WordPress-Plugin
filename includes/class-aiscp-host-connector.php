<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AISCP_Host_Connector
 *
 * Handles all outbound API communication from the client plugin to the host plugin.
 * Uses an async webhook pattern — no HTTP timeouts.
 */
class AISCP_Host_Connector {

	const WEBHOOK_ROUTE = 'aiscp-client/v1/webhook';

	private static function endpoint( $path ) {
		return trailingslashit( AISCP_HOST_URL ) . 'wp-json/aiscp/v1/' . ltrim( $path, '/' );
	}

	/**
	 * The webhook URL on THIS site that the host plugin will POST results back to.
	 */
	public static function get_webhook_url() {
		return rest_url( self::WEBHOOK_ROUTE );
	}

	/**
	 * Collect all user preferences to send to host.
	 */
	public static function get_preferences() {
		return array(
			'domain'              => home_url(),
			'webhook_url'         => self::get_webhook_url(),
			'ai_model'            => get_option( 'aiscp_ai_model', 'claude' ),
			'content_language'    => get_option( 'aiscp_content_language', 'he' ),
			'writing_style'       => get_option( 'aiscp_writing_style', 'professional' ),
			'tone'                => get_option( 'aiscp_tone', 'informative' ),
			'tone_examples'          => get_option( 'aiscp_tone_examples', '' ),
			'content_restrictions'   => get_option( 'aiscp_content_restrictions', '' ),
			'target_keywords'     => get_option( 'aiscp_target_keywords', '' ),
			'negative_keywords'   => get_option( 'aiscp_negative_keywords', '' ),
			'internal_links'      => get_option( 'aiscp_internal_links', '' ),
			'sitemap_url'         => get_option( 'aiscp_sitemap_url', '' ),
			'competitor_domains'  => get_option( 'aiscp_competitor_domains', '' ),
			'publish_mode'        => get_option( 'aiscp_publish_mode', 'pending' ),
			'enable_thumbnails'   => get_option( 'aiscp_enable_thumbnails', '1' ),
			'enable_stock_images' => get_option( 'aiscp_enable_stock_images', '1' ),
			'auto_categorize'     => get_option( 'aiscp_auto_categorize', '1' ),
			'fact_checking'       => get_option( 'aiscp_fact_checking', '1' ),
		);
	}

	/**
	 * Validate license with host plugin (domain-based).
	 */
	public static function validate_license() {
		$response = wp_remote_post( AISCP_LICENSE_API, array(
			'timeout' => 15,
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( array( 'domain' => home_url() ) ),
		) );
		return self::parse_response( $response );
	}

	/**
	 * Request async post generation from host.
	 * Host responds immediately with job_id (no timeout risk).
	 * Result is delivered back to our webhook.
	 */
	public static function request_generate_post( $extra = array() ) {
		$payload = array_merge( self::get_preferences(), $extra );

		$response = wp_remote_post( self::endpoint( 'generate/post' ), array(
			'timeout' => 15, // Only waiting for job acceptance, not generation
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( $payload ),
		) );

		return self::parse_response( $response );
	}

	/**
	 * Insert a generated post into WordPress using data returned by host via webhook.
	 */
	public static function insert_post( $post_data ) {
		if ( empty( $post_data['title'] ) || empty( $post_data['content'] ) ) {
			return new WP_Error( 'missing_data', __( 'Post title or content missing.', 'ai-seo-content-plugin' ) );
		}

		$publish_mode = get_option( 'aiscp_publish_mode', 'pending' );
		$status_map   = array( 'publish' => 'publish', 'draft' => 'draft', 'pending' => 'pending' );
		$post_status  = $status_map[ $publish_mode ] ?? 'pending';

		// Resolve or create categories
		$cat_ids = array();
		if ( ! empty( $post_data['categories'] ) && is_array( $post_data['categories'] ) ) {
			foreach ( $post_data['categories'] as $cat_name ) {
				$term = get_term_by( 'name', $cat_name, 'category' );
				if ( $term ) {
					$cat_ids[] = $term->term_id;
				} elseif ( get_option( 'aiscp_auto_categorize', '1' ) === '1' ) {
					$new_term = wp_insert_term( sanitize_text_field( $cat_name ), 'category' );
					if ( ! is_wp_error( $new_term ) ) {
						$cat_ids[] = $new_term['term_id'];
					}
				}
			}
		}

		// Step 1: Download all inline images (image_2, image_3...) before inserting post
		$inline_attachment_ids = array();
		$image_urls = $post_data['image_urls'] ?? array();
		foreach ( $image_urls as $index => $url ) {
			if ( (int) $index === 1 ) continue; // image_1 = featured, handled separately
			if ( empty( $url ) ) continue;
			$attach_id = self::sideload_image( $url, 0, $post_data['title'] ?? '' );
			if ( $attach_id && ! is_wp_error( $attach_id ) ) {
				$inline_attachment_ids[ (int) $index ] = $attach_id;
			}
		}

		// Step 2: Replace {{IMAGE_N}} placeholders in content with WordPress image block HTML
		$post_content = $post_data['content'] ?? '';
		foreach ( $inline_attachment_ids as $index => $attach_id ) {
			$img_url = wp_get_attachment_url( $attach_id );
			$img_alt = sanitize_text_field( $post_data['title'] ?? '' );

			// Gutenberg block format (also renders correctly in Classic Editor)
			$block  = "\n" . '<!-- wp:image {"id":' . $attach_id . ',"sizeSlug":"large","linkDestination":"none"} -->' . "\n";
			$block .= '<figure class="wp-block-image size-large">';
			$block .= '<img src="' . esc_url( $img_url ) . '" alt="' . esc_attr( $img_alt ) . '" class="wp-image-' . $attach_id . '"/>';
			$block .= '</figure>' . "\n";
			$block .= '<!-- /wp:image -->' . "\n";

			$post_content = str_replace( '{{IMAGE_' . $index . '}}', $block, $post_content );
		}

		// Remove any unresolved placeholders (failed downloads)
		$post_content = preg_replace( '/\{\{IMAGE_\d+\}\}/', '', $post_content );

		// Step 3: Insert the post with processed content
		$post_id = wp_insert_post( array(
			'post_title'    => sanitize_text_field( $post_data['title'] ),
			'post_content'  => wp_kses_post( $post_content ),
			'post_excerpt'  => isset( $post_data['excerpt'] ) ? sanitize_textarea_field( $post_data['excerpt'] ) : '',
			'post_status'   => $post_status,
			'post_author'   => 1,
			'post_category' => $cat_ids,
		) );

		if ( is_wp_error( $post_id ) ) return $post_id;

		// Update inline image attachment parent to this post
		foreach ( $inline_attachment_ids as $attach_id ) {
			wp_update_post( array( 'ID' => $attach_id, 'post_parent' => $post_id ) );
		}

		// Tags
		if ( ! empty( $post_data['tags'] ) && is_array( $post_data['tags'] ) ) {
			wp_set_post_tags( $post_id, $post_data['tags'], false );
		}

		// Featured image — image_1 from image_urls, or thumbnail_url
		$featured_url = $image_urls[1] ?? $post_data['thumbnail_url'] ?? '';
		if ( ! empty( $featured_url ) && get_option( 'aiscp_enable_thumbnails', '1' ) === '1' ) {
			self::attach_thumbnail_from_url( $post_id, $featured_url, $post_data['title'] ?? '' );
		}

		// SEO meta — save to all supported SEO plugins
		if ( ! empty( $post_data['seo_meta'] ) && is_array( $post_data['seo_meta'] ) ) {
			AISCP_SEO::save( $post_id, $post_data['seo_meta'] );
		}

		// Stats
		update_option( 'aiscp_stat_posts_generated', (int) get_option( 'aiscp_stat_posts_generated', 0 ) + 1 );
		update_option( 'aiscp_stat_posts_this_month', (int) get_option( 'aiscp_stat_posts_this_month', 0 ) + 1 );
		update_option( 'aiscp_stat_last_generated', current_time( 'mysql' ) );

		return $post_id;
	}

	/**
	 * Download a remote image, add to media library, set as post featured image.
	 */
	private static function attach_thumbnail_from_url( $post_id, $url, $title = '' ) {
		$attach_id = self::sideload_image( $url, $post_id, $title );
		if ( $attach_id && ! is_wp_error( $attach_id ) ) {
			set_post_thumbnail( $post_id, $attach_id );
		}
	}

	/**
	 * Download a remote image into the WP media library.
	 *
	 * @return int|WP_Error  Attachment ID on success.
	 */
	private static function sideload_image( $url, $post_id = 0, $title = '' ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$tmp = download_url( esc_url_raw( $url ) );
		if ( is_wp_error( $tmp ) ) return $tmp;

		$ext        = pathinfo( parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) ?: 'jpg';
		$filename   = sanitize_file_name( 'ai-image-' . time() . '.' . $ext );
		$file_array = array( 'name' => $filename, 'tmp_name' => $tmp );

		$attach_id = media_handle_sideload( $file_array, (int) $post_id, sanitize_text_field( $title ) );
		@unlink( $tmp );

		return $attach_id;
	}

	private static function parse_response( $response ) {
		if ( is_wp_error( $response ) ) {
			return array( 'success' => false, 'message' => $response->get_error_message(), 'data' => array() );
		}
		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( in_array( $code, array( 200, 202 ) ) && ! empty( $body['success'] ) ) {
			return array( 'success' => true, 'message' => $body['message'] ?? 'Success', 'data' => $body );
		}
		return array( 'success' => false, 'message' => $body['message'] ?? "Host returned HTTP {$code}", 'data' => array() );
	}
}
