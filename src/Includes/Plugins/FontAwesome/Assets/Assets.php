<?php
/**
 * Assets class for FontAwesome plugin.
 *
 * @package LicencePress\Includes\Plugins\FontAwesome\Assets
 */
namespace LicencePress\Includes\Plugins\FontAwesome\Assets;

use LicencePress\Assets\Assets as RootAssets;
use LicencePress\Includes\Functions\Helpers\LoaderHelper;
use LicencePress\Includes\Plugins\FontAwesome\Includes\Settings\Settings as FontAwesomeSettings;

final class Assets extends RootAssets {
    /**
     * Registers the asset filters for the Font Awesome plugin.
     *
     * @since 1.0.0
     */
    public function register(): void {
        ( new LoaderHelper() )->register_component(
            $this,
            array(
                array(
                    'type' => 'filter',
                    'hook' => 'licencepress_admin_assets',
                    'callback' => 'register_admin_assets',
                    'accepted_args' => 2,
                ),
                array(
                    'type' => 'filter',
                    'hook' => 'licencepress_frontend_assets',
                    'callback' => 'register_frontend_assets',
                    'accepted_args' => 2,
                ),
            )
        )->run();
    }

    /**
     * Register Font Awesome asset definitions for the admin context.
     *
     * @param array  $assets The existing asset bundle.
     * @param string $context The current asset context.
     * @return array The updated asset bundle.
     */
    public function register_admin_assets( array $assets, string $context = 'admin' ): array {
        if ( 'admin' !== $context ) {
            return $assets;
        }

        $type = FontAwesomeSettings::get_type();
        $kit_id = trim( FontAwesomeSettings::get_kit_id() );
        $cdn_technology = FontAwesomeSettings::get_cdn_technology();

        if ( 'kit' === $type && '' !== $kit_id ) {
            $assets['scripts'][] = array(
                'handle' => 'licencepress-fontawesome-kit',
                'src' => 'https://kit.fontawesome.com/' . rawurlencode( $kit_id ) . '.js',
                'deps' => array( 'licencepress-bootstrap' ),
                'in_footer' => false
            );
        } elseif ( 'cdn' === $type && 'webfont' === $cdn_technology ) {
            /**$assets['styles'][] = array(
                'handle' => 'licencepress-fontawesome-cdn-style',
                'src' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/fontawesome.min.css'
            );*/
            $assets['styles'][] = array(
                'handle' => 'licencepress-fontawesome-cdn-style',
                'src' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/fontawesome.min.css',
            );
            $assets['styles'][] = array(
                'handle' => 'licencepress-fontawesome-cdn-style-solid',
                'src' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/solid.min.css',
                'deps' => array( 'licencepress-fontawesome-cdn-style' ),
            );
            $assets['styles'][] = array(
                'handle' => 'licencepress-fontawesome-cdn-style-brands',
                'src' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/brands.min.css',
                'deps' => array( 'licencepress-fontawesome-cdn-style' ),
            );
        } elseif ( 'cdn' === $type && 'svg' === $cdn_technology ) {
            $assets['scripts'][] = array(
                'handle' => 'licencepress-fontawesome-cdn-svg',
                'src' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/js/fontawesome.min.js',
                'in_footer' => true
            );
            $assets['scripts'][] = array(
                'handle' => 'licencepress-fontawesome-cdn-svg-inline',
                'src' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/js/solid.min.js',
                'deps' => array( 'licencepress-fontawesome-cdn-svg' ),
                'in_footer' => true
            );
            $assets['scripts'][] = array(
                'handle' => 'licencepress-fontawesome-cdn-svg-brands',
                'src' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/js/brands.min.js',
                'deps' => array( 'licencepress-fontawesome-cdn-svg' ),
                'in_footer' => true
            );
        }

        $current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
        $current_group = isset( $_GET['group'] ) ? sanitize_text_field( wp_unslash( $_GET['group'] ) ) : '';
        $current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';

        if ( 'licencepress' === $current_page && 'settings' === $current_group && 'plugins' === $current_tab ) {
            $assets['scripts'][] = array(
                'handle' => 'licencepress-fontawesome-plugin-settings',
                'src' => LICENCEPRESS_PLUGINS_URL . '/FontAwesome/Assets/dist/js/admin.plugins.fontawesome.js',
                'deps' => array( 'licencepress-bootstrap' ),
                'version' => LICENCEPRESS_VERSION,
                'in_footer' => true,
            );
        }

        if ( FontAwesomeSettings::enable_icon_picker() ) {
            $assets['styles'][] = array(
                'handle' => 'licencepress-fontawesome-icon-picker',
                'src' => LICENCEPRESS_PLUGINS_URL . '/FontAwesome/Assets/dist/css/icon-picker.css',
                'deps' => array( 'licencepress-bootstrap' ),
                'version' => LICENCEPRESS_VERSION,
            );
            $assets['scripts'][] = array(
                'handle' => 'licencepress-fontawesome-icon-picker',
                'src' => LICENCEPRESS_PLUGINS_URL . '/FontAwesome/Assets/dist/js/icon-picker.js',
                'deps' => array( 'jquery' ),
                'version' => LICENCEPRESS_VERSION,
                'in_footer' => true,
                'localize' => array(
                    'object_name' => 'licencepress_fa_picker',
                    'data' => array(
                        'ajax_url' => admin_url( 'admin-ajax.php' ),
                        'nonce' => wp_create_nonce( 'licencepress_fontawesome_picker' ),
                        'strings' => array(
                            'search_placeholder' => __( 'Search icons...', 'licencepress' ),
                            'no_icons_found' => __( 'No icons found', 'licencepress' ),
                            'loading' => __( 'Loading...', 'licencepress' ),
                            'select_icon' => __( 'Select Icon', 'licencepress' ),
                            'close' => __( 'Close', 'licencepress' ),
                        ),
                    ),
                ),
            );
        }

        return $assets;
    }

    /**
     * Register Font Awesome asset definitions for the frontend context.
     *
     * @param array  $assets The existing asset bundle.
     * @param string $context The current asset context.
     * @return array The updated asset bundle.
     */
    public function register_frontend_assets( array $assets, string $context = 'frontend' ): array {
        if ( 'frontend' !== $context ) {
            return $assets;
        }

        $type = FontAwesomeSettings::get_type();
        $kit_id = trim( FontAwesomeSettings::get_kit_id() );

        if ( 'kit' === $type && '' !== $kit_id ) {
            $assets['scripts'][] = array(
                'handle' => 'licencepress-fontawesome-kit',
                'src' => 'https://kit.fontawesome.com/' . rawurlencode( $kit_id ) . '.js',
                'deps' => array(),
                'in_footer' => true,
            );
            return $assets;
        }

        $assets['styles'][] = array(
            'handle' => 'licencepress-fontawesome-cdn-style',
            'src' => 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7/css/fontawesome.min.css',
            'deps' => array(),
            'version' => '7.0.0',
        );
        $assets['scripts'][] = array(
            'handle' => 'licencepress-fontawesome-cdn-script',
            'src' => 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7/js/fontawesome.min.js',
            'deps' => array(),
            'version' => '7.0.0',
            'in_footer' => true,
        );

        return $assets;
    }
}


