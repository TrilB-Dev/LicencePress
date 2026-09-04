<?php
/**
 * Language internationalization (i18n) for the PayPal plugin.
 *
 * @package LicencePress
 * @subpackage Plugins\PayPal\Includes
 * @since 1.0.0
 */
namespace LicencePress\Includes\Plugins\PayPal\Includes;

class I18n {
	/**
	 * Loads the plugin's text domain for translation.
	 */
	public static function load_textdomain(): void {
		load_plugin_textdomain(
			'licencepress',
			false,
			dirname( plugin_basename( LICENCEPRESS_FILE ) ) . '/src/Includes/Plugins/PayPal/Language/'
		);
	}
}
