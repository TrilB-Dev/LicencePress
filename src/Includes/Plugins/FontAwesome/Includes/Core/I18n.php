<?php
/**
 * This file manages the internationalization functionality of the plugin.
 *
 * @package LicencePress\Includes\Plugins\FontAwesome\Includes
 * @since 1.0.0
 */

namespace LicencePress\Includes\Plugins\FontAwesome\Includes\Core;

final class I18n {
	public static function load_textdomain(): void {
		load_plugin_textdomain(
			'licencepress',
			false,
			dirname( plugin_basename( LICENCEPRESS_FILE ) ) . '/src/Includes/Plugins/FontAwesome/Language/'
		);
	}
}
