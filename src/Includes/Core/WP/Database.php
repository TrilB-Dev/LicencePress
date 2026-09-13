<?php
/**
 * Database class for managing custom LicencePress database tables.
 *
 * @package LicencePress\Includes\Core\WP
 */
namespace LicencePress\Includes\Core\WP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Database {
	/**
	 * Registered custom database tables and their schema callbacks.
	 *
	 * @var array<string, callable>
	 */
	private static array $registered_plugin_tables = array();
	/**
	 * Registered core database tables and their schema callbacks.
	 *
	 * @var array<string, callable>
	 */
	private static array $registered_core_tables = array();

	/**
	 * Normalize a table slug before registering or resolving it.
	 *
	 * @param string $table Unprefixed table suffix.
	 * @return string The normalized table suffix.
	 */
	private static function normalize_table_key( string $table ): string {
		$table = sanitize_key( $table );
		return '' === $table ? '' : $table;
	}

	/**
	 * Register a core table schema for the next installation/update.
	 *
	 * The callback receives the fully prefixed table name and charset/collation
	 * string, and must return a dbDelta-compatible CREATE TABLE statement.
	 *
	 * @param string   $table  Unprefixed LicencePress table suffix.
	 * @param callable $schema Schema callback.
	 * @return bool Whether the table was registered.
	 */
	public static function register_core_table( string $table, callable $schema ): bool {
		$table = self::normalize_table_key( $table );
		if ( '' === $table ) {
			return false;
		}

		self::$registered_core_tables[ $table ] = $schema;
		return true;
	}

	/**
	 * Register an extension table schema for the next installation/update.
	 *
	 * @param string   $table  Unprefixed LicencePress table suffix.
	 * @param callable $schema Schema callback.
	 * @return bool Whether the table was registered.
	 */
	public static function register_plugin_table( string $table, callable $schema ): bool {
		$table = self::normalize_table_key( $table );
		if ( '' === $table ) {
			return false;
		}

		self::$registered_plugin_tables[ $table ] = $schema;
		return true;
	}

	/**
	 * Get the registered core table schema callbacks.
	 *
	 * @return array<string, callable>
	 */
	public static function get_core_tables(): array {
		return self::$registered_core_tables;
	}

	/**
	 * Get the registered plugin table schema callbacks.
	 *
	 * @return array<string, callable>
	 */
	public static function get_plugin_tables(): array {
		return self::$registered_plugin_tables;
	}

	/**
	 * Determine whether the requested table exists.
	 *
	 * @param string $table Unprefixed table suffix.
	 * @return bool True if the table exists.
	 */
	public static function table_exists( string $table ): bool {
		global $wpdb;

		$table_name = self::table_name( $table );
		return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) === $table_name;
	}

	/**
	 * Create or update a single table using the registered schema callback.
	 *
	 * @param string $table Unprefixed table suffix.
	 * @return bool Whether the table was created or updated.
	 */
	public static function create_table( string $table ): bool {
		global $wpdb;

		$table = self::normalize_table_key( $table );
		if ( '' === $table ) {
			return false;
		}

		$schema = self::$registered_core_tables[ $table ] ?? self::$registered_plugin_tables[ $table ] ?? null;
		if ( ! is_callable( $schema ) ) {
			return false;
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$statement = call_user_func( $schema, self::table_name( $table ), $wpdb->get_charset_collate() );
		if ( ! is_string( $statement ) || '' === trim( $statement ) ) {
			return false;
		}

		dbDelta( $statement );
		return true;
	}

	/**
	 * Empty all rows from a table.
	 *
	 * @param string $table Unprefixed table suffix.
	 * @return bool True if the content was dropped.
	 */
	public static function drop_table_contents( string $table ): bool {
		global $wpdb;

		if ( ! self::table_exists( $table ) ) {
			return false;
		}

		return false !== $wpdb->query( 'TRUNCATE TABLE ' . self::table_name( $table ) );
	}

	/**
	 * Alias for dropping table contents.
	 *
	 * @param string $table Unprefixed table suffix.
	 * @return bool True if the content was dropped.
	 */
	public static function truncate_table( string $table ): bool {
		return self::drop_table_contents( $table );
	}

	/**
	 * Drop a LicencePress table entirely.
	 *
	 * @param string $table Unprefixed table suffix.
	 * @return bool True if the table was deleted.
	 */
	public static function delete_table( string $table ): bool {
		global $wpdb;

		if ( ! self::table_exists( $table ) ) {
			return false;
		}

		return false !== $wpdb->query( 'DROP TABLE IF EXISTS ' . self::table_name( $table ) );
	}

	/**
	 * Alias for dropping a table.
	 *
	 * @param string $table Unprefixed table suffix.
	 * @return bool True if the table was deleted.
	 */
	public static function drop_table( string $table ): bool {
		return self::delete_table( $table );
	}

	/**
	 * Install or update all LicencePress Core & Plugin tables.
	 *
	 * @return void
	 */
	public static function install(): void {
		foreach ( self::$registered_core_tables as $table => $schema ) {
			self::create_table( $table );
		}

		foreach ( self::$registered_plugin_tables as $table => $schema ) {
			self::create_table( $table );
		}

		update_option( 'licencepress_db_version', defined( 'LICENCEPRESS_VERSION' ) ? LICENCEPRESS_VERSION : '1.0.0' );
	}

	/**
	 * Return a prefixed LicencePress table name.
	 *
	 * @param string $table Unprefixed table suffix.
	 * @return string Full table name.
	 */
	public static function table_name( string $table ): string {
		global $wpdb;
		return $wpdb->prefix . 'licencepress_' . self::normalize_table_key( $table );
	}
}
