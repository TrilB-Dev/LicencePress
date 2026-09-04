<?php
/**
 * Import-related admin functions for LicencePress.
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

final class FunctionsImport {

	/**
	 * Import LicencePress data from an uploaded JSON file.
	 *
	 * @return void
	 */
	public function import_data(): void {
		if ( ! current_user_can( 'licencepress_tools_import' ) ) {
			wp_die( esc_html__( 'You are not allowed to import LicencePress data.', 'licencepress' ), 403 );
		}
		check_admin_referer( 'licencepress_import' );
		$file = $_FILES['licencepress_import_file'] ?? array();
		if ( empty( $file['tmp_name'] ) || ( $file['error'] ?? UPLOAD_ERR_NO_FILE ) !== UPLOAD_ERR_OK ) {
			wp_die( esc_html__( 'Please upload a valid LicencePress JSON export.', 'licencepress' ), 400 );
		}
		$data = json_decode( file_get_contents( $file['tmp_name'] ), true );
		if ( ! is_array( $data ) ) {
			wp_die( esc_html__( 'The uploaded file is not valid JSON.', 'licencepress' ), 400 );
		}
		$result = DataTransfer::import( $data );
		if ( is_wp_error( $result ) ) {
			wp_die( esc_html( $result->get_error_message() ), 400 );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=licencepress-settings&tab=tools&imported=1' ) );
		exit;
	}
}
