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
use LicencePress\Includes\Settings\Settings as BaseSettings;

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
				'paypal_environment'                => self::DEFAULT_ENVIRONMENT,
				'paypal_checkout_enabled'           => true,
				'paypal_subscriptions_enabled'      => false,
				'paypal_currency'                   => 'USD',
				'paypal_webhook_id'                 => '',
				'paypal_client_id'                  => '',
				'paypal_client_secret'              => '',
				'paypal_oauth_connected'            => false,
				'paypal_access_token'               => '',
				'paypal_refresh_token'              => '',
				'paypal_callback'                   => '',
				'paypal_live_client_id'             => '',
				'paypal_live_client_secret'         => '',
				'paypal_live_oauth_connected'       => false,
				'paypal_live_access_token'          => '',
				'paypal_live_refresh_token'         => '',
				'paypal_live_callback'              => '',
				'paypal_sandbox_client_id'          => '',
				'paypal_sandbox_client_secret'      => '',
				'paypal_sandbox_oauth_connected'    => false,
				'paypal_sandbox_access_token'       => '',
				'paypal_sandbox_refresh_token'      => '',
				'paypal_sandbox_callback'           => '',
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

	private static function encrypt_value( ?string $value ): string {
		if ( ! is_string( $value ) || '' === trim( (string) $value ) ) {
			return '';
		}

		$encrypted = EncryptionHelper::encrypt( $value );
		return null !== $encrypted ? $encrypted : $value;
	}

	private static function get_configured_value( string $environment, string $key_suffix ): string {
		$constant_map = array(
			'live'    => array(
				'client_id'     => 'LICENCEPRESS_PAYPAL_LIVE_CLIENT_ID',
				'client_secret' => 'LICENCEPRESS_PAYPAL_LIVE_CLIENT_SECRET',
			),
			'sandbox' => array(
				'client_id'     => 'LICENCEPRESS_PAYPAL_SANDBOX_CLIENT_ID',
				'client_secret' => 'LICENCEPRESS_PAYPAL_SANDBOX_CLIENT_SECRET',
			),
		);

		foreach ( $constant_map[ $environment ] ?? array() as $suffix => $constant_name ) {
			if ( $suffix === $key_suffix && defined( $constant_name ) ) {
				return (string) constant( $constant_name );
			}
		}

		$generic_constant = 'LICENCEPRESS_PAYPAL_' . strtoupper( $key_suffix );
		if ( defined( $generic_constant ) ) {
			return (string) constant( $generic_constant );
		}

		return '';
	}

	public static function get_client_id( ?string $environment = null ): string {
		$environment = self::normalize_environment( $environment );
		$constant_value = self::get_configured_value( $environment, 'client_id' );
		if ( '' !== $constant_value ) {
			return sanitize_text_field( $constant_value );
		}

		$key = 'paypal_' . $environment . '_client_id';
		$value = self::decrypt_value( BaseSettings::get( $key, BaseSettings::get( 'paypal_client_id', '' ) ) );
		return '' !== $value ? sanitize_text_field( $value ) : sanitize_text_field( (string) self::decrypt_value( BaseSettings::get( 'paypal_client_id', '' ) ) );
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

		$key = 'paypal_' . $environment . '_client_secret';
		$value = self::decrypt_value( BaseSettings::get( $key, BaseSettings::get( 'paypal_client_secret', '' ) ) );
		return '' !== $value ? sanitize_text_field( $value ) : sanitize_text_field( (string) self::decrypt_value( BaseSettings::get( 'paypal_client_secret', '' ) ) );
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
		$key = 'paypal_' . $environment . '_callback';
		$value = sanitize_text_field( (string) BaseSettings::get( $key, BaseSettings::get( 'paypal_callback', '' ) ) );
		return '' !== $value ? $value : sanitize_text_field( (string) BaseSettings::get( 'paypal_callback', '' ) );
	}

	/**
	 * Get the access token for the supplied environment.
	 *
	 * @param string|null $environment The environment.
	 * @return string The access token.
	 */
	public static function get_access_token( ?string $environment = null ): string {
		$environment = self::normalize_environment( $environment );
		$key = 'paypal_' . $environment . '_access_token';
		$value = sanitize_text_field( (string) BaseSettings::get( $key, BaseSettings::get( 'paypal_access_token', '' ) ) );
		return '' !== $value ? $value : sanitize_text_field( (string) BaseSettings::get( 'paypal_access_token', '' ) );
	}

	/**
	 * Get the refresh token for the supplied environment.
	 *
	 * @param string|null $environment The environment.
	 * @return string The refresh token.
	 */
	public static function get_refresh_token( ?string $environment = null ): string {
		$environment = self::normalize_environment( $environment );
		$key = 'paypal_' . $environment . '_refresh_token';
		$value = sanitize_text_field( (string) BaseSettings::get( $key, BaseSettings::get( 'paypal_refresh_token', '' ) ) );
		return '' !== $value ? $value : sanitize_text_field( (string) BaseSettings::get( 'paypal_refresh_token', '' ) );
	}

	/**
	 * Check if PayPal OAuth is connected for the active environment.
	 *
	 * @param string|null $environment The environment override.
	 * @return bool True if connected, false otherwise.
	 */
	public static function is_oauth_connected( ?string $environment = null ): bool {
		$environment = self::normalize_environment( $environment );
		$key = 'paypal_' . $environment . '_oauth_connected';
		$value = BaseSettings::get_bool( $key, BaseSettings::get_bool( 'paypal_oauth_connected', false ) );
		return $value || BaseSettings::get_bool( 'paypal_oauth_connected', false );
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

	private static function site_url( string $path = '' ): string {
		if ( function_exists( 'home_url' ) ) {
			return home_url( $path );
		}

		$base_url = 'https://example.com';
		if ( '' !== $path ) {
			$base_url = rtrim( $base_url, '/' ) . '/' . ltrim( $path, '/' );
		}

		return $base_url;
	}

	public static function get_webhook_url( ?string $environment = null ): string {
		$environment = self::normalize_environment( $environment );
		return self::site_url( '/?paypal_action=webhook&paypal_environment=' . $environment );
	}

	public static function get_required_webhook_events(): array {
		return array(
			'CHECKOUT.ORDER.APPROVED',
			'CHECKOUT.ORDER.COMPLETED',
			'PAYMENT.CAPTURE.COMPLETED',
			'PAYMENT.CAPTURE.DENIED',
			'PAYMENT.CAPTURE.REFUNDED',
			'PAYMENT.CAPTURE.REVERSED',
			'BILLING.SUBSCRIPTION.CREATED',
			'BILLING.SUBSCRIPTION.ACTIVATED',
			'BILLING.SUBSCRIPTION.CANCELLED',
			'BILLING.SUBSCRIPTION.EXPIRED',
			'BILLING.SUBSCRIPTION.PAYMENT_FAILED',
			'BILLING.SUBSCRIPTION.RE-ACTIVATED',
			'BILLING.SUBSCRIPTION.SUSPENDED',
			'BILLING.SUBSCRIPTION.UPDATED',
		);
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
					'key'         => 'paypal_live_client_config',
					'label'       => __( 'Live PayPal app configuration', 'licencepress' ),
					'description' => __( 'Use the PayPal app configuration from your server environment. The app secret is kept server-side and is not stored in the WordPress admin form.', 'licencepress' ),
					'type'        => 'custom',
					'render'      => array( self::class, 'render_oauth_connection' ),
				),
				array(
					'key'         => 'paypal_sandbox_client_config',
					'label'       => __( 'Sandbox PayPal app configuration', 'licencepress' ),
					'description' => __( 'Use the PayPal sandbox app configuration from your server environment. Store the secret outside WordPress so it is not exposed in the admin dashboard.', 'licencepress' ),
					'type'        => 'custom',
					'render'      => array( self::class, 'render_oauth_connection' ),
				),
				array(
					'key'         => 'paypal_live_webhook_url',
					'label'       => __( 'Live webhook URL', 'licencepress' ),
					'description' => __( 'Copy this URL into your PayPal live webhook configuration.', 'licencepress' ),
					'type'        => 'custom',
					'render'      => array( self::class, 'render_webhook_url' ),
				),
				array(
					'key'         => 'paypal_oauth_connect',
					'label'       => __( 'PayPal connection', 'licencepress' ),
					'description' => __( 'Complete the OAuth flow to unlock the sidebar configuration and PayPal operations.', 'licencepress' ),
					'type'        => 'custom',
					'render'      => array( self::class, 'render_oauth_connection' ),
				),
				array(
					'key'         => 'paypal_sandbox_webhook_url',
					'label'       => __( 'Sandbox webhook URL', 'licencepress' ),
					'description' => __( 'Copy this URL into your PayPal sandbox webhook configuration.', 'licencepress' ),
					'type'        => 'custom',
					'render'      => array( self::class, 'render_webhook_url' ),
				),
				array(
					'key'         => 'paypal_sandbox_oauth_connect',
					'label'       => __( 'PayPal Sandbox connection', 'licencepress' ),
					'description' => __( 'Complete the OAuth flow to unlock the sidebar configuration and PayPal Sandbox operations.', 'licencepress' ),
					'type'        => 'custom',
					'render'      => array( self::class, 'render_oauth_connection' ),
				),
			),
		);
	}

	public static function render_oauth_connection( $value, string $name, string $id ): void {
		$environment = str_contains( $name, 'sandbox' ) || str_contains( $id, 'sandbox' ) ? 'sandbox' : 'live';
		$settings    = BaseSettings::get_group( self::GROUP, array() );
		$settings    = is_array( $settings ) ? $settings : array();
		$connected   = ! empty( $settings[ 'paypal_' . $environment . '_oauth_connected' ] );
		$client_id   = sanitize_text_field( (string) ( $settings[ 'paypal_' . $environment . '_client_id' ] ?? '' ) );
		$connect_url = self::site_url( '/?paypal_oauth=1&paypal_environment=' . $environment );
		$status      = $connected ? __( 'Connected', 'licencepress' ) : __( 'Not connected', 'licencepress' );
		$tone        = $connected ? 'success' : 'warning';
		$label       = 'sandbox' === $environment ? __( 'PayPal Sandbox connection', 'licencepress' ) : __( 'PayPal Live connection', 'licencepress' );
		$message     = $connected
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
			<?php if ( '' !== $client_id ) : ?>
				<a class="btn btn-primary align-self-start" href="<?php echo esc_url( $connect_url ); ?>">
					<?php echo esc_html( $connected ? __( 'Reconnect PayPal', 'licencepress' ) : __( 'Connect PayPal', 'licencepress' ) ); ?>
				</a>
			<?php else : ?>
				<div class="small text-secondary">
					<?php echo esc_html( 'sandbox' === $environment ? __( 'Configure the PayPal Sandbox app from your server environment and then connect.', 'licencepress' ) : __( 'Configure the live PayPal app from your server environment and then connect.', 'licencepress' ) ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	public static function render_webhook_url( $value, string $name, string $id ): void {
		$environment = str_contains( $name, 'sandbox' ) || str_contains( $id, 'sandbox' ) ? 'sandbox' : 'live';
		$url         = self::get_webhook_url( $environment );
		$label       = 'sandbox' === $environment ? __( 'Sandbox webhook URL', 'licencepress' ) : __( 'Live webhook URL', 'licencepress' );
		$events      = self::get_required_webhook_events();
		?>
		<div class="d-flex flex-column gap-2" style="max-width: 700px;">
			<label class="form-label mb-0"><strong><?php echo esc_html( $label ); ?></strong></label>
			<input
				type="text"
				readonly
				class="form-control font-monospace"
				value="<?php echo esc_attr( $url ); ?>"
				aria-label="<?php echo esc_attr( $label ); ?>"
			/>
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
			'paypal_environment'             => in_array( sanitize_key( (string) ( $input['paypal_environment'] ?? self::DEFAULT_ENVIRONMENT ) ), self::ENVIRONMENTS, true ) ? sanitize_key( (string) ( $input['paypal_environment'] ?? self::DEFAULT_ENVIRONMENT ) ) : self::DEFAULT_ENVIRONMENT,
			'paypal_live_client_id'          => self::encrypt_value( sanitize_text_field( (string) ( $input['paypal_live_client_id'] ?? $input['paypal_client_id'] ?? '' ) ) ),
			'paypal_live_client_secret'      => self::encrypt_value( sanitize_text_field( (string) ( $input['paypal_live_client_secret'] ?? $input['paypal_client_secret'] ?? '' ) ) ),
			'paypal_live_oauth_connected'    => ! empty( $input['paypal_live_oauth_connected'] ) || ! empty( $input['paypal_oauth_connected'] ),
			'paypal_live_access_token'       => sanitize_text_field( (string) ( $input['paypal_live_access_token'] ?? $input['paypal_access_token'] ?? '' ) ),
			'paypal_live_refresh_token'      => sanitize_text_field( (string) ( $input['paypal_live_refresh_token'] ?? $input['paypal_refresh_token'] ?? '' ) ),
			'paypal_live_callback'           => esc_url_raw( (string) ( $input['paypal_live_callback'] ?? $input['paypal_callback'] ?? '' ) ),
			'paypal_sandbox_client_id'       => self::encrypt_value( sanitize_text_field( (string) ( $input['paypal_sandbox_client_id'] ?? '' ) ) ),
			'paypal_sandbox_client_secret'   => self::encrypt_value( sanitize_text_field( (string) ( $input['paypal_sandbox_client_secret'] ?? '' ) ) ),
			'paypal_sandbox_oauth_connected' => ! empty( $input['paypal_sandbox_oauth_connected'] ) || ! empty( $input['paypal_oauth_connected'] ),
			'paypal_sandbox_access_token'    => sanitize_text_field( (string) ( $input['paypal_sandbox_access_token'] ?? $input['paypal_access_token'] ?? '' ) ),
			'paypal_sandbox_refresh_token'   => sanitize_text_field( (string) ( $input['paypal_sandbox_refresh_token'] ?? $input['paypal_refresh_token'] ?? '' ) ),
			'paypal_sandbox_callback'        => esc_url_raw( (string) ( $input['paypal_sandbox_callback'] ?? $input['paypal_callback'] ?? '' ) ),
			'paypal_client_id'               => self::encrypt_value( sanitize_text_field( (string) ( $input['paypal_live_client_id'] ?? $input['paypal_client_id'] ?? '' ) ) ),
			'paypal_client_secret'           => self::encrypt_value( sanitize_text_field( (string) ( $input['paypal_live_client_secret'] ?? $input['paypal_client_secret'] ?? '' ) ) ),
			'paypal_oauth_connected'         => ! empty( $input['paypal_oauth_connect'] ) || ! empty( $input['paypal_live_oauth_connected'] ),
			'paypal_access_token'            => sanitize_text_field( (string) ( $input['paypal_live_access_token'] ?? $input['paypal_access_token'] ?? '' ) ),
			'paypal_refresh_token'           => sanitize_text_field( (string) ( $input['paypal_live_refresh_token'] ?? $input['paypal_refresh_token'] ?? '' ) ),
			'paypal_callback'                => esc_url_raw( (string) ( $input['paypal_live_callback'] ?? $input['paypal_callback'] ?? '' ) ),
		);

		BaseSettings::set_group( self::GROUP, $settings );

		return $settings;
	}
}
