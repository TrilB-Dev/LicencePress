<?php
/**
 * Settings for the PayPal plugin.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\Includes\Settings
 * @since 1.0.0
 */

namespace LicencePress\Includes\Plugins\PayPal\Includes\Settings;

use LicencePress\Includes\Functions\Helpers\EncryptionHelper;
use LicencePress\Includes\Plugins\PayPal\Includes\Functions\Helpers\PayPalConnectionService;
use LicencePress\Includes\Settings\Settings as BaseSettings;
use LicencePress\Includes\Plugins\PayPal\API\PayPalRESTAPI;

final class Settings {
	/**
	 * Register the PayPal plugin settings.
	 *
	 * @return void
	 */
	public const GROUP = 'paypal';
	/**
	 * The default environment for the PayPal plugin.
	 *
	 * @var string
	 */
	public const DEFAULT_ENVIRONMENT = 'sandbox';
	/**
	 * The available environments for the PayPal plugin.
	 *
	 * @var string[]
	 */
	public const ENVIRONMENTS = array( 'sandbox', 'live' );
	/**
	 * Register the PayPal plugin settings.
	 *
	 * @return void
	 */
	public function register(): void {
		BaseSettings::register_group(
			self::GROUP,
			array(
				'paypal_environment'                	=> self::DEFAULT_ENVIRONMENT,
				'paypal_checkout_enabled'           	=> false,
				'paypal_subscriptions_enabled'      	=> false,
				'paypal_currency'                   	=> 'USD',
				'paypal_api_live_client_id'             => '',
				'paypal_api_live_client_secret'         => '',
				'paypal_api_live_oauth_connected'       => false,
				'paypal_api_live_access_token'          => '',
				'paypal_api_live_refresh_token'         => '',
				'paypal_api_live_callback'              => '',
				'paypal_api_live_webhook_id'            => '',
				'paypal_api_sandbox_client_id'          => '',
				'paypal_api_sandbox_client_secret'      => '',
				'paypal_api_sandbox_oauth_connected'    => false,
				'paypal_api_sandbox_access_token'       => '',
				'paypal_api_sandbox_refresh_token'      => '',
				'paypal_api_sandbox_callback'           => '',
				'paypal_api_sandbox_webhook_id'         => '',
			)
		);
	}

	/**
	 * Get the PayPal client ID for the active environment.
	 *
	 * @param string|null $environment The environment override.
	 * @return string The client ID.
	 */
	private static function decrypt_value( ?string $value ): string {
		if ( ! is_string( $value ) || '' === $value ) {
			return '';
		}

		$decrypted = EncryptionHelper::decrypt( $value );
		return null !== $decrypted ? $decrypted : $value;
	}
	/**
	 * Encrypt the given value for storage.
	 *
	 * @param string|null $value The value to encrypt.
	 * @return string The encrypted value.
	 */
	private static function encrypt_value( ?string $value ): string {
		if ( ! is_string( $value ) || '' === trim( (string) $value ) ) {
			return '';
		}

		$encrypted = EncryptionHelper::encrypt( $value );
		return null !== $encrypted ? $encrypted : $value;
	}
	/**
	 * Get the configured value for the given environment and key suffix from server configuration.
	 *
	 * @param string $environment The environment.
	 * @param string $key_suffix The key suffix.
	 * @return string The configured value.
	 */
	private static function get_configured_value( string $environment, string $key_suffix ): string {
		$environment_name = strtoupper( $environment );
		$variable_name    = 'LICENCEPRESS_PAYPAL_API_' . $environment_name . '_' . strtoupper( str_replace( 'client_secret', 'CLIENT_SECRET', str_replace( 'client_id', 'CLIENT_ID', $key_suffix ) ) );
		$value            = self::get_environment_variable( $variable_name );

		if ( '' !== $value ) {
			return $value;
		}

		return '';
	}
	/**
	 * Get the value of an environment variable for PayPal configuration.
	 *
	 * @param string $name The name of the environment variable.
	 * @return string The value of the environment variable.
	 */
	private static function get_environment_variable( string $name ): string {
		$values = array(
			getenv( $name ),
			$_ENV[ $name ] ?? null,
			$_SERVER[ $name ] ?? null,
		);

		foreach ( $values as $value ) {
			if ( is_string( $value ) ) {
				$value = trim( $value );
				if ( '' !== $value ) {
					return sanitize_text_field( $value );
				}
			}
		}

		return '';
	}
	/**
	 * Get the stored value for the given environment and key suffix.
	 *
	 * @param string $environment The environment.
	 * @param string $key_suffix The key suffix.
	 * @return string The stored value.
	 */
	private static function get_stored_value( string $environment, string $key_suffix ): string {
		$key = 'paypal_api_' . $environment . '_' . $key_suffix;
		$value = self::decrypt_value( BaseSettings::get( $key, '' ) );
		if ( '' !== $value ) {
			return sanitize_text_field( $value );
		}

		return '';
	}
	/**
	 * Get the PayPal client ID for the active environment.
	 *
	 * @param string|null $environment The environment override.
	 * @return string The client ID.
	 */
	public static function get_client_id( ?string $environment = null ): string {
		$environment = self::normalize_environment( $environment );
		$constant_value = self::get_configured_value( $environment, 'client_id' );
		if ( '' !== $constant_value ) {
			return sanitize_text_field( $constant_value );
		}

		return self::get_stored_value( $environment, 'client_id' );
	}

