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
	/**
	 * Registers the database schema for the licence table.
	 *
	 * @return void
	 */
	public static function register_schema(): void {
		Database::register_table(
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
	}
	/**
	 * Retrieves the table name for the licence table.
	 *
	 * @return string The table name.
	 */
	public static function table_name(): string {
		return Database::table_name( 'licence' );
	}
	/**
	 * Creates a new licence record in the database.
	 *
	 * @param array $record The licence record data.
	 * @return int The ID of the newly created record.
	 */
	public static function create( array $record ): int {
		global $wpdb;

		$now     = gmdate( 'Y-m-d H:i:s' );
		$payload = array(
			'licence_type_id'         => isset( $record['licence_type_id'] ) ? (int) $record['licence_type_id'] : 0,
			'licence_type_variant_id' => isset( $record['licence_type_variant_id'] ) ? (int) $record['licence_type_variant_id'] : 0,
			'user_id'                => isset( $record['user_id'] ) ? (int) $record['user_id'] : 0,
			'creation_date'          => isset( $record['creation_date'] ) ? (string) $record['creation_date'] : $now,
			'licence_status'         => isset( $record['licence_status'] ) ? (int) $record['licence_status'] : 2,
			'licence_use'            => isset( $record['licence_use'] ) ? ( is_string( $record['licence_use'] ) ? $record['licence_use'] : wp_json_encode( $record['licence_use'] ) ) : '[]',
			'licence'                => (string) ( $record['licence'] ?? $record['payload_encrypted'] ?? '' ),
			'product_id'             => (string) ( $record['product_id'] ?? '' ),
			'customer_id'            => (string) ( $record['customer_id'] ?? '' ),
			'token_hash'             => (string) ( $record['token_hash'] ?? '' ),
			'site_hash'              => isset( $record['site_hash'] ) ? (string) $record['site_hash'] : '',
			'payload_encrypted'      => (string) ( $record['payload_encrypted'] ?? '' ),
			'payload'                => isset( $record['payload'] ) ? maybe_serialize( $record['payload'] ) : '',
			'status'                 => (string) ( $record['status'] ?? 'active' ),
			'issued_at'              => isset( $record['issued_at'] ) ? gmdate( 'Y-m-d H:i:s', (int) $record['issued_at'] ) : $now,
			'expires_at'             => isset( $record['expires_at'] ) ? gmdate( 'Y-m-d H:i:s', (int) $record['expires_at'] ) : null,
			'created_at'             => $now,
			'updated_at'             => $now,
		);

		$wpdb->insert(
			self::table_name(),
			$payload,
			array( '%d', '%d', '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return (int) $wpdb->insert_id;
	}
	/**
	 * Finds a licence record by its token.
	 *
	 * @param string $token The licence token.
	 * @return array|null The licence record if found, null otherwise.
	 */
	public static function find_by_token( string $token ): ?array {
		$runtime_key = KeyManager::runtime_key();
		if ( null === $runtime_key || '' === $runtime_key ) {
			return null;
		}

		$hash = hash_hmac( 'sha256', strtoupper( trim( $token ) ), $runtime_key );
		return self::find_by_hash( $hash );
	}

	/**
	 * Finds a licence record by its token hash.
	 *
	 * @param string $token_hash The hash of the licence token.
	 * @return array|null The licence record if found, null otherwise.
	 */
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
	/**
	 * Updates the status of a licence record.
	 *
	 * @param string $token The licence token.
	 * @param string $status The new status for the licence.
	 * @return bool True if the update was successful, false otherwise.
	 */
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
	/**
	 * Revokes a licence by setting its status to 'revoked'.
	 *
	 * @param string $token The licence token to revoke.
	 * @return bool True if the licence was successfully revoked, false otherwise.
	 */
	public static function list_by_customer( string $customer_id ): array {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare( 'SELECT * FROM ' . self::table_name() . ' WHERE customer_id = %s ORDER BY created_at DESC', $customer_id ),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}
	/**
	 * Lists all licences for a given customer.
	 *
	 * @param string $customer_id The ID of the customer.
	 * @return array The array of licence records for the customer.
	 */
	public static function count_active(): int {
		global $wpdb;

		$count = $wpdb->get_var(
			'SELECT COUNT(*) FROM ' . self::table_name() . " WHERE status = 'active'"
		);

		return (int) $count;
	}
	/**
	 * Counts the number of active licences.
	 *
	 * @return int The count of active licences.
	 */
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
	/**
	 * Counts the number of licences that are expiring soon.
	 *
	 * @param int $days The number of days to consider as "soon".
	 * @return int The count of licences expiring soon.
	 */
	public static function count_revoked(): int {
		global $wpdb;

		$count = $wpdb->get_var(
			'SELECT COUNT(*) FROM ' . self::table_name() . " WHERE status = 'revoked'"
		);

		return (int) $count;
	}
	/**
	 * Counts the number of revoked licences.
	 *
	 * @return int The count of revoked licences.
	 */
	public static function count_customers(): int {
		global $wpdb;

		$count = $wpdb->get_var(
			'SELECT COUNT(DISTINCT customer_id) FROM ' . self::table_name()
		);

		return (int) $count;
	}
}
