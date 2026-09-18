<?php
/**
 * Helper methods for the PayPal OAuth connect flow.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\Includes\Functions\Helpers
 */

namespace LicencePress\Includes\Plugins\PayPal\Includes\Functions\Helpers;

use LicencePress\Includes\Plugins\PayPal\Includes\Settings\Settings as PayPalSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PayPalOAuthHelper {
	/**
	 * Registers the allowed redirect hosts for the PayPal OAuth flow.
	 *
	 * @return array The list of allowed redirect hosts.
	 * @since 1.0.0
	 */
	public static function register_allowed_redirect_hosts(): array {
		$hosts = array(
			'paypal.com',
			'sandbox.paypal.com',
			'www.paypal.com',
			'www.sandbox.paypal.com',
		);

		if ( function_exists( 'add_filter' ) ) {
			add_filter(
				'allowed_redirect_hosts',
				static function ( array $existing_hosts ) use ( $hosts ): array {
					foreach ( $hosts as $host ) {
						$existing_hosts[] = $host;
					}

					return array_values( array_unique( array_filter( $existing_hosts ) ) );
				},
				10,
				1
			);
		}

		if ( function_exists( 'apply_filters' ) ) {
			return apply_filters( 'allowed_redirect_hosts', $hosts );
		}

		return $hosts;
	}
	/**
	 * Retrieves the site URL, falling back to a default if necessary.
	 *
	 * @param string $path Optional path to append to the site URL.
	 * @return string The site URL.
	 * @since 1.0.0
	 */
	private static function site_url( string $path = '' ): string {
		if ( function_exists( 'home_url' ) ) {
			return home_url( $path );
		}

		$base = 'https://example.com';
		if ( '' === $path ) {
			return $base;
		}

		return rtrim( $base, '/' ) . '/' . ltrim( $path, '/' );
	}
	/**
	 * Builds a query string for a given base URL and query parameters.
	 *
	 * @param array  $query    The query parameters as an associative array.
	 * @param string $base_url The base URL to append the query string to.
	 * @return string The resulting URL with the query string.
	 * @since 1.0.0
	 */
	private static function build_query_string( array $query, string $base_url ): string {
		if ( function_exists( 'add_query_arg' ) ) {
			return add_query_arg( $query, $base_url );
		}

		$separator = false === strpos( $base_url, '?' ) ? '?' : '&';
		$parts = array();
		foreach ( $query as $key => $value ) {
			$parts[] = rawurlencode( (string) $key ) . '=' . rawurlencode( (string) $value );
		}

		if ( empty( $parts ) ) {
			return $base_url;
		}

		return $base_url . $separator . implode( '&', $parts );
	}
	/**
	 * Generates a unique state string for the OAuth flow.
	 *
	 * @return string The generated state string.
	 * @since 1.0.0
	 */
	public static function generate_state(): string {
		if ( function_exists( 'wp_generate_uuid4' ) ) {
			return wp_generate_uuid4();
		}

		$random = function_exists( 'random_bytes' ) ? bin2hex( random_bytes( 16 ) ) : md5( uniqid( (string) microtime( true ), true ) );
		return md5( $random . microtime( true ) );
	}
	/**
	 * Saves the generated state string for the current user and environment.
	 *
	 * @param string      $state       The state string to save.
	 * @param string|null $environment The PayPal environment (sandbox or live).
	 * @since 1.0.0
	 */
	public static function save_state( string $state, ?string $environment = null ): void {
		$environment = sanitize_key( (string) ( $environment ?? 'sandbox' ) );
		$user_id = function_exists( 'get_current_user_id' ) ? get_current_user_id() : 0;
		if ( function_exists( 'set_transient' ) ) {
			set_transient( 'licencepress_paypal_oauth_state_' . $user_id . '_' . $environment, $state, 600 );
		}
	}

	/**
	 * Validates the provided state string against the saved state for the current user and environment.
	 *
	 * @param string      $state       The state string to validate.
	 * @param string|null $environment The PayPal environment (sandbox or live).
	 * @return bool True if the state is valid, false otherwise.
	 * @since 1.0.0
	 */
	public static function validate_state( string $state, ?string $environment = null ): bool {
		$environment = sanitize_key( (string) ( $environment ?? 'sandbox' ) );
		$user_id = function_exists( 'get_current_user_id' ) ? get_current_user_id() : 0;
		$expected = function_exists( 'get_transient' ) ? get_transient( 'licencepress_paypal_oauth_state_' . $user_id . '_' . $environment ) : null;
		return '' !== $state && $state === (string) $expected;
	}

	/**
	 * Clears the saved state string for the current user and environment.
	 *
	 * @param string|null $environment The PayPal environment (sandbox or live).
	 * @since 1.0.0
	 */
	public static function clear_state( ?string $environment = null ): void {
		$environment = sanitize_key( (string) ( $environment ?? 'sandbox' ) );
		$user_id = function_exists( 'get_current_user_id' ) ? get_current_user_id() : 0;
		if ( function_exists( 'delete_transient' ) ) {
			delete_transient( 'licencepress_paypal_oauth_state_' . $user_id . '_' . $environment );
		}
	}
	/**
	 * Retrieves the public callback URL for the OAuth flow.
	 *
	 * @param string|null $environment The PayPal environment (sandbox or live).
	 * @return string The public callback URL.
	 * @since 1.0.0
	 */
	public static function get_public_callback_url( ?string $environment = null ): string {
		$environment = sanitize_key( (string) ( $environment ?? 'sandbox' ) );
		$callback = self::site_url( '/?paypal_action=callback&paypal_environment=' . $environment );

		if ( function_exists( 'apply_filters' ) ) {
			$callback = (string) apply_filters( 'licencepress_paypal_callback_url', $callback, $environment );
		}

		return $callback;
	}
	/**
	 * Retrieves the success redirect URL after a successful OAuth connection.
	 *
	 * @return string The success redirect URL.
	 * @since 1.0.0
	 */
	public static function get_success_redirect_url(): string {
		if ( function_exists( 'admin_url' ) ) {
			return admin_url( 'admin.php?page=licencepress&group=settings&tab=billing#paypal' );
		}

		return self::site_url( '/wp-admin/admin.php?page=licencepress&group=settings&tab=billing#paypal' );
	}
	/**
	 * Builds the PayPal connect URL for initiating the OAuth flow.
	 *
	 * @param array       $settings    The PayPal settings array.
	 * @param string      $state       The state string for the OAuth flow.
	 * @param string|null $environment The PayPal environment (sandbox or live).
	 * @return string The constructed PayPal connect URL.
	 * @since 1.0.0
	 */
	public static function build_connect_url( array $settings, string $state, ?string $environment = null ): string {
		$environment = sanitize_key( (string) ( $environment ?? ( $settings['paypal_environment'] ?? ( $settings['environment'] ?? 'sandbox' ) ) ) );
		$client_id = trim( (string) ( $settings['client_id'] ?? $settings[ 'paypal_' . $environment . '_client_id' ] ?? $settings['paypal_client_id'] ?? PayPalSettings::get_client_id( $environment ) ) );
		if ( '' === $client_id ) {
			return self::site_url( '/?page=licencepress&group=settings&tab=billing&paypal_error=missing_client_id&paypal_environment=' . $environment . '#paypal' );
		}

		$base_url = 'sandbox' === $environment ? 'https://www.sandbox.paypal.com/connect' : 'https://www.paypal.com/connect';
		$callback_url = self::get_public_callback_url( $environment );

		return self::build_query_string(
			array(
				'flowEntry'    => 'static',
				'client_id'    => $client_id,
				'scope'        => 'openid profile email https://uri.paypal.com/services/payments/reporting',
				'redirect_uri' => $callback_url,
				'response_type' => 'code',
				'state'        => $state,
			),
			$base_url
		);
	}
}
