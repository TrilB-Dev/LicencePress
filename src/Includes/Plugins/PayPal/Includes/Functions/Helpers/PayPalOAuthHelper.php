<?php
/**
 * OAuth helper functions for the PayPal plugin.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\Includes\Functions
 * @since 1.0.0
 */

namespace LicencePress\Includes\Plugins\PayPal\Includes\Functions\Helpers;

final class PayPalOAuthHelper {
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

	private static function site_url( string $path = '' ): string {
		if ( function_exists( 'home_url' ) ) {
			return home_url( $path );
		}

		$uri = 'https://example.com';
		if ( '' !== $path ) {
			$uri = rtrim( $uri, '/' ) . '/' . ltrim( $path, '/' );
		}

		return $uri;
	}

	private static function build_query_string( array $query, string $base_url ): string {
		if ( function_exists( 'add_query_arg' ) ) {
			return add_query_arg( $query, $base_url );
		}

		$separator = false === strpos( $base_url, '?' ) ? '?' : '&';
		$parts     = array();
		foreach ( $query as $key => $value ) {
			$parts[] = rawurlencode( (string) $key ) . '=' . rawurlencode( (string) $value );
		}

		if ( empty( $parts ) ) {
			return $base_url;
		}

		return $base_url . $separator . implode( '&', $parts );
	}

	public static function generate_state(): string {
		return function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : md5( wp_rand() . microtime() );
	}

	public static function save_state( string $state, ?string $environment = null ): void {
		$environment = sanitize_key( (string) ( $environment ?? 'sandbox' ) );
		set_transient( 'licencepress_paypal_oauth_state_' . get_current_user_id() . '_' . $environment, $state, 600 );
	}

	public static function validate_state( string $state, ?string $environment = null ): bool {
		$environment = sanitize_key( (string) ( $environment ?? 'sandbox' ) );
		$expected    = get_transient( 'licencepress_paypal_oauth_state_' . get_current_user_id() . '_' . $environment );
		return '' !== $state && $state === (string) $expected;
	}

	public static function clear_state( ?string $environment = null ): void {
		$environment = sanitize_key( (string) ( $environment ?? 'sandbox' ) );
		delete_transient( 'licencepress_paypal_oauth_state_' . get_current_user_id() . '_' . $environment );
	}

	public static function get_public_callback_url( ?string $environment = null ): string {
		$environment = sanitize_key( (string) ( $environment ?? 'sandbox' ) );
		return self::site_url( '/?paypal_action=callback&paypal_environment=' . $environment );
	}

	public static function get_success_redirect_url(): string {
		if ( function_exists( 'admin_url' ) ) {
			return admin_url( 'admin.php?page=licencepress&group=settings&tab=billing#paypal' );
		}

		return self::site_url( '/?page=licencepress&group=settings&tab=billing#paypal' );
	}

	public static function build_connect_url( array $settings, string $state, ?string $environment = null ): string {
		$environment = sanitize_key( (string) ( $environment ?? ( $settings['paypal_environment'] ?? 'sandbox' ) ) );
		$client_id   = sanitize_text_field( (string) ( $settings[ 'paypal_' . $environment . '_client_id' ] ?? $settings['paypal_client_id'] ?? '' ) );
		if ( '' === $client_id ) {
			return self::site_url( '/?page=licencepress&group=settings&tab=billing&paypal_error=missing_client_id&paypal_environment=' . $environment . '#paypal' );
		}

		/*
		 * PayPal's official PHP Server SDK implements the OAuth 2 client-credentials
		 * grant used for API access. It does not handle the user consent authorization
		 * code flow for PayPal Connect login. We therefore launch the consent flow
		 * manually using the official PayPal Connect endpoint and then exchange the
		 * returned authorization code at the OAuth token endpoint.
		 */
		$base_url = 'https://www.paypal.com/connect';
		if ( 'sandbox' === $environment ) {
			$base_url = 'https://www.sandbox.paypal.com/connect';
		}

		return self::build_query_string(
			array(
				'flowEntry'    => 'static',
				'client_id'    => $client_id,
				'scope'        => 'openid profile email https://uri.paypal.com/services/payments/reporting',
				'redirect_uri' => self::get_public_callback_url( $environment ),
				'state'        => $state,
			),
			$base_url
		);
	}
}
