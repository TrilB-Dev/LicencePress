<?php
/**
 * Central PayPal connection lifecycle service.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\Includes\Functions\Helpers
 * @since 1.0.0
 */

namespace LicencePress\Includes\Plugins\PayPal\Includes\Functions\Helpers;

use LicencePress\Includes\Plugins\PayPal\Includes\API\PayPalClient;
use LicencePress\Includes\Plugins\PayPal\Includes\Settings\Settings as PayPalSettings;
use LicencePress\Includes\Settings\Settings as LicencePressSettings;

final class PayPalConnectionService {
	public static function get_environment( array $settings = array(), ?string $environment = null ): string {
		$raw = sanitize_key(
			(string) ( $environment ?? ( $settings['paypal_environment'] ?? 'sandbox' ) )
		);

		return in_array( $raw, array( 'live', 'sandbox' ), true ) ? $raw : 'sandbox';
	}

	public static function has_required_credentials( string $environment ): bool {
		$credentials = PayPalSettings::get_client_credentials( $environment );
		return '' !== trim( (string) $credentials['client_id'] )
			&& '' !== trim( (string) $credentials['client_secret'] );
	}

	public static function start_oauth_connect( array $settings, string $environment ): string {
		$environment = self::get_environment( $settings, $environment );
		$state       = PayPalOAuthHelper::generate_state();
		PayPalOAuthHelper::save_state( $state, $environment );

		return PayPalOAuthHelper::build_connect_url( $settings, $state, $environment );
	}

	public static function complete_oauth_connect( array $request ): array {
		$environment = self::get_environment( $request, $request['paypal_environment'] ?? null );
		$code        = sanitize_text_field( (string) ( $request['code'] ?? '' ) );
		$state       = sanitize_text_field( (string) ( $request['state'] ?? '' ) );

		if ( '' === $code || ! PayPalOAuthHelper::validate_state( $state, $environment ) ) {
			return array(
				'success'    => false,
				'error'      => 'invalid_callback',
				'environment' => $environment,
			);
		}

		if ( ! self::has_required_credentials( $environment ) ) {
			return array(
				'success'    => false,
				'error'      => 'missing_credentials',
				'environment' => $environment,
			);
		}

		$settings = LicencePressSettings::get_group( 'paypal', array() );
		$settings = is_array( $settings ) ? $settings : array();
		$settings[ 'paypal_' . $environment . '_client_id' ]     = PayPalSettings::get_client_id( $environment );
		$settings[ 'paypal_' . $environment . '_client_secret' ] = PayPalSettings::get_client_secret( $environment );

		$body = PayPalClient::exchange_code_for_token( $settings, $code, $environment );
		if ( ! is_array( $body ) ) {
			return array(
				'success'    => false,
				'error'      => 'token_exchange_failed',
				'environment' => $environment,
			);
		}

		$settings[ 'paypal_' . $environment . '_access_token' ]    = sanitize_text_field( (string) ( $body['access_token'] ?? '' ) );
		$settings[ 'paypal_' . $environment . '_refresh_token' ]   = sanitize_text_field( (string) ( $body['refresh_token'] ?? '' ) );
		$settings[ 'paypal_' . $environment . '_oauth_connected' ] = ! empty( $body['access_token'] );
		$settings[ 'paypal_' . $environment . '_callback' ]        = PayPalOAuthHelper::get_public_callback_url( $environment );
		$settings['paypal_environment']                            = $environment;
		$settings['paypal_access_token']                           = $settings[ 'paypal_' . $environment . '_access_token' ];
		$settings['paypal_refresh_token']                          = $settings[ 'paypal_' . $environment . '_refresh_token' ];
		$settings['paypal_oauth_connected']                        = $settings[ 'paypal_' . $environment . '_oauth_connected' ];
		$settings['paypal_callback']                               = $settings[ 'paypal_' . $environment . '_callback' ];
		$settings['paypal_client_id']                              = $settings[ 'paypal_' . $environment . '_client_id' ] ?? $settings['paypal_client_id'] ?? '';
		$settings['paypal_client_secret']                          = $settings[ 'paypal_' . $environment . '_client_secret' ] ?? $settings['paypal_client_secret'] ?? '';

		LicencePressSettings::set_group( 'paypal', $settings );
		PayPalOAuthHelper::clear_state( $environment );

		return array(
			'success'    => true,
			'environment' => $environment,
			'body'       => $body,
			'settings'   => $settings,
		);
	}
}
