<?php
/**
 * Settings for the Stripe plugin.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\Stripe\Includes\Settings
 * @since 1.0.0
 */

namespace LicencePress\Includes\Plugins\Stripe\Includes\Settings;

use LicencePress\Includes\Functions\Helpers\EncryptionHelper;
use LicencePress\Includes\Plugins\Stripe\API\StripeAPI;
use LicencePress\Includes\Plugins\Stripe\Includes\Functions\Helpers\StripeConnectionService;
use LicencePress\Includes\Settings\Settings as BaseSettings;

final class Settings {
	/**
	 * Register the Stripe plugin settings.
	 *
	 * @return void
	 */
	public const GROUP = 'stripe';
	/**
	 * The default environment for the Stripe plugin.
	 *
	 * @var string
	 */
	public const DEFAULT_ENVIRONMENT = 'sandbox';
	/**
	 * The available environments for the Stripe plugin.
	 *
	 * @var string[]
	 */
	public const ENVIRONMENTS = array( 'sandbox', 'live' );
	/**
	 * Register the Stripe plugin settings.
	 *
	 * @return void
	 */
	public function register(): void {
		BaseSettings::register_group(
			self::GROUP,
			array(
				'stripe_environment'                  => self::DEFAULT_ENVIRONMENT,
				'stripe_checkout_enabled'             => true,
				'stripe_subscriptions_enabled'        => false,
				'stripe_currency'                     => 'USD',
				'stripe_feature_one_time_payments'    => true,
				'stripe_feature_subscription_billing' => false,
				'stripe_feature_invoicing'           => false,
				'stripe_feature_refunds'             => false,
				'stripe_feature_transaction_search'  => true,
				'stripe_feature_saved_payment_methods'=> false,
				'stripe_feature_apple_pay'           => false,
				'stripe_feature_google_pay'          => false,
				'stripe_feature_advanced_cards'      => false,
				'stripe_feature_fastlane'            => false,
				'stripe_feature_ic_plus'             => false,
				'stripe_feature_customer_disputes'   => false,
				'stripe_api_live_secret_key'          => '',
				'stripe_api_live_connected'           => false,
				'stripe_api_live_webhook_id'          => '',
				'stripe_api_sandbox_secret_key'       => '',
				'stripe_api_sandbox_connected'        => false,
				'stripe_api_sandbox_webhook_id'       => '',
			)
		);
	}

	/**
	 * Get the Stripe secret key for the active environment.
	 *
	 * @param string|null $environment The environment override.
	 * @return string The secret key.
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
		$normalized_key   = strtoupper( str_replace( '-', '_', $key_suffix ) );
		$variable_name    = 'LICENCEPRESS_STRIPE_API_' . $environment_name . '_' . $normalized_key;
		$value            = self::get_environment_variable( $variable_name );

		if ( '' !== $value ) {
			return $value;
		}

		return '';
	}
	/**
	 * Get the value of an environment variable for Stripe configuration.
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
		$key = 'stripe_api_' . $environment . '_' . $key_suffix;
		$value = self::decrypt_value( BaseSettings::get( $key, '' ) );
		if ( '' !== $value ) {
			return sanitize_text_field( $value );
		}

		return '';
	}
	/**
	 * Get the Stripe secret key for the active environment.
	 *
	 * @param string|null $environment The environment override.
	 * @return string The secret key.
	 */
	public static function get_secret_key( ?string $environment = null ): string {
		$environment = self::normalize_environment( $environment );
		$constant_value = self::get_configured_value( $environment, 'secret_key' );
		if ( '' !== $constant_value ) {
			return sanitize_text_field( $constant_value );
		}

		return self::get_stored_value( $environment, 'secret_key' );
	}

	/**
	 * Back-compat alias for older client_secret naming.
	 *
	 * @param string|null $environment The environment override.
	 * @return string The Stripe secret key.
	 */
	public static function get_client_secret( ?string $environment = null ): string {
		return self::get_secret_key( $environment );
	}

	/**
	 * Back-compat alias for older client ID naming.
	 *
	 * @param string|null $environment The environment override.
	 * @return string The Stripe secret key.
	 */
	public static function get_client_id( ?string $environment = null ): string {
		return self::get_secret_key( $environment );
	}

	/**
	 * Get the decrypted Stripe credentials for the supplied environment.
	 *
	 * @param string|null $environment The environment override.
	 * @return array{secret_key:string} Decrypted key pair.
	 */
	public static function get_client_credentials( ?string $environment = null ): array {
		$environment = self::normalize_environment( $environment );
		$secret      = self::get_secret_key( $environment );

		return array(
			'secret_key' => $secret,
		);
	}

	/**
	 * Get the Stripe environment.
	 *
	 * @return string The environment.
	 */
	public static function get_environment(): string {
		$environment = sanitize_key( (string) BaseSettings::get( 'stripe_environment', self::DEFAULT_ENVIRONMENT ) );
		return self::normalize_environment( $environment );
	}

