<?php
/**
 * PayPal admin screens and OAuth handlers.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\Admin
 * @since 1.0.0
 */

namespace LicencePress\Includes\Plugins\PayPal\Admin;

use LicencePress\Includes\Plugins\PayPal\Includes\API\PayPalClient;
use LicencePress\Includes\Plugins\PayPal\Includes\Functions\Helpers\PayPalConnectionService;
use LicencePress\Includes\Plugins\PayPal\Includes\Functions\Helpers\PayPalOAuthHelper;
use LicencePress\Includes\Plugins\PayPal\Includes\Settings\Settings as PayPalSettings;
use LicencePress\Includes\Settings\Settings as LicencePressSettings;

final class PayPalAdmin {
	public static function get_admin_menu(): array {
		return array(
			array(
				'menu_title' => __( 'PayPal', 'licencepress' ),
				'page_title' => __( 'PayPal', 'licencepress' ),
				'menu_slug'  => 'licencepress-paypal',
				'capability' => 'licencepress_paypal_view',
				'icon'       => 'dashicons-money-alt',
				'position'   => 32,
				'callback'   => array( self::class, 'render_dashboard' ),
				'children'   => array(
					array(
						'menu_title' => __( 'Dashboard', 'licencepress' ),
						'page_title' => __( 'PayPal Dashboard', 'licencepress' ),
						'menu_slug'  => 'licencepress-paypal',
						'capability' => 'licencepress_paypal_view',
						'callback'   => array( self::class, 'render_dashboard' ),
					),
					array(
						'menu_title' => __( 'Checkout', 'licencepress' ),
						'page_title' => __( 'Checkout', 'licencepress' ),
						'menu_slug'  => 'licencepress-paypal-checkout',
						'capability' => 'licencepress_paypal_checkout',
						'callback'   => array( self::class, 'render_checkout' ),
					),
					array(
						'menu_title' => __( 'Subscriptions', 'licencepress' ),
						'page_title' => __( 'Subscriptions', 'licencepress' ),
						'menu_slug'  => 'licencepress-paypal-subscriptions',
						'capability' => 'licencepress_paypal_subscriptions',
						'callback'   => array( self::class, 'render_subscriptions' ),
					),
					array(
						'menu_title' => __( 'Settings', 'licencepress' ),
						'page_title' => __( 'PayPal Settings', 'licencepress' ),
						'menu_slug'  => 'licencepress-paypal-settings',
						'capability' => 'licencepress_paypal_manage',
						'callback'   => array( self::class, 'render_settings' ),
					),
				),
			),
		);
	}

	public static function get_admin_sidebar(): array {
		$settings = LicencePressSettings::get_group( 'paypal', array() );
		$settings = is_array( $settings ) ? $settings : array();

		if ( empty( $settings['paypal_api_live_oauth_connected'] ) && empty( $settings['paypal_api_sandbox_oauth_connected'] ) ) {
			return array();
		}

		return array(
			array(
				'type'       => 'group',
				'label'      => __( 'PayPal', 'licencepress' ),
				'slug'       => 'licencepress-paypal-group',
				'icon'       => 'fa-brands fa-paypal',
				'capability' => 'licencepress_paypal_view',
				'items'      => array(
					array(
						'label'      => __( 'Overview', 'licencepress' ),
						'page'       => 'licencepress-paypal',
						'capability' => 'licencepress_paypal_view',
						'icon'       => 'fa-solid fa-gauge-high',
					),
					array(
						'label'      => __( 'Checkout', 'licencepress' ),
						'page'       => 'licencepress-paypal-checkout',
						'capability' => 'licencepress_paypal_checkout',
						'icon'       => 'fa-solid fa-cart-shopping',
					),
					array(
						'label'      => __( 'Subscriptions', 'licencepress' ),
						'page'       => 'licencepress-paypal-subscriptions',
						'capability' => 'licencepress_paypal_subscriptions',
						'icon'       => 'fa-solid fa-repeat',
					),
					array(
						'label'      => __( 'Settings', 'licencepress' ),
						'page'       => 'licencepress-paypal-settings',
						'capability' => 'licencepress_paypal_manage',
						'icon'       => 'fa-solid fa-gear',
					),
				),
			),
		);
	}

	public static function render_dashboard(): void {
		$settings    = LicencePressSettings::get_group( 'paypal', array() );
		$settings    = is_array( $settings ) ? $settings : array();
		$environment = sanitize_key( (string) ( $settings['paypal_environment'] ?? 'sandbox' ) );
		$connected   = ! empty( $settings[ 'paypal_api_' . $environment . '_oauth_connected' ] );
		$client_id   = (string) ( $settings[ 'paypal_api_' . $environment . '_client_id' ] ?? '' );
		$client_id_label = '' !== $client_id ? $client_id : __( 'Not configured', 'licencepress' );
		?>
		<div class="wrap licencepress-paypal-wrap">
			<h1><?php echo esc_html__( 'PayPal', 'licencepress' ); ?></h1>
			<p class="description"><?php echo esc_html__( 'Connect your PayPal app, enable one-time checkout, and manage recurring subscriptions from LicencePress.', 'licencepress' ); ?></p>

			<div class="card" style="max-width: 920px; padding: 20px; margin-top: 20px;">
				<h2><?php echo esc_html__( 'Connection status', 'licencepress' ); ?></h2>
				<p><strong><?php echo esc_html__( 'Environment:', 'licencepress' ); ?></strong> <?php echo esc_html( $environment ); ?></p>
				<p><strong><?php echo esc_html__( 'OAuth:', 'licencepress' ); ?></strong> <?php echo esc_html( $connected ? __( 'Connected', 'licencepress' ) : __( 'Not connected', 'licencepress' ) ); ?></p>
				<p><strong><?php echo esc_html__( 'Client ID:', 'licencepress' ); ?></strong> <?php echo esc_html( $client_id_label ); ?></p>
				<p class="text-secondary mb-0"><?php echo esc_html__( 'Use the PayPal plugin settings modal to complete the OAuth connection and enable the sidebar configuration.', 'licencepress' ); ?></p>
			</div>
		</div>
		<?php
	}

