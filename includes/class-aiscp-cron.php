<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AISCP_Cron
 *
 * Handles scheduling, batching, and execution of AI post generation.
 *
 * Flow:
 *  1. Main cron fires at the user-defined interval + start time.
 *  2. It queues N posts (cron_posts_per_run) into a batch queue.
 *  3. A separate "batch processor" cron fires every 2 minutes and
 *     generates ONE post per run, chaining itself until the queue is empty.
 *  4. On the next main interval the cycle repeats.
 */
class AISCP_Cron {

	/** Hook name for the main interval trigger */
	const MAIN_HOOK = 'aiscp_cron_main';

	/** Hook name for the per-batch processor */
	const BATCH_HOOK = 'aiscp_cron_batch_process';

	/** Option key that stores the pending batch queue */
	const QUEUE_OPTION = 'aiscp_batch_queue';

	/** Option key for batch run log */
	const LOG_OPTION = 'aiscp_batch_log';

	public function init() {
		add_action( self::MAIN_HOOK,  array( $this, 'handle_main_cron' ) );
		add_action( self::BATCH_HOOK, array( $this, 'handle_batch_process' ) );
		add_filter( 'cron_schedules',  array( $this, 'register_intervals' ) );
		add_action( 'wp_ajax_aiscp_save_interval_settings', array( $this, 'ajax_save_interval_settings' ) );
		add_action( 'wp_ajax_aiscp_run_now', array( $this, 'ajax_run_now' ) );
	}

	// ---------------------------------------------------------------
	// Register custom WP-Cron intervals
	// ---------------------------------------------------------------
	public function register_intervals( $schedules ) {
		$schedules['aiscp_every_2_minutes'] = array(
			'interval' => 2 * MINUTE_IN_SECONDS,
			'display'  => __( 'Every 2 Minutes (AISCP batch)', 'ai-seo-content-plugin' ),
		);
		$schedules['aiscp_daily'] = array(
			'interval' => DAY_IN_SECONDS,
			'display'  => __( 'Daily', 'ai-seo-content-plugin' ),
		);
		$schedules['aiscp_every_3_days'] = array(
			'interval' => 3 * DAY_IN_SECONDS,
			'display'  => __( 'Every 3 Days', 'ai-seo-content-plugin' ),
		);
		$schedules['aiscp_weekly'] = array(
			'interval' => WEEK_IN_SECONDS,
			'display'  => __( 'Weekly', 'ai-seo-content-plugin' ),
		);
		$schedules['aiscp_biweekly'] = array(
			'interval' => 2 * WEEK_IN_SECONDS,
			'display'  => __( 'Every 2 Weeks', 'ai-seo-content-plugin' ),
		);
		$schedules['aiscp_monthly'] = array(
			'interval' => 30 * DAY_IN_SECONDS,
			'display'  => __( 'Monthly', 'ai-seo-content-plugin' ),
		);
		return $schedules;
	}

	// ---------------------------------------------------------------
	// Schedule / unschedule helpers
	// ---------------------------------------------------------------
	public static function schedule() {
		if ( ! AISCP_License::is_active() ) return;

		$interval   = get_option( 'aiscp_cron_interval', 'weekly' );
		$start_time = get_option( 'aiscp_cron_start_time', '08:00' );
		$enabled    = get_option( 'aiscp_cron_enabled', '1' );

		if ( $enabled !== '1' ) return;

		// Calculate the first run timestamp based on today + start_time
		$timestamp = self::next_run_timestamp( $start_time );

		if ( ! wp_next_scheduled( self::MAIN_HOOK ) ) {
			wp_schedule_event( $timestamp, 'aiscp_' . $interval, self::MAIN_HOOK );
		}
	}

	public static function unschedule() {
		wp_clear_scheduled_hook( self::MAIN_HOOK );
		wp_clear_scheduled_hook( self::BATCH_HOOK );
	}

	public static function reschedule() {
		self::unschedule();
		self::schedule();
	}

	/**
	 * Calculate next run timestamp for a given HH:MM time today (or tomorrow if past).
	 */
	private static function next_run_timestamp( $time_str ) {
		list( $hour, $minute ) = array_map( 'intval', explode( ':', $time_str ) );
		$now    = current_time( 'timestamp' );
		$target = mktime( $hour, $minute, 0, date( 'n', $now ), date( 'j', $now ), date( 'Y', $now ) );
		if ( $target <= $now ) {
			$target += DAY_IN_SECONDS;
		}
		return $target;
	}

	// ---------------------------------------------------------------
	// Main cron handler — builds the batch queue
	// ---------------------------------------------------------------
	public function handle_main_cron() {
		if ( ! AISCP_License::is_active() ) return;

		$posts_to_generate = (int) get_option( 'aiscp_cron_posts_per_run', 5 );
		if ( $posts_to_generate < 1 ) return;

		// Build a queue of jobs (one entry per post to generate)
		$queue = array();
		for ( $i = 0; $i < $posts_to_generate; $i++ ) {
			$queue[] = array(
				'job_id'    => uniqid( 'aiscp_', true ),
				'queued_at' => current_time( 'mysql' ),
				'status'    => 'pending',
			);
		}

		update_option( self::QUEUE_OPTION, $queue );
		self::log( sprintf( 'Main cron fired. Queued %d posts for generation.', $posts_to_generate ) );

		// Kick off the batch processor immediately
		if ( ! wp_next_scheduled( self::BATCH_HOOK ) ) {
			wp_schedule_event( time() + 10, 'aiscp_every_2_minutes', self::BATCH_HOOK );
		}
	}

