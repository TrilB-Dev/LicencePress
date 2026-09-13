<?php
/**
 * Role registration for LicencePress.
 *
 * @package LicencePress\Includes\Core
 * @since 1.0.0
 */
namespace LicencePress\Includes\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Roles {
	/**
	 * Role identifiers for LicencePress.
	 * 
	 * @since 1.0.0
	 * @var string The role identifier for the customer role.
	 */
	public const CUSTOMER_ROLE = 'licencepress_customer';
	/**
	 * Role identifier for the internal customer role.
	 *
	 * @since 1.0.0
	 * @var string The role identifier for the internal customer role.
	 */
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

		self::customer_role();
		self::internal_customer_role();
	}
	/**
	 * Get the customer role identifier.
	 *
	 * @since 1.0.0
	 * @return string The customer role identifier.
	 */
	public static function customer_role(): string {
		if ( ! get_role( self::CUSTOMER_ROLE ) ) {
			add_role(
				self::CUSTOMER_ROLE,
				__( 'Customers', 'licencepress' ),
				array(
					'read'                              	=> true,
					'licencepress_customer_manage'       	=> true,
					'licencepress_customer_edit'         	=> true,
					'licencepress_customer_add'          	=> true,
					'licencepress_customer_licence_create'  => true,
					'licencepress_customer_licence_manage'  => true,
				)
			);
		}
		return self::CUSTOMER_ROLE;
	}

	/**
	 * Get the internal customer role identifier.
	 *
	 * @since 1.0.0
	 * @return string The internal customer role identifier.
	 */
	public static function internal_customer_role(): string {
		if ( ! get_role( self::INTERNAL_CUSTOMER_ROLE ) ) {
			add_role(
				self::INTERNAL_CUSTOMER_ROLE,
				__( 'Internal Customers', 'licencepress' ),
				array(
					'read'                                 	 => true,
					'licencepress_customer_manage'         	 => true,
					'licencepress_customer_edit'           	 => true,
					'licencepress_customer_add'            	 => true,
					'licencepress_customer_delete'         	 => true,
					'licencepress_customer_licence_create' 	 => true,
					'licencepress_customer_licence_manage' 	 => true,
					'licencepress_customer_licence_transfer' => true,
				)
			);
		}
		return self::INTERNAL_CUSTOMER_ROLE;
	}
}
