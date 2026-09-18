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
	public string $environment = 'sandbox';
	public string $client_id = '';
	public string $client_secret = '';
	public string $redirect_uri = '';
	public string $access_token = '';
	public string $refresh_token = '';
	public string $app_name = 'LicencePress PayPal';
	public bool $connected = false;

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
