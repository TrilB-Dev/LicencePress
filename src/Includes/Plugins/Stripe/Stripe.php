<?php
/**
 * LicencePress - Stripe Plugin
 *
 * @package LicencePress
 * @since 1.0.0
 */

namespace LicencePress\Includes\Plugins\Stripe;

use LicencePress\Includes\Functions\Helpers\LoaderHelper;
use LicencePress\Includes\Plugins\Stripe\Includes\Core\Capabilities as StripeCapabilities;
use LicencePress\Includes\Plugins\AdminMenuProviderInterface;
use LicencePress\Includes\Plugins\Stripe\API\StripeAPI;
use LicencePress\Includes\Plugins\AdminSidebarProviderInterface;
use LicencePress\Includes\Plugins\AssetsProviderInterface;
use LicencePress\Includes\Plugins\I18nProviderInterface;
use LicencePress\Includes\Plugins\PluginInterface;
use LicencePress\Includes\Plugins\RestRouteProviderInterface;
use LicencePress\Includes\Plugins\SettingsProviderInterface;
use LicencePress\Includes\Plugins\SettingsPageProviderInterface;
use LicencePress\Includes\Plugins\Stripe\Admin\BillingSettingsStripe;
use LicencePress\Includes\Plugins\Stripe\Admin\StripeAdmin;
use LicencePress\Includes\Plugins\Stripe\Assets\Assets;
use LicencePress\Includes\Plugins\Stripe\Includes\Core\I18n;
use LicencePress\Includes\Plugins\Stripe\Includes\Functions\Helpers\StripeConnectionService;
use LicencePress\Includes\Plugins\Stripe\Includes\Includes;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

final class Stripe implements PluginInterface, RestRouteProviderInterface, SettingsProviderInterface, SettingsPageProviderInterface, AssetsProviderInterface, I18nProviderInterface, AdminMenuProviderInterface, AdminSidebarProviderInterface {
	/**
	 * The loader helper instance.
	 *
	 * @var LoaderHelper The loader helper instance.
	 */
	private LoaderHelper $loader;
	/**
	 * Constructor for the Stripe plugin.
	 */
	public function __construct() {
		$this->loader = new LoaderHelper();
	}

	/**
	 * Get the plugin slug.
	 *
	 * @return string The plugin slug.
	 */
	public function get_slug(): string {
		return 'licencepress-stripe';
	}
	/**
	 * Get the plugin name.
	 *
	 * @return string The plugin name.
	 */
	public function get_name(): string {
		return 'Stripe';
	}
	/**
	 * Get the plugin version.
	 *
	 * @return string The plugin version.
	 */
	public function get_version(): string {
		return '1.0.0';
	}
	/**
	 * Get the plugin icon.
	 *
	 * @return string The plugin icon.
	 */
	public function get_icon(): string {
		return Assets::get_image( 'logo/Stripe-wordmark-Blurple-Small.png' );
	}
	/**
	 * Get the plugin author.
	 *
	 * @return string The plugin author.
	 */
	public function get_author(): string {
		return 'TrilB.Dev Team';
	}
	/**
	 * Get the plugin author URI.
	 *
	 * @return string The plugin author URI.
	 */
	public function get_author_uri(): string {
		return 'https://trilb.dev/';
	}
	/**
	 * Get the plugin description.
	 *
	 * @return string The plugin description.
	 */
	public function get_description(): string {
		return __( 'Introduces a local Stripe 8.8 editor for LicencePress.', 'licencepress' );
	}
	/**
	 * Get the plugin URI.
	 *
	 * @return string The plugin URI.
	 */
	public function get_uri(): string {
		return 'https://trilb.dev/collection/web-extension/wordpress/licencepress';
	}
	/**
	 * Get the plugin license.
	 *
	 * @return string The plugin license.
	 */
	public function get_license(): string {
		return 'GPL-2.0-or-later';
	}
	/**
	 * Check if the plugin is active.
	 *
	 * @return bool True if the plugin is active, false otherwise.
	 */
	public function is_active(): bool {
		return true;
	}
	/**
	 * Initializes the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init(): void {
		StripeCapabilities::register();
		Includes::get_instance()->init();

		$stripe_admin = new StripeAdmin();
		$billing_tabs = new BillingSettingsStripe();

		$this->loader->register_component(
			$stripe_admin,
			array()
		)->register_component(
			$billing_tabs,
			array(
				array(
					'type'     => 'filter',
					'hook'     => 'licencepress_billing_settings_tabs',
					'callback' => 'register_billing_tab',
				),
			)
		)->add_action( 'rest_api_init', $this, 'register_rest_routes' )
		->run();
	}
	/**
	 * Register the settings for the plugin.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		Includes::get_instance()->settings()->register();
	}
	/**
	 * Get the settings page for the plugin.
	 *
	 * @return array The settings page configuration.
	 */
	public function get_settings_page(): array {
		return Includes::get_instance()->settings()->get_settings_page();
	}
	/**
	 * Sanitize the settings input for the plugin.
	 *
	 * @param mixed $input The input to sanitize.
	 * @return array The sanitized settings.
	 */
	public function sanitize_settings( $input ): array {
		return Includes::get_instance()->settings()->sanitize( $input );
	}
	/**
	 * Register the assets for the plugin.
	 *
	 * @return void
	 */
	public function register_assets(): void {
		( new Assets() )->register();
	}

	public function register_rest_routes(): void {
		register_rest_route(
			'licencepress/v1',
			'/stripe/test-connection',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( self::class, 'rest_test_connection' ),
				'permission_callback' => array( self::class, 'rest_permission_callback' ),
			)
		);

	}

	public static function rest_permission_callback(): bool {
		return current_user_can( 'licencepress_stripe_manage' ) || current_user_can( 'manage_options' );
	}

	public static function rest_test_connection( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$settings = array();
		$body     = $request->get_json_params();
		if ( ! is_array( $body ) ) {
			$body = $request->get_body_params();
		}
		if ( ! is_array( $body ) ) {
			$body = array();
		}

		$environment = sanitize_key( (string) ( $body['environment'] ?? $body['stripe_environment'] ?? 'sandbox' ) );
		$settings['stripe_environment'] = $environment;
		$settings['secret_key'] = $body['secret_key'] ?? $body['stripe_api_' . $environment . '_secret_key'] ?? $body['client_secret'] ?? $body['stripe_api_' . $environment . '_client_secret'] ?? '';

		$result = StripeConnectionService::test_connection( $settings, $environment );
		if ( empty( $result['success'] ) ) {
			return new WP_Error( $result['error'] ?? 'stripe_connection_test_failed', __( 'Stripe connection test failed.', 'licencepress' ) );
		}

		return new WP_REST_Response(
			array(
				'success'     => true,
				'connected'   => true,
				'environment' => $environment,
			),
			200
		);
	}

	/**
	 * Load the text domain for the plugin.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		I18n::load_textdomain();
	}
	/**
	 * Get the admin menu for the plugin.
	 *
	 * @return array The admin menu configuration.
	 */
	public function get_admin_menu(): array {
		return StripeAdmin::get_admin_menu();
	}
	/**
	 * Get the admin sidebar for the plugin.
	 *
	 * @return array The admin sidebar configuration.
	 */
	public function get_admin_sidebar(): array {
		return StripeAdmin::get_admin_sidebar();
	}
}
