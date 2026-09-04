<?php
/**
 * Stores and retrieves secure licence records.
 *
 * @package LicencePress
 */

namespace LicencePress\Includes\Licence;

use LicencePress\Includes\Core\WP\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LicenceRepository {
	public static function register_schema(): void {
		Database::register_table(
			'licences',
			static function ( string $table_name, string $charset ) {
				return "CREATE TABLE {$table_name} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                product_id varchar(120) NOT NULL,
                customer_id varchar(120) NOT NULL,
                token_hash varchar(128) NOT NULL,
                site_hash varchar(128) DEFAULT NULL,
                payload_encrypted longtext NOT NULL,
                payload longtext DEFAULT NULL,
                status varchar(32) NOT NULL DEFAULT 'active',
                issued_at datetime NOT NULL,
                expires_at datetime DEFAULT NULL,
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY token_hash (token_hash),
                KEY product_id (product_id),
                KEY customer_id (customer_id),
                KEY status (status),
                KEY expires_at (expires_at)
            ) {$charset};";
			}
		);
	}

	public static function table_name(): string {
		return Database::table_name( 'licences' );
	}

	public static function create( array $record ): int {
		global $wpdb;

		$now     = gmdate( 'Y-m-d H:i:s' );
		$payload = array(
			'product_id'        => (string) ( $record['product_id'] ?? '' ),
			'customer_id'       => (string) ( $record['customer_id'] ?? '' ),
			'token_hash'        => (string) ( $record['token_hash'] ?? '' ),
			'site_hash'         => isset( $record['site_hash'] ) ? (string) $record['site_hash'] : '',
			'payload_encrypted' => (string) ( $record['payload_encrypted'] ?? '' ),
			'payload'           => isset( $record['payload'] ) ? maybe_serialize( $record['payload'] ) : '',
			'status'            => (string) ( $record['status'] ?? 'active' ),
			'issued_at'         => isset( $record['issued_at'] ) ? gmdate( 'Y-m-d H:i:s', (int) $record['issued_at'] ) : $now,
			'expires_at'        => isset( $record['expires_at'] ) ? gmdate( 'Y-m-d H:i:s', (int) $record['expires_at'] ) : null,
			'created_at'        => $now,
			'updated_at'        => $now,
		);

		$wpdb->insert(
			self::table_name(),
			$payload,
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return (int) $wpdb->insert_id;
	}

	public static function find_by_token( string $token ): ?array {
		$runtime_key = KeyManager::runtime_key();
		if ( null === $runtime_key || '' === $runtime_key ) {
			return null;
		}

		$hash = hash_hmac( 'sha256', strtoupper( trim( $token ) ), $runtime_key );
		return self::find_by_hash( $hash );
	}

	public static function find_by_hash( string $token_hash ): ?array {
		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM ' . self::table_name() . ' WHERE token_hash = %s LIMIT 1', $token_hash ),
			ARRAY_A
		);

		if ( ! is_array( $row ) ) {
			return null;
		}

		if ( isset( $row['payload'] ) ) {
			$row['payload'] = maybe_unserialize( $row['payload'] );
		}

		if ( ! is_array( $row['payload'] ?? null ) ) {
			$row['payload'] = array();
		}

		return $row;
	}

	public static function update_status( string $token, string $status ): bool {
		$record = self::find_by_token( $token );
		if ( null === $record ) {
			return false;
		}

		global $wpdb;

		return false !== $wpdb->update(
			self::table_name(),
			array(
				'status'     => $status,
				'updated_at' => gmdate( 'Y-m-d H:i:s' ),
			),
			array( 'id' => (int) $record['id'] ),
			array( '%s', '%s' ),
			array( '%d' )
		);
	}

	public static function revoke( string $token ): bool {
		return self::update_status( $token, 'revoked' );
	}

	public static function list_by_customer( string $customer_id ): array {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare( 'SELECT * FROM ' . self::table_name() . ' WHERE customer_id = %s ORDER BY created_at DESC', $customer_id ),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}

	public static function count_active(): int {
		global $wpdb;

		$count = $wpdb->get_var(
			'SELECT COUNT(*) FROM ' . self::table_name() . " WHERE status = 'active'"
		);

		return (int) $count;
	}

	public static function count_expiring_soon( int $days = 30 ): int {
		global $wpdb;

		$threshold = gmdate( 'Y-m-d H:i:s', time() + ( $days * DAY_IN_SECONDS ) );
		$count     = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM ' . self::table_name() . ' WHERE status = %s AND expires_at IS NOT NULL AND expires_at <= %s',
				'active',
				$threshold
			)
		);

		return (int) $count;
	}

	public static function count_revoked(): int {
		global $wpdb;

		$count = $wpdb->get_var(
			'SELECT COUNT(*) FROM ' . self::table_name() . " WHERE status = 'revoked'"
		);

		return (int) $count;
	}

	public static function count_customers(): int {
		global $wpdb;

		$count = $wpdb->get_var(
			'SELECT COUNT(DISTINCT customer_id) FROM ' . self::table_name()
		);

		return (int) $count;
	}
}
