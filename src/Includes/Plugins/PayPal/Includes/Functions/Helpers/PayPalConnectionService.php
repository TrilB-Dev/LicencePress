<?php
/**
 * Connect and complete the PayPal OAuth flow.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\Includes\Functions\Helpers
 */

namespace LicencePress\Includes\Plugins\PayPal\Includes\Functions\Helpers;

use LicencePress\Includes\Plugins\PayPal\API\PayPalRESTAPI;
use LicencePress\Includes\Plugins\PayPal\Includes\Settings\Settings as PayPalSettings;
use LicencePress\Includes\Settings\Settings as BaseSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PayPalConnectionService {
	/**
	 * Starts the PayPal OAuth connection process.
	 *
	 * @param array       $settings    The PayPal settings array.
	 * @param string|null $environment The PayPal environment (sandbox or live).
	 * @return string The URL to redirect the user to for OAuth connection.
	 * @since 1.0.0
	 */
	public static function start_oauth_connect( array $settings = array(), ?string $environment = null ): string {
		$environment = self::normalize_environment( $settings, $environment );
		if ( function_exists( 'admin_url' ) ) {
			return admin_url( 'admin.php?page=licencepress-paypal&paypal_action=test_connection&paypal_environment=' . $environment );
		}

		return home_url( '/wp-admin/admin.php?page=licencepress-paypal&paypal_action=test_connection&paypal_environment=' . $environment );
	}

	public static function test_connection( array $settings = array(), ?string $environment = null ): array {
		$environment = self::normalize_environment( $settings, $environment );
		$client_id = trim( (string) ( $settings['client_id'] ?? $settings[ 'paypal_api_' . $environment . '_client_id' ] ?? PayPalSettings::get_client_id( $environment ) ) );
		$client_secret = trim( (string) ( $settings['client_secret'] ?? $settings[ 'paypal_api_' . $environment . '_client_secret' ] ?? PayPalSettings::get_client_secret( $environment ) ) );

		if ( '' === $client_id || '' === $client_secret ) {
			return array(
				'success' => false,
				'connected' => false,
				'error' => 'missing_client_credentials',
				'environment' => $environment,
			);
		}

		$response = wp_remote_post(
			'https://api-m.' . ( 'sandbox' === $environment ? 'sandbox.' : '' ) . 'paypal.com/v1/oauth2/token',
			array(
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( $client_id . ':' . $client_secret ),
					'Content-Type'  => 'application/x-www-form-urlencoded',
				),
				'body' => array(
					'grant_type' => 'client_credentials',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'connected' => false,
				'error' => $response->get_error_message(),
				'environment' => $environment,
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$token = is_array( $body ) && ! empty( $body['access_token'] ) ? (string) $body['access_token'] : '';
		$connected = '' !== $token;

		$stored = BaseSettings::get_group( 'paypal', array() );
		$stored = is_array( $stored ) ? $stored : array();
		$stored['paypal_environment'] = $environment;
		$stored[ 'paypal_api_' . $environment . '_oauth_connected' ] = $connected;
		BaseSettings::set_group( 'paypal', $stored );

		return array(
			'success' => $connected,
			'connected' => $connected,
			'error' => $connected ? '' : 'token_exchange_failed',
			'environment' => $environment,
			'token' => $token,
		);
	}
	/**
	 * Completes the PayPal OAuth connection process.
	 *
	 * @param array $request The request array containing the OAuth response parameters.
	 * @return array The result of the OAuth connection attempt.
	 * @since 1.0.0
	 */
	public static function complete_oauth_connect( array $request ): array {
		$environment = self::normalize_environment( $request, $request['paypal_environment'] ?? null );
		$client_id = PayPalSettings::get_client_id( $environment );
		$client_secret = PayPalSettings::get_client_secret( $environment );

		if ( '' === $client_id || '' === $client_secret ) {
			return array(
				'success' => false,
				'error' => 'missing_client_credentials',
				'environment' => $environment,
			);
		}

		$settings = $request;
		$settings['environment'] = $environment;
		$settings['client_id'] = $client_id;
		$settings['client_secret'] = $client_secret;
		PayPalRESTAPI::save_oauth_credentials( $settings );

		$stored = BaseSettings::get_group( 'paypal', array() );
		$stored = is_array( $stored ) ? $stored : array();
		$stored['paypal_environment'] = $environment;
		$stored['paypal_api_' . $environment . '_oauth_connected'] = true;
		BaseSettings::set_group( 'paypal', $stored );

		return array(
			'success' => true,
			'body' => array(
				'client_id' => $client_id,
				'client_secret' => $client_secret,
			),
			'environment' => $environment,
		);
	}
	/**
	 * Normalizes the PayPal environment value.
	 *
	 * @param array       $settings    The settings array containing the environment information.
	 * @param string|null $environment The environment value to normalize.
	 * @return string The normalized environment ('sandbox' or 'live').
	 * @since 1.0.0
	 */
	private static function normalize_environment( array $settings, ?string $environment = null ): string {
		$raw = sanitize_key( (string) ( $environment ?? ( $settings['paypal_environment'] ?? ( $settings['environment'] ?? 'sandbox' ) ) ) );
		return in_array( $raw, array( 'sandbox', 'live' ), true ) ? $raw : 'sandbox';
	}
}