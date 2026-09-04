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
				'paypal_client_id'             => '',
				'paypal_client_secret'         => '',
				'paypal_environment'           => self::DEFAULT_ENVIRONMENT,
				'paypal_checkout_enabled'      => true,
				'paypal_subscriptions_enabled' => false,
				'paypal_oauth_connected'       => false,
				'paypal_currency'              => 'USD',
				'paypal_webhook_id'            => '',
				'paypal_access_token'          => '',
				'paypal_refresh_token'         => '',
			)
		);
	}
	/**
	 * Get the PayPal client ID.
	 *
	 * @return string The client ID.
	 */
	public static function get_client_id(): string {
		return sanitize_text_field( (string) BaseSettings::get( 'paypal_client_id', '' ) );
	}

	/**
	 * Get the PayPal client secret.
	 *
	 * @return string The client secret.
	 */
	public static function get_client_secret(): string {
		return sanitize_text_field( (string) BaseSettings::get( 'paypal_client_secret', '' ) );
	}

	/**
	 * Get the PayPal environment.
	 *
	 * @return string The environment.
	 */
	public static function get_environment(): string {
		$environment = sanitize_key( (string) BaseSettings::get( 'paypal_environment', self::DEFAULT_ENVIRONMENT ) );
		return in_array( $environment, self::ENVIRONMENTS, true ) ? $environment : self::DEFAULT_ENVIRONMENT;
	}

	/**
	 * Check if PayPal OAuth is connected.
	 *
	 * @return bool True if connected, false otherwise.
	 */
	public static function is_oauth_connected(): bool {
		return (bool) BaseSettings::get_bool( 'paypal_oauth_connected', false );
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
					'key'         => 'paypal_environment',
					'label'       => __( 'Environment', 'licencepress' ),
					'description' => __( 'Choose whether PayPal requests are sent to Sandbox or Live.', 'licencepress' ),
					'type'        => 'select',
					'options'     => array(
						'sandbox' => __( 'Sandbox', 'licencepress' ),
						'live'    => __( 'Live', 'licencepress' ),
					),
					'default'     => self::DEFAULT_ENVIRONMENT,
				),
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
					'key'         => 'paypal_currency',
					'label'       => __( 'Currency', 'licencepress' ),
					'description' => __( 'Default currency code for licenses and subscriptions.', 'licencepress' ),
					'type'        => 'text',
					'default'     => 'USD',
				),
				array(
					'key'         => 'paypal_checkout_enabled',
					'label'       => __( 'Enable one-time checkout', 'licencepress' ),
					'description' => __( 'Allow one-time PayPal checkout for purchases and renewals.', 'licencepress' ),
					'type'        => 'checkbox',
					'default'     => true,
				),
				array(
					'key'         => 'paypal_subscriptions_enabled',
					'label'       => __( 'Enable subscriptions', 'licencepress' ),
					'description' => __( 'Allow recurring plans and subscription billing.', 'licencepress' ),
					'type'        => 'checkbox',
					'default'     => false,
				),
				array(
					'key'         => 'paypal_oauth_connect',
					'label'       => __( 'PayPal connection', 'licencepress' ),
					'description' => __( 'Complete the OAuth flow to unlock the sidebar configuration and PayPal operations.', 'licencepress' ),
					'type'        => 'custom',
					'render'      => array( self::class, 'render_oauth_connection' ),
				),
			),
		);
	}

	public static function render_oauth_connection( $value, string $name, string $id ): void {
		if ( 'paypal_oauth_connect' !== $name && 'paypal_oauth_connect' !== $id && '' !== $value ) {
			$unused = $value . $name . $id;
		}

		$settings    = BaseSettings::get_group( self::GROUP, array() );
		$settings    = is_array( $settings ) ? $settings : array();
		$connected   = ! empty( $settings['paypal_oauth_connected'] );
		$client_id   = sanitize_text_field( (string) ( $settings['paypal_client_id'] ?? '' ) );
		$connect_url = admin_url( 'admin.php?page=licencepress-paypal&paypal_action=connect' );
		$status      = $connected ? __( 'Connected', 'licencepress' ) : __( 'Not connected', 'licencepress' );
		$tone        = $connected ? 'success' : 'warning';
		$message     = $connected
			? __( 'Your PayPal integration is active and the PayPal sidebar is available.', 'licencepress' )
			: __( 'Connect PayPal to unlock the PayPal sidebar and checkout settings.', 'licencepress' );
		?>
		<div class="d-flex flex-column gap-3" style="max-width: 540px;">
			<div class="card border-<?php echo esc_attr( $tone ); ?> shadow-none mb-0">
				<div class="card-body">
					<div class="d-flex align-items-center justify-content-between gap-3 mb-2">
						<strong><?php echo esc_html__( 'PayPal connection', 'licencepress' ); ?></strong>
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
					<?php echo esc_html__( 'Enter your PayPal client ID and client secret first, then connect.', 'licencepress' ); ?>
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

		$environment        = sanitize_key( (string) ( $input['paypal_environment'] ?? '' ) );
		$currency           = sanitize_text_field( (string) ( $input['paypal_currency'] ?? 'USD' ) );
		$currency           = strtoupper( $currency );
		$sanitized_currency = '' !== $currency ? $currency : 'USD';

		$settings = array(
			'paypal_environment'           => in_array( $environment, self::ENVIRONMENTS, true ) ? $environment : self::DEFAULT_ENVIRONMENT,
			'paypal_client_id'             => sanitize_text_field( (string) ( $input['paypal_client_id'] ?? '' ) ),
			'paypal_client_secret'         => sanitize_text_field( (string) ( $input['paypal_client_secret'] ?? '' ) ),
			'paypal_currency'              => $sanitized_currency,
			'paypal_checkout_enabled'      => ! empty( $input['paypal_checkout_enabled'] ),
			'paypal_subscriptions_enabled' => ! empty( $input['paypal_subscriptions_enabled'] ),
			'paypal_oauth_connected'       => ! empty( $input['paypal_oauth_connected'] ),
		);

		BaseSettings::set_group( self::GROUP, $settings );

		return $settings;
	}
}
