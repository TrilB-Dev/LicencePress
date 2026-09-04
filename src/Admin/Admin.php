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
use LicencePress\Includes\Functions\Helpers\AjaxHelper;
use LicencePress\Includes\Core\Capabilities;
use LicencePress\Includes\Functions\Helpers\LoaderHelper;
use LicencePress\Includes\Functions\Helpers\RequestHelper;
use LicencePress\Includes\Functions\Helpers\SanitizationHelper;
use LicencePress\Includes\Functions\Admin\FunctionsSidebar;
use LicencePress\Assets\Assets;
use LicencePress\Admin\Manager\Tools\ToolsManager;
use LicencePress\Admin\Manager\Dashboard\DashboardManager;
use LicencePress\Admin\Manager\Licences\LicencesManager;
use LicencePress\Admin\Manager\Settings\SettingsManager;
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
     * Assets instance for managing admin assets.
     *
     * @var Assets
     */
    private Assets $assets;
    /**
     * Constructor for the Admin class.
     *
     * Initializes the various admin managers and registers their assets.
     *
     * @param Assets $assets The Assets instance for managing admin assets.
     */
    public function __construct( Assets $assets ) {
        /**
         * Initialize the admin managers and register their assets.
         */
        $this->dashboard_manager = new DashboardManager();
        /**
         * Initialize the settings manager.
         */
        $this->settings_manager = new SettingsManager();
        /**
         * Initialize the licences manager.
         */
        $this->licences_manager = new LicencesManager();
        /**
         * Initialize the tools manager.
         */
        $this->tools_manager = new ToolsManager();
        /**
         * Initialize the plugin functions manager.
         */
        $this->plugin_functions = new FunctionsPlugins();
        /**
         * Initialize the loader helper.
         */
        $this->loader = new LoaderHelper();
        /**
         * Initialize the assets manager.
         */
        $this->assets = $assets;
        /**
         * Register assets for the admin managers.
         */
        $this->dashboard_manager->register_assets( $assets );
        /**
         * Register assets for the settings manager.
         */
        $this->settings_manager->register_assets( $assets );
        /**
         * Register assets for the licences manager.
         */
        $this->licences_manager->register_assets( $assets );
        /**
         * Register assets for the tools manager.
         */
        $this->tools_manager->register_assets( $assets );
        /**
         * Register assets for the plugin functions manager.
         */
        /**
         * Register assets for the plugin functions manager.
         */
        $this->loader->register_component( $this, [
            [ 
                'type' => 'action',
                'hook' => 'wp_ajax_licencepress_load_settings_tab',
                'callback' => 'load_settings_tab' 
            ],
            [ 
                'type' => 'action',
                'hook' => 'wp_ajax_licencepress_preview_licence_type',
                'callback' => 'preview_licence_type'
            ],
            [ 
                'type' => 'action',
                'hook' => 'wp_ajax_licencepress_load_licence_type',
                'callback' => 'load_licence_type'
            ],
            [ 
                'type' => 'action',
                'hook' => 'wp_ajax_licencepress_save_licence_type',
                'callback' => 'save_licence_type'
            ],
            [ 
                'type' => 'action',
                'hook' => 'wp_ajax_licencepress_toggle_licence_type_retired',
                'callback' => 'toggle_licence_type_retired'
            ],
            [ 
                'type' => 'action',
                'hook' => 'wp_ajax_licencepress_delete_licence_type',
                'callback' => 'delete_licence_type'
            ],
            [ 
                'type' => 'action',
                'hook' => 'wp_ajax_licencepress_dismiss_onboarding',
                'callback' => 'dismiss_onboarding'
            ],
        ] );
        $this->loader->register_component( $this->plugin_functions, [
            [ 
                'type' => 'action',
                'hook' => 'wp_ajax_licencepress_toggle_plugin',
                'callback' => 'toggle_plugin'
            ],
            [ 
                'type' => 'action',
                'hook' => 'wp_ajax_licencepress_save_plugin_settings',
                'callback' => 'save_plugin_settings'
            ],
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
    /**
     * Dismiss the onboarding modal.
     *
     * This method handles the AJAX request to dismiss the onboarding modal for the LicencePress plugin.
     */
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
     * Render LicencePress licences page.
     *
     * This method is responsible for rendering the licences page of the LicencePress plugin.
     * It delegates the rendering to the LicencesManager instance.
     */
    public function render_licences(): void {
        $this->licences_manager->render();
    }
    /**
     * Render the add licence type page.
     *
     * This method is responsible for rendering the add licence type page of the LicencePress plugin.
     * It delegates the rendering to the LicencesManager instance.
     */
    public function render_licence_type_add(): void {
        $this->licences_manager->render_add_type();
    }
    /**
     * Render the manage licence types page.
     *
     * This method is responsible for rendering the manage licence types page of the LicencePress plugin.
     * It delegates the rendering to the LicencesManager instance.
     */
    public function render_licence_types(): void {
        $this->licences_manager->render_manage_types();
    }
    /**
     * Render the manage licences page.
     *
     * This method is responsible for rendering the manage licences page of the LicencePress plugin.
     * It delegates the rendering to the LicencesManager instance.
     */
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
        $tab = RequestHelper::get_key( 'tab', 'general' );
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

        $layout_section = RequestHelper::get_key( 'layout_section', 'general' );
        ob_start();
        $this->settings_manager->render_tab_content( $tab, $layout_section );
        $html = (string) ob_get_clean();
        AjaxHelper::success( [ 'html' => $html, 'tab' => $tab, 'layout_section' => $layout_section ] );
    }
    /**
     * Preview a licence type.
     *
     * This method handles the AJAX request to preview a licence type based on the provided settings.
     */
    public function preview_licence_type(): void {
        if ( ! AjaxHelper::authorized( 'licencepress_licence_type_form', 'licencepress_licence_issue' ) ) {
            AjaxHelper::unauthorized( __( 'You are not authorized to preview a LicencePress licence type.', 'licencepress' ) );
        }

        $settings = [
            'name' => SanitizationHelper::text( RequestHelper::value( $_POST, 'name', '' ) ),
            'prefix' => SanitizationHelper::text( RequestHelper::value( $_POST, 'prefix', '' ) ),
            'suffix' => SanitizationHelper::text( RequestHelper::value( $_POST, 'suffix', '' ) ),
            'length' => max( 8, RequestHelper::integer( $_POST, 'length', 12 ) ),
            'pattern' => SanitizationHelper::text( RequestHelper::value( $_POST, 'pattern', 'prefix-segment' ) ),
        ];

        AjaxHelper::success( LicenceTypeManager::generate_preview( $settings ) );
    }
    /**
     * Load a licence type.
     *
     * This method handles the AJAX request to load a licence type based on the provided ID.
     */
    public function load_licence_type(): void {
        if ( ! AjaxHelper::authorized( 'licencepress_licence_type_form', 'licencepress_licence_issue' ) ) {
            AjaxHelper::unauthorized( __( 'You are not authorized to load a LicencePress licence type.', 'licencepress' ) );
        }

        $id = RequestHelper::integer( $_POST, 'id', 0 );
        $type = $id > 0 ? LicenceTypeManager::get_type( $id ) : null;

        if ( null === $type ) {
            AjaxHelper::error( [ 'message' => __( 'Licence type not found.', 'licencepress' ) ], 404 );
        }

        AjaxHelper::success( [ 'type' => $type ] );
    }
    /**
     * Save a licence type.
     *
     * This method handles the AJAX request to save a licence type based on the provided settings.
     */
    public function save_licence_type(): void {
        if ( ! AjaxHelper::authorized( 'licencepress_licence_type_form', 'licencepress_licence_issue' ) ) {
            AjaxHelper::unauthorized( __( 'You are not authorized to save a LicencePress licence type.', 'licencepress' ) );
        }

        $id = RequestHelper::integer( $_POST, 'id', 0 );
        $settings = [
            'name' => SanitizationHelper::text( RequestHelper::value( $_POST, 'name', '' ) ),
            'slug' => SanitizationHelper::key( RequestHelper::value( $_POST, 'slug', '' ) ),
            'parent_id' => RequestHelper::integer( $_POST, 'parent_id', 0 ),
            'is_variant' => RequestHelper::boolean( $_POST, 'is_variant', false ) ? 1 : 0,
            'is_retired' => RequestHelper::boolean( $_POST, 'is_retired', false ) ? 1 : 0,
            'prefix' => SanitizationHelper::text( RequestHelper::value( $_POST, 'prefix', '' ) ),
            'suffix' => SanitizationHelper::text( RequestHelper::value( $_POST, 'suffix', '' ) ),
            'length' => max( 8, RequestHelper::integer( $_POST, 'length', 12 ) ),
            'pattern' => SanitizationHelper::text( RequestHelper::value( $_POST, 'pattern', 'prefix-segment' ) ),
            'description' => SanitizationHelper::textarea( RequestHelper::value( $_POST, 'description', '' ) ),
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
    /**
     * Toggle the retired status of a licence type.
     *
     * This method handles the AJAX request to toggle the retired status of a licence type based on the provided ID.
     */
    public function toggle_licence_type_retired(): void {
        if ( ! AjaxHelper::authorized( 'licencepress_licence_type_form', 'licencepress_licence_issue' ) ) {
            AjaxHelper::unauthorized( __( 'You are not authorized to retire a LicencePress licence type.', 'licencepress' ) );
        }

        $id = RequestHelper::integer( $_POST, 'id', 0 );
        if ( $id <= 0 ) {
            AjaxHelper::error( [ 'message' => __( 'A valid licence type ID is required.', 'licencepress' ) ], 400 );
        }

        $retired = RequestHelper::boolean( $_POST, 'retired', false );
        if ( ! LicenceTypeManager::retire_type( $id, $retired ) ) {
            AjaxHelper::error( [ 'message' => __( 'Licence type status could not be updated.', 'licencepress' ) ], 400 );
        }

        AjaxHelper::success( [
            'message' => $retired ? __( 'Licence type retired successfully.', 'licencepress' ) : __( 'Licence type reactivated successfully.', 'licencepress' ),
            'retired' => $retired,
        ] );
    }
    /**
     * Delete a licence type.
     *
     * This method handles the AJAX request to delete a licence type based on the provided ID.
     */
    public function delete_licence_type(): void {
        if ( ! AjaxHelper::authorized( 'licencepress_licence_type_form', 'licencepress_licence_issue' ) ) {
            AjaxHelper::unauthorized( __( 'You are not authorized to delete a LicencePress licence type.', 'licencepress' ) );
        }

        $id = RequestHelper::integer( $_POST, 'id', 0 );
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
            $capability = SanitizationHelper::key( $value, $fallback );
            if ( in_array( $capability, $allowed, true ) ) {
                return $capability;
            }
        }
        return $fallback;
    }

}
