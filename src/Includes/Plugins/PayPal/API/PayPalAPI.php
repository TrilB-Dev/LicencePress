<?php
/**
 * Clean PayPal REST API facade for OAuth connect, checkout, subscriptions, and advanced payment features.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\API
 * @version 1.0.0
 */

namespace LicencePress\Includes\Plugins\PayPal\API;

use LicencePress\Includes\Plugins\PayPal\API\Client\PayPalClient;
use LicencePress\Includes\Plugins\PayPal\API\Models\Connection;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PayPalAPI {
	/**
	 * Normalizes the PayPal environment.
	 *
	 * @param array       $settings    The settings array.
	 * @param string|null $environment The environment to normalize.
	 * @return string The normalized environment ('sandbox' or 'live').
	 * @since 1.0.0
	 */
	public static function normalize_environment( array $settings = array(), ?string $environment = null ): string {
		$raw = sanitize_key( (string) ( $environment ?? ( $settings['environment'] ?? ( $settings['paypal_environment'] ?? 'sandbox' ) ) ) );
		return in_array( $raw, array( 'sandbox', 'live' ), true ) ? $raw : 'sandbox';
	}

	/**
	 * Normalizes the payload to an array.
	 *
	 * @param mixed $model_or_array The model object or array to normalize.
	 * @return array<string, mixed> The normalized array.
	 * @since 1.0.0
	 */
	private static function normalize_payload( $model_or_array ): array {
		if ( is_object( $model_or_array ) ) {
			if ( method_exists( $model_or_array, 'to_payload' ) ) {
				$payload = $model_or_array->to_payload();
				if ( is_array( $payload ) ) {
					return $payload;
				}
			}

			if ( method_exists( $model_or_array, 'to_array' ) ) {
				$payload = $model_or_array->to_array();
				if ( is_array( $payload ) ) {
					return $payload;
				}
			}
		}

		return is_array( $model_or_array ) ? $model_or_array : array();
	}

	/**
	 * Makes a request to the PayPal API.
	 *
	 * @param string      $path        The API endpoint path.
	 * @param string      $method      The HTTP method (GET, POST, etc.).
	 * @param array       $payload     The request payload.
	 * @param string|null $environment The PayPal environment (sandbox or live).
	 * @param array       $query       Optional query string values.
	 * @param array       $headers     Optional headers.
	 * @return array<string, mixed> The API response.
	 * @since 1.0.0
	 */
	private static function request_api( string $path, string $method = 'GET', array $payload = array(), ?string $environment = null, array $query = array(), array $headers = array() ): array {
		$environment = self::normalize_environment( array(), $environment );
		$token = PayPalClient::get_access_token( $environment );
		if ( '' === $token ) {
			return array(
				'success' => false,
				'error' => 'missing_access_token',
				'environment' => $environment,
			);
		}

		$base_url = 'https://api-m.' . ( 'sandbox' === $environment ? 'sandbox.' : '' ) . 'paypal.com';
		$full_path = $path;
		if ( ! empty( $query ) ) {
			$separator = false === strpos( $full_path, '?' ) ? '?' : '&';
			$full_path .= $separator . http_build_query( $query );
		}

		$args = array(
			'timeout' => 30,
			'headers' => array_merge(
				array(
					'Authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
				),
				$headers
			),
		);

		if ( 'GET' !== strtoupper( $method ) ) {
			$args['body'] = wp_json_encode( $payload );
		}

		$response = wp_remote_request( $base_url . $full_path, $args );
		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'error' => $response->get_error_message(),
				'environment' => $environment,
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) ) {
			$body = array();
		}

		return array(
			'success' => $status_code >= 200 && $status_code < 300,
			'status_code' => $status_code,
			'body' => $body,
			'environment' => $environment,
		);
	}
	/**
	 * Normalizes the payload for API requests.
	 *
	 * @param mixed $payload The payload to normalize.
	 * @return array The normalized payload.
	 * @since 1.0.0
	 */
	public static function validate_connection( array $settings = array(), ?string $environment = null ): array {
		$environment = self::normalize_environment( $settings, $environment );
		$connection  = Connection::from_settings( array_merge( $settings, array( 'environment' => $environment ) ) );
		$client_id   = trim( (string) $connection->client_id );
		$secret      = trim( (string) $connection->client_secret );

		if ( '' === $client_id || '' === $secret ) {
			return array(
				'success' => false,
				'connected' => false,
				'error' => 'missing_client_credentials',
				'environment' => $environment,
			);
		}

		$decoded_secret = html_entity_decode( $secret, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$basic_auth     = 'Basic ' . base64_encode( $client_id . ':' . $decoded_secret );

		$response = wp_remote_post(
			'https://api-m.' . ( 'sandbox' === $environment ? 'sandbox.' : '' ) . 'paypal.com/v1/oauth2/token',
			array(
				'timeout' => 30,
				'headers' => array(
					'Authorization' => $basic_auth,
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

		return array(
			'success' => $connected,
			'connected' => $connected,
			'token' => $token,
			'environment' => $environment,
			'error' => $connected ? '' : 'token_exchange_failed',
		);
	}
	/**
	 * Creates a new PayPal order.
	 *
	 * @param mixed       $order       The order data.
	 * @param string|null $environment The environment to use.
	 * @return array The API response.
	 * @since 1.0.0
	 */
	public static function create_order( $order, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		$payload = self::normalize_payload( $order );
		return PayPalClient::create_order( $payload, $environment );
	}
	/**
	 * Captures a PayPal order.
	 *
	 * @param string      $order_id    The ID of the order to capture.
	 * @param string|null $environment The environment to use.
	 * @return array The API response.
	 * @since 1.0.0
	 */
	public static function capture_order( string $order_id, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return PayPalClient::capture_order( $order_id, $environment );
	}
	/**
	 * Creates a new PayPal product.
	 *
	 * @param mixed       $product     The product data.
	 * @param string|null $environment The environment to use.
	 * @return array The API response.
	 * @since 1.0.0
	 */
	public static function create_product( $product, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return self::request_api( '/v1/catalogs/products', 'POST', self::normalize_payload( $product ), $environment );
	}
	/**
	 * Retrieves a PayPal product by its ID.
	 *
	 * @param string      $product_id  The ID of the product to retrieve.
	 * @param string|null $environment The environment to use.
	 * @return array The API response.
	 * @since 1.0.0
	 */
	public static function get_product( string $product_id, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return self::request_api( '/v1/catalogs/products/' . rawurlencode( $product_id ), 'GET', array(), $environment );
	}

	/**
	 * Creates a new PayPal plan.
	 *
	 * @param mixed       $plan        The plan data.
	 * @param string|null $environment The environment to use.
	 * @return array The API response.
	 * @since 1.0.0
	 */
	public static function create_plan( $plan, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		$payload = self::normalize_payload( $plan );
		return PayPalClient::create_plan( $payload, $environment );
	}

	/**
	 * Retrieves a PayPal plan by its ID.
	 *
	 * @param string      $plan_id     The ID of the plan to retrieve.
	 * @param string|null $environment The environment to use.
	 * @return array The API response.
	 * @since 1.0.0
	 */
	public static function get_plan( string $plan_id, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return PayPalClient::get_plan( $plan_id, $environment );
	}

	/**
	 * Creates a new PayPal subscription.
	 *
	 * @param mixed       $subscription The subscription data.
	 * @param string|null $environment  The environment to use.
	 * @return array The API response.
	 * @since 1.0.0
	 */
	public static function create_subscription( $subscription, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		$payload = self::normalize_payload( $subscription );
		return PayPalClient::create_subscription( $payload, $environment );
	}

	/**
	 * Retrieves a PayPal subscription by its ID.
	 *
	 * @param string      $subscription_id The ID of the subscription to retrieve.
	 * @param string|null $environment     The environment to use.
	 * @return array The API response.
	 * @since 1.0.0
	 */
	public static function get_subscription( string $subscription_id, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return PayPalClient::get_subscription( $subscription_id, $environment );
	}

	/**
	 * Activates a PayPal subscription.
	 *
	 * @param string      $subscription_id The ID of the subscription to activate.
	 * @param string      $reason          The reason for activation.
	 * @param string|null $environment     The environment to use.
	 * @return array The API response.
	 * @since 1.0.0
	 */
	public static function activate_subscription( string $subscription_id, string $reason = 'Activation requested by LicencePress', ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return PayPalClient::activate_subscription( $subscription_id, $environment, $reason );
	}

	/**
	 * Searches for PayPal transactions based on criteria.
	 *
	 * @param array       $criteria    The search criteria.
	 * @param string|null $environment The environment to use.
	 * @return array The API response.
	 * @since 1.0.0
	 */
	public static function search_transactions( array $criteria = array(), ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return self::request_api( '/v1/reporting/transactions', 'GET', array(), $environment, $criteria );
	}

	/**
	 * Creates a new PayPal invoice.
	 *
	 * @param mixed       $invoice     The invoice data.
	 * @param string|null $environment The environment to use.
	 * @return array The API response.
	 * @since 1.0.0
	 */
	public static function create_invoice( $invoice, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return self::request_api( '/v2/invoicing/invoices', 'POST', self::normalize_payload( $invoice ), $environment );
	}
	/**
	 * Retrieves a PayPal invoice by its ID.
	 *
	 * @param string      $invoice_id  The ID of the invoice to retrieve.
	 * @param string|null $environment The environment to use.
	 * @return array The API response.
	 * @since 1.0.0
	 */
	public static function get_invoice( string $invoice_id, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return self::request_api( '/v2/invoicing/invoices/' . rawurlencode( $invoice_id ), 'GET', array(), $environment );
	}

	/**
	 * Refunds a PayPal capture by its ID.
	 *
	 * @param string      $capture_id  The ID of the capture to refund.
	 * @param array       $payload     The refund payload.
	 * @param string|null $environment The environment to use.
	 * @return array The API response.
	 * @since 1.0.0
	 */
	public static function refund_capture( string $capture_id, array $payload = array(), ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return self::request_api( '/v2/payments/captures/' . rawurlencode( $capture_id ) . '/refund', 'POST', $payload, $environment );
	}
	/**
	 * Lists all PayPal payment methods.
	 *
	 * @param string|null $environment The environment to use.
	 * @return array The API response.
	 * @since 1.0.0
	 */
	public static function list_payment_methods( ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return self::request_api( '/v3/vault/payment-tokens', 'GET', array(), $environment );
	}

	/**
	 * Creates a new PayPal payment method token.
	 *
	 * @param array       $payload     The payment method token payload.
	 * @param string|null $environment The environment to use.
	 * @return array The API response.
	 * @since 1.0.0
	 */
	public static function create_payment_method_token( array $payload = array(), ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return self::request_api( '/v3/vault/setup-tokens', 'POST', $payload, $environment );
	}

	/**
	 * Lists all PayPal disputes.
	 *
	 * @param string|null $environment The environment to use.
	 * @return array The API response.
	 * @since 1.0.0
	 */
	public static function list_disputes( ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return self::request_api( '/v1/customer/disputes', 'GET', array(), $environment );
	}

	/**
	 * Retrieves a PayPal dispute by its ID.
	 *
	 * @param string      $dispute_id  The ID of the dispute to retrieve.
	 * @param string|null $environment The environment to use.
	 * @return array The API response.
	 * @since 1.0.0
	 */
	public static function get_dispute( string $dispute_id, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return self::request_api( '/v1/customer/disputes/' . rawurlencode( $dispute_id ), 'GET', array(), $environment );
	}
}
