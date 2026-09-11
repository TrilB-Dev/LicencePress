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
	private static array $extensions = array();

	/**
	 * Return the core and registered extension capability definitions.
	 *
	 * @return array<string, array{group: string, label: string, description: string}>
	 */
	public static function definitions(): array {
		return array_merge(
			array(
				'licencepress_admin_view'                => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'View Licence Administration', 'licencepress' ),
					'description' => __( 'Allows access to the LicencePress dashboard and admin pages.', 'licencepress' ),
				),
				'licencepress_dashboard_view'            => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'View Licence Dashboard', 'licencepress' ),
					'description' => __( 'Allows viewing the LicencePress dashboard and summary status.', 'licencepress' ),
				),
				'licencepress_licence_overview_view'              => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'View Licence Overview', 'licencepress' ),
					'description' => __( 'Allows viewing licence overview.', 'licencepress' ),
				),
				'licencepress_licence_type_view'          => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'View Licence Types', 'licencepress' ),
					'description' => __( 'Allows viewing the different licence types available.', 'licencepress' ),
				),
				'licencepress_licence_type_create'        => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Create Licence Types', 'licencepress' ),
					'description' => __( 'Allows creating new licence types.', 'licencepress' ),
				),
				'licencepress_licence_type_edit'          => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Edit Licence Types', 'licencepress' ),
					'description' => __( 'Allows editing existing licence types.', 'licencepress' ),
				),
				'licencepress_licence_type_delete'        => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Delete Licence Types', 'licencepress' ),
					'description' => __( 'Allows deleting existing licence types.', 'licencepress' ),
				),
				'licencepress_licence_type_retire'        => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Retire Licence Types', 'licencepress' ),
					'description' => __( 'Allows retiring existing licence types.', 'licencepress' ),
				),
				'licencepress_licence_type_manage'        => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Manage Licence Types', 'licencepress' ),
					'description' => __( 'Allows managing all aspects of licence types.', 'licencepress' ),
				),
				'licencepress_licence_type_varient_manage' => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Manage Licence Type Variants', 'licencepress' ),
					'description' => __( 'Allows managing all aspects of licence type variants.', 'licencepress' ),
				),
				'licencepress_licence_type_varient_create' => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Create Licence Type Variants', 'licencepress' ),
					'description' => __( 'Allows creating new licence type variants.', 'licencepress' ),
				),
				'licencepress_licence_type_varient_edit' => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Edit Licence Type Variants', 'licencepress' ),
					'description' => __( 'Allows editing existing licence type variants.', 'licencepress' ),
				),
				'licencepress_licence_type_varient_delete' => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Delete Licence Type Variants', 'licencepress' ),
					'description' => __( 'Allows deleting existing licence type variants.', 'licencepress' ),
				),
				'licencepress_licence_type_varient_retire' => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Retire Licence Type Variants', 'licencepress' ),
					'description' => __( 'Allows retiring existing licence type variants.', 'licencepress' ),
				),
				'licencepress_customer_manage'             => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Issue Licences', 'licencepress' ),
					'description' => __( 'Allows creating and issuing new licences.', 'licencepress' ),
				),
				'licencepress_customer_edit'              => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Edit LicencePress Customers', 'licencepress' ),
					'description' => __( 'Allows modifying existing LicencePress customer records.', 'licencepress' ),
				),
				'licencepress_customer_add'            => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Add LicencePress Customers', 'licencepress' ),
					'description' => __( 'Allows adding new LicencePress customers.', 'licencepress' ),
				),
				'licencepress_customer_suspend'            => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Suspend Licences', 'licencepress' ),
					'description' => __( 'Allows suspending active customer accounts.', 'licencepress' ),
				),
				'licencepress_customer_delete'            => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Delete LicencePress Customers', 'licencepress' ),
					'description' => __( 'Allows deleting existing LicencePress customer records.', 'licencepress' ),
				),
				'licencepress_customer_reinstate'            => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Reinstate Licences', 'licencepress' ),
					'description' => __( 'Allows reinstating suspended customer accounts.', 'licencepress' ),
				),
				'licencepress_customer_licence_revoke' => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Revoke Licences', 'licencepress' ),
					'description' => __( 'Allows revoking or disabling active licences for customers.', 'licencepress' ),
				),
				'licencepress_customer_licence_validate' => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Validate Customer Licences', 'licencepress' ),
					'description' => __( 'Allows validating customer licence tokens and checking site binding.', 'licencepress' ),
				),
				'licencepress_customer_licence_extend' => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Extend Customer Licences', 'licencepress' ),
					'description' => __( 'Allows extending the duration of active customer licences.', 'licencepress' ),
				),
				'licencepress_customer_licence_transfer' => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Transfer Customer Licences', 'licencepress' ),
					'description' => __( 'Allows transferring active customer licences to another account.', 'licencepress' ),
				),
				'licencepress_customer_licence_delete' => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Delete Customer Licences', 'licencepress' ),
					'description' => __( 'Allows deleting active customer licences.', 'licencepress' ),
				),
				'licencepress_customer_licence_create' => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Create Customer Licences', 'licencepress' ),
					'description' => __( 'Allows creating new customer licences.', 'licencepress' ),
				),
				'licencepress_customer_licence_manage' => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Manage Customer Licences', 'licencepress' ),
					'description' => __( 'Allows managing all aspects of customer licences.', 'licencepress' ),
				),
				'licencepress_customer_licence_export' => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Export Customer Licences', 'licencepress' ),
					'description' => __( 'Allows exporting customer licence data.', 'licencepress' ),
				),
				'licencepress_customer_licence_import' => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Import Customer Licences', 'licencepress' ),
					'description' => __( 'Allows importing customer licence data.', 'licencepress' ),
				),
				'licencepress_licence_validate'          => array(
					'group'       => 'LicencePress Licence',
					'label'       => __( 'Validate Licences', 'licencepress' ),
					'description' => __( 'Allows validating licence tokens and checking site binding.', 'licencepress' ),
				),
				'licencepress_settings_general_view'     => array(
					'group'       => 'LicencePress Settings',
					'label'       => __( 'View Licence Settings', 'licencepress' ),
					'description' => __( 'Allows viewing the general licence management settings.', 'licencepress' ),
				),
				'licencepress_settings_general_edit'     => array(
					'group'       => 'LicencePress Settings',
					'label'       => __( 'Edit Licence Settings', 'licencepress' ),
					'description' => __( 'Allows editing the licence management settings.', 'licencepress' ),
				),
				'licencepress_settings_access_view'      => array(
					'group'       => 'LicencePress Settings',
					'label'       => __( 'View Access Controls', 'licencepress' ),
					'description' => __( 'Allows viewing who can do what inside LicencePress.', 'licencepress' ),
				),
				'licencepress_settings_access_edit'      => array(
					'group'       => 'LicencePress Settings',
					'label'       => __( 'Edit Access Controls', 'licencepress' ),
					'description' => __( 'Allows changing licence access roles and permission boundaries.', 'licencepress' ),
				),
				'licencepress_settings_security_view'    => array(
					'group'       => 'LicencePress Settings',
					'label'       => __( 'View Security Settings', 'licencepress' ),
					'description' => __( 'Allows viewing security and export protection settings.', 'licencepress' ),
				),
				'licencepress_settings_security_edit'    => array(
					'group'       => 'LicencePress Settings',
					'label'       => __( 'Edit Security Settings', 'licencepress' ),
					'description' => __( 'Allows editing export passwords, encryption controls, and security flags.', 'licencepress' ),
				),
				'licencepress_tools_import'              => array(
					'group'       => 'LicencePress Tools',
					'label'       => __( 'Import Licence Data', 'licencepress' ),
					'description' => __( 'Allows importing licence exports into the system securely.', 'licencepress' ),
				),
				'licencepress_tools_export'              => array(
					'group'       => 'LicencePress Tools',
					'label'       => __( 'Export Licence Data', 'licencepress' ),
					'description' => __( 'Allows exporting licence records using encryption and a password.', 'licencepress' ),
				),
				'licencepress_tools_debug'               => array(
					'group'       => 'LicencePress Tools',
					'label'       => __( 'View Debug Tools', 'licencepress' ),
					'description' => __( 'Allows using LicencePress debug and diagnostics tools.', 'licencepress' ),
				),
				'licencepress_tools_reset'               => array(
					'group'       => 'LicencePress Tools',
					'label'       => __( 'Reset Licence Data', 'licencepress' ),
					'description' => __( 'Allows resetting or clearing licence records and related data.', 'licencepress' ),
				),
				'licencepress_settings_plugins_view'     => array(
					'group'       => 'LicencePress Settings',
					'label'       => __( 'View Plugin Settings', 'licencepress' ),
					'description' => __( 'Allows viewing LicencePress plugin settings.', 'licencepress' ),
				),
				'licencepress_settings_plugins_int_view' => array(
					'group'       => 'LicencePress Settings',
					'label'       => __( 'View Internal Plugin Settings', 'licencepress' ),
					'description' => __( 'Allows viewing settings for internal LicencePress plugins.', 'licencepress' ),
				),
				'licencepress_settings_plugins_int_edit' => array(
					'group'       => 'LicencePress Settings',
					'label'       => __( 'Edit Internal Plugin Settings', 'licencepress' ),
					'description' => __( 'Allows editing settings for internal LicencePress plugins.', 'licencepress' ),
				),
				'licencepress_settings_plugins_ext_view' => array(
					'group'       => 'LicencePress Settings',
					'label'       => __( 'View External Plugin Settings', 'licencepress' ),
					'description' => __( 'Allows viewing settings for external LicencePress plugins.', 'licencepress' ),
				),
				'licencepress_settings_plugins_ext_edit' => array(
					'group'       => 'LicencePress Settings',
					'label'       => __( 'Edit External Plugin Settings', 'licencepress' ),
					'description' => __( 'Allows editing settings for external LicencePress plugins.', 'licencepress' ),
				),
			),
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
