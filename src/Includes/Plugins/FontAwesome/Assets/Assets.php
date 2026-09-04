<?php
/**
 * LicencePress - FontAwesome Assets
 *
 * @package LicencePress
 * @since 1.0.0
 */
namespace LicencePress\Includes\Plugins\FontAwesome\Assets;

use LicencePress\Includes\Functions\Helpers\ImageHelper;
use LicencePress\Includes\Functions\Helpers\LoaderHelper;
use LicencePress\Includes\Functions\Helpers\RequestHelper;
use LicencePress\Includes\Plugins\FontAwesome\Includes\Settings\Settings as FontAwesomeSettings;

final class Assets {
	/**
	 * The loader helper instance.
	 *
	 * @var LoaderHelper
	 */
	private LoaderHelper $loader;

	/**
	 * Constructor for the Assets class.
	 *
	 * @param LoaderHelper|null $loader The loader helper instance.
	 */
	public function __construct( ?LoaderHelper $loader = null ) {
		$this->loader = $loader ?? new LoaderHelper();
	}

	/**
	 * Registers the assets for the plugin.
	 *
	 * @return void
	 */
	public function register(): void {
		$this->loader->register_component(
			$this,
			array(
				array(
					'type'          => 'action',
					'hook'          => 'admin_enqueue_scripts',
					'callback'      => 'enqueue_wordpress_fontawesome_handles',
					'accepted_args' => 1,
				),
				array(
					'type'          => 'filter',
					'hook'          => 'licencepress_admin_assets',
					'callback'      => 'register_icon_picker_assets',
					'accepted_args' => 2,
				),
			)
		)->run();
	}

	/**
	 * Enqueue the active WordPress FontAwesome plugin handles.
	 *
	 * We intentionally only use the WordPress plugin handles so we do not load a
	 * duplicate LicencePress-managed FontAwesome bundle.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_wordpress_fontawesome_handles( string $hook_suffix = '' ): void {
		$page = RequestHelper::get_key( 'page' );
		if ( false === strpos( $hook_suffix, 'licencepress' ) && 0 !== strpos( $page, 'licencepress' ) ) {
			return;
		}

		$handle = $this->resolve_wordpress_fontawesome_handle();
		if ( '' === $handle ) {
			return;
		}

		if ( wp_style_is( $handle, 'registered' ) || wp_style_is( $handle, 'enqueued' ) ) {
			LoaderHelper::enqueue_style( $handle );
		}

		if ( wp_script_is( $handle, 'registered' ) || wp_script_is( $handle, 'enqueued' ) ) {
			LoaderHelper::enqueue_script( $handle );
		}
	}

	/**
	 * Add the icon picker assets to the central asset bundle.
	 *
	 * @param array  $assets The current asset bundle.
	 * @param string $context The asset context.
	 * @return array Updated asset bundle.
	 */
	public function register_icon_picker_assets( array $assets, string $context = 'admin' ): array {
		if ( 'admin' !== strtolower( $context ) && 'frontend' !== strtolower( $context ) ) {
			return $assets;
		}

		if ( ! $this->should_enqueue_icon_picker() ) {
			return $assets;
		}

		$assets['styles'][]  = array(
			'handle' => 'licencepress-fontawesome-icon-picker',
			'src'    => LICENCEPRESS_URL . 'src/Includes/Plugins/FontAwesome/Assets/dist/css/fontawesome.icon-picker.css',
		);
		$assets['scripts'][] = array(
			'handle'    => 'licencepress-fontawesome-icon-picker',
			'src'       => LICENCEPRESS_URL . 'src/Includes/Plugins/FontAwesome/Assets/dist/js/fontawesome.icon-picker.js',
			'deps'      => array( 'jquery' ),
			'in_footer' => true,
			'localize'  => array(
				'object_name' => 'licencepress_fa_picker',
				'data'        => array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'licencepress_fontawesome_picker' ),
					'strings'  => array(
						'search_placeholder' => __( 'Search icons...', 'licencepress' ),
						'no_icons_found'     => __( 'No icons found', 'licencepress' ),
						'loading'            => __( 'Loading...', 'licencepress' ),
						'select_icon'        => __( 'Select Icon', 'licencepress' ),
						'close'              => __( 'Close', 'licencepress' ),
					),
				),
			),
		);

		return $assets;
	}

	/**
	 * Resolve the active WordPress FontAwesome handle.
	 *
	 * @return string The handle to enqueue: font-awesome-kit or font-awesome-cdn.
	 */
	private function resolve_wordpress_fontawesome_handle(): string {
		$source = strtolower( (string) FontAwesomeSettings::source() );

		if ( 'kit' === $source ) {
			return 'font-awesome-kit';
		}

		return 'font-awesome-cdn';
	}

	/**
	 * Determine whether the icon picker should be loaded for the current screen.
	 *
	 * @return bool True when the picker is valid for the current screen.
	 */
	private function should_enqueue_icon_picker(): bool {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen ) {
			return false;
		}

		return false !== strpos( $screen->id, 'licencepress' )
			|| in_array( $screen->id, array( 'post', 'page', 'custom_css', 'customize' ), true );
	}

	/**
	 * Get an image asset URL from the core Images directory.
	 *
	 * @param string $file The image path relative to Assets/images.
	 * @return string The image URL, or an empty string when the path is invalid.
	 */
	public static function get_image( string $file ): string {
		return ImageHelper::get_image_url( 'licencepress-fontawesome', $file );
	}
}
