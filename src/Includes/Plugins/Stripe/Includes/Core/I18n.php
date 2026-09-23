<?php
/**
 * Language internationalization (i18n) for the Stripe plugin.
 *
 * @package LicencePress
 * @subpackage Plugins\Stripe\Includes
 * @since 1.0.0
 */
namespace LicencePress\Includes\Plugins\Stripe\Includes\Core;

use LicencePress\Includes\Core\WP\I18n as CoreI18n;

final class I18n {
	/**
	 * Loads the plugin's text domain for translation.
	 */
	public static function load_textdomain(): void {
		load_plugin_textdomain(
			'licencepress',
			false,
			dirname( plugin_basename( LICENCEPRESS_PLUGINS ) ) . '/Stripe/Language/'
		);
	}
}
