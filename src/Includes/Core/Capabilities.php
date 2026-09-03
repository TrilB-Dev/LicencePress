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
				'licencepress_admin_view' => [ 'group' => 'LicencePress Wikis', 'label' => __( 'View Wikis Administration', 'licencepress' ), 'description' => __( 'Allows access to the LicencePress Wikis administration area.', 'licencepress' ) ],
				'licencepress_create' => [ 'group' => 'LicencePress Wikis', 'label' => __( 'Create Wikis', 'licencepress' ), 'description' => __( 'Allows creating Wikis.', 'licencepress' ) ],
				'licencepress_edit' => [ 'group' => 'LicencePress Wikis', 'label' => __( 'Edit Wikis', 'licencepress' ), 'description' => __( 'Allows editing Wikis.', 'licencepress' ) ],
				'licencepress_delete' => [ 'group' => 'LicencePress Wikis', 'label' => __( 'Delete Wikis', 'licencepress' ), 'description' => __( 'Allows deleting Wikis.', 'licencepress' ) ],
				'licencepress_publish' => [ 'group' => 'LicencePress Wikis', 'label' => __( 'Publish Wikis', 'licencepress' ), 'description' => __( 'Allows publishing Wikis.', 'licencepress' ) ],
				'licencepress_edit_published' => [ 'group' => 'LicencePress Wikis', 'label' => __( 'Edit Published Wikis', 'licencepress' ), 'description' => __( 'Allows editing published Wikis.', 'licencepress' ) ],
				'licencepress_delete_published' => [ 'group' => 'LicencePress Wikis', 'label' => __( 'Delete Published Wikis', 'licencepress' ), 'description' => __( 'Allows deleting published Wikis.', 'licencepress' ) ],
				'licencepress_edit_others' => [ 'group' => 'LicencePress Wikis', 'label' => __( 'Edit Others Wikis', 'licencepress' ), 'description' => __( 'Allows editing Wikis created by other users.', 'licencepress' ) ],
				'licencepress_delete_others' => [ 'group' => 'LicencePress Wikis', 'label' => __( 'Delete Others Wikis', 'licencepress' ), 'description' => __( 'Allows deleting Wikis created by other users.', 'licencepress' ) ],
				'licencepress_admin_page_view' => [ 'group' => 'LicencePress Wiki Pages', 'label' => __( 'View Wiki Pages Administration', 'licencepress' ), 'description' => __( 'Allows access to the LicencePress Wiki Pages administration area.', 'licencepress' ) ],
				'licencepress_page_create' => [ 'group' => 'LicencePress Wiki Pages', 'label' => __( 'Create Wiki Pages', 'licencepress' ), 'description' => __( 'Allows creating Wiki Pages.', 'licencepress' ) ],
				'licencepress_page_edit' => [ 'group' => 'LicencePress Wiki Pages', 'label' => __( 'Edit Wiki Pages', 'licencepress' ), 'description' => __( 'Allows editing Wiki Pages.', 'licencepress' ) ],
				'licencepress_page_delete' => [ 'group' => 'LicencePress Wiki Pages', 'label' => __( 'Delete Wiki Pages', 'licencepress' ), 'description' => __( 'Allows deleting Wiki Pages.', 'licencepress' ) ],
				'licencepress_page_edit_others' => [ 'group' => 'LicencePress Wiki Pages', 'label' => __( 'Edit Others Wiki Pages', 'licencepress' ), 'description' => __( 'Allows editing Wiki Pages created by other users.', 'licencepress' ) ],
				'licencepress_page_delete_others' => [ 'group' => 'LicencePress Wiki Pages', 'label' => __( 'Delete Others Wiki Pages', 'licencepress' ), 'description' => __( 'Allows deleting Wiki Pages created by other users.', 'licencepress' ) ],
				'licencepress_page_publish' => [ 'group' => 'LicencePress Wiki Pages', 'label' => __( 'Publish Wiki Pages', 'licencepress' ), 'description' => __( 'Allows publishing Wiki Pages.', 'licencepress' ) ],
				'licencepress_page_edit_published' => [ 'group' => 'LicencePress Wiki Pages', 'label' => __( 'Edit Published Wiki Pages', 'licencepress' ), 'description' => __( 'Allows editing published Wiki Pages.', 'licencepress' ) ],
				'licencepress_page_delete_published' => [ 'group' => 'LicencePress Wiki Pages', 'label' => __( 'Delete Published Wiki Pages', 'licencepress' ), 'description' => __( 'Allows deleting published Wiki Pages.', 'licencepress' ) ],
				'licencepress_settings_general_view' => [ 'group' => 'LicencePress Settings', 'label' => __( 'View General Settings', 'licencepress' ), 'description' => __( 'Allows viewing general LicencePress settings.', 'licencepress' ) ],
				'licencepress_settings_general_edit' => [ 'group' => 'LicencePress Settings', 'label' => __( 'Edit General Settings', 'licencepress' ), 'description' => __( 'Allows editing general LicencePress settings.', 'licencepress' ) ],
				'licencepress_settings_layout_view' => [ 'group' => 'LicencePress Settings', 'label' => __( 'View Layout Settings', 'licencepress' ), 'description' => __( 'Allows viewing LicencePress layout settings.', 'licencepress' ) ],
				'licencepress_settings_layout_edit' => [ 'group' => 'LicencePress Settings', 'label' => __( 'Edit Layout Settings', 'licencepress' ), 'description' => __( 'Allows editing LicencePress layout settings.', 'licencepress' ) ],
				'licencepress_settings_plugins_view' => [ 'group' => 'LicencePress Settings', 'label' => __( 'View Plugin Settings', 'licencepress' ), 'description' => __( 'Allows viewing LicencePress plugin settings.', 'licencepress' ) ],
				'licencepress_settings_plugins_int_view' => [ 'group' => 'LicencePress Settings', 'label' => __( 'View Internal Plugin Settings', 'licencepress' ), 'description' => __( 'Allows viewing settings for internal LicencePress plugins.', 'licencepress' ) ],
				'licencepress_settings_plugins_int_edit' => [ 'group' => 'LicencePress Settings', 'label' => __( 'Edit Internal Plugin Settings', 'licencepress' ), 'description' => __( 'Allows editing settings for internal LicencePress plugins.', 'licencepress' ) ],
				'licencepress_settings_plugins_ext_view' => [ 'group' => 'LicencePress Settings', 'label' => __( 'View External Plugin Settings', 'licencepress' ), 'description' => __( 'Allows viewing settings for external LicencePress plugins.', 'licencepress' ) ],
				'licencepress_settings_plugins_ext_edit' => [ 'group' => 'LicencePress Settings', 'label' => __( 'Edit External Plugin Settings', 'licencepress' ), 'description' => __( 'Allows editing settings for external LicencePress plugins.', 'licencepress' ) ],
				'licencepress_settings_access_view' => [ 'group' => 'LicencePress Settings', 'label' => __( 'View Access Settings', 'licencepress' ), 'description' => __( 'Allows viewing LicencePress access settings.', 'licencepress' ) ],
				'licencepress_settings_access_edit' => [ 'group' => 'LicencePress Settings', 'label' => __( 'Edit Access Settings', 'licencepress' ), 'description' => __( 'Allows editing LicencePress access settings.', 'licencepress' ) ],
				'licencepress_tools_import' => [ 'group' => 'LicencePress Tools', 'label' => __( 'Import LicencePress Data', 'licencepress' ), 'description' => __( 'Allows importing LicencePress data.', 'licencepress' ) ],
				'licencepress_tools_export' => [ 'group' => 'LicencePress Tools', 'label' => __( 'Export LicencePress Data', 'licencepress' ), 'description' => __( 'Allows exporting LicencePress data.', 'licencepress' ) ],
				'licencepress_tools_debug' => [ 'group' => 'LicencePress Tools', 'label' => __( 'View Debug Tools', 'licencepress' ), 'description' => __( 'Allows using LicencePress debug tools.', 'licencepress' ) ],
				'licencepress_tools_analytics' => [ 'group' => 'LicencePress Tools', 'label' => __( 'View Analytics Tools', 'licencepress' ), 'description' => __( 'Allows viewing LicencePress analytics.', 'licencepress' ) ],
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