	/**
	 * Get the PayPal client secret for the active environment.
	 *
	 * @param string|null $environment The environment override.
	 * @return string The client secret.
	 */
	public static function get_client_secret( ?string $environment = null ): string {
		$environment = self::normalize_environment( $environment );
		$constant_value = self::get_configured_value( $environment, 'client_secret' );
		if ( '' !== $constant_value ) {
			return sanitize_text_field( $constant_value );
		}

		return self::get_stored_value( $environment, 'client_secret' );
	}

	/**
	 * Get the decrypted PayPal client credentials for the supplied environment.
	 *
	 * @param string|null $environment The environment override.
	 * @return array{client_id:string, client_secret:string} Decrypted key pair.
	 */
	public static function get_client_credentials( ?string $environment = null ): array {
		$environment = self::normalize_environment( $environment );
		$client_id = self::get_client_id( $environment );
		$secret    = self::get_client_secret( $environment );

		return array(
			'client_id'     => $client_id,
			'client_secret' => $secret,
		);
	}

	/**
	 * Get the PayPal environment.
	 *
	 * @return string The environment.
	 */
	public static function get_environment(): string {
		$environment = sanitize_key( (string) BaseSettings::get( 'paypal_environment', self::DEFAULT_ENVIRONMENT ) );
		return self::normalize_environment( $environment );
	}

	/**
	 * Get the PayPal callback URL for the supplied environment.
	 *
	 * @param string|null $environment The environment.
	 * @return string The callback URL.
	 */
	public static function get_callback_url( ?string $environment = null ): string {
		$environment = self::normalize_environment( $environment );
		$value = sanitize_text_field( (string) BaseSettings::get( 'paypal_api_' . $environment . '_callback', '' ) );
		return $value;
	}

	/**
	 * Get the access token for the supplied environment.
	 *
	 * @param string|null $environment The environment.
	 * @return string The access token.
	 */
	public static function get_access_token( ?string $environment = null ): string {
		$environment = self::normalize_environment( $environment );
		return sanitize_text_field( (string) BaseSettings::get( 'paypal_api_' . $environment . '_access_token', '' ) );
	}

	/**
	 * Get the refresh token for the supplied environment.
	 *
	 * @param string|null $environment The environment.
	 * @return string The refresh token.
	 */
	public static function get_refresh_token( ?string $environment = null ): string {
		$environment = self::normalize_environment( $environment );
		return sanitize_text_field( (string) BaseSettings::get( 'paypal_api_' . $environment . '_refresh_token', '' ) );
	}

