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

	public static function build_connect_url( array $settings, string $state, ?string $environment = null ): string {
		$environment = sanitize_key( (string) ( $environment ?? ( $settings['paypal_environment'] ?? 'sandbox' ) ) );
		$client_id   = sanitize_text_field( (string) ( $settings[ 'paypal_' . $environment . '_client_id' ] ?? $settings['paypal_client_id'] ?? '' ) );
		if ( '' === $client_id ) {
			return admin_url( 'admin.php?page=licencepress-paypal' );
		}

		$base_url = 'https://www.paypal.com/connect';
		if ( 'sandbox' === $environment ) {
			$base_url = 'https://www.sandbox.paypal.com/connect';
		}

		return add_query_arg(
			array(
				'flowEntry'    => 'static',
				'client_id'    => $client_id,
				'scope'        => 'openid profile email https://uri.paypal.com/services/payments/reporting',
				'redirect_uri' => admin_url( 'admin.php?page=licencepress-paypal&paypal_action=callback&paypal_environment=' . $environment ),
				'state'        => $state,
			),
			$base_url
		);
	}
}