	/**
	 * Get the Stripe callback URL for the supplied environment.
	 *
	 * @param string|null $environment The environment.
	 * @return string The callback URL.
	 */
	public static function get_callback_url( ?string $environment = null ): string {
		$environment = self::normalize_environment( $environment );
		$value = sanitize_text_field( (string) BaseSettings::get( 'stripe_api_' . $environment . '_callback', '' ) );
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
		return sanitize_text_field( (string) BaseSettings::get( 'stripe_api_' . $environment . '_access_token', '' ) );
	}

	/**
	 * Get the refresh token for the supplied environment.
	 *
	 * @param string|null $environment The environment.
	 * @return string The refresh token.
	 */
	public static function get_refresh_token( ?string $environment = null ): string {
		$environment = self::normalize_environment( $environment );
		return sanitize_text_field( (string) BaseSettings::get( 'stripe_api_' . $environment . '_refresh_token', '' ) );
	}

	/**
	 * Check if the Stripe API is connected for the active environment.
	 *
	 * @param string|null $environment The environment override.
	 * @return bool True if connected, false otherwise.
	 */
	public static function is_connected( ?string $environment = null ): bool {
		$environment = self::normalize_environment( $environment );
		return BaseSettings::get_bool( 'stripe_api_' . $environment . '_connected', false );
	}

	/**
	 * Normalize the supplied environment name.
	 *
	 * @param string|null $environment The raw environment value.
	 * @return string The normalized environment.
	 */
	private static function normalize_environment( ?string $environment = null ): string {
		$environment = sanitize_key( (string) ( $environment ?? BaseSettings::get( 'stripe_environment', self::DEFAULT_ENVIRONMENT ) ) );
		return in_array( $environment, self::ENVIRONMENTS, true ) ? $environment : self::DEFAULT_ENVIRONMENT;
	}

	/**
	 * Check if Stripe checkout is enabled.
	 *
	 * @return bool True if enabled, false otherwise.
	 */
	public static function is_checkout_enabled(): bool {
		return BaseSettings::get_bool( 'stripe_checkout_enabled', true );
	}

	/**
	 * Check if Stripe subscriptions are enabled.
	 *
	 * @return bool True if enabled, false otherwise.
	 */
	public static function is_subscriptions_enabled(): bool {
		return BaseSettings::get_bool( 'stripe_subscriptions_enabled', false );
	}

