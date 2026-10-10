<?php
/**
 * Plugin Name:       AI SEO Content Generator
 * Plugin URI:        https://aliyanfaisal.com
 * Description:       Generates SEO-optimized blog posts with Anthropic Claude or OpenAI and publishes them on a schedule. Standalone: no external server or license required.
 * Version:           2.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Aliyan Faisal
 * Author URI:        https://aliyanfaisal.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ai-seo-content-generator
 * Domain Path:       /languages
 *
 * @package AISCG
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AISCG_VERSION', '2.0.0' );
define( 'AISCG_PLUGIN_FILE', __FILE__ );
define( 'AISCG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AISCG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AISCG_MIN_PHP', '7.4' );
define( 'AISCG_MIN_WP', '5.8' );

require_once AISCG_PLUGIN_DIR . 'includes/class-aiscg-autoloader.php';
AISCG_Autoloader::register( AISCG_PLUGIN_DIR . 'includes/' );

register_activation_hook( __FILE__, array( 'AISCG_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'AISCG_Plugin', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'AISCG_Plugin', 'boot' ) );
