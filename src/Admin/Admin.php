<?php
/**
 * Admin class for LicencePress plugin.
 *
 * @package LicencePress
 * @subpackage Admin
 * @since 1.0.0
 */
namespace LicencePress\Admin;

use LicencePress\Includes\Settings\Settings;
use LicencePress\Includes\Functions\Admin\FunctionsPlugins;
use LicencePress\Includes\Functions\Helpers\AjaxHelper;
use LicencePress\Includes\Core\Capabilities;
use LicencePress\Includes\Functions\Helpers\LoaderHelper;
use LicencePress\Includes\Functions\Helpers\LoggerHelper;
use LicencePress\Includes\Functions\Helpers\RequestHelper;
use LicencePress\Includes\Functions\Helpers\SanitizationHelper;
use LicencePress\Includes\Functions\Admin\FunctionsSidebar;
use LicencePress\Assets\Assets;
use LicencePress\Admin\Manager\Manager;
use LicencePress\Admin\Manager\Tools\ToolsManager;
use LicencePress\Admin\Manager\Dashboard\DashboardManager;
use LicencePress\Admin\Manager\Customer\CustomerManager;
use LicencePress\Admin\Manager\Licences\LicencesManager;
use LicencePress\Admin\Manager\Settings\SettingsManager;

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
	 * CustomerManager instance for managing customer records and account data.
	 *
	 * @var CustomerManager
	 */
	private CustomerManager $customer_manager;
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
	 * Registry of the admin managers.
	 *
	 * @var array<string, Manager>
	 */
	private array $managers;
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
		$this->managers = array(
			'dashboard' => new DashboardManager(),
			'settings'  => new SettingsManager(),
			'customers' => new CustomerManager(),
			'licences'  => new LicencesManager(),
			'tools'     => new ToolsManager(),
		);

		$this->dashboard_manager = $this->managers['dashboard'];
		$this->settings_manager  = $this->managers['settings'];
		$this->customer_manager  = $this->managers['customers'];
		$this->licences_manager  = $this->managers['licences'];
		$this->tools_manager     = $this->managers['tools'];
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
		foreach ( $this->managers as $manager ) {
			$manager->register_assets( $assets );
		}
		/**
		 * Register assets for the plugin functions manager.
		 */
		$this->loader->register_component(
			$this,
			array(
				array(
					'type'     => 'action',
					'hook'     => 'wp_ajax_licencepress_load_settings_tab',
					'callback' => 'load_settings_tab',
				),
				array(
					'type'     => 'action',
					'hook'     => 'wp_ajax_licencepress_preview_licence_type',
					'callback' => 'preview_licence_type',
				),
				array(
					'type'     => 'action',
					'hook'     => 'wp_ajax_licencepress_load_licence_type',
					'callback' => 'load_licence_type',
				),
				array(
					'type'     => 'action',
					'hook'     => 'wp_ajax_licencepress_save_licence_type',
					'callback' => 'save_licence_type',
				),
				array(
					'type'     => 'action',
					'hook'     => 'wp_ajax_licencepress_toggle_licence_type_retired',
					'callback' => 'toggle_licence_type_retired',
				),
				array(
					'type'     => 'action',
					'hook'     => 'wp_ajax_licencepress_delete_licence_type',
					'callback' => 'delete_licence_type',
				),
				array(
					'type'     => 'action',
					'hook'     => 'wp_ajax_licencepress_dismiss_onboarding',
					'callback' => 'dismiss_onboarding',
				),
				array(
					'type'     => 'action',
					'hook'     => 'wp_ajax_licencepress_issue_customer_licence',
					'callback' => 'issue_customer_licence',
				),
				array(
					'type'     => 'action',
					'hook'     => 'wp_ajax_licencepress_revoke_customer_licence',
					'callback' => 'revoke_customer_licence',
				),
			)
		);
		$this->loader->register_component(
			$this->plugin_functions,
			array(
				array(
					'type'     => 'action',
					'hook'     => 'wp_ajax_licencepress_toggle_plugin',
					'callback' => 'toggle_plugin',
				),
				array(
					'type'     => 'action',
					'hook'     => 'wp_ajax_licencepress_save_plugin_settings',
					'callback' => 'save_plugin_settings',
				),
			)
		)->run();
	}
	/**
	 * Register admin menu pages and subpages.
	 *
	 * @since 1.0.0
	 */
	public function register_admin_menu(): void {
		LoggerHelper::write_log( 'LicencePress admin menu registration started.' );

		try {
			FunctionsSidebar::register_admin_menu( $this );
			LoggerHelper::write_log( 'LicencePress admin menu registration complete.' );
		} catch ( \Throwable $e ) {
			LoggerHelper::write_log( 'LicencePress admin menu registration failed: ' . $e->getMessage() );
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				wp_die( esc_html( $e->getMessage() ), __( 'LicencePress admin menu error', 'licencepress' ), array( 'back_link' => true ) );
			}
		}
	}
	/**
	 * Render the dashboard page.
	 *
	 * This method is responsible for rendering the dashboard page of the LicencePress plugin.
	 * It delegates the rendering to the DashboardManager instance.
	 */
	public function render_dashboard(): void {
		$group = RequestHelper::get_key( 'group', '' );
		$tab   = RequestHelper::get_key( 'tab', '' );
		LoggerHelper::write_log( sprintf( 'LicencePress dashboard render triggered. Group=%s Tab=%s', $group, $tab ) );

		try {
			switch ( $group ) {
				case 'customers':
					LoggerHelper::write_log( 'LicencePress dashboard routed to customer page.' );
					$this->render_customers();
					return;
				case 'licences':
					switch ( $tab ) {
						case 'manage-types':
							LoggerHelper::write_log( 'LicencePress dashboard routed to manage licence types page.' );
							$this->render_licence_types();
							return;
						case 'add-type':
							LoggerHelper::write_log( 'LicencePress dashboard routed to add licence type page.' );
							$this->render_licence_type_add();
							return;
						case 'overview':
						default:
							LoggerHelper::write_log( 'LicencePress dashboard routed to licences overview page.' );
							$this->render_licences();
							return;
					}
				case 'settings':
					LoggerHelper::write_log( 'LicencePress dashboard routed to settings page.' );
					$this->render_settings();
					return;
				case 'tools':
					LoggerHelper::write_log( 'LicencePress dashboard routed to tools page.' );
					$this->render_tools();
					return;
				default:
					LoggerHelper::write_log( 'LicencePress dashboard default render path selected.' );
					$this->dashboard_manager->render();
			}
		} catch ( \Throwable $e ) {
			LoggerHelper::write_log( 'LicencePress dashboard render failed: ' . $e->getMessage() );
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				wp_die( esc_html( $e->getMessage() ), __( 'LicencePress dashboard error', 'licencepress' ), array( 'back_link' => true ) );
			}
		}
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

		Settings::register_group( 'setup', array( 'first_install_complete' => false ) );
		Settings::set( 'first_install_complete', true );
		Settings::set( 'onboarding_steps_complete', 3 );

		AjaxHelper::success( array( 'dismissed' => true ) );
	}

	/**
	 * Issue a new licence for a customer via AJAX.
	 *
	 * @return void
	 */
	public function issue_customer_licence(): void {
		if ( ! AjaxHelper::authorized( 'licencepress_issue_customer_licence', 'licencepress_customer_licence_create' ) ) {
			AjaxHelper::unauthorized( __( 'You are not authorized to issue licences for this customer.', 'licencepress' ) );
		}

		$user_id = RequestHelper::integer( $_POST, 'customer_id', 0 );
		if ( $user_id <= 0 ) {
			AjaxHelper::error( array( 'message' => __( 'A valid customer is required.', 'licencepress' ) ), 400 );
		}

		$product_id = SanitizationHelper::text( RequestHelper::value( $_POST, 'product_id', '' ) );
		if ( '' === $product_id ) {
			AjaxHelper::error( array( 'message' => __( 'A product identifier is required to issue a licence.', 'licencepress' ) ), 400 );
		}

		$licence = CustomerManager::issue_customer_licence(
			$user_id,
			array(
				'product_id' => $product_id,
				'days'       => max( 1, RequestHelper::integer( $_POST, 'days', 30 ) ),
				'site_url'   => SanitizationHelper::text( RequestHelper::value( $_POST, 'site_url', '' ) ),
				'features'   => RequestHelper::array( $_POST, 'features', array() ),
			)
		);

		if ( null === $licence ) {
			AjaxHelper::error( array( 'message' => __( 'The customer licence could not be created.', 'licencepress' ) ), 400 );
		}

		AjaxHelper::success(
			array(
				'message' => __( 'Customer licence issued successfully.', 'licencepress' ),
				'licence' => $licence,
			)
		);
	}

	/**
	 * Revoke a customer licence via AJAX.
	 *
	 * @return void
	 */
	public function revoke_customer_licence(): void {
		if ( ! AjaxHelper::authorized( 'licencepress_revoke_customer_licence', 'licencepress_customer_licence_revoke' ) ) {
			AjaxHelper::unauthorized( __( 'You are not authorized to revoke licences for this customer.', 'licencepress' ) );
		}

		$user_id = RequestHelper::integer( $_POST, 'customer_id', 0 );
		$token   = SanitizationHelper::text( RequestHelper::value( $_POST, 'token', '' ) );
		if ( $user_id <= 0 || '' === $token ) {
			AjaxHelper::error( array( 'message' => __( 'A valid customer and licence token are required.', 'licencepress' ) ), 400 );
		}

		if ( ! CustomerManager::revoke_customer_licence( $user_id, $token ) ) {
			AjaxHelper::error( array( 'message' => __( 'The customer licence could not be revoked.', 'licencepress' ) ), 400 );
		}

		AjaxHelper::success( array( 'message' => __( 'Customer licence revoked successfully.', 'licencepress' ) ) );
	}
	/**
	 * Render LicencePress licences page.
	 *
	 * This method is responsible for rendering the licences page of the LicencePress plugin.
	 * It delegates the rendering to the LicencesManager instance.
	 */
	public function render_customers(): void {
		LoggerHelper::write_log( 'LicencePress customer render started.' );
		$this->customer_manager->render();
		LoggerHelper::write_log( 'LicencePress customer render complete.' );
	}
	/**
	 * Render LicencePress licences page.
	 *
	 * This method is responsible for rendering the licences page of the LicencePress plugin.
	 * It delegates the rendering to the LicencesManager instance.
	 */
	public function render_licences(): void {
		LoggerHelper::write_log( 'LicencePress licences render started.' );
		$this->licences_manager->render();
		LoggerHelper::write_log( 'LicencePress licences render complete.' );
	}
	/**
	 * Render the add licence type page.
	 *
	 * This method is responsible for rendering the add licence type page of the LicencePress plugin.
	 * It delegates the rendering to the LicencesManager instance.
	 */
	public function render_licence_type_add(): void {
		LoggerHelper::write_log( 'LicencePress add licence type page render started.' );
		$this->licences_manager->render_add_type();
		LoggerHelper::write_log( 'LicencePress add licence type page render complete.' );
	}
	/**
	 * Render the manage licence types page.
	 *
	 * This method is responsible for rendering the manage licence types page of the LicencePress plugin.
	 * It delegates the rendering to the LicencesManager instance.
	 */
	public function render_licence_types(): void {
		LoggerHelper::write_log( 'LicencePress manage licence types page render started.' );
		$this->licences_manager->render_manage_types();
		LoggerHelper::write_log( 'LicencePress manage licence types page render complete.' );
	}
	/**
	 * Render the manage licences page.
	 *
	 * This method is responsible for rendering the manage licences page of the LicencePress plugin.
	 * It delegates the rendering to the LicencesManager instance.
	 */
	public function render_licence_management(): void {
		LoggerHelper::write_log( 'LicencePress licence management page render started.' );
		$this->licences_manager->render_manage_licences();
		LoggerHelper::write_log( 'LicencePress licence management page render complete.' );
	}
	/**
	 * Render the settings page.
	 *
	 * This method is responsible for rendering the settings page of the LicencePress plugin.
	 * It delegates the rendering to the SettingsManager instance.
	 */
	public function render_settings(): void {
		LoggerHelper::write_log( 'LicencePress settings page render started.' );
		$this->settings_manager->render();
		LoggerHelper::write_log( 'LicencePress settings page render complete.' );
	}
	/**
	 * Render the tools page.
	 *
	 * @return void
	 */
	public function render_tools(): void {
		LoggerHelper::write_log( 'LicencePress tools page render started.' );
		$this->tools_manager->render();
		LoggerHelper::write_log( 'LicencePress tools page render complete.' );
	}
	/**
	 * Render the analytics page.
	 *
	 * This method is responsible for rendering the analytics page of the LicencePress plugin.
	 * It delegates the rendering to the AnalyticsManager instance.
	 */
	public function load_settings_tab(): void {
		$tab             = RequestHelper::get_key( 'tab', 'general' );
		$view_capability = array(
			'general'     => 'licencepress_settings_general_view',
			'access'      => 'licencepress_settings_access_view',
			'plugins'     => 'licencepress_settings_plugins_view',
			'third-party' => 'licencepress_settings_plugins_ext_view',
		)[ $tab ] ?? 'licencepress_settings_general_view';
		if ( ! AjaxHelper::authorized( 'licencepress_settings_tabs', $view_capability ) ) {
			AjaxHelper::unauthorized( __( 'You are not authorized to load LicencePress settings.', 'licencepress' ) );
		}

		ob_start();
		$this->settings_manager->render_tab_content( $tab );
		$html = (string) ob_get_clean();
		AjaxHelper::success(
			array(
				'html' => $html,
				'tab'  => $tab,
			)
		);
	}
	/**
	 * Preview a licence type.
	 *
	 * This method handles the AJAX request to preview a licence type based on the provided settings.
	 */
	public function preview_licence_type(): void {
		if ( ! AjaxHelper::authorized( 'licencepress_licence_type_form', 'licencepress_licence_type_manage' ) ) {
			AjaxHelper::unauthorized( __( 'You are not authorized to preview a LicencePress licence type.', 'licencepress' ) );
		}

		$settings = array(
			'name'    => SanitizationHelper::text( RequestHelper::value( $_POST, 'name', '' ) ),
			'prefix'  => SanitizationHelper::text( RequestHelper::value( $_POST, 'prefix', '' ) ),
			'suffix'  => SanitizationHelper::text( RequestHelper::value( $_POST, 'suffix', '' ) ),
			'length'  => max( 8, RequestHelper::integer( $_POST, 'length', 12 ) ),
			'pattern' => SanitizationHelper::text( RequestHelper::value( $_POST, 'pattern', 'prefix-segment' ) ),
		);

		AjaxHelper::success( LicenceTypeManager::generate_preview( $settings ) );
	}
	/**
	 * Load a licence type.
	 *
	 * This method handles the AJAX request to load a licence type based on the provided ID.
	 */
	public function load_licence_type(): void {
		if ( ! AjaxHelper::authorized( 'licencepress_licence_type_form', 'licencepress_licence_type_manage' ) ) {
			AjaxHelper::unauthorized( __( 'You are not authorized to load a LicencePress licence type.', 'licencepress' ) );
		}

		$id   = RequestHelper::integer( $_POST, 'id', 0 );
		$type = $id > 0 ? LicenceTypeManager::get_type( $id ) : null;

		if ( null === $type ) {
			AjaxHelper::error( array( 'message' => __( 'Licence type not found.', 'licencepress' ) ), 404 );
		}

		AjaxHelper::success( array( 'type' => $type ) );
	}
	/**
	 * Save a licence type.
	 *
	 * This method handles the AJAX request to save a licence type based on the provided settings.
	 */
	public function save_licence_type(): void {
		if ( ! AjaxHelper::authorized( 'licencepress_licence_type_form', 'licencepress_licence_type_manage' ) ) {
			AjaxHelper::unauthorized( __( 'You are not authorized to save a LicencePress licence type.', 'licencepress' ) );
		}

		$id       = RequestHelper::integer( $_POST, 'id', 0 );
		$settings = array(
			'name'        => SanitizationHelper::text( RequestHelper::value( $_POST, 'name', '' ) ),
			'slug'        => SanitizationHelper::key( RequestHelper::value( $_POST, 'slug', '' ) ),
			'parent_id'   => RequestHelper::integer( $_POST, 'parent_id', 0 ),
			'is_variant'  => RequestHelper::boolean( $_POST, 'is_variant', false ) ? 1 : 0,
			'is_retired'  => RequestHelper::boolean( $_POST, 'is_retired', false ) ? 1 : 0,
			'prefix'      => SanitizationHelper::text( RequestHelper::value( $_POST, 'prefix', '' ) ),
			'suffix'      => SanitizationHelper::text( RequestHelper::value( $_POST, 'suffix', '' ) ),
			'length'      => max( 8, RequestHelper::integer( $_POST, 'length', 12 ) ),
			'pattern'     => SanitizationHelper::text( RequestHelper::value( $_POST, 'pattern', 'prefix-segment' ) ),
			'description' => SanitizationHelper::textarea( RequestHelper::value( $_POST, 'description', '' ) ),
		);

		if ( '' === $settings['name'] ) {
			AjaxHelper::error( array( 'message' => __( 'A licence type name is required.', 'licencepress' ) ), 400 );
		}

		$saved_id = 0;
		$message  = __( 'Licence type created successfully.', 'licencepress' );
		if ( $id > 0 ) {
			$saved_id = $id;
			$updated  = LicenceTypeManager::update_type( $id, $settings );
			if ( ! $updated ) {
				AjaxHelper::error( array( 'message' => __( 'Licence type could not be updated.', 'licencepress' ) ), 400 );
			}
			$message = __( 'Licence type updated successfully.', 'licencepress' );
		} else {
			$saved_id = LicenceTypeManager::create_type( $settings );
		}

		AjaxHelper::success(
			array(
				'message' => $message,
				'id'      => $saved_id,
				'preview' => LicenceTypeManager::generate_preview( $settings ),
			)
		);
	}
	/**
	 * Toggle the retired status of a licence type.
	 *
	 * This method handles the AJAX request to toggle the retired status of a licence type based on the provided ID.
	 */
	public function toggle_licence_type_retired(): void {
		if ( ! AjaxHelper::authorized( 'licencepress_licence_type_form', 'licencepress_licence_type_manage' ) ) {
			AjaxHelper::unauthorized( __( 'You are not authorized to retire a LicencePress licence type.', 'licencepress' ) );
		}

		$id = RequestHelper::integer( $_POST, 'id', 0 );
		if ( $id <= 0 ) {
			AjaxHelper::error( array( 'message' => __( 'A valid licence type ID is required.', 'licencepress' ) ), 400 );
		}

		$retired = RequestHelper::boolean( $_POST, 'retired', false );
		if ( ! LicenceTypeManager::retire_type( $id, $retired ) ) {
			AjaxHelper::error( array( 'message' => __( 'Licence type status could not be updated.', 'licencepress' ) ), 400 );
		}

		AjaxHelper::success(
			array(
				'message' => $retired ? __( 'Licence type retired successfully.', 'licencepress' ) : __( 'Licence type reactivated successfully.', 'licencepress' ),
				'retired' => $retired,
			)
		);
	}
	/**
	 * Delete a licence type.
	 *
	 * This method handles the AJAX request to delete a licence type based on the provided ID.
	 */
	public function delete_licence_type(): void {
		if ( ! AjaxHelper::authorized( 'licencepress_licence_type_form', 'licencepress_licence_type_manage' ) ) {
			AjaxHelper::unauthorized( __( 'You are not authorized to delete a LicencePress licence type.', 'licencepress' ) );
		}

		$id = RequestHelper::integer( $_POST, 'id', 0 );
		if ( $id <= 0 ) {
			AjaxHelper::error( array( 'message' => __( 'A valid licence type ID is required.', 'licencepress' ) ), 400 );
		}

		if ( ! LicenceTypeManager::delete_type( $id ) ) {
			AjaxHelper::error( array( 'message' => __( 'Licence type could not be deleted.', 'licencepress' ) ), 400 );
		}

		AjaxHelper::success( array( 'message' => __( 'Licence type deleted successfully.', 'licencepress' ) ) );
	}
	/**
	 * Get the capability for a given key, with a fallback.
	 *
	 * @param string $key The settings key to retrieve the capability for.
	 * @param string $fallback The fallback capability if the key is not set or invalid.
	 * @return string The capability associated with the key, or the fallback if not valid.
	 */
	public function capability( string $key, string $fallback ): string {
		$value   = Settings::get( $key, $fallback );
		$values  = is_array( $value ) ? $value : array( $value );
		$allowed = array_merge( array( 'manage_options', 'edit_posts', 'publish_posts', 'manage_categories', 'delete_posts' ), array_keys( Capabilities::definitions() ) );
		foreach ( $values as $value ) {
			$capability = SanitizationHelper::key( $value, $fallback );
			if ( in_array( $capability, $allowed, true ) ) {
				return $capability;
			}
		}
		return $fallback;
	}
}