	/**
	 * Check if PayPal OAuth is connected for the active environment.
	 *
	 * @param string|null $environment The environment override.
	 * @return bool True if connected, false otherwise.
	 */
	public static function is_oauth_connected( ?string $environment = null ): bool {
		$environment = self::normalize_environment( $environment );
		return BaseSettings::get_bool( 'paypal_api_' . $environment . '_oauth_connected', false );
	}

	/**
	 * Normalize the supplied environment name.
	 *
	 * @param string|null $environment The raw environment value.
	 * @return string The normalized environment.
	 */
	private static function normalize_environment( ?string $environment = null ): string {
		$environment = sanitize_key( (string) ( $environment ?? BaseSettings::get( 'paypal_environment', self::DEFAULT_ENVIRONMENT ) ) );
		return in_array( $environment, self::ENVIRONMENTS, true ) ? $environment : self::DEFAULT_ENVIRONMENT;
	}

	/**
	 * Check if PayPal checkout is enabled.
	 *
	 * @return bool True if enabled, false otherwise.
	 */
	public static function is_checkout_enabled(): bool {
		return BaseSettings::get_bool( 'paypal_checkout_enabled', true );
	}

	/**
	 * Check if PayPal subscriptions are enabled.
	 *
	 * @return bool True if enabled, false otherwise.
	 */
	public static function is_subscriptions_enabled(): bool {
		return BaseSettings::get_bool( 'paypal_subscriptions_enabled', false );
	}

	/**
	 * Get the PayPal currency.
	 *
	 * @return string The currency code.
	 */
	public static function get_currency(): string {
		$currency = sanitize_text_field( (string) BaseSettings::get( 'paypal_currency', 'USD' ) );
		$currency = strtoupper( $currency );
		return '' !== $currency ? $currency : 'USD';
	}
	/**
	 * Get the site URL with an optional path appended.
	 *
	 * @param string $path The path to append.
	 * @return string The full site URL.
	 */
	private static function site_url( string $path = '' ): string {
		if ( function_exists( 'home_url' ) ) {
			return home_url( $path );
		}

		$base_url = site_url();
		if ( '' !== $path ) {
			$base_url = rtrim( $base_url, '/' ) . '/' . ltrim( $path, '/' );
		}

		return $base_url;
	}

