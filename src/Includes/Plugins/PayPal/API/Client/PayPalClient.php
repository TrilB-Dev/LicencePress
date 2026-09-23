<?php
/**
 * Raw PayPal REST client used instead of the PayPal PHP SDK.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\API\Client
 */

namespace LicencePress\Includes\Plugins\PayPal\API\Client;

use LicencePress\Includes\Plugins\PayPal\Includes\Settings\Settings as PayPalSettings;
use PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSdkLib\Environment;
use PaypalServerSdkLib\PaypalServerSdkClient;
use PaypalServerSdkLib\PaypalServerSdkClientBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PayPalClient {
	/**
	 * Get the list of supported PayPal API controllers.
	 *
	 * @return array List of supported controllers.
	 * @since 1.0.0
	 */
	public static function get_supported_controllers(): array {
		return array(
			'orders',
			'payments',
			'vault',
			'transaction_search',
			'subscriptions',
		);
	}
	/**
	 * Resolve the PayPal environment based on the given environment string.
	 *
	 * @param string|null $environment The environment string ('live' or 'sandbox').
	 * @return string The resolved environment constant from the PayPal SDK.
	 * @since 1.0.0
	 */
	public static function resolve_environment( ?string $environment = null ): string {
		$environment = sanitize_key( (string) ( $environment ?? PayPalSettings::get_environment() ) );
		return 'live' === $environment ? Environment::PRODUCTION : Environment::SANDBOX;
	}
	/**
	 * Build and return a PayPal SDK client instance.
	 *
	 * @param string|null $environment The environment string ('live' or 'sandbox').
	 * @param string|null $client_id The PayPal client ID.
	 * @param string|null $client_secret The PayPal client secret.
	 * @return PaypalServerSdkClient|null The PayPal SDK client instance or null if credentials are missing.
	 * @since 1.0.0
	 */
	public static function build_sdk_client( ?string $environment = null, ?string $client_id = null, ?string $client_secret = null ): ?PaypalServerSdkClient {
		$environment_name = self::resolve_environment( $environment );
		$environment_key  = 'Production' === $environment_name ? 'live' : 'sandbox';
		$credentials      = PayPalSettings::get_client_credentials( $environment_key );
		$client_id        = trim( (string) ( $client_id ?? $credentials['client_id'] ) );
		$client_secret    = trim( (string) ( $client_secret ?? $credentials['client_secret'] ) );

		if ( '' === $client_id || '' === $client_secret ) {
			return null;
		}

		return PaypalServerSdkClientBuilder::init()
			->clientCredentialsAuthCredentials(
				ClientCredentialsAuthCredentialsBuilder::init( $client_id, $client_secret )
			)
			->environment( $environment_name )
			->build();
	}
	/**
	 * Normalize the response from the PayPal SDK into a consistent array format.
	 *
	 * @param mixed $response The response object from the PayPal SDK.
	 * @return array The normalized response array.
	 * @since 1.0.0
	 */
	private static function normalize_sdk_response( $response ): array {
		if ( ! is_object( $response ) || ! method_exists( $response, 'getStatusCode' ) ) {
			return array(
				'success' => false,
				'error'   => 'invalid_sdk_response',
			);
		}

		$result = $response->getResult();
		if ( is_object( $result ) && method_exists( $result, 'toArray' ) ) {
			$result = $result->toArray();
		}

		if ( is_object( $result ) && method_exists( $result, 'jsonSerialize' ) ) {
			$result = $result->jsonSerialize();
		}

		return array(
			'success'    => $response->isSuccess(),
			'status_code' => $response->getStatusCode(),
			'body'       => is_array( $result ) ? $result : array(),
			'raw'        => $response,
		);
	}
	/**
	 * Get the base URL for the PayPal API based on the environment.
	 *
	 * @param string|null $environment The environment string ('live' or 'sandbox').
	 * @return string The base URL for the PayPal API.
	 * @since 1.0.0
	 */
	public static function get_api_base_url( ?string $environment = null ): string {
		$environment = sanitize_key( (string) ( $environment ?? PayPalSettings::get_environment() ) );
		if ( 'live' === $environment ) {
			return 'https://api-m.paypal.com';
		}

		return 'https://api-m.sandbox.paypal.com';
	}
	/**
	 * Get the access token for the PayPal API based on the environment.
	 *
	 * @param string|null $environment The environment string ('live' or 'sandbox').
	 * @return string The access token for the PayPal API.
	 * @since 1.0.0
	 */
	public static function get_access_token( ?string $environment = null ): string {
		$environment = sanitize_key( (string) ( $environment ?? PayPalSettings::get_environment() ) );
		$credentials = PayPalSettings::get_client_credentials( $environment );
		$client_id = trim( (string) $credentials['client_id'] );
		$secret = html_entity_decode( trim( (string) $credentials['client_secret'] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
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
	/**
	 * Prepare the payload for creating a PayPal order.
	 *
	 * @param array $order_data The order data including amount, currency, description, custom_id, return_url, and cancel_url.
	 * @param string $checkout_type The type of checkout ('checkout' by default).
	 * @return array The prepared order payload for the PayPal API.
	 * @since 1.0.0
	 */
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
	/**
	 * Create a new product in the PayPal catalog.
	 *
	 * @param array $payload The product data payload.
	 * @param string|null $environment The environment string ('live' or 'sandbox').
	 * @return array The response from the PayPal API.
	 * @since 1.0.0
	 */
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
	/**
	 * Retrieve a product from the PayPal catalog by its ID.
	 *
	 * @param string $product_id The ID of the product to retrieve.
	 * @param string|null $environment The environment string ('live' or 'sandbox').
	 * @return array The response from the PayPal API.
	 * @since 1.0.0
	 */
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
	/**
	 * Create a new billing plan in the PayPal system.
	 *
	 * @param array $payload The plan data payload.
	 * @param string|null $environment The environment string ('live' or 'sandbox').
	 * @return array The response from the PayPal API.
	 * @since 1.0.0
	 */
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
	/**
	 * Create a new subscription in the PayPal system.
	 *
	 * @param array $payload The subscription data payload.
	 * @param string|null $environment The environment string ('live' or 'sandbox').
	 * @return array The response from the PayPal API.
	 * @since 1.0.0
	 */
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
	/**
	 * Retrieve a billing plan from the PayPal system by its ID.
	 *
	 * @param string $plan_id The ID of the plan to retrieve.
	 * @param string|null $environment The environment string ('live' or 'sandbox').
	 * @return array The response from the PayPal API.
	 * @since 1.0.0
	 */
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
	/**
	 * Retrieve a subscription from the PayPal system by its ID.
	 *
	 * @param string $subscription_id The ID of the subscription to retrieve.
	 * @param string|null $environment The environment string ('live' or 'sandbox').
	 * @return array The response from the PayPal API.
	 * @since 1.0.0
	 */
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
	/**
	 * Activate a subscription in the PayPal system.
	 *
	 * @param string $subscription_id The ID of the subscription to activate.
	 * @param string|null $environment The environment string ('live' or 'sandbox').
	 * @param string $reason The reason for activation.
	 * @return array The response from the PayPal API.
	 * @since 1.0.0
	 */
	public static function activate_subscription( string $subscription_id, ?string $environment = null, string $reason = 'Activation requested by LicencePress' ): array {
		$environment = sanitize_key( (string) ( $environment ?? PayPalSettings::get_environment() ) );
		$token = self::get_access_token( $environment );
		if ( '' === $token ) {
			return array(
				'success' => false,
				'error' => 'missing_access_token',
			);
		}

		$response = wp_remote_post(
			self::get_api_base_url( $environment ) . '/v1/billing/subscriptions/' . rawurlencode( $subscription_id ) . '/activate',
			array(
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
				),
				'body' => wp_json_encode(
					array(
						'reason' => $reason,
					)
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
		return is_array( $body ) ? array( 'success' => true, 'body' => $body ) : array( 'success' => false, 'error' => 'invalid_response' );
	}
	/**
	 * Create a new order in the PayPal system.
	 *
	 * @param array $payload The order data payload.
	 * @param string|null $environment The environment string ('live' or 'sandbox').
	 * @return array The response from the PayPal API.
	 * @since 1.0.0
	 */
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
	/**
	 * Capture an existing order in the PayPal system.
	 *
	 * @param string $order_id The ID of the order to capture.
	 * @param string|null $environment The environment string ('live' or 'sandbox').
	 * @return array The response from the PayPal API.
	 * @since 1.0.0
	 */
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
	/**
	 * Search for transactions in the PayPal system based on a query.
	 *
	 * @param array $query The query parameters for the search.
	 * @param string|null $environment The environment string ('live' or 'sandbox').
	 * @return array The response from the PayPal API.
	 * @since 1.0.0
	 */
	public static function search_transactions( array $query = array(), ?string $environment = null ): array {
		$environment = sanitize_key( (string) ( $environment ?? PayPalSettings::get_environment() ) );
		$token = self::get_access_token( $environment );
		if ( '' === $token ) {
			return array(
				'success' => false,
				'error' => 'missing_access_token',
			);
		}

		$endpoint = self::get_api_base_url( $environment ) . '/v1/reporting/transactions';
		if ( ! empty( $query ) ) {
			$endpoint .= '?' . http_build_query( $query );
		}

		$response = wp_remote_get(
			$endpoint,
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
		return is_array( $body ) ? array( 'success' => true, 'body' => $body ) : array( 'success' => false, 'error' => 'invalid_response' );
	}
	/**
	 * Create a new invoice in the PayPal system.
	 *
	 * @param array $payload The invoice data payload.
	 * @param string|null $environment The environment string ('live' or 'sandbox').
	 * @return array The response from the PayPal API.
	 * @since 1.0.0
	 */
	public static function create_invoice( array $payload, ?string $environment = null ): array {
		$environment = sanitize_key( (string) ( $environment ?? PayPalSettings::get_environment() ) );
		$token = self::get_access_token( $environment );
		if ( '' === $token ) {
			return array(
				'success' => false,
				'error' => 'missing_access_token',
			);
		}

		$response = wp_remote_post(
			self::get_api_base_url( $environment ) . '/v2/invoicing/invoices',
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
		return is_array( $body ) ? array( 'success' => true, 'body' => $body ) : array( 'success' => false, 'error' => 'invalid_response' );
	}
	/**
	 * Retrieve an invoice from the PayPal system by its ID.
	 *
	 * @param string $invoice_id The ID of the invoice to retrieve.
	 * @param string|null $environment The environment string ('live' or 'sandbox').
	 * @return array The response from the PayPal API.
	 * @since 1.0.0
	 */
	public static function get_invoice( string $invoice_id, ?string $environment = null ): array {
		$environment = sanitize_key( (string) ( $environment ?? PayPalSettings::get_environment() ) );
		$token = self::get_access_token( $environment );
		if ( '' === $token ) {
			return array(
				'success' => false,
				'error' => 'missing_access_token',
			);
		}

		$response = wp_remote_get(
			self::get_api_base_url( $environment ) . '/v2/invoicing/invoices/' . rawurlencode( $invoice_id ),
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
		return is_array( $body ) ? array( 'success' => true, 'body' => $body ) : array( 'success' => false, 'error' => 'invalid_response' );
	}
	/**
	 * Refund a captured payment in the PayPal system.
	 *
	 * @param string $capture_id The ID of the captured payment to refund.
	 * @param array $payload The refund data payload.
	 * @param string|null $environment The environment string ('live' or 'sandbox').
	 * @return array The response from the PayPal API.
	 * @since 1.0.0
	 */
	public static function refund_capture( string $capture_id, array $payload = array(), ?string $environment = null ): array {
		$environment = sanitize_key( (string) ( $environment ?? PayPalSettings::get_environment() ) );
		$token = self::get_access_token( $environment );
		if ( '' === $token ) {
			return array(
				'success' => false,
				'error' => 'missing_access_token',
			);
		}

		$response = wp_remote_post(
			self::get_api_base_url( $environment ) . '/v2/payments/captures/' . rawurlencode( $capture_id ) . '/refund',
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
		return is_array( $body ) ? array( 'success' => true, 'body' => $body ) : array( 'success' => false, 'error' => 'invalid_response' );
	}
	/**
	 * List the available payment methods in the PayPal system.
	 *
	 * @param string|null $environment The environment string ('live' or 'sandbox').
	 * @return array The response from the PayPal API.
	 * @since 1.0.0
	 */
	public static function list_payment_methods( ?string $environment = null ): array {
		$environment = sanitize_key( (string) ( $environment ?? PayPalSettings::get_environment() ) );
		$token = self::get_access_token( $environment );
		if ( '' === $token ) {
			return array(
				'success' => false,
				'error' => 'missing_access_token',
			);
		}

		$response = wp_remote_get(
			self::get_api_base_url( $environment ) . '/v3/vault/payment-tokens',
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
		return is_array( $body ) ? array( 'success' => true, 'body' => $body ) : array( 'success' => false, 'error' => 'invalid_response' );
	}
	/**
	 * Create a new payment method token in the PayPal system.
	 *
	 * @param array $payload The payment method token data payload.
	 * @param string|null $environment The environment string ('live' or 'sandbox').
	 * @return array The response from the PayPal API.
	 * @since 1.0.0
	 */
	public static function create_payment_method_token( array $payload, ?string $environment = null ): array {
		$environment = sanitize_key( (string) ( $environment ?? PayPalSettings::get_environment() ) );
		$token = self::get_access_token( $environment );
		if ( '' === $token ) {
			return array(
				'success' => false,
				'error' => 'missing_access_token',
			);
		}

		$response = wp_remote_post(
			self::get_api_base_url( $environment ) . '/v3/vault/setup-tokens',
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
		return is_array( $body ) ? array( 'success' => true, 'body' => $body ) : array( 'success' => false, 'error' => 'invalid_response' );
	}
	/**
	 * List the disputes in the PayPal system.
	 *
	 * @param string|null $environment The environment string ('live' or 'sandbox').
	 * @return array The response from the PayPal API.
	 * @since 1.0.0
	 */
	public static function list_disputes( ?string $environment = null ): array {
		$environment = sanitize_key( (string) ( $environment ?? PayPalSettings::get_environment() ) );
		$token = self::get_access_token( $environment );
		if ( '' === $token ) {
			return array(
				'success' => false,
				'error' => 'missing_access_token',
			);
		}

		$response = wp_remote_get(
			self::get_api_base_url( $environment ) . '/v1/customer/disputes',
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
		return is_array( $body ) ? array( 'success' => true, 'body' => $body ) : array( 'success' => false, 'error' => 'invalid_response' );
	}
	/**
	 * Retrieve a specific dispute from the PayPal system by its ID.
	 *
	 * @param string $dispute_id The ID of the dispute to retrieve.
	 * @param string|null $environment The environment string ('live' or 'sandbox').
	 * @return array The response from the PayPal API.
	 * @since 1.0.0
	 */
	public static function get_dispute( string $dispute_id, ?string $environment = null ): array {
		$environment = sanitize_key( (string) ( $environment ?? PayPalSettings::get_environment() ) );
		$token = self::get_access_token( $environment );
		if ( '' === $token ) {
			return array(
				'success' => false,
				'error' => 'missing_access_token',
			);
		}

		$response = wp_remote_get(
			self::get_api_base_url( $environment ) . '/v1/customer/disputes/' . rawurlencode( $dispute_id ),
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
		return is_array( $body ) ? array( 'success' => true, 'body' => $body ) : array( 'success' => false, 'error' => 'invalid_response' );
	}
}
