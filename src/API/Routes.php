<?php
/**
 * REST API routes for LicencePress.
 *
 * @package LicencePress
 */

namespace LicencePress\API;

use LicencePress\Includes\Licence\LicenceManager;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Routes {
	public static function register_routes(): void {
		register_rest_route(
			'licencepress/v1',
			'/licence/validate',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( self::class, 'validate_licence' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public static function validate_licence( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$token      = sanitize_text_field( (string) $request->get_param( 'token' ) );
		$product_id = sanitize_key( (string) $request->get_param( 'product_id' ) );
		$site_url   = esc_url_raw( (string) $request->get_param( 'site_url' ) );

		if ( '' === $token || '' === $product_id ) {
			return new WP_Error( 'invalid_request', 'A licence token and product id are required.' );
		}

		$valid = LicenceManager::validate_license( $token, $product_id, '' !== $site_url ? $site_url : null );

		return new WP_REST_Response(
			array(
				'valid'      => $valid,
				'token'      => $token,
				'product_id' => $product_id,
			),
			$valid ? 200 : 400
		);
	}
}
