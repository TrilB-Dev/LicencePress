<?php
/**
 * Connect and complete the PayPal OAuth flow.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\Includes\Functions\Helpers
 */

namespace LicencePress\Includes\Plugins\PayPal\Includes\Functions\Helpers;

use LicencePress\Includes\Plugins\PayPal\API\PayPalAPI;
use LicencePress\Includes\Plugins\PayPal\Includes\Settings\Settings as PayPalSettings;
use LicencePress\Includes\Settings\Settings as BaseSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PayPalConnectionService {
	public static function test_connection( array $settings = array(), ?string $environment = null ): array {
		$environment = self::normalize_environment( $settings, $environment );
		$settings['paypal_environment'] = $environment;

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

		$result = PayPalAPI::validate_connection(
			array(
				'client_id' => $client_id,
				'client_secret' => $client_secret,
				'paypal_environment' => $environment,
			),
			$environment
		);

		$connected = ! empty( $result['connected'] );
		$stored = BaseSettings::get_group( 'paypal', array() );
		$stored = is_array( $stored ) ? $stored : array();
		$stored['paypal_environment'] = $environment;
		$stored[ 'paypal_api_' . $environment . '_oauth_connected' ] = $connected;
		$stored[ 'paypal_api_' . $environment . '_access_token' ] = (string) ( $result['token'] ?? '' );
		BaseSettings::set_group( 'paypal', $stored );

		return array(
			'success' => $connected,
			'connected' => $connected,
			'error' => $connected ? '' : ( $result['error'] ?? 'token_exchange_failed' ),
			'environment' => $environment,
			'token' => (string) ( $result['token'] ?? '' ),
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