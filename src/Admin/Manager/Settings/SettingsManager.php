<?php
/**
 * SettingsManager class for LicencePress plugin.
 *
 * @package LicencePress
 * @subpackage Admin\Manager\Settings
 * @since 1.0.0
 */
namespace LicencePress\Admin\Manager\Settings;

use LicencePress\Admin\Manager\Manager;
use LicencePress\Assets\Assets;
use LicencePress\Admin\Manager\Settings\SettingsPlugins;
use LicencePress\Includes\Functions\Helpers\SanitizationHelper;
use LicencePress\Includes\Functions\Helpers\FormFieldHelper;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class SettingsManager extends Manager {
    private SettingsPlugins $plugins_page;
    protected $page;

    public function __construct() {
        $this->page = 'settings';
        $this->plugins_page = new SettingsPlugins();
    }

    public function render(): void {
        $tab = SanitizationHelper::key( wp_unslash( $_GET['tab'] ?? 'general' ), 'general' );
        $tab = $this->normalize_tab( $tab );

        $this->header( __( 'Settings', 'licencepress' ) );
        echo '<div id="licencepress-settings-panel" data-current-tab="' . esc_attr( $tab ) . '">';
        $this->render_tab_content( $tab );
        echo '</div>';
        $this->footer();
    }

    public function render_tab_content( string $tab ): void {
        $tab = $this->normalize_tab( $tab );
        $view_capabilities = [
            'general' => [ 'licencepress_settings_general_view' ],
            'access' => [ 'licencepress_settings_access_view' ],
            'plugins' => [ 'licencepress_settings_plugins_view' ],
            'third-party' => [ 'licencepress_settings_plugins_view', 'licencepress_settings_plugins_ext_view' ],
        ];

        if ( $this->plugins_page->has_settings_page( $tab ) && ! $this->plugins_page->can_view_settings_page( $tab ) ) {
            wp_die( esc_html__( 'You are not authorized to view these LicencePress settings.', 'licencepress' ) );
        }

        $can_view = true;
        foreach ( $view_capabilities[ $tab ] ?? [] as $capability ) {
            if ( ! current_user_can( $capability ) ) {
                $can_view = false;
                break;
            }
        }

        if ( ! $can_view ) {
            wp_die( esc_html__( 'You are not authorized to view these LicencePress settings.', 'licencepress' ) );
        }

        echo '<div class="licencepress-settings-tab-content" role="tabpanel">';

        if ( 'general' === $tab ) {
            echo '<div class="mb-3"><h2 class="h5 mb-1">' . esc_html__( 'Licence configuration', 'licencepress' ) . '</h2><p class="text-secondary mb-0">' . esc_html__( 'Set the default commercial rules for generated licences, expiry, and validation.', 'licencepress' ) . '</p></div>';
            echo '<table class="form-table" role="presentation"><tbody>';
            ( new \LicencePress\Admin\Manager\Settings\SettingsGeneral() )->render( [] );
            echo '</tbody></table>';
        } elseif ( 'access' === $tab ) {
            echo '<div class="mb-3"><h2 class="h5 mb-1">' . esc_html__( 'Access control', 'licencepress' ) . '</h2><p class="text-secondary mb-0">' . esc_html__( 'Define who can issue, revoke, export, review, and manage licences.', 'licencepress' ) . '</p></div>';
            echo '<table class="form-table" role="presentation"><tbody>';
            ( new \LicencePress\Admin\Manager\Settings\SettingsAccess() )->render( [] );
            echo '</tbody></table>';
        } else {
            $this->plugins_page->render( $tab );
        }

        echo '</div>';
    }

    private function normalize_tab( string $tab ): string {
        $allowed = [ 'general', 'access', 'plugins', 'third-party' ];
        if ( in_array( $tab, $allowed, true ) || $this->plugins_page->has_settings_page( $tab ) ) {
            return $tab;
        }
        return 'general';
    }

    public function register_assets( Assets $assets ): void {
        $settings_assets = $this->assets( 'settings' );
        $settings_assets['scripts'][] = [
            'handle' => 'licencepress-admin-plugins',
            'src' => LICENCEPRESS_URL . 'src/Assets/dist/js/plugins.admin.js',
            'deps' => [ 'licencepress-bootstrap' ],
            'in_footer' => true,
        ];
        $assets->register_page( 'licencepress-settings', $settings_assets );
    }
}
