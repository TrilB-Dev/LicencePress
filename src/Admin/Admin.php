<?php
/**
 * Admin class for LicencePress plugin.
 *
 * @package LicencePress
 * @subpackage Admin
 * @since 1.0.0
 * 
 */
namespace LicencePress\Admin;

use LicencePress\Includes\Settings\Settings;
use LicencePress\Includes\Functions\Admin\FunctionsPlugins;
use LicencePress\Includes\Functions\Admin\FunctionsPlugName;
use LicencePress\Includes\Functions\Helpers\AjaxHelper;
use LicencePress\Includes\Core\Capabilities;
use LicencePress\Includes\Functions\Helpers\LoaderHelper;
use LicencePress\Includes\Functions\Admin\FunctionsSidebar;
use LicencePress\Assets\Assets;
use LicencePress\Admin\Manager\Tools\ToolsManager;
use LicencePress\Admin\Manager\Dashboard\DashboardManager;
use LicencePress\Admin\Manager\Licences\LicencesManager;
use LicencePress\Admin\Manager\Settings\SettingsManager;
use LicencePress\Admin\Manager\PlugName\PlugNameManager;
use LicencePress\Includes\Licence\LicenceTypeManager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Admin {
    /**
     * The DashboardManager instance for managing the dashboard page. 
     * 
     * @var DashboardManager
     * */
    private DashboardManager $dashboard_manager;
    /**
     * PlugNameManager instance for managing content-related admin pages.
     *
     * @var PlugNameManager
     */
    private PlugNameManager $plugname_manager;
    /**
     * SettingsManager instance for managing settings-related admin pages.
     *
     * @var SettingsManager
     */
    private SettingsManager $settings_manager;
    /**
     * LicencesManager instance for managing licence-type and issued licence pages.
     *
     * @var LicencesManager
     */
    private LicencesManager $licences_manager;
    /**
    * ToolsManager instance for managing tools-related admin pages.
     *
    * @var ToolsManager
     */
    private ToolsManager $tools_manager;
    /**
     * LoaderHelper instance for managing action and filter hooks.
     *
     * @var LoaderHelper
     */
    private LoaderHelper $loader;
    /**
     * FunctionsPlugins instance for managing plugin-related admin functions.
     *
     * @var FunctionsPlugins
     */
    private FunctionsPlugins $plugin_functions;
    /** 
     * PlugName functions instance for managing plugname-related admin functions.
     * 
     * @var FunctionsPlugName
     *  */
    private FunctionsPlugName $plugname_functions;

    public function __construct( Assets $assets ) {
        $this->dashboard_manager = new DashboardManager();
        $this->plugname_functions = new FunctionsPlugName();
        $this->plugname_manager = new PlugNameManager( $this->plugname_functions );
        $this->settings_manager = new SettingsManager();
        $this->licences_manager = new LicencesManager();
        $this->tools_manager = new ToolsManager();
        $this->plugin_functions = new FunctionsPlugins();
        $this->loader = new LoaderHelper();
        $this->dashboard_manager->register_assets( $assets );
        $this->plugname_manager->register_assets( $assets );
        $this->settings_manager->register_assets( $assets );
        $this->licences_manager->register_assets( $assets );
        $this->tools_manager->register_assets( $assets );
        $this->loader->register_component( $this, [
            [ 'type' => 'action', 'hook' => 'wp_ajax_licencepress_load_settings_tab', 'callback' => 'load_settings_tab' ],
            [ 'type' => 'action', 'hook' => 'wp_ajax_licencepress_preview_licence_type', 'callback' => 'preview_licence_type' ],
            [ 'type' => 'action', 'hook' => 'wp_ajax_licencepress_load_licence_type', 'callback' => 'load_licence_type' ],
            [ 'type' => 'action', 'hook' => 'wp_ajax_licencepress_save_licence_type', 'callback' => 'save_licence_type' ],
            [ 'type' => 'action', 'hook' => 'wp_ajax_licencepress_toggle_licence_type_retired', 'callback' => 'toggle_licence_type_retired' ],
            [ 'type' => 'action', 'hook' => 'wp_ajax_licencepress_delete_licence_type', 'callback' => 'delete_licence_type' ],
            [ 'type' => 'action', 'hook' => 'wp_ajax_licencepress_dismiss_onboarding', 'callback' => 'dismiss_onboarding' ],
        ] );
        $this->loader->register_component( $this->plugname_functions, [
            [ 'type' => 'action', 'hook' => 'wp_ajax_licencepress_save_plugname_settings', 'callback' => 'save_plugname_settings' ],
            [ 'type' => 'action', 'hook' => 'wp_ajax_licencepress_delete_plugname', 'callback' => 'delete_plugname' ],
            [ 'type' => 'action', 'hook' => 'wp_ajax_licencepress_delete_plugname_page', 'callback' => 'delete_plugname_page' ],
            [ 'type' => 'action', 'hook' => 'wp_ajax_licencepress_save_plugname_term', 'callback' => 'save_plugname_term' ],
            [ 'type' => 'action', 'hook' => 'wp_ajax_licencepress_delete_plugname_term', 'callback' => 'delete_plugname_term' ],
        ] );
        $this->loader->register_component( $this->plugin_functions, [
            [ 'type' => 'action', 'hook' => 'wp_ajax_licencepress_toggle_plugin', 'callback' => 'toggle_plugin' ],
            [ 'type' => 'action', 'hook' => 'wp_ajax_licencepress_save_plugin_settings', 'callback' => 'save_plugin_settings' ],
        ] )->run();
    }
    /**
     * Register admin menu pages and subpages.
     * @since 1.0.0
     */
    public function register_admin_menu(): void {
        FunctionsSidebar::register_admin_menu( $this );
    }

    /**
     * Render the dashboard page.
     *
     * This method is responsible for rendering the dashboard page of the LicencePress plugin.
     * It delegates the rendering to the DashboardManager instance.
     */
    public function render_dashboard(): void {
        $this->dashboard_manager->render();
    }

    public function dismiss_onboarding(): void {
        if ( ! AjaxHelper::authorized( 'licencepress_dismiss_onboarding', 'manage_options' ) ) {
            AjaxHelper::unauthorized( __( 'You are not authorized to dismiss the LicencePress onboarding modal.', 'licencepress' ) );
        }

        Settings::register_group( 'setup', [ 'first_install_complete' => false ] );
        Settings::set( 'first_install_complete', true );
        Settings::set( 'onboarding_steps_complete', 3 );

        AjaxHelper::success( [ 'dismissed' => true ] );
    }
    /**
     * Render the manage plugnames page.
     *
     * This method is responsible for rendering the manage plugnames page of the LicencePress plugin.
     * It delegates the rendering to the PlugNameManager instance.
     */
    public function render_plugnames(): void {
        $this->plugname_manager->render();
    }

    public function render_licences(): void {
        $this->licences_manager->render();
    }

    public function render_licence_type_add(): void {
        $this->licences_manager->render_add_type();
    }

    public function render_licence_types(): void {
        $this->licences_manager->render_manage_types();
    }

    public function render_licence_management(): void {
        $this->licences_manager->render_manage_licences();
    }
    /**
     * Render the settings page.
     *
     * This method is responsible for rendering the settings page of the LicencePress plugin.
     * It delegates the rendering to the SettingsManager instance.
     */
    public function render_settings(): void {
        $this->settings_manager->render();
    }
    /**
     * Render the tools page.
     *
     * @return void
     */
    public function render_tools(): void {
        $this->tools_manager->render();
    }
    /**
     * Render the analytics page.
     *
     * This method is responsible for rendering the analytics page of the LicencePress plugin.
     * It delegates the rendering to the AnalyticsManager instance.
     */
    public function load_settings_tab(): void {
        $tab = sanitize_key( $_POST['tab'] ?? 'general' );
        $view_capability = [
            'general' => 'licencepress_settings_general_view',
            'layout' => 'licencepress_settings_layout_view',
            'access' => 'licencepress_settings_access_view',
            'plugins' => 'licencepress_settings_plugins_view',
            'third-party' => 'licencepress_settings_plugins_ext_view',
        ][ $tab ] ?? 'licencepress_settings_general_view';
        if ( ! AjaxHelper::authorized( 'licencepress_settings_tabs', $view_capability ) ) {
            AjaxHelper::unauthorized( __( 'You are not authorized to load LicencePress settings.', 'licencepress' ) );
        }

        $layout_section = sanitize_key( $_POST['layout_section'] ?? 'general' );
        ob_start();
        $this->settings_manager->render_tab_content( $tab, $layout_section );
        $html = (string) ob_get_clean();
        AjaxHelper::success( [ 'html' => $html, 'tab' => $tab, 'layout_section' => $layout_section ] );
    }

    public function preview_licence_type(): void {
        if ( ! AjaxHelper::authorized( 'licencepress_licence_type_form', 'licencepress_licence_issue' ) ) {
            AjaxHelper::unauthorized( __( 'You are not authorized to preview a LicencePress licence type.', 'licencepress' ) );
        }

        $payload = isset( $_POST ) ? wp_unslash( $_POST ) : [];
        $settings = [
            'name' => sanitize_text_field( (string) ( $payload['name'] ?? '' ) ),
            'prefix' => sanitize_text_field( (string) ( $payload['prefix'] ?? '' ) ),
            'suffix' => sanitize_text_field( (string) ( $payload['suffix'] ?? '' ) ),
            'length' => max( 8, (int) ( $payload['length'] ?? 12 ) ),
            'pattern' => sanitize_text_field( (string) ( $payload['pattern'] ?? 'prefix-segment' ) ),
        ];

        AjaxHelper::success( LicenceTypeManager::generate_preview( $settings ) );
    }

    public function load_licence_type(): void {
        if ( ! AjaxHelper::authorized( 'licencepress_licence_type_form', 'licencepress_licence_issue' ) ) {
            AjaxHelper::unauthorized( __( 'You are not authorized to load a LicencePress licence type.', 'licencepress' ) );
        }

        $id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
        $type = $id > 0 ? LicenceTypeManager::get_type( $id ) : null;

        if ( null === $type ) {
            AjaxHelper::error( [ 'message' => __( 'Licence type not found.', 'licencepress' ) ], 404 );
        }

        AjaxHelper::success( [ 'type' => $type ] );
    }

    public function save_licence_type(): void {
        if ( ! AjaxHelper::authorized( 'licencepress_licence_type_form', 'licencepress_licence_issue' ) ) {
            AjaxHelper::unauthorized( __( 'You are not authorized to save a LicencePress licence type.', 'licencepress' ) );
        }

        $payload = isset( $_POST ) ? wp_unslash( $_POST ) : [];
        $id = isset( $payload['id'] ) ? absint( $payload['id'] ) : 0;
        $settings = [
            'name' => sanitize_text_field( (string) ( $payload['name'] ?? '' ) ),
            'slug' => sanitize_key( (string) ( $payload['slug'] ?? '' ) ),
            'parent_id' => ! empty( $payload['parent_id'] ) ? (int) $payload['parent_id'] : 0,
            'is_variant' => ! empty( $payload['is_variant'] ) ? 1 : 0,
            'is_retired' => ! empty( $payload['is_retired'] ) ? 1 : 0,
            'prefix' => sanitize_text_field( (string) ( $payload['prefix'] ?? '' ) ),
            'suffix' => sanitize_text_field( (string) ( $payload['suffix'] ?? '' ) ),
            'length' => max( 8, (int) ( $payload['length'] ?? 12 ) ),
            'pattern' => sanitize_text_field( (string) ( $payload['pattern'] ?? 'prefix-segment' ) ),
            'description' => sanitize_textarea_field( (string) ( $payload['description'] ?? '' ) ),
        ];

        if ( '' === $settings['name'] ) {
            AjaxHelper::error( [ 'message' => __( 'A licence type name is required.', 'licencepress' ) ], 400 );
        }

        $saved_id = 0;
        $message = __( 'Licence type created successfully.', 'licencepress' );
        if ( $id > 0 ) {
            $saved_id = $id;
            $updated = LicenceTypeManager::update_type( $id, $settings );
            if ( ! $updated ) {
                AjaxHelper::error( [ 'message' => __( 'Licence type could not be updated.', 'licencepress' ) ], 400 );
            }
            $message = __( 'Licence type updated successfully.', 'licencepress' );
        } else {
            $saved_id = LicenceTypeManager::create_type( $settings );
        }

        AjaxHelper::success( [
            'message' => $message,
            'id' => $saved_id,
            'preview' => LicenceTypeManager::generate_preview( $settings ),
        ] );
    }

    public function toggle_licence_type_retired(): void {
        if ( ! AjaxHelper::authorized( 'licencepress_licence_type_form', 'licencepress_licence_issue' ) ) {
            AjaxHelper::unauthorized( __( 'You are not authorized to retire a LicencePress licence type.', 'licencepress' ) );
        }

        $id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
        if ( $id <= 0 ) {
            AjaxHelper::error( [ 'message' => __( 'A valid licence type ID is required.', 'licencepress' ) ], 400 );
        }

        $retired = ! empty( $_POST['retired'] );
        if ( ! LicenceTypeManager::retire_type( $id, $retired ) ) {
            AjaxHelper::error( [ 'message' => __( 'Licence type status could not be updated.', 'licencepress' ) ], 400 );
        }

        AjaxHelper::success( [
            'message' => $retired ? __( 'Licence type retired successfully.', 'licencepress' ) : __( 'Licence type reactivated successfully.', 'licencepress' ),
            'retired' => $retired,
        ] );
    }

    public function delete_licence_type(): void {
        if ( ! AjaxHelper::authorized( 'licencepress_licence_type_form', 'licencepress_licence_issue' ) ) {
            AjaxHelper::unauthorized( __( 'You are not authorized to delete a LicencePress licence type.', 'licencepress' ) );
        }

        $id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
        if ( $id <= 0 ) {
            AjaxHelper::error( [ 'message' => __( 'A valid licence type ID is required.', 'licencepress' ) ], 400 );
        }

        if ( ! LicenceTypeManager::delete_type( $id ) ) {
            AjaxHelper::error( [ 'message' => __( 'Licence type could not be deleted.', 'licencepress' ) ], 400 );
        }

        AjaxHelper::success( [ 'message' => __( 'Licence type deleted successfully.', 'licencepress' ) ] );
    }

    /**
     * Get the capability for a given key, with a fallback.
     *
     * @param string $key The settings key to retrieve the capability for.
     * @param string $fallback The fallback capability if the key is not set or invalid.
     * @return string The capability associated with the key, or the fallback if not valid.
     */
    public function capability( string $key, string $fallback ): string {
        $value = Settings::get( $key, $fallback );
        $values = is_array( $value ) ? $value : [ $value ];
        $allowed = array_merge( [ 'manage_options', 'edit_posts', 'publish_posts', 'manage_categories', 'delete_posts' ], array_keys( Capabilities::definitions() ) );
        foreach ( $values as $value ) {
            $capability = sanitize_key( (string) $value );
            if ( in_array( $capability, $allowed, true ) ) {
                return $capability;
            }
        }
        return $fallback;
    }

}
