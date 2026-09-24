<?php
/**
 * Stripe capability definitions.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\Stripe\Includes\Core
 * @since 1.0.0
 */

namespace LicencePress\Includes\Plugins\Stripe\Includes\Core;

use LicencePress\Includes\Core\Capabilities as CoreCapabilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Capabilities {
	/**
	 * Register any plugin-level Stripe capability metadata.
	 *
	 * @return void
	 */
	public static function register(): void {
		// This plugin contributes its capability checks through the core capability registry.
		CoreCapabilities::extend( 
			array(
				'licencepress_stripe_view'               => array(
					'group'       => 'LicencePress Stripe',
					'label'       => __( 'View Stripe Dashboard', 'licencepress' ),
					'description' => __( 'Allows viewing the Stripe operations dashboard and onboarding flow.', 'licencepress' ),
				),
				'licencepress_stripe_manage'             => array(
					'group'       => 'LicencePress Stripe',
					'label'       => __( 'Manage Stripe Settings', 'licencepress' ),
					'description' => __( 'Allows changing Stripe connection, checkout, and subscription settings.', 'licencepress' ),
				),
				'licencepress_stripe_checkout'           => array(
					'group'       => 'LicencePress Stripe',
					'label'       => __( 'Manage Stripe Checkout', 'licencepress' ),
					'description' => __( 'Allows configuring one-time Stripe checkout flows.', 'licencepress' ),
				),
				'licencepress_stripe_subscriptions'      => array(
					'group'       => 'LicencePress Stripe',
					'label'       => __( 'Manage Stripe Subscriptions', 'licencepress' ),
					'description' => __( 'Allows configuring recurring subscription billing.', 'licencepress' ),
				),
				'licencepress_stripe_refunds'              => array(
					'group'       => 'LicencePress Stripe',
					'label'       => __( 'Manage Stripe Refunds', 'licencepress' ),
					'description' => __( 'Allows managing refunds for Stripe transactions.', 'licencepress' ),
				),
				'licencepress_stripe_disputes'              => array(
					'group'       => 'LicencePress Stripe',
					'label'       => __( 'Manage Stripe Disputes', 'licencepress' ),
					'description' => __( 'Allows managing disputes for Stripe transactions.', 'licencepress' ),
				),
				'licencepress_stripe_billing'              => array(
					'group'       => 'LicencePress Stripe',
					'label'       => __( 'Manage Stripe Billing', 'licencepress' ),
					'description' => __( 'Allows managing billing for Stripe transactions.', 'licencepress' ),
				),
			)
		);
	}
}
