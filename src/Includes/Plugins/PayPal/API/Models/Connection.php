<?php
/**
 * PayPal connection model used for storing credentials and validating a client_credentials connection.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\API\Models
 */

namespace LicencePress\Includes\Plugins\PayPal\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Connection {
	/**
	 * The PayPal environment ('sandbox' or 'live').
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public string $environment = 'sandbox';
	/**
	 * The PayPal client ID.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public string $client_id = '';
	/**
	 * The PayPal client secret.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public string $client_secret = '';
	/**
	 * The redirect URI for the PayPal connection.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public string $redirect_uri = '';
	/**
	 * The access token for the PayPal connection.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public string $access_token = '';
	/**
	 * The refresh token for the PayPal connection.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public string $refresh_token = '';
	/**
	 * The name of the PayPal application.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public string $app_name = 'LicencePress PayPal';
	/**
	 * Whether the PayPal connection is established.
	 *
	 * @var bool
	 * @since 1.0.0
	 */
	public bool $connected = false;
	/**
	 * Create a new Connection model instance from the given settings array.
	 *
	 * @param array $settings The settings array.
	 * @return self The Connection model instance.
	 * @since 1.0.0
	 */
	public static function from_settings( array $settings = array() ): self {
		$model = new self();
		$model->environment = sanitize_key( (string) ( $settings['environment'] ?? $settings['paypal_environment'] ?? 'sandbox' ) );
		$model->client_id = html_entity_decode( (string) ( $settings['client_id'] ?? $settings[ 'paypal_api_' . $model->environment . '_client_id' ] ?? '' ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$model->client_secret = html_entity_decode( (string) ( $settings['client_secret'] ?? $settings[ 'paypal_api_' . $model->environment . '_client_secret' ] ?? '' ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$model->redirect_uri = (string) ( $settings['redirect_uri'] ?? '' );
		$model->access_token = (string) ( $settings['access_token'] ?? '' );
		$model->refresh_token = (string) ( $settings['refresh_token'] ?? '' );
		$model->app_name = (string) ( $settings['app_name'] ?? 'LicencePress PayPal' );
		$model->connected = ! empty( $settings['connected'] );

		return $model;
	}
	/**
	 * Determine if the connection model has valid credentials.
	 *
	 * @return bool True if the connection has valid credentials, false otherwise.
	 * @since 1.0.0
	 */
	public function is_valid(): bool {
		return '' !== trim( $this->client_id ) && '' !== trim( $this->client_secret );
	}
	/**
	 * Convert the connection model to a payload array.
	 *
	 * @return array The payload array.
	 * @since 1.0.0
	 */
	public function to_payload(): array {
		return $this->to_settings_array();
	}
	/**
	 * Convert the connection model to a settings array.
	 *
	 * @return array The settings array.
	 * @since 1.0.0
	 */
	public function to_settings_array(): array {
		return array(
			'environment' => $this->environment,
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
			'redirect_uri' => $this->redirect_uri,
			'access_token' => $this->access_token,
			'refresh_token' => $this->refresh_token,
			'app_name' => $this->app_name,
			'connected' => $this->connected,
		);
	}
}
