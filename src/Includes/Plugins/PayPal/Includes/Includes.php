<?php
/**
 * PayPal Plugin Includes
 *
 * @package LicencePress
 * @subpackage Plugins\PayPal\Includes
 * @since 1.0.0
 */

namespace LicencePress\Includes\Plugins\PayPal\Includes;

use LicencePress\Includes\Plugins\PayPal\Includes\Settings\Settings;

final class Includes {
	/**
	 * Singleton instance of the Includes class.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;
	/**
	 * The settings instance.
	 *
	 * @var Settings
	 */
	private Settings $settings;
	/**
	 * Initialize the Includes class.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->settings = new Settings();
	}
	/**
	 * Get the singleton instance of the Includes class.
	 *
	 * @return self The singleton instance.
	 */
	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	/**
	 * Initialize the plugin includes.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->settings->register();
	}
	/**
	 * Get the settings instance.
	 *
	 * @return Settings The settings instance.
	 */
	public function settings(): Settings {
		return $this->settings;
	}
}
