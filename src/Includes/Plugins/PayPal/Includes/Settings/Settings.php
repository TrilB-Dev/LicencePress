<?php
/**
 * Settings for the PayPal plugin.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\Includes\Settings
 * @since 1.0.0
 */

namespace LicencePress\Includes\Plugins\PayPal\Includes\Settings;

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
	public static function get_client_id( ?string $environment = null ): string {
		$environment = self::normalize_environment( $environment );
		$key = 'paypal_' . $environment . '_client_id';
		$value = sanitize_text_field( (string) BaseSettings::get( $key, BaseSettings::get( 'paypal_client_id', '' ) ) );
		return '' !== $value ? $value : sanitize_text_field( (string) BaseSettings::get( 'paypal_client_id', '' ) );
	}

	/**
	 * Get the PayPal client secret for the active environment.
	 *
	 * @param string|null $environment The environment override.
	 * @return string The client secret.
	 */
	public static function get_client_secret( ?string $environment = null ): string {
		$environment = self::normalize_environment( $environment );
		$key = 'paypal_' . $environment . '_client_secret';
		$value = sanitize_text_field( (string) BaseSettings::get( $key, BaseSettings::get( 'paypal_client_secret', '' ) ) );
		return '' !== $value ? $value : sanitize_text_field( (string) BaseSettings::get( 'paypal_client_secret', '' ) );
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
					'key'         => 'paypal_client_id',
					'label'       => __( 'Client ID', 'licencepress' ),
					'description' => __( 'Your PayPal REST API client ID.', 'licencepress' ),
					'type'        => 'text',
					'default'     => '',
				),
				array(
					'key'         => 'paypal_client_secret',
					'label'       => __( 'Client Secret', 'licencepress' ),
					'description' => __( 'Your PayPal app secret. Store it securely and limit access to trusted admins.', 'licencepress' ),
					'type'        => 'text',
					'default'     => '',
				),
				array(
					'key'         => 'paypal_sandbox_client_id',
					'label'       => __( 'Sandbox Client ID', 'licencepress' ),
					'description' => __( 'Your PayPal Sandbox REST API client ID.', 'licencepress' ),
					'type'        => 'text',
					'default'     => '',
				),
				array(
					'key'         => 'paypal_sandbox_client_secret',
					'label'       => __( 'Sandbox Client Secret', 'licencepress' ),
					'description' => __( 'Your PayPal Sandbox app secret. Store it securely and limit access to trusted admins.', 'licencepress' ),
					'type'        => 'text',
					'default'     => '',
				),
				array(
					'key'         => 'paypal_oauth_connect',
					'label'       => __( 'PayPal connection', 'licencepress' ),
					'description' => __( 'Complete the OAuth flow to unlock the sidebar configuration and PayPal operations.', 'licencepress' ),
					'type'        => 'custom',
					'render'      => array( self::class, 'render_oauth_connection' ),
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
		$connect_url = admin_url( 'admin.php?page=licencepress-paypal&paypal_action=connect&paypal_environment=' . $environment );
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
					<?php echo esc_html( 'sandbox' === $environment ? __( 'Enter your PayPal Sandbox client ID and client secret first, then connect.', 'licencepress' ) : __( 'Enter your live PayPal client ID and client secret first, then connect.', 'licencepress' ) ); ?>
				</div>
			<?php endif; ?>
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
			'paypal_live_client_id'          => sanitize_text_field( (string) ( $input['paypal_live_client_id'] ?? $input['paypal_client_id'] ?? '' ) ),
			'paypal_live_client_secret'      => sanitize_text_field( (string) ( $input['paypal_live_client_secret'] ?? $input['paypal_client_secret'] ?? '' ) ),
			'paypal_live_oauth_connected'    => ! empty( $input['paypal_live_oauth_connected'] ) || ! empty( $input['paypal_oauth_connected'] ),
			'paypal_live_access_token'       => sanitize_text_field( (string) ( $input['paypal_live_access_token'] ?? $input['paypal_access_token'] ?? '' ) ),
			'paypal_live_refresh_token'      => sanitize_text_field( (string) ( $input['paypal_live_refresh_token'] ?? $input['paypal_refresh_token'] ?? '' ) ),
			'paypal_live_callback'           => esc_url_raw( (string) ( $input['paypal_live_callback'] ?? $input['paypal_callback'] ?? '' ) ),
			'paypal_sandbox_client_id'       => sanitize_text_field( (string) ( $input['paypal_sandbox_client_id'] ?? '' ) ),
			'paypal_sandbox_client_secret'   => sanitize_text_field( (string) ( $input['paypal_sandbox_client_secret'] ?? '' ) ),
			'paypal_sandbox_oauth_connected' => ! empty( $input['paypal_sandbox_oauth_connected'] ) || ! empty( $input['paypal_oauth_connected'] ),
			'paypal_sandbox_access_token'    => sanitize_text_field( (string) ( $input['paypal_sandbox_access_token'] ?? $input['paypal_access_token'] ?? '' ) ),
			'paypal_sandbox_refresh_token'   => sanitize_text_field( (string) ( $input['paypal_sandbox_refresh_token'] ?? $input['paypal_refresh_token'] ?? '' ) ),
			'paypal_sandbox_callback'        => esc_url_raw( (string) ( $input['paypal_sandbox_callback'] ?? $input['paypal_callback'] ?? '' ) ),
			'paypal_client_id'               => sanitize_text_field( (string) ( $input['paypal_live_client_id'] ?? $input['paypal_client_id'] ?? '' ) ),
			'paypal_client_secret'           => sanitize_text_field( (string) ( $input['paypal_live_client_secret'] ?? $input['paypal_client_secret'] ?? '' ) ),
			'paypal_oauth_connected'         => ! empty( $input['paypal_oauth_connect'] ) || ! empty( $input['paypal_live_oauth_connected'] ),
			'paypal_access_token'            => sanitize_text_field( (string) ( $input['paypal_live_access_token'] ?? $input['paypal_access_token'] ?? '' ) ),
			'paypal_refresh_token'           => sanitize_text_field( (string) ( $input['paypal_live_refresh_token'] ?? $input['paypal_refresh_token'] ?? '' ) ),
			'paypal_callback'                => esc_url_raw( (string) ( $input['paypal_live_callback'] ?? $input['paypal_callback'] ?? '' ) ),
		);

		BaseSettings::set_group( self::GROUP, $settings );

		return $settings;
	}
}
