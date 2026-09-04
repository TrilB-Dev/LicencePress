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

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Assets
 *
 * Manages the registration and enqueueing of assets for the LicencePress plugin.
 */
final class Assets {
    /**
     * Array to hold registered assets for different pages.
     *
     * @var array
     */
    private array $pages = [];
    /**
     * Registers the default assets for the plugin.
     *
     * @return void
     */
    public function register(): void {
        add_filter( 'licencepress_base_assets', [ $this, 'default_assets' ], 10, 2 );
    }
    /**
     * Registers assets for a specific page.
     *
     * @param string $page The page identifier.
     * @param array  $assets The assets to register for the page.
     * @return void
     */
    public function register_page( string $page, array $assets ): void {
        $page = sanitize_key( $page );
        $this->pages[ $page ] = [
            'styles' => array_merge( $this->pages[ $page ]['styles'] ?? [], $assets['styles'] ?? [] ),
            'scripts' => array_merge( $this->pages[ $page ]['scripts'] ?? [], $assets['scripts'] ?? [] ),
        ];
    }
    /**
     * Returns the default assets for the plugin.
     *
     * @param array  $assets The current assets.
     * @param string $context The context (e.g., 'frontend', 'admin').
     * @return array The default assets.
     */
    public function default_assets( array $assets, string $context ): array {
        $defaults = [
            'styles'  => [
                [
                    'handle' => 'licencepress-wp-override',
                    'src' => LICENCEPRESS_URL . 'src/Assets/dist/css/wpoverride.css',
                    'deps' => [ 'forms' ],
                ],
                [
                    'handle' => 'licencepress-bootstrap',
                    'src' => LICENCEPRESS_URL . 'src/Assets/dist/css/bootstrap.css',
                    'version' => '5.3.8',
                    'deps' => [ 'licencepress-wp-override' ],
                ],
                [
                    'handle' => 'licencepress-bootstrap-select',
                    'src' => LICENCEPRESS_URL . 'src/Assets/dist/css/bootstrap-select.css',
                    'version' => '1.2.2',
                    'deps' => [
                        'licencepress-bootstrap'
                        ]
                ],
            ],
            'scripts' => [
                [
                    'handle' => 'licencepress-bootstrap',
                    'src' => LICENCEPRESS_URL . 'src/Assets/dist/js/bootstrap.js',
                    'version' => '5.3.8',
                    'in_footer' => true
                ],
                [
                    'handle' => 'licencepress-bootstrap-select',
                    'src' => LICENCEPRESS_URL . 'src/Assets/dist/js/bootstrap-select.js',
                    'version' => '1.2.2',
                    'deps' => [ 'licencepress-bootstrap' ],
                    'in_footer' => true
                ],
            ],
        ];

        if ( 'admin' === $context ) {
            $defaults['styles'][] = [
                'handle' => 'licencepress-admin-ui',
                'src' => LICENCEPRESS_URL . 'src/Assets/dist/css/admin.ui.css',
            ];
            $defaults['scripts'][] = [
                'handle' => 'licencepress-admin-ui',
                'src' => LICENCEPRESS_URL . 'src/Assets/dist/js/admin.ui.js',
                'deps' => [ 'licencepress-bootstrap' ],
                'in_footer' => true,
            ];
        }

        return [ 'base' => $defaults ] + $defaults;
    }

