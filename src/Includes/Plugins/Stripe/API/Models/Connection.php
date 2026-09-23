<?php
/**
 * Stripe connection model used for storing credentials and validating a secret-key connection.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\Stripe\API\Models
 */

namespace LicencePress\Includes\Plugins\Stripe\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Connection {
	/**
	 * The Stripe environment ('sandbox' or 'live').
	 *
	 * @var string
	 */
	public string $environment = 'sandbox';

	/**
	 * The Stripe secret key for the selected environment.
	 *
	 * @var string
	 */
	public string $secret_key = '';

	/**
	 * Whether the Stripe connection is established.
	 *
	 * @var bool
	 */
	public bool $connected = false;

	/**
	 * Create a new Connection model instance from the given settings array.
	 *
	 * @param array $settings The settings array.
	 * @return self The Connection model instance.
	 */
	public static function from_settings( array $settings = array() ): self {
		$model = new self();
		$model->environment = sanitize_key( (string) ( $settings['environment'] ?? $settings['stripe_environment'] ?? 'sandbox' ) );
		$model->secret_key = html_entity_decode(
			(string) ( $settings['secret_key'] ?? $settings['stripe_api_' . $model->environment . '_secret_key'] ?? '' ),
			ENT_QUOTES | ENT_HTML5,
			'UTF-8'
		);
		$model->connected = ! empty( $settings['connected'] );

		return $model;
	}

	/**
	 * Determine if the connection model has valid credentials.
	 *
	 * @return bool True if the connection has a secret key, false otherwise.
	 */
	public function is_valid(): bool {
		return '' !== trim( $this->secret_key );
	}

	/**
	 * Convert the connection model to an associative array.
	 *
	 * @return array The payload array.
	 */
	public function to_payload(): array {
		return $this->to_settings_array();
	}

	/**
	 * Convert the connection model to a settings array.
	 *
	 * @return array The settings array.
	 */
	public function to_settings_array(): array {
		return array(
			'environment' => $this->environment,
			'secret_key' => $this->secret_key,
			'connected' => $this->connected,
		);
	}
}