	// ---------------------------------------------------------------
	// Batch processor — runs every 2 min, processes ONE post per run
	// ---------------------------------------------------------------
	public function handle_batch_process() {
		if ( ! AISCP_License::is_active() ) {
			self::unschedule();
			return;
		}

		$queue = get_option( self::QUEUE_OPTION, array() );

		// Find the first pending job
		$pending_index = null;
		foreach ( $queue as $index => $job ) {
			if ( $job['status'] === 'pending' ) {
				$pending_index = $index;
				break;
			}
		}

		// No pending jobs — stop the batch processor
		if ( $pending_index === null ) {
			wp_clear_scheduled_hook( self::BATCH_HOOK );
			update_option( self::QUEUE_OPTION, array() );
			self::log( 'Batch complete. All posts generated. Batch processor stopped.' );
			return;
		}

		// Mark as processing
		$queue[ $pending_index ]['status']       = 'processing';
		$queue[ $pending_index ]['started_at']   = current_time( 'mysql' );
		update_option( self::QUEUE_OPTION, $queue );

		// Generate the post
		$result = $this->generate_post( $queue[ $pending_index ] );

		// Mark as done or failed
		$queue[ $pending_index ]['status']       = $result ? 'done' : 'failed';
		$queue[ $pending_index ]['completed_at'] = current_time( 'mysql' );
		update_option( self::QUEUE_OPTION, $queue );

		$remaining = count( array_filter( $queue, fn( $j ) => $j['status'] === 'pending' ) );
		self::log( sprintf(
			'Processed job %s — %s. %d posts remaining in queue.',
			$queue[ $pending_index ]['job_id'],
			$result ? 'success' : 'failed',
			$remaining
		) );
	}

	// ---------------------------------------------------------------
	// Post generation — async request to host via webhook pattern
	// ---------------------------------------------------------------
	private function generate_post( $job ) {
		// Send async request to host — returns immediately with job_id
		// The actual post insertion happens in AISCP_Webhook when host calls back
		$result = AISCP_Host_Connector::request_generate_post( array( 'batch_job_id' => $job['job_id'] ) );

		if ( ! $result['success'] ) {
			self::log( sprintf( 'Host API error for job %s: %s', $job['job_id'], $result['message'] ) );
			return false;
		}

		self::log( sprintf( 'Job %s sent to host. Host job ID: %s', $job['job_id'], $result['data']['job_id'] ?? 'unknown' ) );
		return true;
	}

	// ---------------------------------------------------------------
	// Logging helper
	// ---------------------------------------------------------------
	public static function log( $message ) {
		$log   = get_option( self::LOG_OPTION, array() );
		$log[] = array(
			'time'    => current_time( 'mysql' ),
			'message' => $message,
		);
		// Keep last 50 entries only
		if ( count( $log ) > 50 ) {
			$log = array_slice( $log, -50 );
		}
		update_option( self::LOG_OPTION, $log );
	}

	public static function get_log() {
		return array_reverse( get_option( self::LOG_OPTION, array() ) );
	}

	public static function get_queue() {
		return get_option( self::QUEUE_OPTION, array() );
	}

	// ---------------------------------------------------------------
	// AJAX: save interval settings & reschedule
	// ---------------------------------------------------------------
	public function ajax_save_interval_settings() {
		check_ajax_referer( 'aiscp_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( -1 );

		$interval   = sanitize_text_field( $_POST['cron_interval'] ?? 'weekly' );
		$start_time = sanitize_text_field( $_POST['cron_start_time'] ?? '08:00' );
		$posts      = absint( $_POST['cron_posts_per_run'] ?? 5 );
		$enabled    = isset( $_POST['cron_enabled'] ) ? '1' : '0';

		update_option( 'aiscp_cron_interval', $interval );
		update_option( 'aiscp_cron_start_time', $start_time );
		update_option( 'aiscp_cron_posts_per_run', $posts );
		update_option( 'aiscp_cron_enabled', $enabled );

		self::reschedule();

		$next = wp_next_scheduled( self::MAIN_HOOK );
		wp_send_json_success( array(
			'message'  => __( 'Interval settings saved and cron rescheduled!', 'ai-seo-content-plugin' ),
			'next_run' => $next ? date_i18n( 'D, M j Y @ H:i', $next ) : __( 'Not scheduled', 'ai-seo-content-plugin' ),
		) );
	}

	// ---------------------------------------------------------------
	// AJAX: trigger a manual run right now
	// ---------------------------------------------------------------
	public function ajax_run_now() {
		check_ajax_referer( 'aiscp_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( -1 );

		$this->handle_main_cron();
		wp_send_json_success( array( 'message' => __( 'Batch queued! Posts will be generated shortly.', 'ai-seo-content-plugin' ) ) );
	}
}
