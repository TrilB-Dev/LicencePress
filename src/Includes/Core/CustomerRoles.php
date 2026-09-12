<?php
/**
 * Customer role registration for LicencePress.
 *
 * @package LicencePress\Includes\Core
 */
namespace LicencePress\Includes\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CustomerRoles {
	public const CUSTOMER_ROLE = 'licencepress_customer';
	public const INTERNAL_CUSTOMER_ROLE = 'licencepress_internal_customer';

	/**
	 * Install the default customer roles used by LicencePress.
	 *
	 * @return void
	 */
	public static function install(): void {
		if ( ! function_exists( 'add_role' ) || ! function_exists( 'get_role' ) ) {
			return;
		}

		if ( ! get_role( self::CUSTOMER_ROLE ) ) {
			add_role(
				self::CUSTOMER_ROLE,
				__( 'Customers', 'licencepress' ),
				array(
					'read'                              => true,
					'licencepress_customer_manage'      => true,
					'licencepress_customer_edit'        => true,
					'licencepress_customer_add'         => true,
					'licencepress_customer_licence_create' => true,
					'licencepress_customer_licence_manage' => true,
				)
			);
		}

		if ( ! get_role( self::INTERNAL_CUSTOMER_ROLE ) ) {
			add_role(
				self::INTERNAL_CUSTOMER_ROLE,
				__( 'Internal Customers', 'licencepress' ),
				array(
					'read'                               => true,
					'licencepress_customer_manage'       => true,
					'licencepress_customer_edit'         => true,
					'licencepress_customer_add'          => true,
					'licencepress_customer_delete'       => true,
					'licencepress_customer_licence_create' => true,
					'licencepress_customer_licence_manage' => true,
					'licencepress_customer_licence_transfer' => true,
				)
			);
		}
	}
}
