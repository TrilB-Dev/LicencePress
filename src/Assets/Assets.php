<?php
/**
 * LicencePress Assets
 *
 * @package LicencePress
 * @subpackage Assets
 * @since 1.0.0
 */
namespace LicencePress\Assets;

use LicencePress\Includes\Functions\Helpers\ImageHelper;
use LicencePress\Includes\Functions\Helpers\LoaderHelper;
use LicencePress\Includes\Functions\Helpers\RequestHelper;
use LicencePress\Includes\Functions\Helpers\SanitizationHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Assets {
	/**
	 * Array to hold registered assets for different pages.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	private array $pages = array();
	/**
	 * Array to hold registered asset groups.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	private array $groups = array();
	/**
	 * Array to hold registered asset tabs.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	private array $tabs = array();
	/**
	 * Registers the default assets for the plugin.
	 *
	 * @return void
	 */
	public function register(): void {
		( new LoaderHelper() )->register_component(
			$this,
			array(
				array(
					'type'          => 'filter',
					'hook'          => 'licencepress_base_assets',
					'callback'      => 'default_assets',
					'priority'      => 90,
					'accepted_args' => 2,
				),
				array(
					'type'     => 'action',
					'hook'     => 'wp_enqueue_scripts',
					'callback' => 'enqueue_frontend',
				),
				array(
					'type'     => 'action',
					'hook'     => 'admin_enqueue_scripts',
					'callback' => 'enqueue_admin',
				),
			)
		)->run();
	}
	/**
	 * Registers assets for a specific page.
	 *
	 * @param string $page The page identifier.
	 * @param array  $assets The assets to register for the page.
	 * @return void
	 */
	public function register_page( string $page, array $assets ): void {
		$page                 = SanitizationHelper::key( $page );
		$this->pages[ $page ] = array(
			'styles'  => array_merge( $this->pages[ $page ]['styles'] ?? array(), $assets['styles'] ?? array() ),
			'scripts' => array_merge( $this->pages[ $page ]['scripts'] ?? array(), $assets['scripts'] ?? array() ),
		);
	}
	/**
	 * Registers assets for a specific group.
	 *
	 * @param string $group The group identifier.
	 * @param array  $assets The assets to register for the group.
	 * @return void
	 */
	public function register_group( string $group, array $assets ): void {
		$group = SanitizationHelper::key( $group );
		$this->groups[ $group ] = array(
			'styles'  => array_merge( $this->groups[ $group ]['styles'] ?? array(), $assets['styles'] ?? array() ),
			'scripts' => array_merge( $this->groups[ $group ]['scripts'] ?? array(), $assets['scripts'] ?? array() ),
		);
	}
	/**
	 * Registers assets for a specific tab.
	 *
	 * @param string $tab The tab identifier.
	 * @param array  $assets The assets to register for the tab.
	 * @return void
	 */
	public function register_tab( string $tab, array $assets ): void {
		$tab = SanitizationHelper::key( $tab );
		$this->tabs[ $tab ] = array(
			'styles'  => array_merge( $this->tabs[ $tab ]['styles'] ?? array(), $assets['styles'] ?? array() ),
			'scripts' => array_merge( $this->tabs[ $tab ]['scripts'] ?? array(), $assets['scripts'] ?? array() ),
		);
	}
	/**
	 * Returns the default assets for the plugin.
	 *
	 * @param array  $assets The current assets.
	 * @param string $context The context (e.g., 'frontend', 'admin').
	 * @return array The default assets.
	 */
	public function default_assets( array $assets, string $context ): array {
		$defaults = array(
			'styles'  => array(
				array(
					'handle' => 'licencepress-wp-override',
					'src'    => LICENCEPRESS_ASSETS_URL . '/dist/css/wpoverride.css',
					'deps'   => array( 'forms' ),
				),
				array(
					'handle'  => 'licencepress-bootstrap',
					'src'     => LICENCEPRESS_ASSETS_URL . '/dist/css/bootstrap.css',
					'version' => '5.3.8',
					'deps'    => array( 'licencepress-wp-override' ),
				),
				array(
					'handle'  => 'licencepress-bootstrap-select',
					'src'     => LICENCEPRESS_ASSETS_URL . '/dist/css/bootstrap-select.min.css',
					'version' => '1.2.2',
					'deps'    => array( 'licencepress-bootstrap' ),
				),
				array(
					'handle'  => 'licencepress-bs-country-data',
					'src'     => LICENCEPRESS_ASSETS_URL . '/dist/css/bs-country-data.min.css',
					'version' => '1.0.4',
					'deps'    => array( 'licencepress-bootstrap-select' ),
				),
			),
			'scripts' => array(
				array(
					'handle'    => 'licencepress-bootstrap',
					'src'       => LICENCEPRESS_ASSETS_URL . '/dist/js/bootstrap.js',
					'version'   => '5.3.8',
					'in_footer' => true,
				),
				array(
					'handle'    => 'licencepress-bootstrap-select',
					'src'       => LICENCEPRESS_ASSETS_URL . '/dist/js/bootstrap-select.min.js',
					'version'   => '1.2.2',
					'deps'      => array( 'licencepress-bootstrap' ),
					'in_footer' => true,
				),
				array(
					'handle'	=> 'licencepress-bs-country-data',
					'src'       => LICENCEPRESS_ASSETS_URL . '/dist/js/bs-country-data.min.js',
					'version'   => '1.0.4',
					'deps'      => array( 'licencepress-bootstrap-select' ),
					'in_footer' => true,
				)
			),
		);

		return array( 'base' => $defaults ) + $defaults;
	}

	/**
	 * Enqueues the frontend assets for the plugin.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function enqueue_frontend(): void {
		if ( ! is_singular( 'licencepress_page' ) ) {
			return;
		}

		$assets = apply_filters( 'licencepress_base_assets', array(), 'frontend' );
		$this->enqueue_registered(
			'frontend',
			array(
				'styles'  => array_merge(
					$assets['base']['styles'] ?? array(),
					array(
						array(
							'handle' => 'licencepress-public',
							'src'    => LICENCEPRESS_ASSETS_URL . '/dist/css/public.css',
						),
					)
				),
				'scripts' => array_merge(
					$assets['base']['scripts'] ?? array(),
					array(
						array(
							'handle'    => 'licencepress-public',
							'src'       => LICENCEPRESS_ASSETS_URL . '/dist/js/public.js',
							'in_footer' => true,
						),
					)
				),
			)
		);
	}
	/**
	 * Enqueues the admin assets for the plugin.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 * @return void
	 * @since 1.0.0
	 */
	public function enqueue_admin( string $hook_suffix ): void {
		if ( false === strpos( $hook_suffix, 'licencepress' ) ) {
			return;
		}

		$page       = RequestHelper::get_key( 'page', 'licencepress' );
		$registered = $this->pages[ $page ] ?? array();
		$base       = apply_filters( 'licencepress_base_assets', array(), 'admin' );
		$this->enqueue_registered(
			'admin',
			array(
				'styles'  => array_merge(
					$base['styles'] ?? array(),
					array(
						array(
							'handle'  => 'licencepress-admin-ui',
							'src'     => LICENCEPRESS_ASSETS_URL . '/dist/css/admin.ui.css',
							'version' => '1.0.0',
							'deps'    => array( 'licencepress-bootstrap' ),
						),
					),
					$registered['styles'] ?? array()
				),
				'scripts' => array_merge(
					$base['scripts'] ?? array(),
					array(
						array(
							'handle'    => 'licencepress-admin-ui',
							'src'       => LICENCEPRESS_ASSETS_URL . '/dist/js/admin.ui.js',
							'deps'      => array( 'licencepress-bootstrap' ),
							'in_footer' => true,
						),
					),
					$registered['scripts'] ?? array()
				),
			)
		);
	}
	/**
	 * Enqueues the registered assets for a given context.
	 *
	 * @param string $context The context (e.g., 'frontend', 'admin').
	 * @param array  $assets The assets to enqueue.
	 * @return void
	 * @since 1.0.0
	 */
	protected function enqueue_registered( string $context, array $assets ): void {
		$assets = apply_filters( 'licencepress_' . $context . '_assets', $assets, $context );
		$this->enqueue_bundle( $assets );
	}
	/**
	 * Enqueues a bundle of assets (styles and scripts).
	 *
	 * @param array $assets The assets to enqueue.
	 * @return void
	 * @since 1.0.0
	 */
	protected function enqueue_bundle( array $assets ): void {
		if ( isset( $assets['styles'] ) && is_string( $assets['styles'] ) ) {
			$assets['styles'] = array(
				array(
					'handle' => 'licencepress-admin-' . $assets['styles'],
					'src'    => LICENCEPRESS_ASSETS_URL . '/dist/css/admin.' . $assets['styles'] . '.css',
					'deps'    => array( 'licencepress-admin-ui' ),
					'version' => LICENCEPRESS_VERSION,
				),
			);
		}
		if ( isset( $assets['scripts'] ) && is_string( $assets['scripts'] ) ) {
			$assets['scripts'] = array(
				array(
					'handle' => 'licencepress-admin-' . $assets['scripts'],
					'src'    => LICENCEPRESS_ASSETS_URL . '/dist/js/admin.' . $assets['scripts'] . '.js',
					'deps'   => array( 'licencepress-admin-ui' ),
					'version' => LICENCEPRESS_VERSION,
					'in_footer' => true,
				),
			);
		}
		foreach ( $assets['styles'] ?? array() as $style ) {
			LoaderHelper::enqueue_style( $style['handle'], $style['src'], $style['deps'] ?? array(), $style['version'] ?? LICENCEPRESS_VERSION, $style['media'] ?? 'all' );
		}
		foreach ( $assets['scripts'] ?? array() as $script ) {
			LoaderHelper::enqueue_script( $script['handle'], $script['src'], $script['deps'] ?? array(), $script['version'] ?? LICENCEPRESS_VERSION, $script['in_footer'] ?? true );
			if ( isset( $script['localize']['object_name'], $script['localize']['data'] ) ) {
				LoaderHelper::localize_script( $script['handle'], $script['localize']['object_name'], $script['localize']['data'] );
			}
		}

		if ( wp_script_is( 'licencepress-admin-ui', 'enqueued' ) ) {
			LoaderHelper::localize_script(
				'licencepress-admin-ui',
				'licencepressOnboarding',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'licencepress_dismiss_onboarding' ),
				)
			);
		}

		$route = $this->current_admin_route();
		$settings_page = 'licencepress' === $route['page'] && 'settings' === $route['group'];
		$plugin_tab = in_array( $route['tab'], array( 'plugins', 'third-party' ), true );

		if ( $settings_page && $plugin_tab ) {
			$settings_config = array(
				'ajaxUrl'             => admin_url( 'admin-ajax.php' ),
				'nonce'               => wp_create_nonce( 'licencepress_settings_tabs' ),
				'pluginNonce'         => wp_create_nonce( 'licencepress_plugin_toggle' ),
				'pluginSettingsNonce' => wp_create_nonce( 'licencepress_save_plugin_settings' ),
			);
			foreach ( array( 'licencepress-admin-settings', 'licencepress-admin-plugins' ) as $handle ) {
				if ( wp_script_is( $handle, 'enqueued' ) ) {
					LoaderHelper::localize_script( $handle, 'licencepressSettingsTabs', $settings_config );
				}
			}
		}
		if ( 'licencepress' === $route['page'] && in_array( $route['group'], array( 'manage-mod', 'mods' ), true ) && wp_script_is( 'licencepress-admin', 'enqueued' ) ) {
			LoaderHelper::localize_script(
				'licencepress-admin',
				'licencepressManager',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'licencepress_manage' ),
				)
			);
		}
	}

	/**
	 * Get the current admin route values.
	 *
	 * @return array{page:string,group:string,tab:string}
	 */
	private function current_admin_route(): array {
		return array(
			'page'  => RequestHelper::get_key( 'page', '' ),
			'group' => RequestHelper::get_key( 'group', '' ),
			'tab'   => RequestHelper::get_key( 'tab', '' ),
		);
	}
	/**
	 * Retrieves the URL of an image asset.
	 *
	 * @param string $file The image file name.
	 * @return string The URL of the image asset.
	 * @since 1.0.0
	 */
	public static function get_image( string $file ): string {

		return ImageHelper::get_image_url( 'core', $file );
	}
}