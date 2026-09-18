<?php
/**
 * PayPal API models used by the module's custom REST connector.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\API\Models
 */

namespace LicencePress\Includes\Plugins\PayPal\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PayPalConnectionSettings {
	/**
	 * PayPal connection settings model.
	 *
	 * @since 1.0.0
	 */
	public string $environment = 'sandbox';
	/**
	 * PayPal client ID.
	 *
	 * @since 1.0.0
	 */
	public string $client_id = '';
	/**
	 * PayPal client secret.
	 *
	 * @since 1.0.0
	 */
	public string $client_secret = '';
	/**
	 * PayPal redirect URI.
	 *
	 * @since 1.0.0
	 */
	public string $redirect_uri = '';
	/**
	 * PayPal access token.
	 *
	 * @since 1.0.0
	 */
	public string $access_token = '';
	/**
	 * PayPal refresh token.
	 *
	 * @since 1.0.0
	 */
	public string $refresh_token = '';
	/**
	 * PayPal app name.
	 *
	 * @since 1.0.0
	 */
	public string $app_name = 'LicencePress PayPal';
	/**
	 * PayPal connection status.
	 *
	 * @since 1.0.0
	 */
	public bool $connected = false;
	/**
	 * Converts the PayPal connection settings to an associative array.
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
