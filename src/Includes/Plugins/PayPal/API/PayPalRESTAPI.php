<?php
/**
 * Clean PayPal REST API facade for OAuth connect and credential storage.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\API
 * @version 1.0.0
 */

namespace LicencePress\Includes\Plugins\PayPal\API;

use LicencePress\Includes\Functions\Helpers\EncryptionHelper;
use LicencePress\Includes\Plugins\PayPal\API\Models\PayPalConnectionSettings;
use LicencePress\Includes\Plugins\PayPal\Includes\API\PayPalClient;
use LicencePress\Includes\Plugins\PayPal\Includes\Settings\Settings as PayPalSettings;
use LicencePress\Includes\Settings\Settings as BaseSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PayPalRESTAPI {
	/**
	 * PayPal connect URL for sandbox environment.
	 *
	 * @since 1.0.0
	 */
	public const CONNECT_URL_SANDBOX = 'https://www.sandbox.paypal.com/connect';
	/**
	 * PayPal connect URL for live environment.
	 *
	 * @since 1.0.0
	 */
	public const CONNECT_URL_LIVE    = 'https://www.paypal.com/connect';
    /**
	 * Normalizes the PayPal environment value.
	 *
	 * @param array       $settings    The settings array containing the environment information.
	 * @param string|null $environment The environment value to normalize.
	 * @return string The normalized environment ('sandbox' or 'live').
	 * @since 1.0.0
	 */
	public static function normalize_environment( array $settings = array(), ?string $environment = null ): string {
		$raw = sanitize_key( (string) ( $environment ?? ( $settings['environment'] ?? ( $settings['paypal_environment'] ?? 'sandbox' ) ) ) );
		return in_array( $raw, array( 'sandbox', 'live' ), true ) ? $raw : 'sandbox';
	}
    /**
	 * Resolves the redirect URI for the OAuth callback based on the environment.
	 *
	 * @param string|null $environment The PayPal environment (sandbox or live).
	 * @return string The resolved redirect URI.
	 * @since 1.0.0
	 */
	public static function resolve_redirect_uri( ?string $environment = null ): string {
		$environment = self::normalize_environment( array(), $environment );
		if ( function_exists( 'home_url' ) ) {
			return home_url( '/?paypal_action=callback&paypal_environment=' . $environment );
		}

		return 'https://example.com/?paypal_action=callback&paypal_environment=' . $environment;
	}
    /**
	 * Generates a unique state string for the OAuth flow.
	 *
	 * @return string The generated state string.
	 * @since 1.0.0
	 */
	private static function generate_state(): string {
		if ( function_exists( 'wp_generate_uuid4' ) ) {
			return wp_generate_uuid4();
		}

		$random = function_exists( 'random_bytes' ) ? bin2hex( random_bytes( 16 ) ) : md5( uniqid( (string) microtime( true ), true ) );
		return md5( $random . microtime( true ) );
	}
    /**
	 * Saves the OAuth state in a transient for later validation.
	 *
	 * @param string      $state       The state string to save.
	 * @param string|null $environment The PayPal environment (sandbox or live).
	 * @return bool True if the state was successfully saved, false otherwise.
	 * @since 1.0.0
	 */
	public static function save_oauth_state( string $state, ?string $environment = null ): bool {
		$environment = self::normalize_environment( array(), $environment );
		$user_id = function_exists( 'get_current_user_id' ) ? get_current_user_id() : 0;
		$transient_key = 'licencepress_paypal_oauth_state_' . $user_id . '_' . $environment;
		if ( function_exists( 'set_transient' ) ) {
			return (bool) set_transient( $transient_key, $state, 600 );
		}

		return true;
	}

	/**
	 * Validates the OAuth state against the stored transient.
	 *
	 * @param string      $state       The state string to validate.
	 * @param string|null $environment The PayPal environment (sandbox or live).
	 * @return bool True if the state is valid, false otherwise.
	 * @since 1.0.0
	 */
	public static function validate_oauth_state( string $state, ?string $environment = null ): bool {
		$environment = self::normalize_environment( array(), $environment );
		$user_id = function_exists( 'get_current_user_id' ) ? get_current_user_id() : 0;
		$transient_key = 'licencepress_paypal_oauth_state_' . $user_id . '_' . $environment;
		$expected = function_exists( 'get_transient' ) ? get_transient( $transient_key ) : null;
		return '' !== $state && $state === (string) $expected;
	}

	/**
	 * Clears the stored OAuth state transient.
	 *
	 * @param string|null $environment The PayPal environment (sandbox or live).
	 * @since 1.0.0
	 */
	public static function clear_oauth_state( ?string $environment = null ): void {
		$environment = self::normalize_environment( array(), $environment );
		$user_id = function_exists( 'get_current_user_id' ) ? get_current_user_id() : 0;
		$transient_key = 'licencepress_paypal_oauth_state_' . $user_id . '_' . $environment;
		if ( function_exists( 'delete_transient' ) ) {
			delete_transient( $transient_key );
		}
	}
    /**
	 * Builds the PayPal connect URL for initiating the OAuth flow.
	 *
	 * @param array       $settings    The PayPal settings array.
	 * @param string|null $environment The PayPal environment (sandbox or live).
	 * @return string The constructed PayPal connect URL.
	 * @since 1.0.0
	 */
	public static function build_connect_url( array $settings = array(), ?string $environment = null ): string {
		$environment = self::normalize_environment( $settings, $environment );
		$base_url = function_exists( 'admin_url' ) ? admin_url( 'admin.php?page=licencepress-paypal&paypal_action=test_connection&paypal_environment=' . $environment ) : 'https://example.com/wp-admin/admin.php?page=licencepress-paypal&paypal_action=test_connection&paypal_environment=' . $environment;
		$client_id = trim( (string) ( $settings['client_id'] ?? $settings['paypal_api_' . $environment . '_client_id'] ?? PayPalSettings::get_client_id( $environment ) ) );
		if ( '' !== $client_id ) {
			if ( function_exists( 'add_query_arg' ) ) {
				$base_url = \add_query_arg( 'client_id', $client_id, $base_url );
			} else {
				$separator = false === strpos( $base_url, '?' ) ? '?' : '&';
				$base_url .= $separator . 'client_id=' . rawurlencode( $client_id );
			}
		}

		return $base_url;
	}
    /**
	 * Normalizes the payload to an array.
	 *
	 * @param mixed $model_or_array The model object or array to normalize.
	 * @return array The normalized array.
	 * @since 1.0.0
	 */
	private static function normalize_payload( $model_or_array ): array {
		if ( is_object( $model_or_array ) && method_exists( $model_or_array, 'to_array' ) ) {
			$model_or_array = $model_or_array->to_array();
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
	 * @return array The API response.
	 * @since 1.0.0
	 */
	private static function request_api( string $path, string $method = 'GET', array $payload = array(), ?string $environment = null ): array {
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
		$args = array(
			'timeout' => 30,
			'headers' => array(
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json',
			),
		);

		if ( 'GET' !== strtoupper( $method ) ) {
			$args['body'] = wp_json_encode( $payload );
		}

		$response = wp_remote_request( $base_url . $path, $args );
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
	 * Creates a new product in the PayPal catalog.
	 *
	 * @param array       $product     The product data.
	 * @param string|null $environment The PayPal environment (sandbox or live).
	 * @return array The API response.
	 * @since 1.0.0
	 */
	public static function create_product( $product, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		$payload = self::normalize_payload( $product );
		return self::request_api( '/v1/catalogs/products', 'POST', $payload, $environment );
	}
    /**
	 * Retrieves a product from the PayPal catalog.
	 *
	 * @param string      $product_id  The ID of the product to retrieve.
	 * @param string|null $environment The PayPal environment (sandbox or live).
	 * @return array The API response.
	 * @since 1.0.0
	 */
	public static function get_product( string $product_id, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return self::request_api( '/v1/catalogs/products/' . rawurlencode( $product_id ), 'GET', array(), $environment );
	}
    /**
	 * Creates a new billing plan in PayPal.
	 *
	 * @param array       $plan        The plan data.
	 * @param string|null $environment The PayPal environment (sandbox or live).
	 * @return array The API response.
	 * @since 1.0.0
	 */
	public static function create_plan( $plan, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		$payload = self::normalize_payload( $plan );
		return self::request_api( '/v1/billing/plans', 'POST', $payload, $environment );
	}
    /**
	 * Retrieves a billing plan from PayPal.
	 *
	 * @param string      $plan_id     The ID of the plan to retrieve.
	 * @param string|null $environment The PayPal environment (sandbox or live).
	 * @return array The API response.
	 * @since 1.0.0
	 */
	public static function get_plan( string $plan_id, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return self::request_api( '/v1/billing/plans/' . rawurlencode( $plan_id ), 'GET', array(), $environment );
	}
    /**
	 * Creates a new subscription in PayPal.
	 *
	 * @param array       $subscription The subscription data.
	 * @param string|null $environment  The PayPal environment (sandbox or live).
	 * @return array The API response.
	 * @since 1.0.0
	 */
	public static function create_subscription( $subscription, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		$payload = self::normalize_payload( $subscription );
		return self::request_api( '/v1/billing/subscriptions', 'POST', $payload, $environment );
	}
    /**
	 * Retrieves a subscription from PayPal.
	 *
	 * @param string      $subscription_id The ID of the subscription to retrieve.
	 * @param string|null $environment     The PayPal environment (sandbox or live).
	 * @return array The API response.
	 * @since 1.0.0
	 */
	public static function get_subscription( string $subscription_id, ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return self::request_api( '/v1/billing/subscriptions/' . rawurlencode( $subscription_id ), 'GET', array(), $environment );
	}
    /**
	 * Activates a subscription in PayPal.
	 *
	 * @param string      $subscription_id The ID of the subscription to activate.
	 * @param string      $reason          The reason for activation.
	 * @param string|null $environment     The PayPal environment (sandbox or live).
	 * @return array The API response.
	 * @since 1.0.0
	 */
	public static function activate_subscription( string $subscription_id, string $reason = 'Activation requested by LicencePress', ?string $environment = null ): array {
		$environment = self::normalize_environment( array(), $environment );
		return self::request_api(
			'/v1/billing/subscriptions/' . rawurlencode( $subscription_id ) . '/activate',
			'POST',
			array(
				'reason' => $reason,
			),
			$environment
		);
	}
    /**
	 * Saves OAuth credentials for PayPal.
	 *
	 * @param array $settings The settings containing OAuth credentials.
	 * @return bool True on success, false on failure.
	 * @since 1.0.0
	 */
	public static function save_oauth_credentials( array $settings ): bool {
		$environment = self::normalize_environment( $settings, $settings['environment'] ?? ( $settings['paypal_environment'] ?? null ) );
		$client_id   = trim( (string) ( $settings['client_id'] ?? $settings['paypal_api_' . $environment . '_client_id'] ?? PayPalSettings::get_client_id( $environment ) ) );
		$secret      = trim( (string) ( $settings['client_secret'] ?? $settings['paypal_api_' . $environment . '_client_secret'] ?? PayPalSettings::get_client_secret( $environment ) ) );
		$app_name    = sanitize_text_field( (string) ( $settings['app_name'] ?? 'LicencePress PayPal' ) );
		$redirect_uri = self::resolve_redirect_uri( $environment );

		if ( '' === $client_id || '' === $secret ) {
			return false;
		}

		$group = BaseSettings::get_group( 'paypal', array() );
		if ( ! is_array( $group ) ) {
			$group = array();
		}

		$group['paypal_environment'] = $environment;
		$group['paypal_api_' . $environment . '_client_id'] = EncryptionHelper::encrypt( $client_id ) ?? $client_id;
		$group['paypal_api_' . $environment . '_client_secret'] = EncryptionHelper::encrypt( $secret ) ?? $secret;
		$group['paypal_api_' . $environment . '_app_name'] = $app_name;
		$group['paypal_api_' . $environment . '_redirect_uri'] = $redirect_uri;
		$group['paypal_api_' . $environment . '_oauth_connected'] = true;
		$group['paypal_api_' . $environment . '_callback'] = $redirect_uri;
		$group['paypal_api_' . $environment . '_access_token'] = trim( (string) ( $settings['access_token'] ?? $group['paypal_api_' . $environment . '_access_token'] ?? '' ) );
		$group['paypal_api_' . $environment . '_refresh_token'] = trim( (string) ( $settings['refresh_token'] ?? $group['paypal_api_' . $environment . '_refresh_token'] ?? '' ) );

		return BaseSettings::set_group( 'paypal', $group );
	}
    /**
	 * Exchanges an authorization code for OAuth tokens with PayPal.
	 *
	 * @param string      $code        The authorization code received from PayPal.
	 * @param string|null $environment The PayPal environment (sandbox or live).
	 * @return array|null The API response containing access and refresh tokens, or null on failure.
	 * @since 1.0.0
	 */
	public static function exchange_authorization_code( string $code, ?string $environment = null ): ?array {
		$environment = self::normalize_environment( array(), $environment );
		$client_id   = PayPalSettings::get_client_id( $environment );
		$client_secret = PayPalSettings::get_client_secret( $environment );
		$redirect_uri = self::resolve_redirect_uri( $environment );

		if ( '' === trim( $code ) || '' === $client_id || '' === $client_secret ) {
			return null;
		}

		$response = wp_remote_post(
			'https://api-m.' . ( 'sandbox' === $environment ? 'sandbox.' : '' ) . 'paypal.com/v1/oauth2/token',
			array(
				'timeout' => 30,
				'headers' => array(
					'Accept'        => 'application/json',
					'Authorization' => 'Basic ' . base64_encode( $client_id . ':' . $client_secret ),
					'Content-Type'  => 'application/x-www-form-urlencoded',
				),
				'body' => array(
					'grant_type'   => 'authorization_code',
					'code'         => $code,
					'redirect_uri' => $redirect_uri,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) ) {
			return null;
		}

		if ( ! empty( $body['access_token'] ) ) {
			$settings = BaseSettings::get_group( 'paypal', array() );
			if ( is_array( $settings ) ) {
				$settings['paypal_api_' . $environment . '_access_token'] = sanitize_text_field( (string) $body['access_token'] );
				$settings['paypal_api_' . $environment . '_refresh_token'] = sanitize_text_field( (string) ( $body['refresh_token'] ?? '' ) );
				$settings['paypal_api_' . $environment . '_oauth_connected'] = true;
				BaseSettings::set_group( 'paypal', $settings );
			}
		}

		return $body;
	}
    /**
	 * Retrieves the PayPal app model with connection settings.
	 *
	 * @param array       $settings    The settings containing PayPal credentials.
	 * @param string|null $environment The PayPal environment (sandbox or live).
	 * @return PayPalConnectionSettings The PayPal connection settings model.
	 * @since 1.0.0
	 */
	public static function get_app_model( array $settings = array(), ?string $environment = null ): PayPalConnectionSettings {
		$environment = self::normalize_environment( $settings, $environment );
		$app = new PayPalConnectionSettings();
		$app->environment = $environment;
		$app->client_id = trim( (string) ( $settings['client_id'] ?? $settings['paypal_' . $environment . '_client_id'] ?? PayPalSettings::get_client_id( $environment ) ) );
		$app->client_secret = trim( (string) ( $settings['client_secret'] ?? $settings['paypal_' . $environment . '_client_secret'] ?? PayPalSettings::get_client_secret( $environment ) ) );
		$app->redirect_uri = self::resolve_redirect_uri( $environment );
		$app->connected = ! empty( BaseSettings::get_group( 'paypal', array() )['paypal_' . $environment . '_oauth_connected'] ?? false );
		return $app;
	}
}