	/**
	 * Get the Stripe currency.
	 *
	 * @return string The currency code.
	 */
	public static function get_currency(): string {
		$currency = sanitize_text_field( (string) BaseSettings::get( 'stripe_currency', 'USD' ) );
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
	 * Get the Stripe settings page configuration.
	 *
	 * @return array The settings page configuration.
	 */
	public function get_settings_page(): array {
		return array(
			'slug'           => self::GROUP,
			'settings_group' => self::GROUP,
			'label'          => __( 'Stripe', 'licencepress' ),
			'title'          => __( 'Stripe checkout and subscriptions', 'licencepress' ),
			'layout'         => 'table',
			'fields'         => array(
				array(
					'key'         => 'stripe_api_live_secret_key',
					'label'       => __( 'Enter your Live Stripe Secret Key', 'licencepress' ),
					'description' => __( 'Enter your live Stripe secret key. It is encrypted before storage.', 'licencepress' ),
					'type'        => 'password',
					'class'       => 'w-100',
				),
				array(
					'key'         => 'stripe_api_live_connection_status',
					'label'       => __( 'Live Stripe connection status', 'licencepress' ),
					'description' => __( 'This is set automatically when the saved key validates with Stripe.', 'licencepress' ),
					'type'        => 'custom',
					'render'      => array( self::class, 'render_connection_status' ),
				),
				array(
					'key'         => 'stripe_api_sandbox_secret_key',
					'label'       => __( 'Enter your Sandbox Stripe Secret Key', 'licencepress' ),
					'description' => __( 'Enter your sandbox Stripe secret key. It is encrypted before storage.', 'licencepress' ),
					'type'        => 'password',
					'class'       => 'w-100',
				),
				array(
					'key'         => 'stripe_api_sandbox_connection_status',
					'label'       => __( 'Sandbox Stripe connection status', 'licencepress' ),
					'description' => __( 'This is set automatically when the saved key validates with Stripe.', 'licencepress' ),
					'type'        => 'custom',
					'render'      => array( self::class, 'render_connection_status' ),
				),
			),
		);
	}
	/**
	 * Render the Stripe connection status UI for the given environment.
	 *
	 * @param mixed  $value The current value.
	 * @param string $name  The field name.
	 * @param string $id    The field ID.
	 */
	public static function render_connection_status( $value, string $name, string $id ): void {
		$environment = str_contains( $name, 'sandbox' ) || str_contains( $id, 'sandbox' ) ? 'sandbox' : 'live';
		$settings    = BaseSettings::get_group( self::GROUP, array() );
		$settings    = is_array( $settings ) ? $settings : array();
		$connected   = ! empty( $settings[ 'stripe_api_' . $environment . '_connected' ] );
		$secret_key  = self::get_secret_key( $environment );
		$status      = $connected ? __( 'Connected', 'licencepress' ) : __( 'Not connected', 'licencepress' );
		$tone        = $connected ? 'success' : 'warning';
		$label       = 'sandbox' === $environment ? __( 'Stripe Sandbox connection', 'licencepress' ) : __( 'Stripe Live connection', 'licencepress' );
		$message     = $connected
			? __( 'The saved Stripe secret key is valid for this environment.', 'licencepress' )
			: __( 'Add the saved secret key for this environment to enable Stripe validation.', 'licencepress' );
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
			<div class="small text-secondary">
				<?php echo esc_html( '' !== $secret_key ? __( 'The Stripe API credentials are configured and ready to validate with Stripe.', 'licencepress' ) : ( 'sandbox' === $environment ? __( 'Add the Sandbox secret key to complete the Stripe configuration.', 'licencepress' ) : __( 'Add the Live secret key to complete the Stripe configuration.', 'licencepress' ) ) ); ?>
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
			<small class="text-muted"><?php echo esc_html( __( 'Copy this URL and paste it into the Stripe webhook configuration for this environment.', 'licencepress' ) ); ?></small>
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
	 * Sanitize the Stripe settings input.
	 *
	 * @param array $input The input data to sanitize.
	 * @return array The sanitized settings.
	 */
	public function sanitize( $input ): array {
		$input = is_array( $input ) ? $input : array();

		$settings = array(
			'stripe_environment'                     => in_array( sanitize_key( (string) ( $input['stripe_environment'] ?? self::DEFAULT_ENVIRONMENT ) ), self::ENVIRONMENTS, true ) ? sanitize_key( (string) ( $input['stripe_environment'] ?? self::DEFAULT_ENVIRONMENT ) ) : self::DEFAULT_ENVIRONMENT,
			'stripe_checkout_enabled'                => ! empty( $input['stripe_checkout_enabled'] ),
			'stripe_subscriptions_enabled'           => ! empty( $input['stripe_subscriptions_enabled'] ),
			'stripe_currency'                        => sanitize_text_field( (string) ( $input['stripe_currency'] ?? 'USD' ) ),
			'stripe_feature_one_time_payments'       => ! empty( $input['stripe_feature_one_time_payments'] ),
			'stripe_feature_subscription_billing'    => ! empty( $input['stripe_feature_subscription_billing'] ),
			'stripe_feature_invoicing'               => ! empty( $input['stripe_feature_invoicing'] ),
			'stripe_feature_refunds'                 => ! empty( $input['stripe_feature_refunds'] ),
			'stripe_feature_transaction_search'      => ! empty( $input['stripe_feature_transaction_search'] ),
			'stripe_feature_saved_payment_methods'   => ! empty( $input['stripe_feature_saved_payment_methods'] ),
			'stripe_feature_apple_pay'               => ! empty( $input['stripe_feature_apple_pay'] ),
			'stripe_feature_google_pay'              => ! empty( $input['stripe_feature_google_pay'] ),
			'stripe_feature_advanced_cards'          => ! empty( $input['stripe_feature_advanced_cards'] ),
			'stripe_feature_fastlane'                => ! empty( $input['stripe_feature_fastlane'] ),
			'stripe_feature_ic_plus'                 => ! empty( $input['stripe_feature_ic_plus'] ),
			'stripe_feature_customer_disputes'       => ! empty( $input['stripe_feature_customer_disputes'] ),
			'stripe_api_live_secret_key'             => self::encrypt_value( sanitize_text_field( (string) ( $input['stripe_api_live_secret_key'] ?? '' ) ) ),
			'stripe_api_live_connected'              => false,
			'stripe_api_live_webhook_id'             => sanitize_text_field( (string) ( $input['stripe_api_live_webhook_id'] ?? '' ) ),
			'stripe_api_sandbox_secret_key'          => self::encrypt_value( sanitize_text_field( (string) ( $input['stripe_api_sandbox_secret_key'] ?? '' ) ) ),
			'stripe_api_sandbox_connected'           => false,
			'stripe_api_sandbox_webhook_id'          => sanitize_text_field( (string) ( $input['stripe_api_sandbox_webhook_id'] ?? '' ) ),
		);

		foreach ( self::ENVIRONMENTS as $environment ) {
			$secret_key = self::decrypt_value( $settings[ 'stripe_api_' . $environment . '_secret_key' ] ?? '' );
			if ( '' === $secret_key ) {
				continue;
			}

			$result = StripeConnectionService::test_connection(
				array(
					'stripe_environment' => $environment,
					'secret_key'         => $secret_key,
				),
				$environment
			);

			$settings[ 'stripe_api_' . $environment . '_connected' ] = ! empty( $result['success'] ) && ! empty( $result['connected'] );
		}

		BaseSettings::set_group( self::GROUP, $settings );

		return $settings;
	}
}
