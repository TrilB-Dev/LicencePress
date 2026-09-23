<?php
/**
 * Stripe connection service for validating saved secret keys and storing connection status.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\Stripe\Includes\Functions\Helpers
 */

namespace LicencePress\Includes\Plugins\Stripe\Includes\Functions\Helpers;

use LicencePress\Includes\Plugins\Stripe\API\StripeAPI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class StripeConnectionService {
	/**
	 * Validate a Stripe connection using an environment-specific secret key.
	 *
	 * @param array       $settings    The Stripe settings payload.
	 * @param string|null $environment The environment override.
	 * @return array The normalized connection validation result.
	 */
	public static function test_connection( array $settings = array(), ?string $environment = null ): array {
		return StripeAPI::validate_connection( $settings, $environment );
	}
}