	/**
	 * Get the PayPal settings page configuration.
	 *
	 * @return array The settings page configuration.
	 */
	public function get_settings_page(): array {
		return array(
			'slug'           => self::GROUP,
			'settings_group' => self::GROUP,
			'label'          => __( 'PayPal', 'licencepress' ),
			'title'          => __( 'PayPal checkout and subscriptions', 'licencepress' ),
			'layout'         => 'table',
			'fields'         => array(
				array(
					'key'         => 'paypal_api_live_client_id',
					'label'       => __( 'Enter your Live PayPal Client ID', 'licencepress' ),
					'description' => __( 'Enter your Live PayPal Client ID.', 'licencepress' ),
					'type'        => 'text',
					'class'       => 'w-100',
				),
				array(
					'key'         => 'paypal_api_live_client_secret',
					'label'       => __( 'Enter your Live PayPal Client Secret', 'licencepress' ),
					'description' => __( 'Enter your Live PayPal Client Secret.', 'licencepress' ),
					'type'        => 'password',
					'class'       => 'w-100',
				),
				array(
					'key'         => 'paypal_api_live_client_config',
					'label'       => __( 'Live PayPal app configuration', 'licencepress' ),
					'description' => __( 'Use the PayPal app configuration from your server environment. The app secret is kept server-side and is not stored in the WordPress admin form.', 'licencepress' ),
					'type'        => 'custom',
					'render'      => array( self::class, 'render_oauth_connection' ),
				),
				array(
					'key'         => 'paypal_api_sandbox_client_id',
					'label'       => __( 'Enter your Sandbox PayPal Client ID', 'licencepress' ),
					'description' => __( 'Enter your Sandbox PayPal Client ID.', 'licencepress' ),
					'type'        => 'text',
					'class'       => 'w-100',
				),
				array(
					'key'         => 'paypal_api_sandbox_client_secret',
					'label'       => __( 'Enter your Sandbox PayPal Client Secret', 'licencepress' ),
					'description' => __( 'Enter your Sandbox PayPal Client Secret.', 'licencepress' ),
					'type'        => 'password',
					'class'       => 'w-100',
				),
				array(
					'key'         => 'paypal_api_sandbox_client_config',
					'label'       => __( 'Sandbox PayPal app configuration', 'licencepress' ),
					'description' => __( 'Use the PayPal sandbox app configuration from your server environment. Store the secret outside WordPress so it is not exposed in the admin dashboard.', 'licencepress' ),
					'type'        => 'custom',
					'render'      => array( self::class, 'render_oauth_connection' ),
				),
			),
		);
	}
	/**
	 * Render the OAuth connection UI for the given environment.
	 *
	 * @param mixed  $value The current value.
	 * @param string $name  The field name.
	 * @param string $id    The field ID.
	 */
	public static function render_oauth_connection( $value, string $name, string $id ): void {
		$environment = str_contains( $name, 'sandbox' ) || str_contains( $id, 'sandbox' ) ? 'sandbox' : 'live';
		$settings    = BaseSettings::get_group( self::GROUP, array() );
		$settings    = is_array( $settings ) ? $settings : array();
		$connected   = ! empty( $settings[ 'paypal_api_' . $environment . '_oauth_connected' ] ) || ! empty( $settings[ 'paypal_' . $environment . '_oauth_connected' ] );
		$client_id   = self::get_client_id( $environment );
		$connect_url  = PayPalConnectionService::start_oauth_connect( $settings, $environment );
		$redirect_uri = PayPalRESTAPI::resolve_redirect_uri( $environment );
		$status       = $connected ? __( 'Connected', 'licencepress' ) : __( 'Not connected', 'licencepress' );
		$tone         = $connected ? 'success' : 'warning';
		$label        = 'sandbox' === $environment ? __( 'PayPal Sandbox connection', 'licencepress' ) : __( 'PayPal Live connection', 'licencepress' );
		$message      = $connected
			? __( 'Your PayPal integration is active and the PayPal sidebar is available for this environment.', 'licencepress' )
			: __( 'Connect this environment to unlock the PayPal sidebar and checkout settings.', 'licencepress' );
		?>
		<div class="d-flex flex-column gap-3" style="max-width: 540px;">
			<div class="card border-<?php echo esc_attr( $tone ); ?> shadow-none mb-0">
				<div class="card-body">
					<div class="d-flex align-items-center justify-content-between gap-3 mb-2">
						<strong><?php echo esc_html( $label ); ?></strong>
						<span class="badge bg-<?php echo esc_attr( $tone ); ?> text-uppercase"><?php echo esc_html( $status ); ?></span>
					</div>
					<div class="small text-muted"><?php echo esc_html( $message ); ?></div>
				</div>
			</div>
			<a
				class="btn <?php echo esc_attr( '' !== $client_id ? 'btn-primary' : 'btn-outline-primary' ); ?> align-self-start"
				href="<?php echo esc_url( $connect_url ); ?>"
			>	<i class="fab fa-paypal"></i>
				<?php echo esc_html( $connected ? __( 'Reconnect PayPal', 'licencepress' ) : __( 'Connect PayPal', 'licencepress' ) ); ?>
			</a>
			<div class="small text-secondary">
				<?php echo esc_html( '' !== $client_id ? __( 'The PayPal app is configured and ready to connect.', 'licencepress' ) : ( 'sandbox' === $environment ? __( 'Configure the PayPal Sandbox app from your server environment and then connect.', 'licencepress' ) : __( 'Configure the live PayPal app from your server environment and then connect.', 'licencepress' ) ) ); ?>
				<?php echo esc_html( __( 'Redirect URI:', 'licencepress' ) ); ?> <code class="text-break"><?php echo esc_html( $redirect_uri ); ?></code>
			</div>
		</div>
		<?php
	}
	/**
	 * Render the webhook URL UI for the given environment.
	 *
	 * @param mixed  $value The current value.
	 * @param string $name  The field name.
	 * @param string $id    The field ID.
	 */
	public static function render_webhook_url( $value, string $name, string $id ): void {
		$environment = str_contains( $name, 'sandbox' ) || str_contains( $id, 'sandbox' ) ? 'sandbox' : 'live';
		$url         = self::get_webhook_url( $environment );
		$label       = 'sandbox' === $environment ? __( 'Sandbox webhook URL', 'licencepress' ) : __( 'Live webhook URL', 'licencepress' );
		$events      = self::get_required_webhook_events();
		?>
		<div class="d-flex flex-column gap-2" style="max-width: 700px;">
			<label class="form-label mb-0"><strong><?php echo esc_html( $label ); ?></strong></label>
			<code class="d-block p-2 bg-light border rounded"><?php echo esc_html( $url ); ?></code>
			<small class="text-muted"><?php echo esc_html( __( 'Copy this URL and paste it into the PayPal webhook configuration for this environment.', 'licencepress' ) ); ?></small>
			<div class="mt-2">
				<strong><?php echo esc_html( __( 'Enable these events:', 'licencepress' ) ); ?></strong>
				<ul class="mb-0 mt-2 ps-3">
					<?php foreach ( $events as $event ) : ?>
						<li><?php echo esc_html( $event ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<?php
	}
	/**
	 * Sanitize the PayPal settings input.
	 *
	 * @param array $input The input data to sanitize.
	 * @return array The sanitized settings.
	 */
	public function sanitize( $input ): array {
		$input = is_array( $input ) ? $input : array();

		$settings = array(
			'paypal_environment'                 => in_array( sanitize_key( (string) ( $input['paypal_environment'] ?? self::DEFAULT_ENVIRONMENT ) ), self::ENVIRONMENTS, true ) ? sanitize_key( (string) ( $input['paypal_environment'] ?? self::DEFAULT_ENVIRONMENT ) ) : self::DEFAULT_ENVIRONMENT,
			'paypal_api_live_client_id'          => self::encrypt_value( sanitize_text_field( (string) ( $input['paypal_api_live_client_id'] ?? '' ) ) ),
			'paypal_api_live_client_secret'      => self::encrypt_value( sanitize_text_field( (string) ( $input['paypal_api_live_client_secret'] ?? '' ) ) ),
			'paypal_api_live_oauth_connected'    => ! empty( $input['paypal_api_live_oauth_connected'] ),
			'paypal_api_live_access_token'       => sanitize_text_field( (string) ( $input['paypal_api_live_access_token'] ?? '' ) ),
			'paypal_api_live_refresh_token'      => sanitize_text_field( (string) ( $input['paypal_api_live_refresh_token'] ?? '' ) ),
			'paypal_api_live_callback'           => esc_url_raw( (string) ( $input['paypal_api_live_callback'] ?? '' ) ),
			'paypal_api_sandbox_client_id'       => self::encrypt_value( sanitize_text_field( (string) ( $input['paypal_api_sandbox_client_id'] ?? '' ) ) ),
			'paypal_api_sandbox_client_secret'   => self::encrypt_value( sanitize_text_field( (string) ( $input['paypal_api_sandbox_client_secret'] ?? '' ) ) ),
			'paypal_api_sandbox_oauth_connected' => ! empty( $input['paypal_api_sandbox_oauth_connected'] ),
			'paypal_api_sandbox_access_token'    => sanitize_text_field( (string) ( $input['paypal_api_sandbox_access_token'] ?? '' ) ),
			'paypal_api_sandbox_refresh_token'   => sanitize_text_field( (string) ( $input['paypal_api_sandbox_refresh_token'] ?? '' ) ),
			'paypal_api_sandbox_callback'        => esc_url_raw( (string) ( $input['paypal_api_sandbox_callback'] ?? '' ) ),
		);

		BaseSettings::set_group( self::GROUP, $settings );

		return $settings;
	}
}
