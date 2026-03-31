<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="aiscp-wrap">

	<?php include __DIR__ . '/partials/header.php'; ?>

	<div class="aiscp-page-content">
		<div id="aiscp-interval-notice" class="aiscp-notice" style="display:none;"></div>

		<div class="aiscp-interval-layout">

			<!-- Left: Settings -->
			<div class="aiscp-interval-main">

				<!-- Schedule Card -->
				<div class="aiscp-card">
					<div class="aiscp-card-header">
						<div>
							<h2><?php _e( 'Generation Schedule', 'ai-seo-content-plugin' ); ?></h2>
							
						</div>
					</div>
					<div class="aiscp-card-body">

						<!-- Enable toggle -->
						<div class="aiscp-toggle-row">
							<div class="toggle-row-info">
								<strong><?php _e( 'Enable Scheduled Generation', 'ai-seo-content-plugin' ); ?></strong>
								<span><?php _e( 'Turn off to pause all automatic post generation.', 'ai-seo-content-plugin' ); ?></span>
							</div>
							<div class="aiscp-toggle">
								<input type="checkbox" id="cron_enabled" name="cron_enabled" value="1" <?php checked( get_option( 'aiscp_cron_enabled', '1' ), '1' ); ?>>
								<span class="toggle-slider"></span>
							</div>
						</div>

						<div class="aiscp-field-row cols-3" style="margin-top:20px;">
							<div class="aiscp-field">
								<label for="cron_interval"><?php _e( 'Run Interval', 'ai-seo-content-plugin' ); ?></label>
								<select id="cron_interval" name="cron_interval">
									<?php
									$intervals = array(
										'daily'        => __( 'Every Day', 'ai-seo-content-plugin' ),
										'every_3_days' => __( 'Every 3 Days', 'ai-seo-content-plugin' ),
										'weekly'       => __( 'Every Week', 'ai-seo-content-plugin' ),
										'biweekly'     => __( 'Every 2 Weeks', 'ai-seo-content-plugin' ),
										'monthly'      => __( 'Every Month', 'ai-seo-content-plugin' ),
									);
									$current = get_option( 'aiscp_cron_interval', 'weekly' );
									foreach ( $intervals as $val => $label ) : ?>
									<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current, $val ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
								
							</div>
							<div class="aiscp-field">
								<label for="cron_start_time"><?php _e( 'Start Time', 'ai-seo-content-plugin' ); ?></label>
								<input type="time" id="cron_start_time" name="cron_start_time" value="<?php echo esc_attr( get_option( 'aiscp_cron_start_time', '08:00' ) ); ?>">
								
							</div>
							<div class="aiscp-field">
								<label for="cron_posts_per_run"><?php _e( 'Posts per Cycle', 'ai-seo-content-plugin' ); ?></label>
								<input type="number" id="cron_posts_per_run" name="cron_posts_per_run" min="1" max="50" value="<?php echo esc_attr( get_option( 'aiscp_cron_posts_per_run', '5' ) ); ?>">
								<span class="aiscp-field-hint"><?php _e( 'Generated one-by-one via batching.', 'ai-seo-content-plugin' ); ?></span>
							</div>
						</div>

					</div>
				</div>

				<!-- Generate a Test Post -->
				<div class="aiscp-card">
					<div class="aiscp-card-header">
						<div>
							<h2><?php _e( 'Generate a Test Post', 'ai-seo-content-plugin' ); ?></h2>
							
						</div>
					</div>
					<div class="aiscp-card-body">
						<div class="aiscp-test-post-body">
							<div class="test-post-info">
								<div class="test-info-row">
									<span><?php _e( 'AI Model', 'ai-seo-content-plugin' ); ?></span>
									<strong><?php echo esc_html( ucfirst( get_option( 'aiscp_ai_model', 'openai' ) ) ); ?></strong>
								</div>
								<div class="test-info-row">
									<span><?php _e( 'Publish Mode', 'ai-seo-content-plugin' ); ?></span>
									<strong><?php echo esc_html( ucfirst( get_option( 'aiscp_publish_mode', 'pending' ) ) ); ?></strong>
								</div>
								<div class="test-info-row">
									<span><?php _e( 'Language', 'ai-seo-content-plugin' ); ?></span>
									<strong><?php echo esc_html( strtoupper( get_option( 'aiscp_content_language', 'he' ) ) ); ?></strong>
								</div>
								<div class="test-info-row">
									<span><?php _e( 'Keywords Set', 'ai-seo-content-plugin' ); ?></span>
									<strong><?php echo count( array_filter( explode( "\n", get_option( 'aiscp_target_keywords', '' ) ) ) ); ?></strong>
								</div>
							</div>
							<div class="test-post-action">
								
								<button type="button" id="aiscp-test-post-btn" class="aiscp-btn aiscp-btn-primary">
									<span class="btn-text">✨ <?php _e( 'Generate Test Post', 'ai-seo-content-plugin' ); ?></span>
									<span class="btn-spinner" style="display:none;">⏳</span>
								</button>
								<div id="aiscp-test-post-result" style="display:none;margin-top:12px;"></div>
							</div>
						</div>
					</div>
				</div>

				<!-- Save -->
				<div class="aiscp-form-footer">
					<button type="button" id="aiscp-interval-save-btn" class="aiscp-btn aiscp-btn-primary">
						<span class="btn-text"><?php _e( 'Save & Reschedule', 'ai-seo-content-plugin' ); ?></span>
						<span class="btn-spinner" style="display:none;">⏳</span>
					</button>
				</div>

			</div><!-- .aiscp-interval-main -->

			<!-- Right: Status sidebar -->
			<div class="aiscp-interval-sidebar">

				<!-- Cron Status -->
				<div class="aiscp-card">
					<div class="aiscp-card-header">
						<div><h2><?php _e( 'Cron Status', 'ai-seo-content-plugin' ); ?></h2></div>
					</div>
					<div class="aiscp-card-body aiscp-cron-status-body">
						<div class="cron-status-row">
							<span><?php _e( 'Status', 'ai-seo-content-plugin' ); ?></span>
							<?php if ( $next_run ) : ?>
								<span class="cron-badge cron-badge--active"><?php _e( 'Scheduled', 'ai-seo-content-plugin' ); ?></span>
							<?php else : ?>
								<span class="cron-badge cron-badge--idle"><?php _e( 'Not Scheduled', 'ai-seo-content-plugin' ); ?></span>
							<?php endif; ?>
						</div>
						<div class="cron-status-row">
							<span><?php _e( 'Next Run', 'ai-seo-content-plugin' ); ?></span>
							<strong><?php echo $next_run ? esc_html( date_i18n( 'D, M j @ H:i', $next_run ) ) : '—'; ?></strong>
						</div>
						<div class="cron-status-row">
							<span><?php _e( 'Interval', 'ai-seo-content-plugin' ); ?></span>
							<strong><?php echo esc_html( get_option( 'aiscp_cron_interval', 'weekly' ) ); ?></strong>
						</div>
						<div class="cron-status-row">
							<span><?php _e( 'Posts / Cycle', 'ai-seo-content-plugin' ); ?></span>
							<strong><?php echo esc_html( get_option( 'aiscp_cron_posts_per_run', '5' ) ); ?></strong>
						</div>
						<button type="button" id="aiscp-run-now-btn" class="aiscp-btn aiscp-btn-ghost" style="width:100%;margin-top:16px;">
							<span class="btn-text">⚡ <?php _e( 'Run Full Cycle Now', 'ai-seo-content-plugin' ); ?></span>
							<span class="btn-spinner" style="display:none;">⏳</span>
						</button>
					</div>
				</div>

				<!-- Queue Status -->
				<div class="aiscp-card">
					<div class="aiscp-card-header">
						<div><h2><?php _e( 'Current Queue', 'ai-seo-content-plugin' ); ?></h2></div>
					</div>
					<div class="aiscp-card-body">
						<?php
						$queue = AISCP_Cron::get_queue();
						if ( empty( $queue ) ) : ?>
							<p class="aiscp-empty-msg"><?php _e( 'No active batch queue.', 'ai-seo-content-plugin' ); ?></p>
						<?php else :
							$done       = count( array_filter( $queue, fn($j) => $j['status'] === 'done' ) );
							$failed     = count( array_filter( $queue, fn($j) => $j['status'] === 'failed' ) );
							$pending    = count( array_filter( $queue, fn($j) => $j['status'] === 'pending' ) );
							$processing = count( array_filter( $queue, fn($j) => $j['status'] === 'processing' ) );
							$total      = count( $queue );
							$progress   = $total > 0 ? round( ( $done + $failed ) / $total * 100 ) : 0;
						?>
							<div class="aiscp-queue-progress">
								<div class="queue-progress-bar">
									<div class="queue-progress-fill" style="width:<?php echo $progress; ?>%"></div>
								</div>
								<span class="queue-progress-label"><?php echo $done + $failed; ?> / <?php echo $total; ?> <?php _e( 'processed', 'ai-seo-content-plugin' ); ?></span>
							</div>
							<div class="aiscp-queue-stats">
								<div class="queue-stat queue-stat--done"><strong><?php echo $done; ?></strong><span><?php _e( 'Done', 'ai-seo-content-plugin' ); ?></span></div>
								<div class="queue-stat queue-stat--processing"><strong><?php echo $processing; ?></strong><span><?php _e( 'Running', 'ai-seo-content-plugin' ); ?></span></div>
								<div class="queue-stat queue-stat--pending"><strong><?php echo $pending; ?></strong><span><?php _e( 'Pending', 'ai-seo-content-plugin' ); ?></span></div>
								<div class="queue-stat queue-stat--failed"><strong><?php echo $failed; ?></strong><span><?php _e( 'Failed', 'ai-seo-content-plugin' ); ?></span></div>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<!-- Activity Log -->
				<div class="aiscp-card">
					<div class="aiscp-card-header aiscp-card-header--flex">
						<div><h2><?php _e( 'Activity Log', 'ai-seo-content-plugin' ); ?></h2></div>
						<button type="button" id="aiscp-clear-log-btn" class="aiscp-btn aiscp-btn-danger-outline">
							<?php _e( 'Clear Log', 'ai-seo-content-plugin' ); ?>
						</button>
					</div>
					<div class="aiscp-card-body aiscp-log-body">
						<?php $log = AISCP_Cron::get_log();
						if ( empty( $log ) ) : ?>
							<p class="aiscp-empty-msg"><?php _e( 'No activity yet.', 'ai-seo-content-plugin' ); ?></p>
						<?php else : ?>
							<ul class="aiscp-log-list">
								<?php foreach ( array_slice( $log, 0, 15 ) as $entry ) : ?>
								<li>
									<span class="log-time"><?php echo esc_html( $entry['time'] ); ?></span>
									<span class="log-msg"><?php echo esc_html( $entry['message'] ); ?></span>
								</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				</div>

			</div><!-- .aiscp-interval-sidebar -->
		</div><!-- .aiscp-interval-layout -->
	</div><!-- .aiscp-page-content -->
</div>
