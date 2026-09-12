<?php
/**
 * LicencePress menu registration and sidebar definitions.
 *
 * @package LicencePress
 * @subpackage Includes\Functions\Admin
 * @since 1.0.0
 */
namespace LicencePress\Includes\Functions\Admin;

use LicencePress\Admin\Admin;
use LicencePress\Includes\Functions\Helpers\LoggerHelper;
use LicencePress\Includes\Functions\Helpers\AMHelper;
use LicencePress\Includes\Functions\Helpers\ASMHelper;
use LicencePress\Includes\Plugins\AdminMenuProviderInterface;
use LicencePress\Includes\Plugins\AdminSidebarProviderInterface;
use LicencePress\Includes\Plugins\Plugins;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Owns LicencePress menu data and registration.
 *
 * Rendering remains in Admin and Sidebar. This class builds menu data,
 * applies extension filters, and calls the WordPress admin API.
 */
final class FunctionsSidebar {
	/**
	 * Register the core WordPress menu followed by plugin-provided menus.
	 *
	 * @param Admin $admin Core admin callbacks and capability resolver.
	 * @return void
	 */
	public static function register_admin_menu( Admin $admin ): void {
		foreach ( self::core_wordpress_menus( $admin ) as $menu ) {
			self::register_wordpress_menu( $menu );
		}

		foreach ( AMHelper::filter( self::plugin_wordpress_menus() ) as $menu ) {
			self::register_wordpress_menu( $menu );
		}
	}

	/**
	 * Return the built-in and filtered LicencePress sidebar groups.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function get_sidebar_groups(): array {
		$groups = self::core_sidebar_groups();
		$menus  = ASMHelper::filter( self::plugin_sidebar_menus() );

		// Create parents first so children can target a parent in any order.
		foreach ( $menus as $menu ) {
			if ( '' === self::parent_slug( $menu ) ) {
				self::add_sidebar_group( $groups, $menu );
			}
		}

		foreach ( $menus as $menu ) {
			$parent = self::parent_slug( $menu );
			if ( '' !== $parent ) {
				self::add_sidebar_item( $groups, $parent, $menu );
			}
		}

		foreach ( $groups as $group_key => $group ) {
			$filtered_items = array();
			foreach ( $group['items'] as $item ) {
				$capability = sanitize_key( (string) ( $item['capability'] ?? '' ) );
				if ( self::can_view_menu_item( $capability ) ) {
					$filtered_items[] = $item;
				}
			}
			$groups[ $group_key ]['items'] = $filtered_items;
		}

		return array_filter( $groups, static fn ( array $group ): bool => ! empty( $group['items'] ) );
	}

	/**
	 * Get a LicencePress sidebar page URL.
	 *
	 * @param string $slug Page slug, optionally followed by a query string.
	 * @return string
	 */
	public static function get_admin_sidebar_menu_page_url( string $slug ): string {
		return admin_url( 'admin.php?page=' . $slug );
	}

	/**
	 * Get the core WordPress menus for the admin sidebar.
	 *
	 * @param Admin $admin Core admin callbacks and capability resolver.
	 * @return array<int, array<string, mixed>> The core WordPress menus.
	 */
	private static function core_wordpress_menus( Admin $admin ): array {
		return array(
			array(
				'name'       => __( 'LicencePress', 'licencepress' ),
				'slug'       => 'licencepress',
				'icon'       => 'dashicons-vault',
				'parent'     => '',
				'callback'   => array( $admin, 'render_dashboard' ),
				'capability' => 'licencepress_admin_view',
				'position'   => 30,
			),
			array(
				'name'       => __( 'Dashboard', 'licencepress' ),
				'slug'       => 'licencepress',
				'parent'     => 'licencepress',
				'callback'   => array( $admin, 'render_dashboard' ),
				'capability' => 'licencepress_dashboard_view',
			),
			array(
				'name'       => __( 'Customers', 'licencepress' ),
				'slug'       => 'licencepress&group=customers&tab=overview',
				'parent'     => 'licencepress',
				'callback'   => array( $admin, 'render_customers' ),
				'capability' => 'licencepress_customer_manage',
			),
			array(
				'name'       => __( 'Licences', 'licencepress' ),
				'slug'       => 'licencepress&group=licences&tab=overview',
				'parent'     => 'licencepress',
				'callback'   => array( $admin, 'render_licence_types' ),
				'capability' => 'licencepress_licence_overview_view',
			),
			array(
				'name'       => __( 'Settings', 'licencepress' ),
				'slug'       => 'licencepress&group=settings&tab=general',
				'parent'     => 'licencepress',
				'callback'   => array( $admin, 'render_settings' ),
				'capability' => 'licencepress_settings_general_view',
			),
			array(
				'name'       => __( 'Tools', 'licencepress' ),
				'slug'       => 'licencepress&group=tools&tool=debug',
				'parent'     => 'licencepress',
				'callback'   => array( $admin, 'render_tools' ),
				'capability' => 'licencepress_tools_debug',
			),
		);
	}

