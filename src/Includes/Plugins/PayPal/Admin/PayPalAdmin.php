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
use LicencePress\Includes\Plugins\PayPal\Includes\Functions\PayPalOAuthHelper;
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

		if ( empty( $settings['paypal_oauth_connected'] ) ) {
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
		$connected   = ! empty( $settings['paypal_oauth_connected'] );
		$environment = strtoupper( (string) ( $settings['paypal_environment'] ?? 'sandbox' ) );
		$client_id   = (string) ( $settings['paypal_client_id'] ?? '' );
		?>
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
		if ( ! is_admin() || ! current_user_can( 'licencepress_paypal_manage' ) ) {
			return;
		}

		if ( empty( $_GET['paypal_action'] ) || 'connect' !== sanitize_key( wp_unslash( $_GET['paypal_action'] ) ) ) {
			return;
		}

		$settings  = LicencePressSettings::get_group( 'paypal', array() );
		$settings  = is_array( $settings ) ? $settings : array();
		$client_id = sanitize_text_field( (string) ( $settings['paypal_client_id'] ?? '' ) );

		if ( '' === $client_id ) {
			wp_safe_redirect( admin_url( 'admin.php?page=licencepress-paypal' ) );
			exit;
		}

		$state = PayPalOAuthHelper::generate_state();
		PayPalOAuthHelper::save_state( $state );

		wp_safe_redirect( PayPalOAuthHelper::build_connect_url( $settings, $state ) );
		exit;
	}

	public static function maybe_handle_oauth_callback(): void {
		if ( ! is_admin() || ! current_user_can( 'licencepress_paypal_manage' ) ) {
			return;
		}

		if ( empty( $_GET['paypal_action'] ) || 'callback' !== sanitize_key( wp_unslash( $_GET['paypal_action'] ) ) ) {
			return;
		}

		$code  = sanitize_text_field( wp_unslash( $_GET['code'] ?? '' ) );
		$state = sanitize_text_field( wp_unslash( $_GET['state'] ?? '' ) );

		if ( '' === $code || ! PayPalOAuthHelper::validate_state( $state ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=licencepress-paypal' ) );
			exit;
		}

		$settings = LicencePressSettings::get_group( 'paypal', array() );
		$settings = is_array( $settings ) ? $settings : array();
		$body     = PayPalClient::exchange_code_for_token( $settings, $code );

		if ( ! is_array( $body ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=licencepress-paypal' ) );
			exit;
		}

		$settings['paypal_access_token']    = sanitize_text_field( (string) ( $body['access_token'] ?? '' ) );
		$settings['paypal_refresh_token']   = sanitize_text_field( (string) ( $body['refresh_token'] ?? '' ) );
		$settings['paypal_oauth_connected'] = ! empty( $body['access_token'] );
		LicencePressSettings::set_group( 'paypal', $settings );
		PayPalOAuthHelper::clear_state();

		wp_safe_redirect( admin_url( 'admin.php?page=licencepress-paypal' ) );
		exit;
	}
}
