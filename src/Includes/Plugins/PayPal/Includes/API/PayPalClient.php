<?php
/**
 * PayPal REST client helpers.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\Includes\API
 * @since 1.0.0
 */

namespace LicencePress\Includes\Plugins\PayPal\Includes\API;

use LicencePress\Includes\Plugins\PayPal\Includes\Settings\Settings as PayPalSettings;
use PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSdkLib\Environment;
use PaypalServerSdkLib\PaypalServerSdkClient;
use PaypalServerSdkLib\PaypalServerSdkClientBuilder;

final class PayPalClient {
	public static function api_base_url( array $settings, ?string $environment = null ): string {
		$environment = sanitize_key( (string) ( $environment ?? ( $settings['paypal_environment'] ?? 'sandbox' ) ) );
		return 'sandbox' === $environment ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
	}

	public static function normalize_environment( array $settings, ?string $environment = null ): string {
		$environment = sanitize_key( (string) ( $environment ?? ( $settings['paypal_environment'] ?? 'sandbox' ) ) );
		return 'live' === $environment ? 'live' : 'sandbox';
	}

	public static function build_sdk_client( array $settings, ?string $environment = null ): PaypalServerSdkClient {
		$environment = self::normalize_environment( $settings, $environment );
		$client_id   = PayPalSettings::get_client_id( $environment );
		$secret      = PayPalSettings::get_client_secret( $environment );

		$builder = PaypalServerSdkClientBuilder::init()
			->environment( 'live' === $environment ? Environment::PRODUCTION : Environment::SANDBOX );

		if ( '' !== $client_id && '' !== $secret ) {
			$builder->clientCredentialsAuthCredentials(
				ClientCredentialsAuthCredentialsBuilder::init( $client_id, $secret )
			);
		}

		return $builder->build();
	}

	public static function prepare_order_payload( array $order_data, string $intent = 'CHECKOUT' ): array {
		$intent = strtoupper( sanitize_key( (string) $intent ) );
		if ( '' === $intent ) {
			$intent = 'CHECKOUT';
		}

		$amount       = (string) ( $order_data['amount'] ?? '0.00' );
		$currency     = strtoupper( sanitize_text_field( (string) ( $order_data['currency'] ?? 'USD' ) ) );
		$description  = sanitize_text_field( (string) ( $order_data['description'] ?? '' ) );
		$custom_id    = sanitize_text_field( (string) ( $order_data['custom_id'] ?? '' ) );
		$return_url   = esc_url_raw( (string) ( $order_data['return_url'] ?? home_url( '/?page=licencepress&group=settings&tab=billing#paypal' ) ) );
		$cancel_url   = esc_url_raw( (string) ( $order_data['cancel_url'] ?? home_url( '/?page=licencepress&group=settings&tab=billing#paypal' ) ) );
		$line_items   = is_array( $order_data['items'] ?? null ) ? $order_data['items'] : array();
		$purchase_unit = array(
			'amount' => array(
				'currency_code' => '' !== $currency ? $currency : 'USD',
				'value'         => '' !== $amount ? $amount : '0.00',
			),
			'description' => $description,
			'custom_id'    => $custom_id,
		);

		if ( ! empty( $line_items ) ) {
			$purchase_unit['items'] = $line_items;
		}

		return array(
			'intent' => $intent,
			'purchase_units' => array( $purchase_unit ),
			'application_context' => array(
				'brand_name' => sanitize_text_field( (string) ( $order_data['brand_name'] ?? 'LicencePress' ) ),
				'return_url' => $return_url,
				'cancel_url' => $cancel_url,
				'locale'     => sanitize_text_field( (string) ( $order_data['locale'] ?? 'en-US' ) ),
			),
		);
	}

	public static function create_order( array $settings, array $order_data, ?string $environment = null ): ?array {
		$environment = self::normalize_environment( $settings, $environment );
		$client      = self::build_sdk_client( $settings, $environment );
		$payload     = self::prepare_order_payload( $order_data );

		try {
			$response = $client->getOrdersController()->createOrder( array( 'body' => $payload ) );
			if ( ! $response || ! $response->isSuccess() ) {
				return null;
			}
			$result = $response->getResult();
			return is_array( $result ) ? $result : (array) $result;
		} catch ( \Throwable $exception ) {
			return null;
		}
	}

	public static function capture_order( array $settings, string $order_id, ?string $environment = null ): ?array {
		$environment = self::normalize_environment( $settings, $environment );
		$client      = self::build_sdk_client( $settings, $environment );

		if ( '' === trim( $order_id ) ) {
			return null;
		}

		try {
			$response = $client->getOrdersController()->captureOrder( array( 'id' => $order_id ) );
			if ( ! $response || ! $response->isSuccess() ) {
				return null;
			}
			$result = $response->getResult();
			return is_array( $result ) ? $result : (array) $result;
		} catch ( \Throwable $exception ) {
			return null;
		}
	}

	public static function exchange_code_for_token( array $settings, string $code, ?string $environment = null ): ?array {
		$environment   = sanitize_key( (string) ( $environment ?? ( $settings['paypal_environment'] ?? 'sandbox' ) ) );
		$client_id     = PayPalSettings::get_client_id( $environment );
		$client_secret = PayPalSettings::get_client_secret( $environment );
		$redirect_uri  = home_url( '/?paypal_action=callback&paypal_environment=' . $environment );

		error_log( '[LicencePress][PayPal] token exchange start env=' . $environment . ' client_id_set=' . ( '' !== $client_id ? 'yes' : 'no' ) . ' client_secret_set=' . ( '' !== $client_secret ? 'yes' : 'no' ) . ' redirect_uri=' . $redirect_uri );

		if ( '' === $client_id || '' === $client_secret || '' === $code ) {
			error_log( '[LicencePress][PayPal] token exchange aborted: missing client_id/client_secret/code.' );
			return null;
		}

		$response = wp_remote_post(
			self::api_base_url( $settings, $environment ) . '/v1/oauth2/token',
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
					'redirect_uri' => $redirect_uri,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( '[LicencePress][PayPal] token exchange wp_remote_post error: ' . $response->get_error_message() );
			return null;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body_raw    = wp_remote_retrieve_body( $response );
		error_log( '[LicencePress][PayPal] token exchange HTTP status=' . (string) $status_code . ' body=' . (string) $body_raw );

		$body = json_decode( $body_raw, true );
		return is_array( $body ) ? $body : null;
	}
}
