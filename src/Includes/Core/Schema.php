<?php
/**
 * Schema class for managing core and extension database schema definitions.
 *
 * @package LicencePress\Includes\Core
 * @since 1.0.0
 */
namespace LicencePress\Includes\Core;

use LicencePress\Includes\Core\WP\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Schema {
    /**
     * Register all core database tables.
     *
     * @return void
     */
    public static function register_tables(): void {
        self::settings_table();
        self::analytics_table();
        self::licence_table();
        self::logs_table();
    }
    /**
     * Register the settings table schema.
     *
     * @return string
     */
    public static function settings_table(): string {
        Database::register_core_table(
            'settings',
            static function ( string $table_name, string $charset ) {
                return "CREATE TABLE {$table_name} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                setting_key varchar(120) NOT NULL,
                setting_value longtext DEFAULT NULL,
                created_at datetime DEFAULT NULL,
                updated_at datetime DEFAULT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY setting_key (setting_key)
            ) {$charset};";
            }
        );

        return "settings";
    }
    /**
     * Register the analytics table schema.
     *
     * @return string
     */
    public static function analytics_table(): string {
        Database::register_core_table(
            'analytics',
            static function ( string $table_name, string $charset ) {
                return "CREATE TABLE {$table_name} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                event_key varchar(120) NOT NULL,
                event_value longtext DEFAULT NULL,
                created_at datetime DEFAULT NULL,
                updated_at datetime DEFAULT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY event_key (event_key)
            ) {$charset};";
            }
        );

        return "analytics";
    }
    /**
     * Register the licence table schema.
     *
     * @return string
     */
	public static function licence_table(): string {
		Database::register_core_table(
			'licence',
			static function ( string $table_name, string $charset ) {
				return "CREATE TABLE {$table_name} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                licence_type_id bigint(20) unsigned NOT NULL DEFAULT 0,
                licence_type_variant_id bigint(20) unsigned NOT NULL DEFAULT 0,
                user_id bigint(20) unsigned NOT NULL DEFAULT 0,
                creation_date datetime NOT NULL,
                licence_status tinyint(1) NOT NULL DEFAULT 2,
                licence_use longtext DEFAULT NULL,
                licence longtext NOT NULL,
                product_id varchar(120) DEFAULT NULL,
                customer_id varchar(120) DEFAULT NULL,
                token_hash varchar(128) DEFAULT NULL,
                site_hash varchar(128) DEFAULT NULL,
                payload_encrypted longtext DEFAULT NULL,
                payload longtext DEFAULT NULL,
                status varchar(32) DEFAULT 'active',
                issued_at datetime DEFAULT NULL,
                expires_at datetime DEFAULT NULL,
                created_at datetime DEFAULT NULL,
                updated_at datetime DEFAULT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY token_hash (token_hash),
                KEY licence_type_id (licence_type_id),
                KEY licence_type_variant_id (licence_type_variant_id),
                KEY user_id (user_id),
                KEY licence_status (licence_status),
                KEY product_id (product_id),
                KEY customer_id (customer_id),
                KEY status (status),
                KEY expires_at (expires_at)
            ) {$charset};";
			}
		);

		return "licence";
	}
    /**
     * Register the logs table schema.
     *
     * @return string
     */
    public static function logs_table(): string {
        Database::register_core_table(
            'logs',
            static function ( string $table_name, string $charset ) {
                return "CREATE TABLE {$table_name} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                log_level varchar(32) NOT NULL,
                message longtext NOT NULL,
                context longtext DEFAULT NULL,
                created_at datetime DEFAULT NULL,
                updated_at datetime DEFAULT NULL,
                PRIMARY KEY  (id),
                KEY log_level (log_level),
                KEY created_at (created_at)
            ) {$charset};";
            }
        );

        return "logs";
    }
}