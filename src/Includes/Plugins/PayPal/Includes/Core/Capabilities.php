<?php
/**
 * PayPal capability definitions.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\Includes\Core
 * @since 1.0.0
 */

namespace LicencePress\Includes\Plugins\PayPal\Includes\Core;

use LicencePress\Includes\Core\Capabilities as CoreCapabilities;

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
		CoreCapabilities::extend( 
			array(
				'licencepress_paypal_view'               => array(
					'group'       => 'LicencePress PayPal',
					'label'       => __( 'View PayPal Dashboard', 'licencepress' ),
					'description' => __( 'Allows viewing the PayPal operations dashboard and onboarding flow.', 'licencepress' ),
				),
				'licencepress_paypal_manage'             => array(
					'group'       => 'LicencePress PayPal',
					'label'       => __( 'Manage PayPal Settings', 'licencepress' ),
					'description' => __( 'Allows changing PayPal connection, checkout, and subscription settings.', 'licencepress' ),
				),
				'licencepress_paypal_checkout'           => array(
					'group'       => 'LicencePress PayPal',
					'label'       => __( 'Manage PayPal Checkout', 'licencepress' ),
					'description' => __( 'Allows configuring one-time PayPal checkout flows.', 'licencepress' ),
				),
				'licencepress_paypal_subscriptions'      => array(
					'group'       => 'LicencePress PayPal',
					'label'       => __( 'Manage PayPal Subscriptions', 'licencepress' ),
					'description' => __( 'Allows configuring recurring subscription billing.', 'licencepress' ),
				),
			)
		);
	}
}
