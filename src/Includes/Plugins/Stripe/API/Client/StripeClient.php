<?php
/**
 * Stripe REST client used for all Stripe API interactions.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\Stripe\API\Client
 */

namespace LicencePress\Includes\Plugins\Stripe\API\Client;

use LicencePress\Includes\Plugins\Stripe\Includes\Settings\Settings as StripeSettings;
use Stripe\Balance;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Invoice;
use Stripe\PaymentIntent as StripePaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Price;
use Stripe\Product;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\StripeClient as StripeSdkClient;
use Stripe\Subscription;
use Stripe\Dispute;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class StripeClient {
	/**
	 * Get the configured secret key for the selected environment.
	 *
	 * @param string|null $environment The environment override.
	 * @return string The Stripe API secret key.
	 */
	public static function get_secret_key( ?string $environment = null ): string {
		$environment = sanitize_key( (string) ( $environment ?? StripeSettings::get_environment() ) );
		$secret_key = StripeSettings::get_secret_key( $environment );
		return is_string( $secret_key ) ? trim( (string) $secret_key ) : '';
	}

	/**
	 * Build and return a Stripe SDK client instance.
	 *
	 * @param string|null $environment The environment override.
	 * @return StripeSdkClient|null The Stripe client or null if credentials are missing.
	 */
	public static function build_sdk_client( ?string $environment = null ): ?StripeSdkClient {
		$secret_key = self::get_secret_key( $environment );
		if ( '' === $secret_key ) {
			return null;
		}

		return new StripeSdkClient( $secret_key );
	}

	/**
	 * Get the API base URL for the selected environment.
	 *
	 * @param string|null $environment The environment override.
	 * @return string The Stripe API base URL.
	 */
	public static function get_api_base_url( ?string $environment = null ): string {
		$environment = sanitize_key( (string) ( $environment ?? StripeSettings::get_environment() ) );
		return 'live' === $environment ? 'https://api.stripe.com/v1' : 'https://api.stripe.com/v1';
	}

	/**
	 * Validate the Stripe secret key by retrieving the balance.
	 *
	 * @param string|null $environment The environment override.
	 * @param string|null $secret_key The secret key override.
	 * @return array The normalized validation result.
	 */
	public static function validate_connection( ?string $environment = null, ?string $secret_key = null ): array {
		$environment = sanitize_key( (string) ( $environment ?? StripeSettings::get_environment() ) );
		$secret_key = trim( (string) ( $secret_key ?? self::get_secret_key( $environment ) ) );
		if ( '' === $secret_key ) {
			return array(
				'success' => false,
				'connected' => false,
				'error' => 'missing_secret_key',
				'environment' => $environment,
			);
		}

		try {
			Stripe::setApiKey( $secret_key );
			$balance = Balance::retrieve();
			$payload = self::normalize_response( $balance );

			return array(
				'success' => true,
				'connected' => true,
				'environment' => $environment,
				'body' => $payload,
			);
		} catch ( \Throwable $exception ) {
			return array(
				'success' => false,
				'connected' => false,
				'error' => $exception->getMessage(),
				'environment' => $environment,
			);
		}
	}

	/**
	 * Create a Stripe checkout session.
	 *
	 * @param array $payload The checkout payload.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function create_checkout_session( array $payload, ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->checkout->sessions->create( $payload );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * Create a Stripe product.
	 *
	 * @param array $payload The product payload.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function create_product( array $payload, ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->products->create( $payload );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * Create a Stripe price.
	 *
	 * @param array $payload The price payload.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function create_price( array $payload, ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->prices->create( $payload );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * Create a Stripe payment intent for one-time payments.
	 *
	 * @param array $payload The payment intent payload.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function create_payment_intent( array $payload, ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->paymentIntents->create( $payload );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * Retrieve a Stripe payment intent.
	 *
	 * @param string $payment_intent_id The payment intent ID.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function retrieve_payment_intent( string $payment_intent_id, ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->paymentIntents->retrieve( $payment_intent_id );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * Create a Stripe setup intent for saving payment methods.
	 *
	 * @param array $payload The setup intent payload.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function create_setup_intent( array $payload, ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->setupIntents->create( $payload );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * Retrieve a Stripe setup intent.
	 *
	 * @param string $setup_intent_id The setup intent ID.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function retrieve_setup_intent( string $setup_intent_id, ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->setupIntents->retrieve( $setup_intent_id );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * Create a Stripe customer.
	 *
	 * @param array $payload The customer payload.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function create_customer( array $payload, ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->customers->create( $payload );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * Create a Stripe subscription.
	 *
	 * @param array $payload The subscription payload.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function create_subscription( array $payload, ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->subscriptions->create( $payload );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * Retrieve a Stripe subscription.
	 *
	 * @param string $subscription_id The subscription ID.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function retrieve_subscription( string $subscription_id, ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->subscriptions->retrieve( $subscription_id );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * Update a Stripe subscription.
	 *
	 * @param string $subscription_id The subscription ID.
	 * @param array $payload The update payload.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function update_subscription( string $subscription_id, array $payload, ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->subscriptions->update( $subscription_id, $payload );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * Attach a Stripe payment method to a customer.
	 *
	 * @param string $customer_id The customer ID.
	 * @param string $payment_method_id The payment method ID.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function attach_payment_method( string $customer_id, string $payment_method_id, ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->paymentMethods->attach( $payment_method_id, array( 'customer' => $customer_id ) );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * Cancel a Stripe subscription.
	 *
	 * @param string $subscription_id The subscription ID.
	 * @param bool $cancel_immediately Whether to cancel immediately or at period end.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function cancel_subscription( string $subscription_id, bool $cancel_immediately = false, ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			if ( $cancel_immediately ) {
				$response = $client->subscriptions->cancel( $subscription_id, array( 'invoice_now' => false ) );
			} else {
				$response = $client->subscriptions->update( $subscription_id, array( 'cancel_at_period_end' => true ) );
			}
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * Create a Stripe payment method.
	 *
	 * @param array $payload The payment method payload.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function create_payment_method( array $payload, ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->paymentMethods->create( $payload );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * Retrieve a Stripe payment method.
	 *
	 * @param string $payment_method_id The payment method ID.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function retrieve_payment_method( string $payment_method_id, ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->paymentMethods->retrieve( $payment_method_id );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * Create a Stripe invoice.
	 *
	 * @param array $payload The invoice payload.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function create_invoice( array $payload, ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->invoices->create( $payload );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * Retrieve a Stripe invoice.
	 *
	 * @param string $invoice_id The invoice ID.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function retrieve_invoice( string $invoice_id, ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->invoices->retrieve( $invoice_id );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * List Stripe invoices.
	 *
	 * @param array $params Additional filters.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function list_invoices( array $params = array(), ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->invoices->all( $params );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * Retrieve a Stripe customer.
	 *
	 * @param string $customer_id The customer ID.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function retrieve_customer( string $customer_id, ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->customers->retrieve( $customer_id );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * List Stripe customers.
	 *
	 * @param array $params Additional filters.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function list_customers( array $params = array(), ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->customers->all( $params );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * List Stripe products.
	 *
	 * @param array $params Additional filters.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function list_products( array $params = array(), ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->products->all( $params );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * List Stripe prices.
	 *
	 * @param array $params Additional filters.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function list_prices( array $params = array(), ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->prices->all( $params );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * Create a Stripe refund.
	 *
	 * @param array $payload The refund payload.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function create_refund( array $payload, ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->refunds->create( $payload );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * Search Stripe transactions.
	 *
	 * @param string $query The Stripe Search query string.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function search_transactions( string $query, ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->balanceTransactions->search( array( 'query' => $query ) );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * List payment methods for a customer.
	 *
	 * @param string $customer_id The customer ID.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function list_payment_methods( string $customer_id, ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->paymentMethods->all( array( 'customer' => $customer_id, 'type' => 'card' ) );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * List disputes.
	 *
	 * @param array $params Additional filters.
	 * @param string|null $environment The environment override.
	 * @return array The normalized response.
	 */
	public static function list_disputes( array $params = array(), ?string $environment = null ): array {
		$client = self::build_sdk_client( $environment );
		if ( null === $client ) {
			return array( 'success' => false, 'error' => 'missing_secret_key' );
		}

		try {
			$response = $client->disputes->all( $params );
			return array( 'success' => true, 'body' => self::normalize_response( $response ) );
		} catch ( \Throwable $exception ) {
			return array( 'success' => false, 'error' => $exception->getMessage() );
		}
	}

	/**
	 * Normalize a Stripe response object to a plain array.
	 *
	 * @param mixed $response The Stripe object or array.
	 * @return array The normalized data.
	 */
	private static function normalize_response( $response ): array {
		if ( is_array( $response ) ) {
			return $response;
		}

		if ( is_object( $response ) ) {
			if ( method_exists( $response, 'jsonSerialize' ) ) {
				return $response->jsonSerialize();
			}

			if ( method_exists( $response, 'toArray' ) ) {
				return $response->toArray();
			}
		}

		return array();
	}
}
