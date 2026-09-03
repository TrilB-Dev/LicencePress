<?php

namespace LicencePress\Includes\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Capabilities {
	/**
	 * Capability definitions contributed by LicencePress extensions.
	 *
	 * @var array<string, array{group: string, label: string, description: string}>
	 */
	private static array $extensions = [];

	/**
	 * Return the core and registered extension capability definitions.
	 *
	 * @return array<string, array{group: string, label: string, description: string}>
	 */
	public static function definitions(): array {
		return array_merge(
			[
				'licencepress_admin_view' => [ 'group' => 'LicencePress Licence', 'label' => __( 'View Licence Administration', 'licencepress' ), 'description' => __( 'Allows access to the LicencePress dashboard and admin pages.', 'licencepress' ) ],
				'licencepress_dashboard_view' => [ 'group' => 'LicencePress Licence', 'label' => __( 'View Licence Dashboard', 'licencepress' ), 'description' => __( 'Allows viewing the LicencePress dashboard and summary status.', 'licencepress' ) ],
				'licencepress_licence_view' => [ 'group' => 'LicencePress Licence', 'label' => __( 'View Licences', 'licencepress' ), 'description' => __( 'Allows viewing licence records and customer licence state.', 'licencepress' ) ],
				'licencepress_licence_issue' => [ 'group' => 'LicencePress Licence', 'label' => __( 'Issue Licences', 'licencepress' ), 'description' => __( 'Allows creating and issuing new licences.', 'licencepress' ) ],
				'licencepress_licence_edit' => [ 'group' => 'LicencePress Licence', 'label' => __( 'Edit Licences', 'licencepress' ), 'description' => __( 'Allows modifying existing licence metadata.', 'licencepress' ) ],
				'licencepress_licence_revoke' => [ 'group' => 'LicencePress Licence', 'label' => __( 'Revoke Licences', 'licencepress' ), 'description' => __( 'Allows revoking or disabling active licences.', 'licencepress' ) ],
				'licencepress_licence_delete' => [ 'group' => 'LicencePress Licence', 'label' => __( 'Delete Licences', 'licencepress' ), 'description' => __( 'Allows removing licence records from the database.', 'licencepress' ) ],
				'licencepress_licence_validate' => [ 'group' => 'LicencePress Licence', 'label' => __( 'Validate Licences', 'licencepress' ), 'description' => __( 'Allows validating licence tokens and checking site binding.', 'licencepress' ) ],
				'licencepress_settings_general_view' => [ 'group' => 'LicencePress Settings', 'label' => __( 'View Licence Settings', 'licencepress' ), 'description' => __( 'Allows viewing the general licence management settings.', 'licencepress' ) ],
				'licencepress_settings_general_edit' => [ 'group' => 'LicencePress Settings', 'label' => __( 'Edit Licence Settings', 'licencepress' ), 'description' => __( 'Allows editing the licence management settings.', 'licencepress' ) ],
				'licencepress_settings_access_view' => [ 'group' => 'LicencePress Settings', 'label' => __( 'View Access Controls', 'licencepress' ), 'description' => __( 'Allows viewing who can do what inside LicencePress.', 'licencepress' ) ],
				'licencepress_settings_access_edit' => [ 'group' => 'LicencePress Settings', 'label' => __( 'Edit Access Controls', 'licencepress' ), 'description' => __( 'Allows changing licence access roles and permission boundaries.', 'licencepress' ) ],
				'licencepress_settings_security_view' => [ 'group' => 'LicencePress Settings', 'label' => __( 'View Security Settings', 'licencepress' ), 'description' => __( 'Allows viewing security and export protection settings.', 'licencepress' ) ],
				'licencepress_settings_security_edit' => [ 'group' => 'LicencePress Settings', 'label' => __( 'Edit Security Settings', 'licencepress' ), 'description' => __( 'Allows editing export passwords, encryption controls, and security flags.', 'licencepress' ) ],
				'licencepress_tools_import' => [ 'group' => 'LicencePress Tools', 'label' => __( 'Import Licence Data', 'licencepress' ), 'description' => __( 'Allows importing licence exports into the system securely.', 'licencepress' ) ],
				'licencepress_tools_export' => [ 'group' => 'LicencePress Tools', 'label' => __( 'Export Licence Data', 'licencepress' ), 'description' => __( 'Allows exporting licence records using encryption and a password.', 'licencepress' ) ],
				'licencepress_tools_debug' => [ 'group' => 'LicencePress Tools', 'label' => __( 'View Debug Tools', 'licencepress' ), 'description' => __( 'Allows using LicencePress debug and diagnostics tools.', 'licencepress' ) ],
				'licencepress_tools_reset' => [ 'group' => 'LicencePress Tools', 'label' => __( 'Reset Licence Data', 'licencepress' ), 'description' => __( 'Allows resetting or clearing licence records and related data.', 'licencepress' ) ],
				'licencepress_paypal_view' => [ 'group' => 'LicencePress PayPal', 'label' => __( 'View PayPal Dashboard', 'licencepress' ), 'description' => __( 'Allows viewing the PayPal operations dashboard and onboarding flow.', 'licencepress' ) ],
				'licencepress_paypal_manage' => [ 'group' => 'LicencePress PayPal', 'label' => __( 'Manage PayPal Settings', 'licencepress' ), 'description' => __( 'Allows changing PayPal connection, checkout, and subscription settings.', 'licencepress' ) ],
				'licencepress_paypal_checkout' => [ 'group' => 'LicencePress PayPal', 'label' => __( 'Manage PayPal Checkout', 'licencepress' ), 'description' => __( 'Allows configuring one-time PayPal checkout flows.', 'licencepress' ) ],
				'licencepress_paypal_subscriptions' => [ 'group' => 'LicencePress PayPal', 'label' => __( 'Manage PayPal Subscriptions', 'licencepress' ), 'description' => __( 'Allows configuring recurring subscription billing.', 'licencepress' ) ],
				'licencepress_settings_plugins_view' => [ 'group' => 'LicencePress Settings', 'label' => __( 'View Plugin Settings', 'licencepress' ), 'description' => __( 'Allows viewing LicencePress plugin settings.', 'licencepress' ) ],
				'licencepress_settings_plugins_int_view' => [ 'group' => 'LicencePress Settings', 'label' => __( 'View Internal Plugin Settings', 'licencepress' ), 'description' => __( 'Allows viewing settings for internal LicencePress plugins.', 'licencepress' ) ],
				'licencepress_settings_plugins_int_edit' => [ 'group' => 'LicencePress Settings', 'label' => __( 'Edit Internal Plugin Settings', 'licencepress' ), 'description' => __( 'Allows editing settings for internal LicencePress plugins.', 'licencepress' ) ],
				'licencepress_settings_plugins_ext_view' => [ 'group' => 'LicencePress Settings', 'label' => __( 'View External Plugin Settings', 'licencepress' ), 'description' => __( 'Allows viewing settings for external LicencePress plugins.', 'licencepress' ) ],
				'licencepress_settings_plugins_ext_edit' => [ 'group' => 'LicencePress Settings', 'label' => __( 'Edit External Plugin Settings', 'licencepress' ), 'description' => __( 'Allows editing settings for external LicencePress plugins.', 'licencepress' ) ],
			],
			self::$extensions
		);
	}

	/**
	 * Register definitions contributed by a plugin and install any missing caps.
	 *
	 * @param array<string, array{group: string, label: string, description: string}> $definitions Definitions to add.
	 * @return void
	 */
	public static function extend( array $definitions ): void {
		self::$extensions = array_merge( self::$extensions, $definitions );
		self::install();
	}

	/**
	 * Install missing capabilities without removing administrator customizations.
	 *
	 * @return void
	 */
	public static function install(): void {
		$administrator = get_role( 'administrator' );
		if ( ! $administrator ) {
			return;
		}

		foreach ( array_keys( self::definitions() ) as $capability ) {
			if ( ! $administrator->has_cap( $capability ) ) {
				$administrator->add_cap( $capability );
			}
		}
	}
}