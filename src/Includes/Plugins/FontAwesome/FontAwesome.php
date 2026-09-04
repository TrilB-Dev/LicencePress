<?php
/**
 * Font Awesome plugin integration for LicencePress.
 *
 * @package FontAwesome
 * @textdomain licencepress
 * @domainpath Languages
 * @author LicencePress Team
 */

namespace LicencePress\Includes\Plugins\FontAwesome;

use LicencePress\Includes\Plugins\AssetsProviderInterface;
use LicencePress\Includes\Plugins\I18nProviderInterface;
use LicencePress\Includes\Plugins\PluginInterface;
use LicencePress\Includes\Plugins\SettingsProviderInterface;
use LicencePress\Includes\Plugins\SettingsPageProviderInterface;
use LicencePress\Includes\Plugins\FontAwesome\Assets\Assets;
use LicencePress\Includes\Plugins\FontAwesome\Includes\IconPicker;
use LicencePress\Includes\Plugins\FontAwesome\API\FontAwesomeAPI;
use LicencePress\Includes\Plugins\FontAwesome\Includes\Core\I18n;
use LicencePress\Includes\Plugins\FontAwesome\Includes\Includes;
use LicencePress\Includes\Core\WP\Activator;

final class FontAwesome implements PluginInterface, SettingsProviderInterface, SettingsPageProviderInterface, AssetsProviderInterface, I18nProviderInterface {
	/**
	 * Singleton instance of the FontAwesome plugin.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;
	/**
	 * IconPicker instance for the FontAwesome plugin.
	 *
	 * @var IconPicker|null
	 */
	private ?IconPicker $icon_picker = null;
	/**
	 * Check if the FontAwesome library is available.
	 *
	 * @return bool True if the FontAwesome library is available, false otherwise.
	 */
	public function get_slug(): string {
		return 'licencepress-fontawesome';
	}
	/**
	 * Get the plugin name.
	 *
	 * @return string The plugin name.
	 */
	public function get_name(): string {
		return 'FontAwesome';
	}
	/**
	 * Get the plugin icon.
	 *
	 * @return array{0: string, 1: string} The plugin icon class and color.
	 */
	public function get_icon(): array {
		return array( 'fab fa-font-awesome', '#74c1fcff' );
	}
	/**
	 * Get the plugin version.
	 *
	 * @return string The plugin version.
	 */
	public function get_version(): string {
		if ( self::is_wordpress_fontawesome_active() && function_exists( 'FortAwesome\\fa' ) && class_exists( '\\FortAwesome\\FontAwesome' ) ) {
			return \FortAwesome\fa()->version();
		}

		return '1.0.0';
	}
	/**
	 * Get the plugin author.
	 *
	 * @return string The plugin author.
	 */
	public function get_author(): string {
		return 'TrilB.Dev Team';
	}
	/**
	 * Get the plugin author URI.
	 *
	 * @return string The plugin author URI.
	 */
	public function get_author_uri(): string {
		return 'https://trilb.dev';
	}
	/**
	 * Get the plugin description.
	 *
	 * @return string The plugin description.
	 */
	public function get_description(): string {
		return __( 'Provides Font Awesome enqueueing in Admin, Frontend, Login Page, icon picking, and styling APIs for LicencePress.', 'licencepress' );
	}
	/**
	 * Get the plugin URI.
	 *
	 * @return string The plugin URI.
	 */
	public function get_uri(): string {
		return 'https://trilb.dev/collection/web-extension/wordpress/licencepress';
	}
	/**
	 * Get the plugin license.
	 *
	 * @return string The plugin license.
	 */
	public function get_license(): string {
		return 'GPL-2.0-or-later';
	}
	/**
	 * Check if the plugin is active.
	 *
	 * @return bool True if the plugin is active, false otherwise.
	 */
	public function is_active(): bool {
		return true;
	}
	/**
	 * Register the plugin's settings.
	 *
	 * This method registers the settings for the FontAwesome plugin using the
	 * Settings class.
	 */
	public function register_settings(): void {
		Includes::get_instance()->settings()->register();
	}
	/**
	 * Get the settings page for the plugin.
	 *
	 * @return array The settings page configuration.
	 */
	public function get_settings_page(): array {
		return Includes::get_instance()->settings()->get_settings_page();
	}
	/**
	 * Sanitize the plugin's settings.
	 *
	 * @param mixed $input The input settings to sanitize.
	 * @return array The sanitized settings.
	 */
	public function sanitize_settings( $input ): array {
		return Includes::get_instance()->settings()->sanitize( $input );
	}
	/**
	 * Register the plugin's assets.
	 *
	 * This method registers the assets for the FontAwesome plugin using the
	 * Assets class.
	 */
	public function register_assets(): void {
		( new Assets() )->register();
	}
	/**
	 * Load the plugin's text domain for internationalization.
	 *
	 * This method loads the text domain for the FontAwesome plugin using the
	 * I18n class.
	 */
	public function load_textdomain(): void {
		I18n::load_textdomain();
	}
	/**
	 * Check if the FontAwesome library is available.
	 *
	 * @return bool True if the FontAwesome library is available, false otherwise.
	 */
	public function is_available(): bool {
		return self::is_wordpress_fontawesome_active() || ( function_exists( 'FortAwesome\\fa' ) && class_exists( '\\FortAwesome\\FontAwesome' ) );
	}

	/**
	 * Determine whether a FontAwesome instance is already present.
	 *
	 * WordPress FontAwesome wins first, and LicencePress reuses that namespace and
	 * bootstrap instead of loading a second copy from Composer.
	 *
	 * @return bool True when an active FontAwesome loader or plugin is present.
	 */
	public static function is_wordpress_fontawesome_active(): bool {
		if ( function_exists( 'FortAwesome\\fa' ) || class_exists( '\\FortAwesome\\FontAwesome' ) || class_exists( '\\FortAwesome\\FontAwesome_Loader' ) ) {
			return true;
		}

		if ( defined( 'FONTAWESOME_PLUGIN_FILE' ) && function_exists( 'is_plugin_active' ) ) {
			return is_plugin_active( FONTAWESOME_PLUGIN_FILE );
		}

		return false;
	}
	/**
	 * Get the icon picker instance for the FontAwesome plugin.
	 *
	 * @return IconPicker|null The icon picker instance, or null if not available.
	 */
	public function get_icon_picker(): ?IconPicker {
		return $this->icon_picker;
	}
	/**
	 * Get the singleton instance of the FontAwesome plugin.
	 *
	 * @return self The singleton instance of the FontAwesome plugin.
	 */
	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	/**
	 * Prevent direct instantiation of the FontAwesome plugin.
	 */

	/**
	 * Loads the bundled FontAwesome package only when some other active
	 * WordPress FontAwesome integration has not already claimed the namespace.
	 */
	private function load_vendor(): void {
		if ( self::is_wordpress_fontawesome_active() ) {
			return;
		}

		$vendor_file = LICENCEPRESS_DIR . 'vendor/fortawesome/wordpress-fontawesome/index.php';
		if ( is_readable( $vendor_file ) ) {
			require_once $vendor_file;
		}
	}
	/**
	 * Initialize the FontAwesome plugin.
	 *
	 * This method configures the FontAwesome API and initializes the IconPicker
	 * if the FontAwesome library is available. It also initializes the Includes
	 * class for additional functionality.
	 */
	public function init(): void {
		FontAwesomeAPI::configure();

		if ( $this->is_available() ) {

			$this->icon_picker = IconPicker::get_instance();

		}

		Includes::get_instance()->init();
	}
}
