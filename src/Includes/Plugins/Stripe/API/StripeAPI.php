<?php
/**
 * Stripe REST API facade for payments, subscriptions, invoices, refunds, and dispute management.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\Stripe\API
 */

namespace LicencePress\Includes\Plugins\Stripe\API;

use LicencePress\Includes\Plugins\Stripe\API\Client\StripeClient;
use LicencePress\Includes\Plugins\Stripe\API\Models\Connection;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class StripeAPI {
	/**
	 * Normalizes the Stripe environment.
	 *
	 * @param array       $settings    The settings array.
	 * @param string|null $environment The environment to normalize.
	 * @return string The normalized environment ('sandbox' or 'live').
	 */
	public static function normalize_environment( array $settings = array(), ?string $environment = null ): string {
		$raw = sanitize_key( (string) ( $environment ?? ( $settings['environment'] ?? ( $settings['stripe_environment'] ?? 'sandbox' ) ) ) );
		return in_array( $raw, array( 'sandbox', 'live' ), true ) ? $raw : 'sandbox';
	}

	/**
	 * Normalizes the payload to an array.
	 *
	 * @param mixed $model_or_array The model object or array to normalize.
	 * @return array<string, mixed> The normalized array.
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
	 * Validate the secret key for the selected environment.
	 *
	 * @param array       $settings    The settings array.
	 * @param string|null $environment The environment override.
	 * @return array The normalized validation result.
	 */
	public static function validate_connection( array $settings = array(), ?string $environment = null ): array {
		$environment = self::normalize_environment( $settings, $environment );
		$connection = Connection::from_settings( array_merge( $settings, array( 'environment' => $environment ) ) );
		$secret_key = trim( (string) $connection->secret_key );

		if ( '' === $secret_key ) {
			return array(
				'success' => false,
				'connected' => false,
				'error' => 'missing_secret_key',
				'environment' => $environment,
			);
		}

		return StripeClient::validate_connection( $environment, $secret_key );
	}

	/**
	 * Create a Stripe checkout session.
	 *
	 * @param mixed       $session     The session payload or model.
	 * @param string|null $environment The environment override.
	 * @return array The API response.
	 */
	public static function create_checkout_session( $session, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::create_checkout_session( self::normalize_payload( $session ), $environment );
	}

	/**
	 * Create a Stripe payment intent for one-time payments.
	 *
	 * @param mixed       $payment_intent The payment intent payload or model.
	 * @param string|null $environment    The environment override.
	 * @return array The API response.
	 */
	public static function create_payment_intent( $payment_intent, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::create_payment_intent( self::normalize_payload( $payment_intent ), $environment );
	}

	/**
	 * Retrieve a Stripe payment intent.
	 *
	 * @param string      $payment_intent_id The Stripe payment intent ID.
	 * @param string|null $environment       The environment override.
	 * @return array The API response.
	 */
	public static function retrieve_payment_intent( string $payment_intent_id, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::retrieve_payment_intent( $payment_intent_id, $environment );
	}

	/**
	 * Create a Stripe setup intent for saved payment method flows.
	 *
	 * @param mixed       $setup_intent The setup intent payload or model.
	 * @param string|null $environment  The environment override.
	 * @return array The API response.
	 */
	public static function create_setup_intent( $setup_intent, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::create_setup_intent( self::normalize_payload( $setup_intent ), $environment );
	}

	/**
	 * Retrieve a Stripe setup intent.
	 *
	 * @param string      $setup_intent_id The Stripe setup intent ID.
	 * @param string|null $environment    The environment override.
	 * @return array The API response.
	 */
	public static function retrieve_setup_intent( string $setup_intent_id, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::retrieve_setup_intent( $setup_intent_id, $environment );
	}

	/**
	 * Create a Stripe payment method.
	 *
	 * @param mixed       $payment_method The payment method payload or model.
	 * @param string|null $environment    The environment override.
	 * @return array The API response.
	 */
	public static function create_payment_method( $payment_method, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::create_payment_method( self::normalize_payload( $payment_method ), $environment );
	}

	/**
	 * Retrieve a Stripe payment method.
	 *
	 * @param string      $payment_method_id The payment method ID.
	 * @param string|null $environment       The environment override.
	 * @return array The API response.
	 */
	public static function retrieve_payment_method( string $payment_method_id, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::retrieve_payment_method( $payment_method_id, $environment );
	}

	/**
	 * Create a Stripe customer.
	 *
	 * @param mixed       $customer    The customer payload or model.
	 * @param string|null $environment The environment override.
	 * @return array The API response.
	 */
	public static function create_customer( $customer, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::create_customer( self::normalize_payload( $customer ), $environment );
	}

	/**
	 * Retrieve a Stripe customer.
	 *
	 * @param string      $customer_id The Stripe customer ID.
	 * @param string|null $environment The environment override.
	 * @return array The API response.
	 */
	public static function retrieve_customer( string $customer_id, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::retrieve_customer( $customer_id, $environment );
	}

	/**
	 * List Stripe customers.
	 *
	 * @param array       $params      The filter parameters.
	 * @param string|null $environment The environment override.
	 * @return array The API response.
	 */
	public static function list_customers( array $params = array(), ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::list_customers( $params, $environment );
	}

	/**
	 * Create a Stripe product.
	 *
	 * @param mixed       $product     The product payload or model.
	 * @param string|null $environment The environment override.
	 * @return array The API response.
	 */
	public static function create_product( $product, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::create_product( self::normalize_payload( $product ), $environment );
	}

	/**
	 * Create a Stripe price.
	 *
	 * @param mixed       $price       The price payload or model.
	 * @param string|null $environment The environment override.
	 * @return array The API response.
	 */
	public static function create_price( $price, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::create_price( self::normalize_payload( $price ), $environment );
	}

	/**
	 * List Stripe products.
	 *
	 * @param array       $params      The filter parameters.
	 * @param string|null $environment The environment override.
	 * @return array The API response.
	 */
	public static function list_products( array $params = array(), ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::list_products( $params, $environment );
	}

	/**
	 * List Stripe prices.
	 *
	 * @param array       $params      The filter parameters.
	 * @param string|null $environment The environment override.
	 * @return array The API response.
	 */
	public static function list_prices( array $params = array(), ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::list_prices( $params, $environment );
	}

	/**
	 * Create a Stripe subscription.
	 *
	 * @param mixed       $subscription The subscription payload or model.
	 * @param string|null $environment  The environment override.
	 * @return array The API response.
	 */
	public static function create_subscription( $subscription, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::create_subscription( self::normalize_payload( $subscription ), $environment );
	}

	/**
	 * Retrieve a Stripe subscription.
	 *
	 * @param string      $subscription_id The Stripe subscription ID.
	 * @param string|null $environment     The environment override.
	 * @return array The API response.
	 */
	public static function retrieve_subscription( string $subscription_id, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::retrieve_subscription( $subscription_id, $environment );
	}

	/**
	 * Update a Stripe subscription.
	 *
	 * @param string      $subscription_id The Stripe subscription ID.
	 * @param mixed       $payload         The update payload or model.
	 * @param string|null $environment     The environment override.
	 * @return array The API response.
	 */
	public static function update_subscription( string $subscription_id, $payload, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::update_subscription( $subscription_id, self::normalize_payload( $payload ), $environment );
	}

	/**
	 * Attach a Stripe payment method to a customer.
	 *
	 * @param string      $customer_id       The Stripe customer ID.
	 * @param string      $payment_method_id The Stripe payment method ID.
	 * @param string|null $environment       The environment override.
	 * @return array The API response.
	 */
	public static function attach_payment_method( string $customer_id, string $payment_method_id, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::attach_payment_method( $customer_id, $payment_method_id, $environment );
	}

	/**
	 * Cancel a Stripe subscription.
	 *
	 * @param string      $subscription_id The Stripe subscription ID.
	 * @param bool        $cancel_immediately Whether to end immediately.
	 * @param string|null $environment The environment override.
	 * @return array The API response.
	 */
	public static function cancel_subscription( string $subscription_id, bool $cancel_immediately = false, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::cancel_subscription( $subscription_id, $cancel_immediately, $environment );
	}

	/**
	 * Create a Stripe invoice.
	 *
	 * @param mixed       $invoice     The invoice payload or model.
	 * @param string|null $environment The environment override.
	 * @return array The API response.
	 */
	public static function create_invoice( $invoice, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::create_invoice( self::normalize_payload( $invoice ), $environment );
	}

	/**
	 * Retrieve a Stripe invoice.
	 *
	 * @param string      $invoice_id  The Stripe invoice ID.
	 * @param string|null $environment The environment override.
	 * @return array The API response.
	 */
	public static function retrieve_invoice( string $invoice_id, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::retrieve_invoice( $invoice_id, $environment );
	}

	/**
	 * List Stripe invoices.
	 *
	 * @param array       $params      The filter parameters.
	 * @param string|null $environment The environment override.
	 * @return array The API response.
	 */
	public static function list_invoices( array $params = array(), ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::list_invoices( $params, $environment );
	}

	/**
	 * Create a Stripe refund.
	 *
	 * @param mixed       $refund      The refund payload or model.
	 * @param string|null $environment The environment override.
	 * @return array The API response.
	 */
	public static function create_refund( $refund, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::create_refund( self::normalize_payload( $refund ), $environment );
	}

	/**
	 * Search Stripe balance transactions.
	 *
	 * @param string      $query       The search query string.
	 * @param string|null $environment The environment override.
	 * @return array The API response.
	 */
	public static function search_transactions( string $query, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::search_transactions( $query, $environment );
	}

	/**
	 * List Stripe payment methods for a customer.
	 *
	 * @param string      $customer_id The Stripe customer ID.
	 * @param string|null $environment The environment override.
	 * @return array The API response.
	 */
	public static function list_payment_methods( string $customer_id, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::list_payment_methods( $customer_id, $environment );
	}

	/**
	 * List Stripe disputes.
	 *
	 * @param array       $params      The dispute query parameters.
	 * @param string|null $environment The environment override.
	 * @return array The API response.
	 */
	public static function list_disputes( array $params = array(), ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return StripeClient::list_disputes( $params, $environment );
	}
}
