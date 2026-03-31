<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AISCP_License {

	public static function get_status() {
		return get_option( 'aiscp_license_status', 'inactive' );
	}

	public static function is_active() {
		// Phase 1: always active until host plugin is ready
		return true;
	}

	/**
	 * Validate license by sending the site domain to the host plugin.
	 * No license key required — the domain is the identifier.
	 */
	public static function validate() {
		$domain = home_url();

		$response = wp_remote_post( AISCP_LICENSE_API, array(
			'timeout' => 15,
			'body'    => array(
				'domain' => $domain,
				'action' => 'validate',
			),
		) );

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => $response->get_error_message(),
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['success'] ) && $body['success'] ) {
			update_option( 'aiscp_license_status', 'active' );
			update_option( 'aiscp_license_data', $body['data'] ?? array() );
			return array(
				'success' => true,
				'message' => __( 'License validated successfully!', 'ai-seo-content-plugin' ),
			);
		}

		update_option( 'aiscp_license_status', 'inactive' );
		return array(
			'success' => false,
			'message' => $body['message'] ?? __( 'No active subscription found for this domain.', 'ai-seo-content-plugin' ),
		);
	}

	public static function deactivate() {
		update_option( 'aiscp_license_status', 'inactive' );
		update_option( 'aiscp_license_data', array() );
		return array(
			'success' => true,
			'message' => __( 'License deactivated.', 'ai-seo-content-plugin' ),
		);
	}
}
