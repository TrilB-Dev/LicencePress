<?php
/**
 * Stripe admin screens and OAuth handlers.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\Stripe\Admin
 * @since 1.0.0
 */

namespace LicencePress\Includes\Plugins\Stripe\Admin;

use LicencePress\Includes\Plugins\Stripe\API\Client\StripeClient;
use LicencePress\Includes\Plugins\Stripe\Includes\Functions\Helpers\StripeConnectionService;
use LicencePress\Includes\Plugins\Stripe\Includes\Settings\Settings as StripeSettings;
use LicencePress\Includes\Settings\Settings as LicencePressSettings;

final class StripeAdmin {
	public static function get_admin_menu(): array {
		return array(
			array(
				'menu_title' => __( 'Stripe', 'licencepress' ),
				'page_title' => __( 'Stripe', 'licencepress' ),
				'menu_slug'  => 'licencepress-stripe',
				'capability' => 'licencepress_stripe_view',
				'icon'       => 'dashicons-money-alt',
				'position'   => 32,
				'callback'   => array( self::class, 'render_dashboard' ),
				'children'   => array(
					array(
						'menu_title' => __( 'Dashboard', 'licencepress' ),
						'page_title' => __( 'Stripe Dashboard', 'licencepress' ),
						'menu_slug'  => 'licencepress-stripe',
						'capability' => 'licencepress_stripe_view',
						'callback'   => array( self::class, 'render_dashboard' ),
					),
					array(
						'menu_title' => __( 'Checkout', 'licencepress' ),
						'page_title' => __( 'Checkout', 'licencepress' ),
						'menu_slug'  => 'licencepress-stripe-checkout',
						'capability' => 'licencepress_stripe_checkout',
						'callback'   => array( self::class, 'render_checkout' ),
					),
					array(
						'menu_title' => __( 'Subscriptions', 'licencepress' ),
						'page_title' => __( 'Subscriptions', 'licencepress' ),
						'menu_slug'  => 'licencepress-stripe-subscriptions',
						'capability' => 'licencepress_stripe_subscriptions',
						'callback'   => array( self::class, 'render_subscriptions' ),
					),
					array(
						'menu_title' => __( 'Settings', 'licencepress' ),
						'page_title' => __( 'Stripe Settings', 'licencepress' ),
						'menu_slug'  => 'licencepress-stripe-settings',
						'capability' => 'licencepress_stripe_manage',
						'callback'   => array( self::class, 'render_settings' ),
					),
				),
			),
		);
	}

	public static function get_admin_sidebar(): array {
		$settings = LicencePressSettings::get_group( 'stripe', array() );
		$settings = is_array( $settings ) ? $settings : array();

		if ( empty( $settings['stripe_api_live_connected'] ) && empty( $settings['stripe_api_sandbox_connected'] ) ) {
			return array();
		}

		return array(
			array(
				'type'       => 'group',
				'label'      => __( 'Stripe', 'licencepress' ),
				'slug'       => 'licencepress-stripe-group',
				'icon'       => 'fa-brands fa-stripe',
				'capability' => 'licencepress_stripe_view',
				'items'      => array(
					array(
						'label'      => __( 'Overview', 'licencepress' ),
						'page'       => 'licencepress-stripe',
						'capability' => 'licencepress_stripe_view',
						'icon'       => 'fa-solid fa-gauge-high',
					),
					array(
						'label'      => __( 'Checkout', 'licencepress' ),
						'page'       => 'licencepress-stripe-checkout',
						'capability' => 'licencepress_stripe_checkout',
						'icon'       => 'fa-solid fa-cart-shopping',
					),
					array(
						'label'      => __( 'Subscriptions', 'licencepress' ),
						'page'       => 'licencepress-stripe-subscriptions',
						'capability' => 'licencepress_stripe_subscriptions',
						'icon'       => 'fa-solid fa-repeat',
					),
					array(
						'label'      => __( 'Settings', 'licencepress' ),
						'page'       => 'licencepress-stripe-settings',
						'capability' => 'licencepress_stripe_manage',
						'icon'       => 'fa-solid fa-gear',
					),
				),
			),
		);
	}

	public static function render_dashboard(): void {
		$settings    = LicencePressSettings::get_group( 'stripe', array() );
		$settings    = is_array( $settings ) ? $settings : array();
		$environment = sanitize_key( (string) ( $settings['stripe_environment'] ?? 'sandbox' ) );
		$connected   = ! empty( $settings[ 'stripe_api_' . $environment . '_connected' ] );
		$secret_key  = (string) ( $settings[ 'stripe_api_' . $environment . '_secret_key' ] ?? '' );
		$secret_key_label = '' !== $secret_key ? '********' : __( 'Not configured', 'licencepress' );
		?>
		<div class="wrap licencepress-stripe-wrap">
			<h1><?php echo esc_html__( 'Stripe', 'licencepress' ); ?></h1>
			<p class="description"><?php echo esc_html__( 'Configure your Stripe app credentials, enable one-time checkout, and manage recurring subscriptions from LicencePress.', 'licencepress' ); ?></p>

			<div class="card" style="max-width: 920px; padding: 20px; margin-top: 20px;">
				<h2><?php echo esc_html__( 'Connection status', 'licencepress' ); ?></h2>
				<p><strong><?php echo esc_html__( 'Environment:', 'licencepress' ); ?></strong> <?php echo esc_html( $environment ); ?></p>
				<p><strong><?php echo esc_html__( 'REST auth:', 'licencepress' ); ?></strong> <?php echo esc_html( $connected ? __( 'Connected', 'licencepress' ) : __( 'Not connected', 'licencepress' ) ); ?></p>
				<p><strong><?php echo esc_html__( 'Secret key:', 'licencepress' ); ?></strong> <?php echo esc_html( $secret_key_label ); ?></p>
				<p class="text-secondary mb-0"><?php echo esc_html__( 'Save a valid Stripe secret key to enable the connection check for this environment.', 'licencepress' ); ?></p>
			</div>
		</div>
		<?php
	}

	public static function render_checkout(): void {
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Stripe checkout', 'licencepress' ); ?></h1>
			<p><?php echo esc_html__( 'One-time Stripe checkout will be embedded here for license purchases, renewals, and product access.', 'licencepress' ); ?></p>
		</div>
		<?php
	}

	public static function render_subscriptions(): void {
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Stripe subscriptions', 'licencepress' ); ?></h1>
			<p><?php echo esc_html__( 'Recurring billing plans and subscription management will be configured here.', 'licencepress' ); ?></p>
		</div>
		<?php
	}

	public static function render_settings(): void {
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Stripe settings', 'licencepress' ); ?></h1>
			<p><?php echo esc_html__( 'Use the plugin settings modal or the default LicencePress settings form to configure credentials, mode, and billing toggles.', 'licencepress' ); ?></p>
		</div>
		<?php
	}

}
