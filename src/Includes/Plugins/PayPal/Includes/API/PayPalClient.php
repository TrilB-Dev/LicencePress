<?php
/**
 * Raw PayPal REST client used instead of the PayPal PHP SDK.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\Includes\API
 */

namespace LicencePress\Includes\Plugins\PayPal\Includes\API;

use LicencePress\Includes\Plugins\PayPal\Includes\Settings\Settings as PayPalSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PayPalClient {
	public static function get_api_base_url( ?string $environment = null ): string {
		$environment = sanitize_key( (string) ( $environment ?? PayPalSettings::get_environment() ) );
		if ( 'live' === $environment ) {
			return 'https://api-m.paypal.com';
		}

		return 'https://api-m.sandbox.paypal.com';
	}

	public static function get_access_token( ?string $environment = null ): string {
		$environment = sanitize_key( (string) ( $environment ?? PayPalSettings::get_environment() ) );
		$credentials = PayPalSettings::get_client_credentials( $environment );
		$client_id = $credentials['client_id'];
		$secret = $credentials['client_secret'];
		if ( '' === $client_id || '' === $secret ) {
			return '';
		}

		$response = wp_remote_post(
			self::get_api_base_url( $environment ) . '/v1/oauth2/token',
			array(
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( $client_id . ':' . $secret ),
					'Content-Type'  => 'application/x-www-form-urlencoded',
				),
				'body' => array(
					'grant_type' => 'client_credentials',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return '';
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		return is_array( $body ) && ! empty( $body['access_token'] ) ? (string) $body['access_token'] : '';
	}

	public static function prepare_order_payload( array $order_data, string $checkout_type = 'checkout' ): array {
		$amount = isset( $order_data['amount'] ) ? (string) $order_data['amount'] : '0.00';
		$currency = isset( $order_data['currency'] ) ? strtoupper( (string) $order_data['currency'] ) : 'USD';
		$description = isset( $order_data['description'] ) ? (string) $order_data['description'] : 'LicencePress order';
		$custom_id = isset( $order_data['custom_id'] ) ? (string) $order_data['custom_id'] : '';
		$return_url = isset( $order_data['return_url'] ) ? (string) $order_data['return_url'] : ( function_exists( 'home_url' ) ? home_url( '/?paypal_action=checkout_return' ) : 'https://example.com/?paypal_action=checkout_return' );
		$cancel_url = isset( $order_data['cancel_url'] ) ? (string) $order_data['cancel_url'] : ( function_exists( 'home_url' ) ? home_url( '/?paypal_action=checkout_cancel' ) : 'https://example.com/?paypal_action=checkout_cancel' );

		return array(
			'intent' => 'CHECKOUT',
			'purchase_units' => array(
				array(
					'reference_id' => $custom_id !== '' ? 'default' : 'order-reference',
					'custom_id' => $custom_id,
					'description' => $description,
					'amount' => array(
						'currency_code' => $currency,
						'value' => $amount,
					),
				),
			),
			'application_context' => array(
				'return_url' => $return_url,
				'cancel_url' => $cancel_url,
				'brand_name' => 'LicencePress',
				'landing_page' => 'LOGIN',
				'user_action' => 'PAY_NOW',
			),
		);
	}

	public static function create_product( array $payload, ?string $environment = null ): array {
		$environment = sanitize_key( (string) ( $environment ?? PayPalSettings::get_environment() ) );
		$token = self::get_access_token( $environment );
		if ( '' === $token ) {
			return array(
				'success' => false,
				'error' => 'missing_access_token',
			);
		}

		$response = wp_remote_post(
			self::get_api_base_url( $environment ) . '/v1/catalogs/products',
			array(
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
				),
				'body' => wp_json_encode( $payload ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'error' => $response->get_error_message(),
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) ) {
			return array(
				'success' => false,
				'error' => 'invalid_response',
			);
		}

		return array(
			'success' => true,
			'body' => $body,
		);
	}

	public static function get_product( string $product_id, ?string $environment = null ): array {
		$environment = sanitize_key( (string) ( $environment ?? PayPalSettings::get_environment() ) );
		$token = self::get_access_token( $environment );
		if ( '' === $token ) {
			return array(
				'success' => false,
				'error' => 'missing_access_token',
			);
		}

		$response = wp_remote_get(
			self::get_api_base_url( $environment ) . '/v1/catalogs/products/' . rawurlencode( $product_id ),
			array(
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'error' => $response->get_error_message(),
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) ) {
			return array(
				'success' => false,
				'error' => 'invalid_response',
			);
		}

		return array(
			'success' => true,
			'body' => $body,
		);
	}

	public static function create_plan( array $payload, ?string $environment = null ): array {
		$environment = sanitize_key( (string) ( $environment ?? PayPalSettings::get_environment() ) );
		$token = self::get_access_token( $environment );
		if ( '' === $token ) {
			return array(
				'success' => false,
				'error' => 'missing_access_token',
			);
		}

		$response = wp_remote_post(
			self::get_api_base_url( $environment ) . '/v1/billing/plans',
			array(
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
				),
				'body' => wp_json_encode( $payload ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'error' => $response->get_error_message(),
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) ) {
			return array(
				'success' => false,
				'error' => 'invalid_response',
			);
		}

		return array(
			'success' => true,
			'body' => $body,
		);
	}

	public static function create_subscription( array $payload, ?string $environment = null ): array {
		$environment = sanitize_key( (string) ( $environment ?? PayPalSettings::get_environment() ) );
		$token = self::get_access_token( $environment );
		if ( '' === $token ) {
			return array(
				'success' => false,
				'error' => 'missing_access_token',
			);
		}

		$response = wp_remote_post(
			self::get_api_base_url( $environment ) . '/v1/billing/subscriptions',
			array(
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
				),
				'body' => wp_json_encode( $payload ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'error' => $response->get_error_message(),
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) ) {
			return array(
				'success' => false,
				'error' => 'invalid_response',
			);
		}

		return array(
			'success' => true,
			'body' => $body,
		);
	}

	public static function get_plan( string $plan_id, ?string $environment = null ): array {
		$environment = sanitize_key( (string) ( $environment ?? PayPalSettings::get_environment() ) );
		$token = self::get_access_token( $environment );
		if ( '' === $token ) {
			return array(
				'success' => false,
				'error' => 'missing_access_token',
			);
		}

		$response = wp_remote_get(
			self::get_api_base_url( $environment ) . '/v1/billing/plans/' . rawurlencode( $plan_id ),
			array(
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'error' => $response->get_error_message(),
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) ) {
			return array(
				'success' => false,
				'error' => 'invalid_response',
			);
		}

		return array(
			'success' => true,
			'body' => $body,
		);
	}

	public static function get_subscription( string $subscription_id, ?string $environment = null ): array {
		$environment = sanitize_key( (string) ( $environment ?? PayPalSettings::get_environment() ) );
		$token = self::get_access_token( $environment );
		if ( '' === $token ) {
			return array(
				'success' => false,
				'error' => 'missing_access_token',
			);
		}

		$response = wp_remote_get(
			self::get_api_base_url( $environment ) . '/v1/billing/subscriptions/' . rawurlencode( $subscription_id ),
			array(
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'error' => $response->get_error_message(),
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) ) {
			return array(
				'success' => false,
				'error' => 'invalid_response',
			);
		}

		return array(
			'success' => true,
			'body' => $body,
		);
	}

	public static function create_order( array $payload, ?string $environment = null ): array {
		$environment = sanitize_key( (string) ( $environment ?? PayPalSettings::get_environment() ) );
		$token = self::get_access_token( $environment );
		if ( '' === $token ) {
			return array(
				'success' => false,
				'error' => 'missing_access_token',
			);
		}

		$response = wp_remote_post(
			self::get_api_base_url( $environment ) . '/v2/checkout/orders',
			array(
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
				),
				'body' => wp_json_encode( $payload ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'error' => $response->get_error_message(),
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) ) {
			return array(
				'success' => false,
				'error' => 'invalid_response',
			);
		}

		return array(
			'success' => true,
			'body' => $body,
		);
	}

	public static function capture_order( string $order_id, ?string $environment = null ): array {
		$environment = sanitize_key( (string) ( $environment ?? PayPalSettings::get_environment() ) );
		$token = self::get_access_token( $environment );
		if ( '' === $token ) {
			return array(
				'success' => false,
				'error' => 'missing_access_token',
			);
		}

		$response = wp_remote_post(
			self::get_api_base_url( $environment ) . '/v2/checkout/orders/' . rawurlencode( $order_id ) . '/capture',
			array(
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'error' => $response->get_error_message(),
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) ) {
			return array(
				'success' => false,
				'error' => 'invalid_response',
			);
		}

		return array(
			'success' => true,
			'body' => $body,
		);
	}
}