	public static function render_checkout(): void {
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'PayPal checkout', 'licencepress' ); ?></h1>
			<p><?php echo esc_html__( 'One-time PayPal checkout will be embedded here for license purchases, renewals, and product access.', 'licencepress' ); ?></p>
		</div>
		<?php
	}

	public static function render_subscriptions(): void {
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'PayPal subscriptions', 'licencepress' ); ?></h1>
			<p><?php echo esc_html__( 'Recurring billing plans and subscription management will be configured here.', 'licencepress' ); ?></p>
		</div>
		<?php
	}

	public static function render_settings(): void {
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'PayPal settings', 'licencepress' ); ?></h1>
			<p><?php echo esc_html__( 'Use the plugin settings modal or the default LicencePress settings form to configure credentials, mode, and billing toggles.', 'licencepress' ); ?></p>
		</div>
		<?php
	}

	public static function maybe_handle_oauth_connect(): void {
		error_log( '[LicencePress][PayPal] maybe_handle_oauth_connect fired: ' . wp_json_encode( $_GET ) );

		if ( ! isset( $_GET['paypal_oauth'] ) ) {
			error_log( '[LicencePress][PayPal] maybe_handle_oauth_connect exit: paypal_oauth missing.' );
			return;
		}

		if ( ! current_user_can( 'licencepress_paypal_manage' ) && ! current_user_can( 'manage_options' ) ) {
			error_log( '[LicencePress][PayPal] maybe_handle_oauth_connect blocked: insufficient capabilities.' );
			return;
		}

		$settings = LicencePressSettings::get_group( 'paypal', array() );
		$settings = is_array( $settings ) ? $settings : array();
		$environment = sanitize_key( wp_unslash( $_GET['paypal_environment'] ?? ( $settings['paypal_environment'] ?? 'sandbox' ) ) );
		$connect_url = PayPalConnectionService::start_oauth_connect( $settings, $environment );
		$client_id = PayPalSettings::get_client_id( $environment );

		error_log( '[LicencePress][PayPal] maybe_handle_oauth_connect environment=' . $environment . ' client_id_set=' . ( '' !== $client_id ? 'yes' : 'no' ) );

		error_log( '[LicencePress][PayPal] maybe_handle_oauth_connect redirecting to PayPal: ' . $connect_url );
		wp_safe_redirect( $connect_url );
		exit;
	}

	public static function maybe_handle_oauth_callback(): void {
		error_log( '[LicencePress][PayPal] maybe_handle_oauth_callback fired: ' . wp_json_encode( $_GET ) );

		if ( ! isset( $_GET['paypal_action'] ) || 'callback' !== sanitize_key( wp_unslash( $_GET['paypal_action'] ) ) ) {
			error_log( '[LicencePress][PayPal] maybe_handle_oauth_callback exit: paypal_action not callback.' );
			return;
		}

		if ( ! current_user_can( 'licencepress_paypal_manage' ) && ! current_user_can( 'manage_options' ) ) {
			error_log( '[LicencePress][PayPal] maybe_handle_oauth_callback blocked: insufficient capabilities.' );
			return;
		}

		$code        = sanitize_text_field( wp_unslash( $_GET['code'] ?? '' ) );
		$state       = sanitize_text_field( wp_unslash( $_GET['state'] ?? '' ) );
		$environment = sanitize_key( wp_unslash( $_GET['paypal_environment'] ?? 'sandbox' ) );
		$request     = array(
			'code'               => $code,
			'state'              => $state,
			'paypal_environment' => $environment,
		);
		error_log( '[LicencePress][PayPal] callback state=' . $state . ' env=' . $environment . ' code_present=' . ( '' !== $code ? 'yes' : 'no' ) );

		$result = PayPalConnectionService::complete_oauth_connect( $request );
		if ( empty( $result['success'] ) ) {
			error_log( '[LicencePress][PayPal] callback failed: ' . ( $result['error'] ?? 'unknown_error' ) . ' env=' . ( $result['environment'] ?? $environment ) );
			wp_safe_redirect( admin_url( 'admin.php?page=licencepress&group=settings&tab=billing&paypal_environment=' . $environment . '#paypal' ) );
			exit;
		}

		error_log( '[LicencePress][PayPal] OAuth success for environment=' . ( $result['environment'] ?? $environment ) . ' token_received=' . ( ! empty( $result['body']['access_token'] ) ? 'yes' : 'no' ) );
		wp_safe_redirect( PayPalOAuthHelper::get_success_redirect_url() );
		exit;
	}
}
