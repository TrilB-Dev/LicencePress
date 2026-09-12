<?php
/**
 * Settings class for managing plugin settings.
 *
 * @package LicencePress\Includes\Settings
 */
namespace LicencePress\Includes\Settings;

use LicencePress\Includes\Functions\Helpers\SanitizationHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Settings {
	/**
	 * Logical group names for settings.
	 * 
	 * These constants represent the logical group names used throughout the plugin.
	 * @since 1.0.0
	 */
	public const GENERAL = 'general';
	/**
	 * Access settings group.
	 *
	 * @since 1.0.0
	 */
	public const ACCESS  = 'access';
	/**
	 * Tools settings group.
	 *
	 * @since 1.0.0
	 */
	public const TOOLS   = 'tools';
	/**
	 * Get the value of a specific setting key.
	 *
	 * @param string $key The setting key.
	 * @param mixed  $default The default value if the setting is not found.
	 * @return mixed The value of the setting or the default.
	 * @since 1.0.0
	 */
	public static function get( string $key, $default = null ) {
		return SettingsManager::get( $key, $default );
	}
	/**
	 * Get the value of a specific setting key as a string.
	 *
	 * @param string $key The setting key.
	 * @param string $default The default value if the setting is not found.
	 * @return string The value of the setting or the default.
	 * @since 1.0.0
	 */
	public static function get_string( string $key, string $default = '' ): string {
		return SanitizationHelper::text( self::get( $key, $default ), $default );
	}

	/**
	 * Get the value of a specific setting key as a sanitized key.
	 *
	 * @param string $key The setting key.
	 * @param string $default The default value if the setting is not found.
	 * @return string The value of the setting or the default.
	 * @since 1.0.0
	 */
	public static function get_key( string $key, string $default = '' ): string {
		return SanitizationHelper::key( self::get( $key, $default ), $default );
	}

	/**
	 * Get the value of a specific setting key as a sanitized slug.
	 *
	 * @param string $key The setting key.
	 * @param string $default The default value if the setting is not found.
	 * @return string The value of the setting or the default.
	 * @since 1.0.0
	 */
	public static function get_slug( string $key, string $default = '' ): string {
		return SanitizationHelper::slug( self::get( $key, $default ), $default );
	}

	/**
	 * Get the value of a specific setting key as an integer.
	 *
	 * @param string $key The setting key.
	 * @param int $default The default value if the setting is not found.
	 * @return int The value of the setting or the default.
	 * @since 1.0.0
	 */
	public static function get_int( string $key, int $default = 0 ): int {
		return SanitizationHelper::integer( self::get( $key, $default ), $default );
	}

	/**
	 * Get the value of a specific setting key as a boolean.
	 *
	 * @param string $key The setting key.
	 * @param bool $default The default value if the setting is not found.
	 * @return bool The value of the setting or the default.
	 * @since 1.0.0
	 */
	public static function get_bool( string $key, bool $default = false ): bool {
		$value = self::get( $key, $default );
		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( ! is_scalar( $value ) ) {
			return $default;
		}

		$parsed = filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
		return null === $parsed ? $default : $parsed;
	}

	/**
	 * Return the core settings group names used by the plugin.
	 *
	 * @return array<int, string>
	 */
	public static function core_groups(): array {
		return array( self::GENERAL, 'billing', self::ACCESS, self::TOOLS, 'setup', 'plugins' );
	}

	/**
	 * Restore a list of settings groups to their factory defaults.
	 *
	 * @param array<int, string> $groups The groups to reset.
	 * @return bool True if every requested group was reset.
	 */
	public static function reset_groups( array $groups ): bool {
		$defaults = SettingsManager::defaults();
		$success  = true;

		foreach ( $groups as $group ) {
			$normalized = self::normalize_group_name( $group );
			if ( '' === $normalized ) {
				$success = false;
				continue;
			}

			$values = $defaults[ $normalized ] ?? array();
			if ( ! SettingsManager::set_group( $normalized, $values ) ) {
				$success = false;
			}
		}

		return $success;
	}

	/**
	 * Restore all core settings groups to their factory defaults.
	 *
	 * @return bool True when the reset succeeds.
	 */
	public static function reset_all(): bool {
		return self::reset_groups( self::core_groups() );
	}

	/**
	 * Normalize a raw settings group name.
	 *
	 * @param string $group The requested group name.
	 * @return string The normalized group name.
	 */
	private static function normalize_group_name( string $group ): string {
		$normalized = sanitize_key( $group );
		if ( '' === $normalized ) {
			return '';
		}

		if ( str_starts_with( $normalized, 'licencepress_' ) ) {
			return substr( $normalized, 13 );
		}

		return $normalized;
	}
	/**
	 * Set the value of a specific setting key.
	 *
	 * @param string $key The setting key.
	 * @param mixed $value The value to set.
	 * @return bool True on success, false on failure.
	 * @since 1.0.0
	 */
	public static function set( string $key, $value ): bool {
		return SettingsManager::set( $key, $value );
	}
	/**
	 * Delete a specific setting key.
	 *
	 * @param string $key The setting key.
	 * @return bool True on success, false on failure.
	 * @since 1.0.0
	 */
	public static function delete( string $key ): bool {
		return SettingsManager::delete( $key );
	}
	/**
	 * Check if a specific setting key exists.
	 *
	 * @param string $key The setting key.
	 * @return bool True if the setting exists, false otherwise.
	 * @since 1.0.0
	 */
	public static function has( string $key ): bool {
		return SettingsManager::has( $key );
	}
	/**
	 * Get all settings within a specific group.
	 *
	 * @param string $group The group name.
	 * @param array|null $default The default value if the group is not found.
	 * @return array|null The settings of the group or the default.
	 * @since 1.0.0
	 */
	public static function get_group( string $group, ?array $default = null ): ?array {
		return SettingsManager::get_group( $group ) ?? $default;
	}
	/**
	 * Set multiple settings within a specific group.
	 *
	 * @param string $group The group name.
	 * @param array $settings The settings to set.
	 * @return bool True on success, false on failure.
	 * @since 1.0.0
	 */
	public static function set_group( string $group, array $settings ): bool {
		return SettingsManager::set_group( $group, $settings );
	}
	/**
	 * Register a new settings group with default values.
	 *
	 * @param string $group The group name.
	 * @param array $defaults The default settings for the group.
	 * @return bool True on success, false on failure.
	 * @since 1.0.0
	 */
	public static function register_group( string $group, array $defaults = array() ): bool {
		return SettingsManager::register_group( $group, $defaults );
	}
	/**
	 * Register a new setting key within a specific group.
	 *
	 * @param string $key The setting key.
	 * @param string $group The group name.
	 * @param mixed $default The default value for the setting.
	 * @return bool True on success, false on failure.
	 * @since 1.0.0
	 */
	public static function register_key( string $key, string $group, $default = null ): bool {
		return SettingsManager::register_key( $key, $group, $default );
	}
	/**
	 * Get all settings.
	 *
	 * @return array An associative array of all settings.
	 * @since 1.0.0
	 */
	public static function get_all(): array {
		return SettingsManager::get_all();
	}
}
