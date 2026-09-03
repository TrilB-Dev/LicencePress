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
use LicencePress\Admin\Manager\Settings\SettingsManager;
use LicencePress\Admin\Manager\PlugName\PlugNameManager;

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
        $this->tools_manager = new ToolsManager();
        $this->plugin_functions = new FunctionsPlugins();
        $this->loader = new LoaderHelper();
        $this->dashboard_manager->register_assets( $assets );
        $this->plugname_manager->register_assets( $assets );
        $this->settings_manager->register_assets( $assets );
        $this->tools_manager->register_assets( $assets );
        $this->loader->register_component( $this, [
            [ 'type' => 'action', 'hook' => 'wp_ajax_licencepress_load_settings_tab', 'callback' => 'load_settings_tab' ],
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
    /**
     * Render the manage plugnames page.
     *
     * This method is responsible for rendering the manage plugnames page of the LicencePress plugin.
     * It delegates the rendering to the PlugNameManager instance.
     */
    public function render_plugnames(): void {
        $this->plugname_manager->render();
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