    /**
     * Enqueues the frontend assets for the plugin.
     *
     * @return void
     */
    public function enqueue_frontend(): void {
        if ( ! is_singular( 'licencepress_page' ) ) {
            return;
        }

        $assets = apply_filters( 'licencepress_base_assets', [], 'frontend' );
        $this->enqueue_registered( 'frontend', [
            'styles'  => array_merge( $assets['base']['styles'] ?? [], [ [ 'handle' => 'licencepress-public', 'src' => LICENCEPRESS_URL . 'src/Assets/dist/css/public.css' ] ] ),
            'scripts' => array_merge( $assets['base']['scripts'] ?? [], [ [ 'handle' => 'licencepress-public', 'src' => LICENCEPRESS_URL . 'src/Assets/dist/js/public.js', 'in_footer' => true ] ] ),
        ] );
    }
    /**
     * Enqueues the admin assets for the plugin.
     *
     * @param string $hook_suffix The current admin page hook suffix.
     * @return void
     */
    public function enqueue_admin( string $hook_suffix ): void {
        if ( false === strpos( $hook_suffix, 'licencepress' ) ) {
            return;
        }

        $page = sanitize_key( $_GET['page'] ?? 'licencepress' );
        $registered = $this->pages[ $page ] ?? [];
        $base = apply_filters( 'licencepress_base_assets', [], 'admin' );
        $this->enqueue_registered( 'admin', [
            'styles'  => array_merge( $base['styles'] ?? [], $registered['styles'] ?? [] ),
            'scripts' => array_merge( $base['scripts'] ?? [], $registered['scripts'] ?? [] ),
        ] );

    }
    /**
     * Enqueues the registered assets for a given context.
     *
     * @param string $context The context (e.g., 'frontend', 'admin').
     * @param array  $assets The assets to enqueue.
     * @return void
     */
    private function enqueue_registered( string $context, array $assets ): void {
        $assets = apply_filters( 'licencepress_' . $context . '_assets', $assets, $context );
        $this->enqueue_bundle( $assets );
    }
    /**
     * Enqueues a bundle of assets (styles and scripts).
     *
     * @param array $assets The assets to enqueue.
     * @return void
     */
    private function enqueue_bundle( array $assets ): void {
        if ( isset( $assets['styles'] ) && is_string( $assets['styles'] ) ) {
            $assets['styles'] = [ [ 'handle' => 'licencepress-admin-' . $assets['styles'], 'src' => LICENCEPRESS_URL . 'src/Assets/dist/css/admin.' . $assets['styles'] . '.css' ] ];
        }
        if ( isset( $assets['scripts'] ) && is_string( $assets['scripts'] ) ) {
            $assets['scripts'] = [ [ 'handle' => 'licencepress-admin-' . $assets['scripts'], 'src' => LICENCEPRESS_URL . 'src/Assets/dist/js/admin.' . $assets['scripts'] . '.js', 'deps' => [ 'licencepress-bootstrap' ] ] ];
        }
        foreach ( $assets['styles'] ?? [] as $style ) {
            wp_enqueue_style( $style['handle'], $style['src'], $style['deps'] ?? [], $style['version'] ?? LICENCEPRESS_VERSION, $style['media'] ?? 'all' );
        }
        foreach ( $assets['scripts'] ?? [] as $script ) {
            wp_enqueue_script( $script['handle'], $script['src'], $script['deps'] ?? [], $script['version'] ?? LICENCEPRESS_VERSION, $script['in_footer'] ?? true );
            if ( isset( $script['localize']['object_name'], $script['localize']['data'] ) ) {
                wp_localize_script( $script['handle'], $script['localize']['object_name'], $script['localize']['data'] );
            }
        }

        if ( wp_script_is( 'licencepress-admin-ui', 'enqueued' ) ) {
            wp_localize_script( 'licencepress-admin-ui', 'licencepressOnboarding', [
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'licencepress_dismiss_onboarding' ),
            ] );
        }

        if ( 'licencepress-settings' === sanitize_key( $_GET['page'] ?? '' ) ) {
            $settings_config = [
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'licencepress_settings_tabs' ),
                'pluginNonce' => wp_create_nonce( 'licencepress_plugin_toggle' ),
                'pluginSettingsNonce' => wp_create_nonce( 'licencepress_plugin_settings' ),
            ];
            foreach ( [ 'licencepress-admin-settings', 'licencepress-admin-plugins' ] as $handle ) {
                if ( wp_script_is( $handle, 'enqueued' ) ) {
                    wp_localize_script( $handle, 'licencepressSettingsTabs', $settings_config );
                }
            }
        }
        if ( 'licencepress-manage' === sanitize_key( $_GET['page'] ?? '' ) && wp_script_is( 'licencepress-admin-wiki', 'enqueued' ) ) {
            wp_localize_script( 'licencepress-admin-wiki', 'licencepressWikiManager', [
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'licencepress_manage_wiki' ),
            ] );
        }
    }
    /**
     * Retrieves the URL of an image asset.
     *
     * @param string $file The image file name.
     * @return string The URL of the image asset.
     */
    public function get_image( string $file ): string {

        return ImageHelper::get_image_url( 'core', $file );
    }
}
