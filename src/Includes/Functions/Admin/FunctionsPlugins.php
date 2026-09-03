<?php
/**
 * Plugin-related admin functions for LicencePress.
 *
 * @package LicencePress
 * @subpackage Includes\Functions\Admin
 * @since 1.0.0
 */
namespace LicencePress\Includes\Functions\Admin;

use LicencePress\Includes\Functions\Helpers\AjaxHelper;
use LicencePress\Includes\Functions\Helpers\AlertHelper;
use LicencePress\Includes\Plugins\PluginInterface;
use LicencePress\Includes\Plugins\Plugins;
use LicencePress\Includes\Plugins\SettingsPageProviderInterface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class FunctionsPlugins {
    /**
     * Toggle the enabled state of a LicencePress plugin.
     *
     * @return void
     */
    public function toggle_plugin(): void {
        if ( ! AjaxHelper::authorized( 'licencepress_plugin_toggle', 'licencepress_settings_plugins_int_edit' ) ) {
            AjaxHelper::unauthorized( __( 'You are not authorized to manage LicencePress plugins.', 'licencepress' ) );
        }

        $slug = sanitize_key( wp_unslash( $_POST['slug'] ?? '' ) );
        $enabled = ! empty( $_POST['enabled'] );
        $plugin = Plugins::get_instance()->get_registered_plugins()[ $slug ] ?? null;

        if ( ! $plugin instanceof PluginInterface ) {
            AjaxHelper::error( [ 'message' => __( 'The requested LicencePress plugin was not found.', 'licencepress' ) ], 404 );
        }
		if ( ! $this->is_internal_plugin( $plugin ) ) {
			AjaxHelper::unauthorized( __( 'You are not authorized to manage external LicencePress plugins.', 'licencepress' ) );
		}

        if ( ! Plugins::get_instance()->set_plugin_enabled( $slug, $enabled ) ) {
            AjaxHelper::error( [ 'message' => __( 'The LicencePress plugin state could not be saved.', 'licencepress' ) ], 500 );
        }

        AjaxHelper::success( [ 'slug' => $slug, 'enabled' => $enabled ] );
    }

    /**
     * Save settings submitted from a LicencePress plugin modal.
     *
     * @return void
     */
    public function save_plugin_settings(): void {
        $slug = sanitize_key( wp_unslash( $_POST['slug'] ?? '' ) );
        $plugin = Plugins::get_instance()->get_registered_plugins()[ $slug ] ?? null;
        if ( ! $plugin instanceof PluginInterface || ! $plugin instanceof SettingsPageProviderInterface ) {
            $message = __( 'The requested LicencePress plugin settings were not found.', 'licencepress' );
            AjaxHelper::error( [ 'message' => $message, 'alert' => AlertHelper::get_admin_notice( $message, 'error' ) ], 404 );
        }
        $capability = $this->is_internal_plugin( $plugin ) ? 'licencepress_settings_plugins_int_edit' : 'licencepress_settings_plugins_ext_edit';
        if ( ! AjaxHelper::authorized( 'licencepress_plugin_settings', $capability ) ) {
            $message = __( 'You are not authorized to save LicencePress plugin settings.', 'licencepress' );
            AjaxHelper::error( [ 'message' => $message, 'alert' => AlertHelper::get_admin_notice( $message, 'error' ) ], 403 );
        }

        $input = isset( $_POST['settings'] ) && is_array( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : [];
        $settings = $plugin->sanitize_settings( $input );

        AjaxHelper::success(
            [
                'slug' => $slug,
                'settings' => $settings,
                'message' => __( 'Plugin settings saved successfully.', 'licencepress' ),
                'alert' => AlertHelper::get_admin_notice( __( 'Plugin settings saved successfully.', 'licencepress' ), 'success' ),
            ]
        );
    }

    private function is_internal_plugin( PluginInterface $plugin ): bool {
        return 0 === strpos( get_class( $plugin ), 'LicencePress\\Includes\\Plugins\\' );
    }

    /**
     * Collect settings pages from enabled LicencePress plugins.
     *
     * @return array<int, array{provider: SettingsPageProviderInterface, slug: string, label: string, title: string, fields: array}>
     */
    public function plugin_settings_pages(): array {
        $pages = [];
        foreach ( Plugins::get_instance()->get_registered_plugins() as $plugin ) {
            if ( ! $plugin instanceof PluginInterface || ! $plugin instanceof SettingsPageProviderInterface || ! Plugins::get_instance()->is_plugin_enabled( $plugin->get_slug() ) ) {
                continue;
            }

            $page = $plugin->get_settings_page();
            if ( empty( $page['slug'] ) || empty( $page['label'] ) || empty( $page['fields'] ) ) {
                continue;
            }

            $page['provider'] = $plugin;
            $pages[] = $page;
        }
        return $pages;
    }
}
