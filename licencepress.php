<?php

/**
 * LicencePress - A WordPress Plugin
 *
 * This is the main plugin file for the LicencePress WordPress plugin. It contains the plugin metadata and initializes the plugin by including necessary files and setting up activation and deactivation hooks.
 *
 * Plugin Name:       LicencePress
 * Plugin URI:        https://trilb.dev/collection/web-extension/wordpress/licencepress
 * Description:       LicencePress is a WordPress plugin.
 * Author:            MrTrilB
 * Author URI:        https://trilb.dev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       licencepress
 * Version:           1.0.0
 * Domain Path:       src/languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'LICENCEPRESS_VERSION', '1.0.0' );
define( 'LICENCEPRESS_NAME', 'licencepress' );
define( 'LICENCEPRESS_DEFAULT_LANGUAGE', 'en_GB' );
define( 'LICENCEPRESS_FILE', __FILE__ );
define( 'LICENCEPRESS_DIR', plugin_dir_path( __FILE__ ) );
define( 'LICENCEPRESS_URL', plugin_dir_url( __FILE__ ) );
define( 'LICENCEPRESS_BASENAME', plugin_basename( __FILE__ ) );
define( 'LICENCEPRESS_ROOT', LICENCEPRESS_DIR );
define( 'LICENCEPRESS_ROOT_URL', LICENCEPRESS_URL );
define( 'LICENCEPRESS_API', LICENCEPRESS_DIR . 'src/API' );
define( 'LICENCEPRESS_ASSETS', LICENCEPRESS_DIR . 'src/Assets' );
define( 'LICENCEPRESS_ASSETS_URL', LICENCEPRESS_URL . 'src/Assets' );
define( 'LICENCEPRESS_ADMIN', LICENCEPRESS_DIR . 'src/Admin' );
define( 'LICENCEPRESS_ADMIN_URL', LICENCEPRESS_URL . 'src/Admin' );
define( 'LICENCEPRESS_LANGUAGES', LICENCEPRESS_DIR . 'src/languages' );
define( 'LICENCEPRESS_INCLUDES', LICENCEPRESS_DIR . 'src/includes' );
define( 'LICENCEPRESS_CORE', LICENCEPRESS_INCLUDES . '/Core' );
define( 'LICENCEPRESS_SETTINGS', LICENCEPRESS_INCLUDES . '/Settings' );
define( 'LICENCEPRESS_PLUGINS', LICENCEPRESS_INCLUDES . '/Plugins' );
define( 'LICENCEPRESS_PLUGINS_URL', LICENCEPRESS_URL . 'src/includes/Plugins' );

$licencepress_autoloader = LICENCEPRESS_DIR . 'vendor/autoload.php';
if ( is_readable( $licencepress_autoloader ) ) {
	require_once $licencepress_autoloader;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-licencepress-activator.php
 */
function activate_licencepress() {
	\LicencePress\Includes\Core\WP\Activator::activate();
}

register_activation_hook( __FILE__, 'activate_licencepress' );
/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-licencepress-deactivator.php
 */
function deactivate_licencepress() {
	\LicencePress\Includes\Core\WP\Deactivator::deactivate();
}

register_deactivation_hook( __FILE__, 'deactivate_licencepress' );
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once LICENCEPRESS_DIR . 'src/LicencePress.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_licencepress() {

	$plugin = new \LicencePress\LicencePress( LICENCEPRESS_FILE, LICENCEPRESS_NAME, LICENCEPRESS_VERSION );
	$plugin->run();

}
run_licencepress();
