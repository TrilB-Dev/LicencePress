<?php
/**
 * TinyMCE Editor Plugin Assets
 *
 * @package LicencePress
 * @subpackage Plugins\TinyMCE\Assets
 * @since 1.0.0
 */

namespace LicencePress\Includes\Plugins\TinyMCE\Assets;

use LicencePress\Includes\Plugins\TinyMCE\Includes\Settings\Settings;
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
     * Constructor for the TinyMCE plugin assets.
     *
     * @param LoaderHelper|null $loader The loader helper instance.
     */
    public function __construct( ?LoaderHelper $loader = null ) {
        $this->loader = $loader ?? new LoaderHelper();
    }

    /**
     * Registers the TinyMCE plugin assets with the core assets manager.
     * 
     * @return void
     */
    public function register(): void {
        $this->loader->register_component( $this,
        [
            [
                'type' => 'filter',
                'hook' => 'licencepress_admin_assets',
                'callback' => 'register_admin_assets',
                'accepted_args' => 2,
            ],
        ] )->run();
    }
    /**
     * Registers the admin assets for the TinyMCE plugin.
     *
     * @param array  $assets The current assets.
     * @param string $context The context (e.g., 'frontend', 'admin').
     * @return array The updated assets with TinyMCE assets included.
     */
    public function register_admin_assets( $assets = [], string $context = 'admin' ): array {
        if ( ! is_array( $assets ) ) {
            $assets = [];
        }

        $base_url = LICENCEPRESS_URL . 'src/Includes/Plugins/TinyMCE/Assets/tinymce/';

        $assets['styles'][] = [
            'handle' => 'licencepress-tinymce-skin',
            'src' => $base_url . 'skins/ui/' . Settings::ui_skin() . '/skin.min.css',
        ];
        $assets['scripts'][] = [
            'handle' => 'licencepress-tinymce',
            'src' => $base_url . 'tinymce.min.js',
            'in_footer' => true,
        ];
        $assets['scripts'][] = [
            'handle' => 'licencepress-tinymce-boot',
            'src' => LICENCEPRESS_URL . 'src/Includes/Plugins/TinyMCE/Assets/js/tinymce.js',
            'deps' => [ 'licencepress-tinymce' ],
            'in_footer' => true,
            'localize' => [
                'object_name' => 'licencepressTinyMCE',
                'data' => [
                    'mediaTitle' => __( 'Insert media', 'licencepress' ),
                    'mediaButton' => __( 'Insert into editor', 'licencepress' ),
                    'mediaTooltip' => __( 'Insert media', 'licencepress' ),
                ],
            ],
        ];

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
        return ImageHelper::get_image_url( 'licencepress-tinymce', $file );
    }
}