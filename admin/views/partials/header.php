<?php if ( ! defined( 'ABSPATH' ) ) exit;
$current_page = $_GET['page'] ?? 'aiscp-dashboard';
?>
<div class="aiscp-header">
	<div class="aiscp-header-brand">
		<?php /*
		<div class="aiscp-logo">
			<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
				<rect width="32" height="32" rx="8" fill="url(#grad)"/>
				<path d="M8 22L12 10L16 18L20 10L24 22" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
				<defs><linearGradient id="grad" x1="0" y1="0" x2="32" y2="32" gradientUnits="userSpaceOnUse"><stop stop-color="#6366F1"/><stop offset="1" stop-color="#8B5CF6"/></linearGradient></defs>
			</svg>
		</div>
		*/ ?>
		<div class="aiscp-header-titles">
			<h1><?php _e( 'AI SEO Content Plugin', 'ai-seo-content-plugin' ); ?></h1>
			<span class="aiscp-version">v<?php echo AISCP_VERSION; ?> &nbsp;·&nbsp; by Jetben</span>
		</div>
	</div>
	<div class="aiscp-header-nav">
		<a href="<?php echo admin_url( 'admin.php?page=aiscp-dashboard' ); ?>" class="aiscp-nav-item <?php echo $current_page === 'aiscp-dashboard' ? 'active' : ''; ?>">
			<span class="nav-icon">📊</span><?php _e( 'Dashboard', 'ai-seo-content-plugin' ); ?>
		</a>
		<a href="<?php echo admin_url( 'admin.php?page=aiscp-settings' ); ?>" class="aiscp-nav-item <?php echo $current_page === 'aiscp-settings' ? 'active' : ''; ?>">
			<span class="nav-icon">⚙️</span><?php _e( 'Settings', 'ai-seo-content-plugin' ); ?>
		</a>
		<a href="<?php echo admin_url( 'admin.php?page=aiscp-license' ); ?>" class="aiscp-nav-item <?php echo $current_page === 'aiscp-license' ? 'active' : ''; ?>">
			<span class="nav-icon">🔐</span><?php _e( 'License', 'ai-seo-content-plugin' ); ?>
		</a>
		<a href="<?php echo admin_url( 'admin.php?page=aiscp-interval' ); ?>" class="aiscp-nav-item <?php echo $current_page === 'aiscp-interval' ? 'active' : ''; ?>">
			<span class="nav-icon">⏱️</span><?php _e( 'Post Generation', 'ai-seo-content-plugin' ); ?>
		</a>
	</div>
</div>
