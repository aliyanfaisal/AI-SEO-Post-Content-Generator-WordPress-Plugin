<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="aiscp-wrap">

	<?php include __DIR__ . '/partials/header.php'; ?>

	<div class="aiscp-page-content">
		<div id="aiscp-license-notice" class="aiscp-notice" style="display:none;"></div>

		<div class="aiscp-license-layout">

			<!-- License Card -->
			<div class="aiscp-card aiscp-license-card">
				<div class="aiscp-card-header">
					<div>
						<h2><?php _e( 'License Activation', 'ai-seo-content-plugin' ); ?></h2>
						<p><?php _e( 'Your license is tied to your domain. No key required — just click Validate.', 'ai-seo-content-plugin' ); ?></p>
					</div>
				</div>
				<div class="aiscp-card-body">

					<!-- Current Status Badge -->
					<div class="aiscp-license-status-badge <?php echo $license_status === 'active' ? 'badge-active' : 'badge-inactive'; ?>">
						<div class="badge-dot"></div>
						<div class="badge-text">
							<strong><?php echo $license_status === 'active' ? __( 'License Active', 'ai-seo-content-plugin' ) : __( 'Not Activated', 'ai-seo-content-plugin' ); ?></strong>
							<span>
								<?php if ( $license_status === 'active' ) : ?>
									<?php _e( 'Your domain has an active subscription.', 'ai-seo-content-plugin' ); ?>
								<?php else : ?>
									<?php _e( 'No active subscription found for this domain.', 'ai-seo-content-plugin' ); ?>
								<?php endif; ?>
							</span>
						</div>
					</div>

					<!-- Domain Info -->
					<div class="aiscp-field">
						<label><?php _e( 'Registered Domain', 'ai-seo-content-plugin' ); ?></label>
						<div class="aiscp-domain-display">
							<span class="domain-icon">🌐</span>
							<strong><?php echo esc_html( home_url() ); ?></strong>
						</div>
						<span class="aiscp-field-hint"><?php _e( 'This is the domain that will be checked against your subscription on our platform.', 'ai-seo-content-plugin' ); ?></span>
					</div>

					<!-- Actions -->
					<div class="aiscp-license-actions">
						<?php if ( $license_status !== 'active' ) : ?>
						<button type="button" id="aiscp-activate-btn" class="aiscp-btn aiscp-btn-primary">
							<span class="btn-text"><?php _e( 'Validate License', 'ai-seo-content-plugin' ); ?></span>
							<span class="btn-spinner" style="display:none;">⏳</span>
						</button>
						<?php else : ?>
						<button type="button" id="aiscp-deactivate-btn" class="aiscp-btn aiscp-btn-danger">
							<?php _e( 'Deactivate', 'ai-seo-content-plugin' ); ?>
						</button>
						<button type="button" id="aiscp-revalidate-btn" class="aiscp-btn aiscp-btn-ghost">
							<?php _e( 'Re-validate', 'ai-seo-content-plugin' ); ?>
						</button>
						<?php endif; ?>
						<a href="https://jetben.com" target="_blank" class="aiscp-btn aiscp-btn-ghost">
							<?php _e( 'Purchase a Subscription →', 'ai-seo-content-plugin' ); ?>
						</a>
					</div>

				</div>
			</div>

			<!-- Sidebar -->
			<div class="aiscp-license-sidebar">

				<?php if ( $license_status === 'active' && ! empty( $license_data ) ) : ?>
				<div class="aiscp-card">
					<div class="aiscp-card-header">
						<div><h2><?php _e( 'Plan Details', 'ai-seo-content-plugin' ); ?></h2></div>
					</div>
					<div class="aiscp-card-body aiscp-plan-details">
						<div class="plan-detail-row">
							<span><?php _e( 'Plan', 'ai-seo-content-plugin' ); ?></span>
							<strong><?php echo esc_html( $license_data['plan'] ?? __( 'Standard', 'ai-seo-content-plugin' ) ); ?></strong>
						</div>
						<div class="plan-detail-row">
							<span><?php _e( 'Posts / Month', 'ai-seo-content-plugin' ); ?></span>
							<strong><?php echo esc_html( $license_data['posts_limit'] ?? '10' ); ?></strong>
						</div>
						<div class="plan-detail-row">
							<span><?php _e( 'Expires', 'ai-seo-content-plugin' ); ?></span>
							<strong><?php echo esc_html( $license_data['expires'] ?? __( 'N/A', 'ai-seo-content-plugin' ) ); ?></strong>
						</div>
					</div>
				</div>
				<?php endif; ?>

				<!-- Plans Overview -->
				<div class="aiscp-card aiscp-plans-card">
					<div class="aiscp-card-header">
						<div><h2><?php _e( 'Available Plans', 'ai-seo-content-plugin' ); ?></h2></div>
					</div>
					<div class="aiscp-card-body">
						<div class="aiscp-plan-tier">
							<div class="plan-tier-name"><?php _e( 'Starter', 'ai-seo-content-plugin' ); ?></div>
							<ul class="plan-tier-features">
								<li>✅ <?php _e( '10 posts/month', 'ai-seo-content-plugin' ); ?></li>
								<li>✅ <?php _e( 'Keyword targeting', 'ai-seo-content-plugin' ); ?></li>
								<li>✅ <?php _e( 'Hebrew SEO content', 'ai-seo-content-plugin' ); ?></li>
								<li>✅ <?php _e( 'Stock images', 'ai-seo-content-plugin' ); ?></li>
							</ul>
						</div>
						<div class="aiscp-plan-tier plan-featured">
							<div class="plan-tier-name"><?php _e( 'Professional', 'ai-seo-content-plugin' ); ?> <span class="plan-badge"><?php _e( 'Popular', 'ai-seo-content-plugin' ); ?></span></div>
							<ul class="plan-tier-features">
								<li>✅ <?php _e( '50 posts/month', 'ai-seo-content-plugin' ); ?></li>
								<li>✅ <?php _e( 'AI-generated media', 'ai-seo-content-plugin' ); ?></li>
								<li>✅ <?php _e( 'Competitor analysis', 'ai-seo-content-plugin' ); ?></li>
								<li>✅ <?php _e( 'Fact-checking layer', 'ai-seo-content-plugin' ); ?></li>
							</ul>
						</div>
						<div class="aiscp-plan-tier">
							<div class="plan-tier-name"><?php _e( 'Agency', 'ai-seo-content-plugin' ); ?></div>
							<ul class="plan-tier-features">
								<li>✅ <?php _e( 'Unlimited posts', 'ai-seo-content-plugin' ); ?></li>
								<li>✅ <?php _e( 'Multi-site support', 'ai-seo-content-plugin' ); ?></li>
								<li>✅ <?php _e( 'Priority support', 'ai-seo-content-plugin' ); ?></li>
								<li>✅ <?php _e( 'White-label option', 'ai-seo-content-plugin' ); ?></li>
							</ul>
						</div>
						<a href="https://jetben.com/#pricing" target="_blank" class="aiscp-btn aiscp-btn-primary" style="width:100%;text-align:center;margin-top:12px;">
							<?php _e( 'View Pricing →', 'ai-seo-content-plugin' ); ?>
						</a>
					</div>
				</div>

			</div><!-- .aiscp-license-sidebar -->
		</div><!-- .aiscp-license-layout -->
	</div><!-- .aiscp-page-content -->
</div><!-- .aiscp-wrap -->
