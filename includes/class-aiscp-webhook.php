<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AISCP_Webhook
 *
 * Registers a REST endpoint that the host plugin POSTs generated post data to.
 * Endpoint: /wp-json/aiscp-client/v1/webhook
 */
class AISCP_Webhook {

	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route( 'aiscp-client/v1', '/webhook', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_webhook' ),
			'permission_callback' => '__return_true',
		) );
	}

	/**
	 * Receive generated post data from host plugin and insert into WordPress.
	 */
	public function handle_webhook( WP_REST_Request $request ) {
		$payload = $request->get_json_params() ?: $request->get_body_params();

		if ( empty( $payload ) ) {
			return new WP_REST_Response( array( 'success' => false, 'message' => 'Empty payload.' ), 400 );
		}

		// Log webhook receipt
		AISCP_Cron::log( sprintf( 'Webhook received. Job: %s', $payload['job_id'] ?? 'unknown' ) );

		// Check if host reported a failure
		if ( empty( $payload['success'] ) ) {
			$error_msg = $payload['message'] ?? 'Host reported generation failure.';
			AISCP_Cron::log( 'Host error: ' . $error_msg );

			// Store last error for UI display
			update_option( 'aiscp_last_webhook_error', array(
				'time'    => current_time( 'mysql' ),
				'message' => $error_msg,
				'job_id'  => $payload['job_id'] ?? '',
			) );

			return new WP_REST_Response( array( 'success' => false, 'message' => $error_msg ), 200 );
		}

		// Insert post
		$post_data = $payload['data'] ?? $payload;
		$post_id   = AISCP_Host_Connector::insert_post( $post_data );

		if ( is_wp_error( $post_id ) ) {
			AISCP_Cron::log( 'Insert failed: ' . $post_id->get_error_message() );
			return new WP_REST_Response( array( 'success' => false, 'message' => $post_id->get_error_message() ), 200 );
		}

		AISCP_Cron::log( sprintf( 'Post inserted successfully. Post ID: %d, Job: %s', $post_id, $payload['job_id'] ?? 'unknown' ) );

		// Store last success for UI display
		update_option( 'aiscp_last_webhook_success', array(
			'time'    => current_time( 'mysql' ),
			'post_id' => $post_id,
			'job_id'  => $payload['job_id'] ?? '',
		) );

		return new WP_REST_Response( array(
			'success' => true,
			'message' => 'Post inserted.',
			'post_id' => $post_id,
		), 200 );
	}
}
