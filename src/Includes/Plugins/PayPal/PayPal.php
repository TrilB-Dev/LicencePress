<?php
/**
 * LicencePress - PayPal Plugin
 *
 * @package LicencePress
 * @since 1.0.0
 */

namespace LicencePress\Includes\Plugins\PayPal;

use LicencePress\Includes\Functions\Helpers\LoaderHelper;
use LicencePress\Includes\Plugins\AdminMenuProviderInterface;
use LicencePress\Includes\Plugins\AdminSidebarProviderInterface;
use LicencePress\Includes\Plugins\AssetsProviderInterface;
use LicencePress\Includes\Plugins\I18nProviderInterface;
use LicencePress\Includes\Plugins\PluginInterface;
use LicencePress\Includes\Plugins\RestRouteProviderInterface;
use LicencePress\Includes\Plugins\SettingsProviderInterface;
use LicencePress\Includes\Plugins\SettingsPageProviderInterface;
use LicencePress\Includes\Plugins\PayPal\Admin\BillingSettingsPayPal;
use LicencePress\Includes\Plugins\PayPal\Admin\PayPalAdmin;
use LicencePress\Includes\Plugins\PayPal\Assets\Assets;
use LicencePress\Includes\Plugins\PayPal\Includes\Core\I18n;
use LicencePress\Includes\Plugins\PayPal\Includes\Functions\Helpers\PayPalConnectionService;
use LicencePress\Includes\Plugins\PayPal\Includes\Functions\Helpers\PayPalOAuthHelper;
use LicencePress\Includes\Plugins\PayPal\Includes\Includes;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

final class PayPal implements PluginInterface, RestRouteProviderInterface, SettingsProviderInterface, SettingsPageProviderInterface, AssetsProviderInterface, I18nProviderInterface, AdminMenuProviderInterface, AdminSidebarProviderInterface {
	/**
	 * The loader helper instance.
	 *
	 * @var LoaderHelper The loader helper instance.
	 */
	private LoaderHelper $loader;
	/**
	 * Constructor for the PayPal plugin.
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
		return 'licencepress-paypal';
	}
	/**
	 * Get the plugin name.
	 *
	 * @return string The plugin name.
	 */
	public function get_name(): string {
		return 'PayPal';
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
		return Assets::get_image( 'logo/PayPal-Monogram-FullColor-RGB.png' );
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
		return __( 'Introduces a local PayPal 8.8 editor for LicencePress.', 'licencepress' );
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
		Includes::get_instance()->init();
		PayPalOAuthHelper::register_allowed_redirect_hosts();

		$paypal_admin = new PayPalAdmin();
		$billing_tabs = new BillingSettingsPayPal();

		$this->loader->register_component(
			$paypal_admin,
			array(
				array(
					'type'     => 'action',
					'hook'     => 'admin_init',
					'callback' => 'maybe_handle_oauth_connect',
				),
				array(
					'type'     => 'action',
					'hook'     => 'template_redirect',
					'callback' => 'maybe_handle_oauth_connect',
				),
				array(
					'type'     => 'action',
					'hook'     => 'admin_init',
					'callback' => 'maybe_handle_oauth_callback',
				),
				array(
					'type'     => 'action',
					'hook'     => 'template_redirect',
					'callback' => 'maybe_handle_oauth_callback',
				),
			)
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
			'/paypal/connect',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( self::class, 'rest_connect' ),
				'permission_callback' => array( self::class, 'rest_permission_callback' ),
			)
		);

		register_rest_route(
			'licencepress/v1',
			'/paypal/callback',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( self::class, 'rest_callback' ),
				'permission_callback' => array( self::class, 'rest_permission_callback' ),
			)
		);
	}

	public static function rest_permission_callback(): bool {
		return current_user_can( 'licencepress_paypal_manage' ) || current_user_can( 'manage_options' );
	}

	public static function rest_connect( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$settings = array();
		$body     = $request->get_json_params();
		if ( ! is_array( $body ) ) {
			$body = $request->get_body_params();
		}
		if ( ! is_array( $body ) ) {
			$body = array();
		}

		$environment = sanitize_key( (string) ( $body['environment'] ?? $body['paypal_environment'] ?? 'sandbox' ) );
		$settings['paypal_environment'] = $environment;

		$connect_url = PayPalConnectionService::start_oauth_connect( $settings, $environment );
		if ( false === strpos( $connect_url, 'bizsignup/partner/entry' ) ) {
			return new WP_Error( 'paypal_partner_signup_unavailable', __( 'PayPal partner signup URL is not available for this environment.', 'licencepress' ) );
		}

		return new WP_REST_Response(
			array(
				'success'     => true,
				'environment' => $environment,
				'connect_url' => $connect_url,
			),
			200
		);
	}

	public static function rest_callback( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$data = array(
			'code'               => sanitize_text_field( (string) $request->get_param( 'code' ) ),
			'state'              => sanitize_text_field( (string) $request->get_param( 'state' ) ),
			'paypal_environment' => sanitize_key( (string) $request->get_param( 'paypal_environment' ) ),
		);

		$result = PayPalConnectionService::complete_oauth_connect( $data );
		if ( empty( $result['success'] ) ) {
			return new WP_Error( $result['error'] ?? 'paypal_oauth_callback_failed', __( 'PayPal OAuth callback failed.', 'licencepress' ) );
		}

		return new WP_REST_Response(
			array(
				'success'     => true,
				'environment' => $result['environment'] ?? 'sandbox',
				'connected'   => true,
				'token'       => $result['body']['access_token'] ?? '',
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
		return PayPalAdmin::get_admin_menu();
	}
	/**
	 * Get the admin sidebar for the plugin.
	 *
	 * @return array The admin sidebar configuration.
	 */
	public function get_admin_sidebar(): array {
		return PayPalAdmin::get_admin_sidebar();
	}
}
