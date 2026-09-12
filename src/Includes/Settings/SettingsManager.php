<?php
/**
 * SettingsManager class.
 *
 * @package LicencePress\Includes\Settings
 */

namespace LicencePress\Includes\Settings;

use LicencePress\Includes\Core\WP\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SettingsManager {
	/**
	 * Registered settings groups.
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private static array $registered_groups = array();

	/**
	 * Registered settings keys.
	 *
	 * @var array<string, string>
	 */
	private static array $registered_keys = array();
	/**
	 * Registered default settings.
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private static array $registered_defaults = array();
	/**
	 * Get the name of the settings table.
	 *
	 * @return string The name of the settings table.
	 * @since 1.0.0
	 */
	public static function table_name(): string {
		return Database::table_name( 'settings' );
	}
	/**
	 * Install the settings table and populate it with default values.
	 * 
	 * This method will create the necessary database table for storing settings
	 * and populate it with the default values defined in the class.
	 * @return void
	 */
	public static function install(): void {
		Database::install();

		foreach ( self::registered_defaults() as $group => $settings ) {
			$stored_settings = self::get_group( $group );
			if ( null === $stored_settings ) {
				$legacy_settings = self::get_legacy_group( $group );
				$stored_settings = is_array( $legacy_settings ) ? $legacy_settings : array();
			}

			self::set_group( $group, array_merge( $settings, $stored_settings ) );
			self::delete_legacy_group( $group );
		}
	}
	/**
	 * Get a setting value by its key.
	 *
	 * @param string $key     The setting key.
	 * @param mixed  $default The default value if the setting is not found.
	 * @return mixed The setting value or the default value.
	 */
	public static function get( string $key, $default = null ) {
		$settings = self::get_all();
		foreach ( $settings as $group_settings ) {
			if ( is_array( $group_settings ) && array_key_exists( $key, $group_settings ) ) {
				return $group_settings[ $key ];
			}
		}
		return self::registered_default( $key, $default );
	}
	/**
	 * Set a setting value by its key.
	 *
	 * @param string $key   The setting key.
	 * @param mixed  $value The value to set.
	 * @return bool True on success, false on failure.
	 */
	public static function set( string $key, $value ): bool {
		$group            = self::group_for_key( $key );
		$settings         = self::get_group( $group ) ?? array();
		$settings[ $key ] = $value;
		return self::set_group( $group, $settings );
	}

	/**
	 * Delete a setting by its key.
	 *
	 * @param string $key The setting key.
	 * @return bool True on success, false on failure.
	 */
	public static function delete( string $key ): bool {
		$group    = self::group_for_key( $key );
		$settings = self::get_group( $group );
		if ( ! is_array( $settings ) || ! array_key_exists( $key, $settings ) ) {
			return false;
		}
		unset( $settings[ $key ] );
		return self::set_group( $group, $settings );
	}

	/**
	 * Check if a setting exists by its key.
	 *
	 * @param string $key The setting key.
	 * @return bool True if the setting exists, false otherwise.
	 */
	public static function has( string $key ): bool {
		foreach ( self::get_all() as $settings ) {
			if ( is_array( $settings ) && array_key_exists( $key, $settings ) ) {
				return true;
			}
		}

		return false;
	}
	/**
	 * Get all settings grouped by their logical group.
	 *
	 * @return array An associative array of all settings.
	 */
	public static function get_all(): array {
		global $wpdb;

		if ( ! self::table_exists() ) {
			return array();
		}

		$rows = $wpdb->get_results( 'SELECT setting_group, setting_value FROM ' . self::table_name(), ARRAY_A );
		$rows = is_array( $rows ) ? $rows : array();

		$settings = array();
		foreach ( $rows as $row ) {
			$group              = self::logical_group( $row['setting_group'] );
			$settings[ $group ] = maybe_unserialize( $row['setting_value'] );
		}
		return $settings;
	}
	/**
	 * Get the default settings.
	 *
	 * @return array An associative array of default settings.
	 */
	public static function defaults(): array {
		return array(
			'general' => array(
				'entity_type'                        => 'individual',
				'licence_name'                       => '',
				'country'                            => '',
				'currency'                           => 'GBP',
				'licence_prefix'                     => '',
				'licence_usage'                      => array(),
				'renewal_policy_mode'                => 'default',
				'renewal_policy_page'                => 0,
				'licence_pattern_type'               => 'standard',
				'licence_pattern_format'             => 'alphanumeric',
				'exclude_ambiguous_characters'      => array(),
				'default_exclude_ambiguous_characters' => array(),
				'pattern_letter_case'                => 'uppercase',
				'pattern_separator'                  => '-',
				'custom_pattern'                     => '',
			),
			'setup'   => array(
				'first_install_complete'    => false,
				'onboarding_steps_complete' => 0,
			),
			'access'  => array(
				'create_licence_types'            => array( 'manage_options' ),
				'write_licence_type_variants'     => array( 'manage_options' ),
				'view_analytics'                  => array( 'manage_options' ),
				'manage_plugins'                  => array( 'manage_options' ),
				'issue_licences'                  => array( 'manage_options' ),
				'revoke_licences'                 => array( 'manage_options' ),
				'export_data'                     => array( 'manage_options' ),
				'review_security'                 => array( 'manage_options' ),
			),
			'plugins' => array(),
			'billing' => array(
				'billing_name'      => '',
				'billing_address_1' => '',
				'billing_address_2' => '',
				'town'              => '',
				'county_state'      => '',
				'country'           => '',
				'vat_number'        => '',
				'email_address'     => '',
				'phone_number'      => '',
				'invoice_prefix'    => 'INV-',
				'invoice_logo'      => '',
				'invoice_style'     => '',
			),
			'tools'   => array(
				'debug_logging'   => false,
				'console_logging' => false,
			),
		);
	}
	/**
	 * Get a settings group by its logical group name.
	 *
	 * @param string $group The logical group name.
	 * @return array|null The settings array if found, null otherwise.
	 */
	public static function get_group( string $group ): ?array {
		global $wpdb;

		if ( ! self::table_exists() ) {
			return null;
		}

		$value    = $wpdb->get_var( $wpdb->prepare( 'SELECT setting_value FROM ' . self::table_name() . ' WHERE setting_group = %s', self::storage_group( $group ) ) );
		$settings = $value === null ? null : maybe_unserialize( $value );
		return is_array( $settings ) ? $settings : null;
	}
	/**
	 * Set a settings group by its logical group name.
	 *
	 * @param string $group    The logical group name.
	 * @param array  $settings The settings array to store.
	 * @return bool True on success, false on failure.
	 */
	public static function set_group( string $group, array $settings ): bool {
		global $wpdb;
		return false !== $wpdb->replace(
			self::table_name(),
			array(
				'setting_group' => self::storage_group( $group ),
				'setting_value' => maybe_serialize( $settings ),
				'autoload'      => 'yes',
				'updated_at'    => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s' )
		);
	}
	/**
	 * Register a settings group with default values.
	 *
	 * @param string $group    The logical group name.
	 * @param array  $defaults The default settings for the group.
	 * @return bool True on success, false on failure.
	 */
	public static function register_group( string $group, array $defaults = array() ): bool {
		$group = self::normalize_group( $group );
		if ( '' === $group ) {
			return false;
		}

		self::$registered_groups[ $group ] = array_merge( self::$registered_groups[ $group ] ?? array(), $defaults );
		foreach ( $defaults as $key => $default ) {
			$key = sanitize_key( (string) $key );
			if ( '' !== $key ) {
				self::$registered_keys[ $key ] = $group;
			}
		}
		return true;
	}
	/**
	 * Register a single setting key with a default value under a specific group.
	 *
	 * @param string $key     The setting key.
	 * @param string $group   The logical group name.
	 * @param mixed  $default The default value for the setting.
	 * @return bool True on success, false on failure.
	 */
	public static function register_key( string $key, string $group, $default = null ): bool {
		$key = sanitize_key( $key );
		if ( '' === $key || ! self::register_group( $group ) ) {
			return false;
		}

		$group                                     = self::normalize_group( $group );
		self::$registered_keys[ $key ]             = $group;
		self::$registered_groups[ $group ][ $key ] = $default;
		return true;
	}
	/**
	 * Get the storage group name for a logical group.
	 *
	 * @param string $group The logical group name.
	 * @return string The storage group name.
	 */
	private static function storage_group( string $group ): string {
		$group = self::normalize_group( $group );
		return str_starts_with( $group, 'licencepress_' ) ? $group : 'licencepress_' . $group;
	}
	/**
	 * Get the logical group name from a storage group name.
	 *
	 * @param string $group The storage group name.
	 * @return string The logical group name.
	 */
	private static function logical_group( string $group ): string {
		$group = str_starts_with( $group, 'licencepress_' ) ? substr( $group, 13 ) : $group;
		return $group;
	}
	/**
	 * Get the legacy settings group from the database.
	 *
	 * @param string $group The logical group name.
	 * @return array|null The settings array if found, null otherwise.
	 */
	private static function get_legacy_group( string $group ): ?array {
		global $wpdb;

		if ( ! self::table_exists() ) {
			return null;
		}

		$value = $wpdb->get_var( $wpdb->prepare( 'SELECT setting_value FROM ' . self::table_name() . ' WHERE setting_group = %s', sanitize_key( $group ) ) );
		return $value === null ? null : maybe_unserialize( $value );
	}

	/**
	 * Check if the legacy settings table exists in the database.
	 *
	 * @return bool True if the table exists, false otherwise.
	 */
	private static function table_exists(): bool {
		global $wpdb;

		if ( ! isset( $wpdb ) || ! is_object( $wpdb ) || ! method_exists( $wpdb, 'get_var' ) ) {
			return false;
		}

		if ( ! method_exists( $wpdb, 'prepare' ) ) {
			return false;
		}

		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', self::table_name() );
		$table = $wpdb->get_var( $query );

		return is_string( $table ) && '' !== $table;
	}
	/**
	 * Delete a legacy settings group from the database.
	 *
	 * @param string $group The logical group name.
	 * @return void
	 */
	private static function delete_legacy_group( string $group ): void {
		global $wpdb;
		$wpdb->delete( self::table_name(), array( 'setting_group' => sanitize_key( $group ) ), array( '%s' ) );
	}
	/**
	 * Determine the settings group for a given key.
	 *
	 * @param string $key The setting key.
	 * @return string The logical group name the key belongs to.
	 */
	private static function group_for_key( string $key ): string {
		$key = sanitize_key( $key );
		if ( isset( self::$registered_keys[ $key ] ) ) {
			return self::$registered_keys[ $key ];
		}
		if ( in_array( $key, array( 'first_install_complete', 'onboarding_steps_complete' ), true ) ) {
			return 'setup';
		}
		if ( in_array( $key, array( 'create_licence_types', 'write_licence_type_variants', 'view_analytics', 'manage_plugins', 'issue_licences', 'revoke_licences', 'export_data', 'review_security' ), true ) ) {
			return 'access';
		}
		if ( in_array(
			$key,
			array(
				'billing_name',
				'billing_address_1',
				'billing_address_2',
				'town',
				'county_state',
				'country',
				'vat_number',
				'email_address',
				'phone_number',
				'invoice_prefix',
				'invoice_logo',
				'invoice_style',
			),
			true
		) || str_contains( $key, 'billing_' ) || str_contains( $key, 'invoice_' ) ) {
			return 'billing';
		}
		if ( str_contains( $key, 'plugin' ) || in_array( $key, array( 'licencepress_plugin_directory', 'mod_plugin_auto_activate' ), true ) ) {
			return 'plugins';
		}
		if ( str_contains( $key, 'access' ) ) {
			return 'access';
		}
		if ( str_contains( $key, 'tool' ) ) {
			return 'tools';
		}
		return 'general';
	}

	/**
	 * Return core and extension defaults for activation and fallback reads.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private static function registered_defaults(): array {
		$defaults = self::defaults();
		foreach ( self::$registered_groups as $group => $settings ) {
			$defaults[ $group ] = array_merge( $defaults[ $group ] ?? array(), $settings );
		}

		return $defaults;
	}
	/**
	 * Get the registered default value for a specific setting key.
	 *
	 * @param string $key The setting key.
	 * @param mixed  $fallback The fallback value if the key is not registered.
	 * @return mixed The registered default value or the fallback.
	 */
	private static function registered_default( string $key, $fallback ) {
		$key = sanitize_key( $key );
		foreach ( self::registered_defaults() as $settings ) {
			if ( array_key_exists( $key, $settings ) ) {
				return $settings[ $key ];
			}
		}

		return $fallback;
	}

	/**
	 * Normalize a storage group name to its logical group name.
	 *
	 * @param string $group The storage group name.
	 * @return string The logical group name.
	 */
	private static function normalize_group( string $group ): string {
		$group = sanitize_key( $group );
		if ( str_starts_with( $group, 'licencepress_' ) ) {
			return substr( $group, 13 );
		}
		return $group;
	}
	/**
	 * Check if the settings table is ready for use.
	 *
	 * @return bool True if the table exists and is ready, false otherwise.
	 */
	private static function table_ready(): bool {
		global $wpdb;

		if ( ! isset( $wpdb ) || ! is_object( $wpdb ) ) {
			return false;
		}

		if ( ! method_exists( $wpdb, 'get_var' ) ) {
			return false;
		}

		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', self::table_name() );
		$table = $wpdb->get_var( $query );

		return null !== $table && '' !== (string) $table;
	}
}
