<?php
/**
 * OAuth helper functions for the PayPal plugin.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\Includes\Functions
 * @since 1.0.0
 */

namespace LicencePress\Includes\Plugins\PayPal\Includes\Functions;

final class PayPalOAuthHelper {
	public static function generate_state(): string {
		return function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : md5( wp_rand() . microtime() );
	}

	public static function save_state( string $state ): void {
		set_transient( 'licencepress_paypal_oauth_state_' . get_current_user_id(), $state, 600 );
	}

	public static function validate_state( string $state ): bool {
		$expected = get_transient( 'licencepress_paypal_oauth_state_' . get_current_user_id() );
		return '' !== $state && $state === (string) $expected;
	}

	public static function clear_state(): void {
		delete_transient( 'licencepress_paypal_oauth_state_' . get_current_user_id() );
	}

	public static function build_connect_url( array $settings, string $state ): string {
		$client_id = sanitize_text_field( (string) ( $settings['paypal_client_id'] ?? '' ) );
		if ( '' === $client_id ) {
			return admin_url( 'admin.php?page=licencepress-paypal' );
		}

		$base_url    = 'https://www.paypal.com/connect';
		$environment = sanitize_key( (string) ( $settings['paypal_environment'] ?? 'sandbox' ) );
		if ( 'sandbox' === $environment ) {
			$base_url = 'https://www.sandbox.paypal.com/connect';
		}

		return add_query_arg(
			array(
				'flowEntry'    => 'static',
				'client_id'    => $client_id,
				'scope'        => 'openid profile email https://uri.paypal.com/services/payments/reporting',
				'redirect_uri' => admin_url( 'admin.php?page=licencepress-paypal&paypal_action=callback' ),
				'state'        => $state,
			),
			$base_url
		);
	}
}
