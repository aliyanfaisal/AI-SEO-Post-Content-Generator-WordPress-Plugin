<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AISCP_Admin {

	public function init() {
		add_action( 'admin_menu', array( $this, 'register_menus' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_aiscp_save_settings', array( $this, 'ajax_save_settings' ) );
		add_action( 'wp_ajax_aiscp_validate_license', array( $this, 'ajax_validate_license' ) );
		add_action( 'wp_ajax_aiscp_deactivate_license', array( $this, 'ajax_deactivate_license' ) );
		add_action( 'wp_ajax_aiscp_test_post',  array( $this, 'ajax_test_post' ) );
		add_action( 'wp_ajax_aiscp_clear_log',   array( $this, 'ajax_clear_log' ) );
	}

	public function register_menus() {
		add_menu_page(
			__( 'AI SEO Content', 'ai-seo-content-plugin' ),
			__( 'AI SEO Content', 'ai-seo-content-plugin' ),
			'manage_options',
			'aiscp-dashboard',
			array( $this, 'render_dashboard' ),
			'dashicons-superhero',
			30
		);

		add_submenu_page(
			'aiscp-dashboard',
			__( 'Dashboard', 'ai-seo-content-plugin' ),
			__( 'Dashboard', 'ai-seo-content-plugin' ),
			'manage_options',
			'aiscp-dashboard',
			array( $this, 'render_dashboard' )
		);

		add_submenu_page(
			'aiscp-dashboard',
			__( 'Settings', 'ai-seo-content-plugin' ),
			__( 'Settings', 'ai-seo-content-plugin' ),
			'manage_options',
			'aiscp-settings',
			array( $this, 'render_settings' )
		);

		add_submenu_page(
			'aiscp-dashboard',
			__( 'License', 'ai-seo-content-plugin' ),
			__( 'License', 'ai-seo-content-plugin' ),
			'manage_options',
			'aiscp-license',
			array( $this, 'render_license' )
		);

		add_submenu_page(
			'aiscp-dashboard',
			__( 'Post Generation', 'ai-seo-content-plugin' ),
			__( 'Post Generation', 'ai-seo-content-plugin' ),
			'manage_options',
			'aiscp-interval',
			array( $this, 'render_interval' )
		);
	}

	public function enqueue_assets( $hook ) {
		if ( strpos( $hook, 'aiscp' ) === false ) return;

		wp_enqueue_style(
			'aiscp-admin',
			AISCP_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			AISCP_VERSION
		);

		wp_enqueue_script(
			'aiscp-admin',
			AISCP_PLUGIN_URL . 'admin/js/admin.js',
			array( 'jquery' ),
			AISCP_VERSION,
			true
		);

		wp_localize_script( 'aiscp-admin', 'AISCP', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'aiscp_nonce' ),
			'strings'  => array(
				'saving'      => __( 'Saving...', 'ai-seo-content-plugin' ),
				'saved'       => __( 'Settings saved!', 'ai-seo-content-plugin' ),
				'validating'  => __( 'Validating...', 'ai-seo-content-plugin' ),
				'error'       => __( 'An error occurred. Please try again.', 'ai-seo-content-plugin' ),
			),
		) );
	}

	public function render_dashboard() {
		$stats = AISCP_Settings::get_stats();
		$license_status = AISCP_License::get_status();
		include AISCP_PLUGIN_DIR . 'admin/views/dashboard.php';
	}

	public function render_settings() {
		include AISCP_PLUGIN_DIR . 'admin/views/settings.php';
	}

	public function render_license() {
		$license_status = AISCP_License::get_status();
		$license_data   = get_option( 'aiscp_license_data', array() );
		include AISCP_PLUGIN_DIR . 'admin/views/license.php';
	}

	public function render_interval() {
		$next_run  = wp_next_scheduled( AISCP_Cron::MAIN_HOOK );
		$queue     = AISCP_Cron::get_queue();
		$log       = AISCP_Cron::get_log();
		include AISCP_PLUGIN_DIR . 'admin/views/interval.php';
	}

	public function ajax_save_settings() {
		check_ajax_referer( 'aiscp_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( -1 );

		$result = AISCP_Settings::save( $_POST );
		wp_send_json_success( array( 'message' => __( 'Settings saved successfully!', 'ai-seo-content-plugin' ) ) );
	}

	public function ajax_validate_license() {
		check_ajax_referer( 'aiscp_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( -1 );

		$result = AISCP_License::validate();
		wp_send_json( $result );
	}

	public function ajax_deactivate_license() {
		check_ajax_referer( 'aiscp_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( -1 );

		$result = AISCP_License::deactivate();
		wp_send_json( $result );
	}

	public function ajax_test_post() {
		check_ajax_referer( 'aiscp_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( -1 );

		// Send async job request to host — returns immediately with job_id
		$result = AISCP_Host_Connector::request_generate_post( array( 'test_mode' => true ) );

		if ( ! $result['success'] ) {
			wp_send_json_error( array( 'message' => $result['message'] ) );
		}

		$job_id = $result['data']['job_id'] ?? 'unknown';
		AISCP_Cron::log( "Test post job queued. Job ID: {$job_id}" );

		wp_send_json_success( array(
			'message' => __( 'Generation job queued! The post will appear shortly in your <a href="edit.php" target="_blank">Posts list</a> once the host processes it.', 'ai-seo-content-plugin' ),
			'job_id'  => $job_id,
		) );
	}

	public function ajax_clear_log() {
		check_ajax_referer( 'aiscp_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( -1 );

		delete_option( 'aiscp_batch_log' );
		delete_option( 'aiscp_batch_queue' );
		delete_option( 'aiscp_last_webhook_error' );
		delete_option( 'aiscp_last_webhook_success' );

		wp_send_json_success( array( 'message' => __( 'Log cleared.', 'ai-seo-content-plugin' ) ) );
	}
}