	/**
	 * Get the core sidebar groups for the admin sidebar.
	 *
	 * @return array<string, array<string, mixed>> The core sidebar groups.
	 */
	private static function core_sidebar_groups(): array {
		return array(
			'customers' => array(
				'label' => __( 'Customers', 'licencepress' ),
				'icon'  => 'fa-solid fa-users',
				'items' => array(
					'customer-directory' => array(
						'label'      => __( 'Customer Directory', 'licencepress' ),
						'icon'       => 'fa-solid fa-user-group',
						'link'       => 'licencepress&group=customers&tab=overview',
						'capability' => 'licencepress_customer_manage',
					),
				),
			),
			'licences' => array(
				'label' => __( 'Licences', 'licencepress' ),
				'icon'  => 'fa-solid fa-file-signature',
				'items' => array(
					'overview' => array(
						'label'      => __( 'Overview', 'licencepress' ),
						'icon'       => 'fa-solid fa-key',
						'link'       => 'licencepress&group=licences&tab=overview',
						'capability' => 'licencepress_licence_overview_view',
					),
					'manage-licence-types' => array(
						'label'      => __( 'Manage Licence Types', 'licencepress' ),
						'icon'       => 'fa-solid fa-list',
						'link'       => 'licencepress&group=licences&tab=manage-types',
						'capability' => 'licencepress_licence_type_view',
					),
					'add-licence-type' => array(
						'label'      => __( 'Add Licence Type', 'licencepress' ),
						'icon'       => 'fa-solid fa-square-plus',
						'link'       => 'licencepress&group=licences&tab=add-type',
						'capability' => 'licencepress_licence_type_edit',
					),
				),
			),
			'settings' => array(
				'label' => __( 'Settings', 'licencepress' ),
				'icon'  => 'fa-solid fa-gear',
				'items' => array(
					'general' => array(
						'label'      => __( 'General', 'licencepress' ),
						'icon'       => 'fa-solid fa-sliders',
						'link'       => 'licencepress&group=settings&tab=general',
						'capability' => 'licencepress_settings_general_view',
					),
					'access' => array(
						'label'      => __( 'Access', 'licencepress' ),
						'icon'       => 'fa-solid fa-user-shield',
						'link'       => 'licencepress&group=settings&tab=access',
						'capability' => 'licencepress_settings_access_view',
					),
					'plugins' => array(
						'label'      => __( 'Plugins', 'licencepress' ),
						'icon'       => 'fa-solid fa-puzzle-piece',
						'link'       => 'licencepress&group=settings&tab=plugins',
						'capability' => 'licencepress_settings_plugins_view',
					),
					'third-party' => array(
						'label'      => __( '3rd Party', 'licencepress' ),
						'icon'       => 'fa-solid fa-plug',
						'link'       => 'licencepress&group=settings&tab=third-party',
						'capability' => 'licencepress_settings_plugins_ext_view',
					),
				),
			),
			'tools'    => array(
				'label' => __( 'Tools', 'licencepress' ),
				'icon'  => 'fa-solid fa-toolbox',
				'items' => array(
					'debug' => array(
						'label'      => __( 'Debug', 'licencepress' ),
						'icon'       => 'fa-solid fa-bug-slash',
						'link'       => 'licencepress&group=tools&tool=debug',
						'capability' => 'licencepress_tools_debug',
					),
					'reset' => array(
						'label'      => __( 'Reset', 'licencepress' ),
						'icon'       => 'fa-solid fa-rotate',
						'link'       => 'licencepress&group=tools&tool=reset',
						'capability' => 'licencepress_tools_reset',
					),
					'import' => array(
						'label'      => __( 'Import', 'licencepress' ),
						'icon'       => 'fa-solid fa-file-import',
						'link'       => 'licencepress&group=tools&tool=import',
						'capability' => 'licencepress_tools_import',
					),
					'export' => array(
						'label'      => __( 'Export', 'licencepress' ),
						'icon'       => 'fa-solid fa-file-export',
						'link'       => 'licencepress&group=tools&tool=export',
						'capability' => 'licencepress_tools_export',
					),
				),
			),
		);
	}
	/**
	 * Register a WordPress menu.
	 *
	 * @param array<string, mixed> $menu The menu definition.
	 * @return void
	 */
	private static function register_wordpress_menu( array $menu ): void {
		$callback   = $menu['callback'] ?? null;
		$raw_slug   = (string) ( $menu['slug'] ?? '' );
		$slug       = self::menu_page_slug( $raw_slug );
		$name       = (string) ( $menu['name'] ?? '' );
		$parent     = self::admin_parent_slug( (string) ( $menu['parent'] ?? '' ) );
		$capability = self::resolve_menu_capability( sanitize_key( (string) ( $menu['capability'] ?? 'manage_options' ) ) );

		if ( '' === $slug || '' === $name || ! is_callable( $callback ) ) {
			LoggerHelper::write_log( sprintf( 'LicencePress skipped menu registration for empty or invalid page: %s', $raw_slug ) );
			return;
		}

		LoggerHelper::write_log( sprintf( 'LicencePress registering admin menu: %s (slug=%s, parent=%s, capability=%s)', $name, $slug, $parent, $capability ) );

		try {
			if ( '' === $parent ) {
				add_menu_page( $name, $name, $capability, $slug, $callback, $menu['icon'] ?? 'dashicons-admin-generic', $menu['position'] ?? null );
				return;
			}

			if ( $slug === $parent ) {
				LoggerHelper::write_log( sprintf( 'LicencePress skipped submenu registration because slug matches parent: %s', $slug ) );
				return;
			}

			add_submenu_page( $parent, $name, $name, $capability, $slug, $callback, $menu['position'] ?? null );
		} catch ( \Throwable $e ) {
			LoggerHelper::write_log( sprintf( 'LicencePress menu registration failed for %s (%s): %s', $name, $slug, $e->getMessage() ) );
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				wp_die( esc_html( $e->getMessage() ), __( 'LicencePress menu registration error', 'licencepress' ), array( 'back_link' => true ) );
			}
		}
	}

	/**
	 * Get the WordPress menus provided by active LicencePress plugins.
	 *
	 * @return array<int, array<string, mixed>> The WordPress menus.
	*/
	private static function plugin_wordpress_menus(): array {
		$menus = array();

		foreach ( Plugins::get_instance()->get_registered_plugins() as $plugin ) {
			if ( ! $plugin instanceof AdminMenuProviderInterface || ! $plugin->is_active() ) {
				continue;
			}

			try {
				foreach ( $plugin->get_admin_menu() as $definition ) {
					if ( ! is_array( $definition ) ) {
						continue;
					}

					$menus[] = self::normalize_wordpress_menu( $definition );
					foreach ( $definition['children'] ?? array() as $child ) {
						if ( is_array( $child ) ) {
							$child['parent'] = $definition['menu_slug'] ?? '';
							$menus[]         = self::normalize_wordpress_menu( $child );
						}
					}
				}
			} catch ( \Throwable $e ) {
				LoggerHelper::write_log( sprintf( 'LicencePress plugin %s failed to provide WordPress menus: %s', $plugin->get_slug(), $e->getMessage() ) );
			}
		}

		return array_values( array_filter( $menus, static fn ( $menu ): bool => is_array( $menu ) ) );
	}

	/**
	 * Normalize a WordPress menu definition.
	 *
	 * @param array<string, mixed> $definition The menu definition.
	 * @return array<string, mixed> The normalized menu.
	 */
	private static function normalize_wordpress_menu( array $definition ): array {
		return array(
			'name'       => $definition['menu_title'] ?? $definition['page_title'] ?? '',
			'slug'       => $definition['menu_slug'] ?? '',
			'icon'       => $definition['icon'] ?? 'dashicons-admin-generic',
			'parent'     => $definition['parent'] ?? '',
			'callback'   => $definition['callback'] ?? null,
			'capability' => $definition['capability'] ?? 'manage_options',
			'position'   => $definition['position'] ?? null,
		);
	}
	/**
	 * Sanitize an admin parent slug.
	 *
	 * @param string $parent The parent slug to sanitize.
	 * @return string The sanitized parent slug.
	 */
	private static function admin_parent_slug( string $parent ): string {
		$parent = strtolower( sanitize_text_field( $parent ) );
		return (string) preg_replace( '/[^a-z0-9._-]/', '', $parent );
	}
	/**
	 * Sanitize a menu page slug.
	 *
	 * @param string $slug The menu page slug.
	 * @return string The sanitized menu page slug.
	 */
	private static function menu_page_slug( string $slug ): string {
		$slug = trim( (string) $slug );
		if ( '' === $slug || preg_match( '/^\d+$/', $slug ) ) {
			return '';
		}

		if ( false !== strpos( $slug, '&' ) ) {
			$base = trim( (string) strtok( $slug, '&' ) );
			if ( '' === $base || preg_match( '/^\d+$/', $base ) ) {
				return '';
			}
			return $base . substr( $slug, strlen( $base ) );
		}

		$base = sanitize_key( $slug );
		return '' !== $base && ! preg_match( '/^\d+$/', $base ) ? $base : '';
	}

	/**
	 * Get the sidebar menus provided by active LicencePress plugins.
	 *
	 * @return array<int, array<string, mixed>> The sidebar menus.
	 */
	private static function plugin_sidebar_menus(): array {
		$menus = array();

		foreach ( Plugins::get_instance()->get_registered_plugins() as $plugin ) {
			if ( ! $plugin instanceof AdminSidebarProviderInterface || ! $plugin->is_active() ) {
				continue;
			}

			try {
				foreach ( $plugin->get_admin_sidebar() as $definition ) {
					if ( ! is_array( $definition ) ) {
						continue;
					}

					if ( 'group' === ( $definition['type'] ?? '' ) ) {
						$menus[] = ASMHelper::define( $definition['label'] ?? '', $definition['slug'] ?? '', $definition['icon'] ?? '', '', $definition['capability'] ?? '' );
						foreach ( $definition['items'] ?? array() as $child ) {
							if ( is_array( $child ) ) {
								$menus[] = ASMHelper::define( $child['label'] ?? '', self::sidebar_slug( $child ), $child['icon'] ?? '', $definition['slug'] ?? '', $child['capability'] ?? '' );
							}
						}
						continue;
					}

					$menus[] = ASMHelper::define( $definition['label'] ?? '', self::sidebar_slug( $definition ), $definition['icon'] ?? '', $definition['parent'] ?? '', $definition['capability'] ?? '' );
				}
			} catch ( \Throwable $e ) {
				LoggerHelper::write_log( sprintf( 'LicencePress plugin %s failed to provide sidebar menus: %s', $plugin->get_slug(), $e->getMessage() ) );
			}
		}

		return $menus;
	}

	/**
	 * Generate a sidebar slug from a menu definition.
	 *
	 * @param array<string, mixed> $definition The menu definition.
	 * @return string The generated sidebar slug.
	 */
	private static function sidebar_slug( array $definition ): string {
		$page  = (string) ( $definition['page'] ?? $definition['slug'] ?? '' );
		$query = $definition['query'] ?? array();

		if ( ! is_array( $query ) || empty( $query ) ) {
			return $page;
		}

		return $page . '&' . http_build_query( array_filter( $query, 'is_scalar' ), '', '&', PHP_QUERY_RFC3986 );
	}

	/**
	 * Add a sidebar group to the collection of groups.
	 *
	 * @param array<string, array<string, mixed>> $groups The collection of sidebar groups.
	 * @param array<string, mixed> $menu The menu definition for the group.
	 * @return void
	 */
	private static function add_sidebar_group( array &$groups, array $menu ): void {
		$slug  = self::menu_slug( $menu );
		$label = (string) ( $menu['name'] ?? '' );
		$icon  = (string) ( $menu['icon'] ?? '' );

		if ( '' !== $slug && ! preg_match( '/^\d+$/', $slug ) && '' !== $label && '' !== $icon ) {
			$groups[ $slug ] = array(
				'label' => $label,
				'icon'  => $icon,
				'items' => array(),
			);
		}
	}

	/**
	 * Add a sidebar item to a parent group.
	 *
	 * @param array<string, array<string, mixed>> $groups The collection of sidebar groups.
	 * @param string $parent The parent group slug.
	 * @param array<string, mixed> $menu The menu definition for the item.
	 * @return void
	 */
	private static function add_sidebar_item( array &$groups, string $parent, array $menu ): void {
		$slug  = trim( (string) ( $menu['slug'] ?? '' ) );
		$label = (string) ( $menu['name'] ?? '' );
		$icon  = (string) ( $menu['icon'] ?? '' );

		$capability = sanitize_key( (string) ( $menu['capability'] ?? '' ) );
		if ( isset( $groups[ $parent ] ) && '' !== $slug && ! preg_match( '/^\d+$/', $slug ) && '' !== $label && '' !== $icon && ( '' === $capability || current_user_can( $capability ) ) ) {
			$groups[ $parent ]['items'][ $slug ] = array(
				'label'      => $label,
				'icon'       => $icon,
				'capability' => $capability,
			);
		}
	}

	/**
	 * Get the parent slug from a menu definition.
	 *
	 * @param array<string, mixed> $menu The menu definition.
	 * @return string The parent slug.
	 */
	private static function parent_slug( array $menu ): string {
		return sanitize_key( (string) ( $menu['parent'] ?? '' ) );
	}

	/**
	 * Get the menu slug from a menu definition.
	 *
	 * @param array<string, mixed> $menu The menu definition.
	 * @return string The menu slug.
	 */
	private static function menu_slug( array $menu ): string {
		return sanitize_key( (string) ( $menu['slug'] ?? '' ) );
	}

	/**
	 * Determine if the current user can view a menu item.
	 *
	 * Administrators keep access even when a fresh role capability install has not
	 * yet refreshed their user capability cache.
	 *
	 * @param string $capability The capability to check.
	 * @return bool True if the menu item should be visible.
	 */
	private static function can_view_menu_item( string $capability ): bool {
		if ( '' === $capability ) {
			return true;
		}

		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		return current_user_can( $capability );
	}

	/**
	 * Resolve the effective capability to use when registering a WordPress menu.
	 *
	 * Admins should remain able to see the LicencePress menu while the custom
	 * role capability map catches up after activation or a role refresh.
	 *
	 * @param string $capability The capability to normalize.
	 * @return string The effective capability.
	 */
	private static function resolve_menu_capability( string $capability ): string {
		if ( '' === $capability ) {
			return 'manage_options';
		}

		if ( current_user_can( 'manage_options' ) ) {
			return 'manage_options';
		}

		return $capability;
	}
}
