<?php
/**
 * Export-related admin functions for LicencePress.
 *
 * @package LicencePress
 * @subpackage Includes\Functions\Admin
 * @since 1.0.0
 */
namespace LicencePress\Includes\Functions\Admin;

use LicencePress\Includes\Tools\DataTransfer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class FunctionsExport {

	/**
	 * Export LicencePress data as a JSON file.
	 *
	 * @return void
	 */
	public function export_data(): void {
		if ( ! current_user_can( 'licencepress_tools_export' ) ) {
			wp_die( esc_html__( 'You are not allowed to export LicencePress data.', 'licencepress' ), 403 );
		}
		check_admin_referer( 'licencepress_export' );
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=licencepress-export-' . gmdate( 'Y-m-d' ) . '.json' );
		echo wp_json_encode( DataTransfer::export(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		exit;
	}
}
