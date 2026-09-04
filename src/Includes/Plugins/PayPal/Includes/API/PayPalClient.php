<?php
/**
 * PayPal REST client helpers.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\Includes\API
 * @since 1.0.0
 */

namespace LicencePress\Includes\Plugins\PayPal\Includes\API;

final class PayPalClient {
	public static function api_base_url( array $settings ): string {
		$environment = sanitize_key( (string) ( $settings['paypal_environment'] ?? 'sandbox' ) );
		return 'sandbox' === $environment ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
	}

	public static function exchange_code_for_token( array $settings, string $code ): ?array {
		$client_id     = sanitize_text_field( (string) ( $settings['paypal_client_id'] ?? '' ) );
		$client_secret = sanitize_text_field( (string) ( $settings['paypal_client_secret'] ?? '' ) );

		if ( '' === $client_id || '' === $client_secret || '' === $code ) {
			return null;
		}

		$response = wp_remote_post(
			self::api_base_url( $settings ) . '/v1/oauth2/token',
			array(
				'timeout' => 30,
				'headers' => array(
					'Accept'        => 'application/json',
					'Authorization' => 'Basic ' . base64_encode( $client_id . ':' . $client_secret ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
					'Content-Type'  => 'application/x-www-form-urlencoded',
				),
				'body'    => array(
					'grant_type'   => 'authorization_code',
					'code'         => $code,
					'redirect_uri' => admin_url( 'admin.php?page=licencepress-paypal&paypal_action=callback' ),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		return is_array( $body ) ? $body : null;
	}
}
