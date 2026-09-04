<?php
/**
 * PayPal capability definitions.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\Includes\Core
 * @since 1.0.0
 */

namespace LicencePress\Includes\Plugins\PayPal\Includes\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Capabilities {
	/**
	 * Register any plugin-level PayPal capability metadata.
	 *
	 * @return void
	 */
	public static function register(): void {
		// This plugin contributes its capability checks through the core capability registry.
	}
}
