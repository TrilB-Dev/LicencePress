<?php
/**
 * PayPal Editor Plugin Assets
 *
 * @package LicencePress
 * @subpackage Plugins\PayPal\Assets
 * @since 1.0.0
 */

namespace LicencePress\Includes\Plugins\PayPal\Assets;

use LicencePress\Includes\Plugins\PayPal\Includes\Settings\Settings;
use LicencePress\Includes\Functions\Helpers\ImageHelper;
use LicencePress\Includes\Functions\Helpers\LoaderHelper;

final class Assets {
	/**
	 * The loader helper instance.
	 *
	 * @var LoaderHelper The loader helper instance.
	 */
	private LoaderHelper $loader;
	/**
	 * Constructor for the PayPal plugin assets.
	 *
	 * @param LoaderHelper|null $loader The loader helper instance.
	 */
	public function __construct( ?LoaderHelper $loader = null ) {
		$this->loader = $loader ?? new LoaderHelper();
	}

	/**
	 * Registers the PayPal plugin assets with the core assets manager.
	 *
	 * @return void
	 */
	public function register(): void {
		$this->loader->register_component(
			$this,
			array(
				array(
					'type'          => 'filter',
					'hook'          => 'licencepress_admin_assets',
					'callback'      => 'register_admin_assets',
					'accepted_args' => 2,
				),
			)
		)->run();
	}
	/**
	 * Registers the admin assets for the PayPal plugin.
	 *
	 * @param array  $assets The current assets.
	 * @param string $context The context (e.g., 'frontend', 'admin').
	 * @return array The updated assets with PayPal assets included.
	 */
	public function register_admin_assets( $assets = array(), string $context = 'admin' ): array {
		if ( ! is_array( $assets ) ) {
			$assets = array();
		}

		if ( 'admin' !== strtolower( $context ) && 'frontend' !== strtolower( $context ) ) {
			return $assets;
		}

		$assets['enqueue_media'] = true;

		return $assets;
	}
	/**
	 * Get an image asset URL from the core Images directory.
	 *
	 * @param string $file The image path relative to Assets/images.
	 * @return string The image URL, or an empty string when the path is invalid.
	 */
	public static function get_image( string $file ): string {
		return ImageHelper::get_image_url( 'licencepress-paypal', $file );
	}
}
