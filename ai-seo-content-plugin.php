<?php
/**
 * Plugin Name: AI SEO Content Plugin
 * Plugin URI:  https://aliyanfaisal.com
 * Description: AI-powered SEO blog post generator. Connects to the Jetben host plugin for AI generation.
 * Version:     1.0.5
 * Author:      Aliyan Faisal
 * Author URI:  https://aliyanfaisal.com
 * Text Domain: ai-seo-content-plugin
 * Domain Path: /languages
 * License:     GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AISCP_VERSION',    '1.0.5' );
define( 'AISCP_PLUGIN_FILE', __FILE__ );
define( 'AISCP_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'AISCP_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );

/**
 * Host plugin base URL.
 * Update this to the domain where the host plugin is installed.
 */
define( 'AISCP_HOST_URL',    'https://benb697.sg-host.com' );
define( 'AISCP_LICENSE_API', 'https://benb697.sg-host.com/wp-json/aiscp/v1/license/validate' );

require_once AISCP_PLUGIN_DIR . 'includes/class-aiscp-license.php';
require_once AISCP_PLUGIN_DIR . 'includes/class-aiscp-settings.php';
require_once AISCP_PLUGIN_DIR . 'includes/class-aiscp-host-connector.php';
require_once AISCP_PLUGIN_DIR . 'includes/class-aiscp-webhook.php';
require_once AISCP_PLUGIN_DIR . 'includes/class-aiscp-seo.php';
require_once AISCP_PLUGIN_DIR . 'includes/class-aiscp-cron.php';
require_once AISCP_PLUGIN_DIR . 'includes/class-aiscp-admin.php';

register_activation_hook( __FILE__, 'aiscp_activate' );
register_deactivation_hook( __FILE__, 'aiscp_deactivate' );

function aiscp_activate() {
	$defaults = array(
		'license_status'       => 'active', // Active by default — Phase 1
		'target_keywords'      => '',
		'negative_keywords'    => '',
		'writing_style'        => 'professional',
		'tone'                 => 'informative',
		'tone_examples'        => '',
		'content_restrictions' => '',
		'competitor_domains'   => '',
		'publish_mode'         => 'pending',
		'enable_thumbnails'    => '1',
		'enable_stock_images'  => '1',
		'internal_links'       => '',
		'sitemap_url'          => '',
		'content_language'     => 'he',
		'ai_model'             => 'openai',
		'posts_per_month'      => '10',
		'auto_categorize'      => '1',
		'fact_checking'        => '1',
		'cron_interval'        => 'weekly',
		'cron_start_time'      => '08:00',
		'cron_posts_per_run'   => '5',
		'cron_enabled'         => '1',
	);
	foreach ( $defaults as $key => $value ) {
		if ( false === get_option( 'aiscp_' . $key ) ) {
			update_option( 'aiscp_' . $key, $value );
		}
	}
	AISCP_Cron::schedule();
}

function aiscp_deactivate() {
	AISCP_Cron::unschedule();
}

function aiscp_init() {
	$webhook = new AISCP_Webhook();
	$webhook->init();
	$cron  = new AISCP_Cron();
	$cron->init();
	$admin = new AISCP_Admin();
	$admin->init();
}
add_action( 'plugins_loaded', 'aiscp_init' );
