<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="aiscp-wrap">

	<?php include __DIR__ . '/partials/header.php'; ?>

	<div class="aiscp-page-content">

		<!-- Status Banner -->
		<div class="aiscp-status-bar <?php echo $license_status === 'active' ? 'status-active' : 'status-inactive'; ?>">
			<span class="status-dot"></span>
			<?php if ( $license_status === 'active' ) : ?>
				<?php _e( 'License Active — Plugin is running', 'ai-seo-content-plugin' ); ?>
			<?php else : ?>
				<?php _e( 'License not activated — Plugin is in demo mode', 'ai-seo-content-plugin' ); ?>
				<a href="<?php echo admin_url( 'admin.php?page=aiscp-license' ); ?>" class="status-action-link"><?php _e( 'Activate Now →', 'ai-seo-content-plugin' ); ?></a>
			<?php endif; ?>
		</div>

		<!-- Stats Grid -->
		<div class="aiscp-stats-grid">
			<div class="aiscp-stat-card">
				<div class="stat-icon">✍️</div>
				<div class="stat-body">
					<div class="stat-number"><?php echo esc_html( $stats['posts_generated'] ); ?></div>
					<div class="stat-label"><?php _e( 'Total Posts Generated', 'ai-seo-content-plugin' ); ?></div>
				</div>
			</div>
			<div class="aiscp-stat-card">
				<div class="stat-icon">📅</div>
				<div class="stat-body">
					<div class="stat-number"><?php echo esc_html( $stats['posts_this_month'] ); ?> / <?php echo esc_html( $stats['posts_limit'] ); ?></div>
					<div class="stat-label"><?php _e( 'Posts This Month', 'ai-seo-content-plugin' ); ?></div>
					<div class="stat-progress-wrap">
						<div class="stat-progress-bar">
							<div class="stat-progress-fill" style="width: <?php echo $stats['posts_limit'] > 0 ? min( 100, round( $stats['posts_this_month'] / $stats['posts_limit'] * 100 ) ) : 0; ?>%"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="aiscp-stat-card">
				<div class="stat-icon">🔑</div>
				<div class="stat-body">
					<div class="stat-number"><?php echo esc_html( $stats['keywords_tracked'] ); ?></div>
					<div class="stat-label"><?php _e( 'Keywords Tracked', 'ai-seo-content-plugin' ); ?></div>
				</div>
			</div>
			<div class="aiscp-stat-card">
				<div class="stat-icon">⏰</div>
				<div class="stat-body">
					<div class="stat-number stat-number--sm"><?php echo esc_html( $stats['last_generated'] ); ?></div>
					<div class="stat-label"><?php _e( 'Last Post Generated', 'ai-seo-content-plugin' ); ?></div>
				</div>
			</div>
		</div>

		<!-- Quick Actions -->
		<div class="aiscp-section-title"><?php _e( 'Quick Actions', 'ai-seo-content-plugin' ); ?></div>
		<div class="aiscp-quick-actions">
			<a href="<?php echo admin_url( 'admin.php?page=aiscp-settings' ); ?>" class="aiscp-action-card">
				<div class="action-icon">⚙️</div>
				<div class="action-text">
					<strong><?php _e( 'Configure Settings', 'ai-seo-content-plugin' ); ?></strong>
					<span><?php _e( 'Keywords, tone, publishing preferences', 'ai-seo-content-plugin' ); ?></span>
				</div>
				<div class="action-arrow">→</div>
			</a>
			<a href="<?php echo admin_url( 'admin.php?page=aiscp-license' ); ?>" class="aiscp-action-card">
				<div class="action-icon">🔐</div>
				<div class="action-text">
					<strong><?php _e( 'Manage License', 'ai-seo-content-plugin' ); ?></strong>
					<span><?php _e( 'Activate, deactivate or check your plan', 'ai-seo-content-plugin' ); ?></span>
				</div>
				<div class="action-arrow">→</div>
			</a>
			<a href="<?php echo admin_url( 'edit.php' ); ?>" class="aiscp-action-card">
				<div class="action-icon">📝</div>
				<div class="action-text">
					<strong><?php _e( 'View Generated Posts', 'ai-seo-content-plugin' ); ?></strong>
					<span><?php _e( 'Review and publish AI-written articles', 'ai-seo-content-plugin' ); ?></span>
				</div>
				<div class="action-arrow">→</div>
			</a>
		</div>

		<!-- Info Box -->
		<div class="aiscp-info-box">
			<div class="info-icon">💡</div>
			<div class="info-text">
				<strong><?php _e( 'Getting Started', 'ai-seo-content-plugin' ); ?></strong>
				<p><?php _e( 'Configure your target keywords and writing preferences in Settings, then activate your license to begin generating SEO-optimized content automatically.', 'ai-seo-content-plugin' ); ?></p>
			</div>
		</div>

	</div><!-- .aiscp-page-content -->
</div><!-- .aiscp-wrap -->
