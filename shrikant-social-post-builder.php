<?php
/**
 * Plugin Name:       Shrikant Social Post Builder
 * Description:       A professional WordPress plugin that generates ready-to-share social media messages from WordPress posts in one click.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            Shri Kant Gaur
 * Author URI:        https://profiles.wordpress.org/shrikantgaur/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       shrikant-social-post-builder
 * Domain Path:       /languages
 *
 * @package           Shrikant\SocialPostBuilder
 */

namespace Shrikant\SocialPostBuilder;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define Constants.
define( 'SHRIKANT_SPB_VERSION', '1.0.0' );
define( 'SHRIKANT_SPB_PLUGIN_FILE', __FILE__ );
define( 'SHRIKANT_SPB_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'SHRIKANT_SPB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Autoloader for Shrikant Social Post Builder.
 * Maps namespaced classes to files under includes/ using WordPress naming conventions.
 */
spl_autoload_register( function( $class ) {
	$namespace = 'Shrikant\\SocialPostBuilder\\';
	if ( strpos( $class, $namespace ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, strlen( $namespace ) );
	// Match class prefixes, e.g., Shrikant_Database -> class-shrikant-database.php
	$file_parts = explode( '_', $relative_class );
	$file_name  = 'class-' . strtolower( implode( '-', $file_parts ) ) . '.php';
	$file_path  = SHRIKANT_SPB_PLUGIN_PATH . 'includes/' . $file_name;

	if ( file_exists( $file_path ) ) {
		require_once $file_path;
	}
} );

// Load procedural helper functions.
if ( file_exists( SHRIKANT_SPB_PLUGIN_PATH . 'includes/helpers.php' ) ) {
	require_once SHRIKANT_SPB_PLUGIN_PATH . 'includes/helpers.php';
}

/**
 * Main plugin activation hook.
 */
register_activation_hook( __FILE__, function() {
	// Trigger DB table creation and seed data.
	Shrikant_Database::activate();

	// Set version option.
	update_option( 'shrikant_spb_version', SHRIKANT_SPB_VERSION );
} );

/**
 * Initialize the plugin classes.
 */
function init_plugin() {
	// Initialize Settings.
	Shrikant_Settings::get_instance();

	// Initialize AJAX actions.
	Shrikant_Ajax::get_instance();

	// Initialize Admin Menu and Pages.
	if ( is_admin() ) {
		Shrikant_Admin::get_instance();
	}
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\init_plugin' );
