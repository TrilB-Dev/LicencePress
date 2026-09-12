<?php
/**
 * Customer admin helpers for the LicencePress plugin.
 *
 * @package LicencePress
 * @subpackage Includes\Functions\Admin
 * @since 1.0.0
 */
namespace LicencePress\Includes\Functions\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class FunctionsCustomer {
	/**
	 * Check whether the current user can manage customer records.
	 *
	 * @return bool
	 */
	public static function can_manage_customers(): bool {
		return current_user_can( 'licencepress_customer_manage' ) || current_user_can( 'manage_options' );
	}

	/**
	 * Build the customer directory URL for a selected customer.
	 *
	 * @param int $customer_id Customer record ID.
	 * @return string
	 */
	public static function customer_directory_url( int $customer_id = 0 ): string {
		$url = admin_url( 'admin.php?page=licencepress&group=customers' );
		if ( $customer_id > 0 ) {
			$url = add_query_arg( 'customer_id', (string) $customer_id, $url );
		}
		return $url;
	}
}
