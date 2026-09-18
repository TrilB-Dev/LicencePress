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
		$state = PayPalOAuthHelper::generate_state();
		PayPalOAuthHelper::save_state( $state, $environment );

		$effective_settings = $settings;
		$effective_settings['paypal_environment'] = $environment;
		$effective_settings['environment'] = $environment;
		$effective_settings['client_id'] = $effective_settings['client_id'] ?? $effective_settings[ 'paypal_' . $environment . '_client_id' ] ?? PayPalSettings::get_client_id( $environment );
		$effective_settings['client_secret'] = $effective_settings['client_secret'] ?? $effective_settings[ 'paypal_' . $environment . '_client_secret' ] ?? PayPalSettings::get_client_secret( $environment );

		return PayPalOAuthHelper::build_connect_url( $effective_settings, $state, $environment );
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
		$code = sanitize_text_field( (string) ( $request['code'] ?? '' ) );
		$state = sanitize_text_field( (string) ( $request['state'] ?? '' ) );

		if ( '' === $code || '' === $state || ! PayPalOAuthHelper::validate_state( $state, $environment ) ) {
			PayPalOAuthHelper::clear_state( $environment );
			return array(
				'success' => false,
				'error' => 'invalid_state_or_code',
				'environment' => $environment,
			);
		}

		PayPalOAuthHelper::clear_state( $environment );
		$body = PayPalRESTAPI::exchange_authorization_code( $code, $environment );
		if ( empty( $body ) || empty( $body['access_token'] ) ) {
			return array(
				'success' => false,
				'error' => 'token_exchange_failed',
				'environment' => $environment,
				'body' => $body,
			);
		}

		$settings = $request;
		$settings['environment'] = $environment;
		$settings['client_id'] = PayPalSettings::get_client_id( $environment );
		$settings['client_secret'] = PayPalSettings::get_client_secret( $environment );
		$settings['access_token'] = $body['access_token'];
		$settings['refresh_token'] = $body['refresh_token'] ?? '';
		PayPalRESTAPI::save_oauth_credentials( $settings );

		$stored = BaseSettings::get_group( 'paypal', array() );
		$stored = is_array( $stored ) ? $stored : array();
		$stored['paypal_environment'] = $environment;
		$stored['paypal_' . $environment . '_oauth_connected'] = true;
		$stored['paypal_oauth_connected'] = true;
		BaseSettings::set_group( 'paypal', $stored );

		return array(
			'success' => true,
			'body' => $body,
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