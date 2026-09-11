<?php
/**
 * High-level licence type management for LicencePress.
 *
 * @package LicencePress
 */
namespace LicencePress\Includes\Licence;

use LicencePress\Includes\Core\WP\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LicenceTypeManager {
	/**
	 * Registers the database schema for the licence type table.
	 *
	 * @since 1.0.0
	 */
	public static function register_schema(): void {
		Database::register_table(
			'licence_type',
			static function ( string $table_name, string $charset ) {
				return "CREATE TABLE {$table_name} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                name varchar(200) NOT NULL,
                slug varchar(120) NOT NULL DEFAULT '',
                parent_id bigint(20) unsigned NOT NULL DEFAULT 0,
                is_variant tinyint(1) NOT NULL DEFAULT 0,
                is_retired tinyint(1) NOT NULL DEFAULT 0,
                retired_at datetime DEFAULT NULL,
                prefix varchar(32) NOT NULL DEFAULT '',
                suffix varchar(32) NOT NULL DEFAULT '',
                length int(11) NOT NULL DEFAULT 12,
                pattern varchar(120) NOT NULL DEFAULT 'prefix-segment',
                description longtext DEFAULT NULL,
                metadata longtext DEFAULT NULL,
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY parent_id (parent_id),
                KEY is_variant (is_variant),
                KEY is_retired (is_retired),
                KEY slug (slug),
                KEY name (name)
            ) {$charset};";
			}
		);
	}
	/**
	 * Retrieves the table name for the licence type table.
	 *
	 * @return string The table name.
	 */
	public static function table_name(): string {
		return Database::table_name( 'licence_type' );
	}
	/**
	 * Creates a new licence type.
	 *
	 * @param array $data The data for the new licence type.
	 * @return int The ID of the newly created licence type.
	 */
	public static function create_type( array $data ): int {
		global $wpdb;

		$now     = gmdate( 'Y-m-d H:i:s' );
		$slug    = self::normalize_slug( (string) ( $data['slug'] ?? '' ), (string) ( $data['name'] ?? '' ) );
		$payload = array(
			'name'        => (string) ( $data['name'] ?? '' ),
			'slug'        => $slug,
			'parent_id'   => ! empty( $data['parent_id'] ) ? (int) $data['parent_id'] : 0,
			'is_variant'  => ! empty( $data['is_variant'] ) ? 1 : 0,
			'is_retired'  => ! empty( $data['is_retired'] ) ? 1 : 0,
			'retired_at'  => ! empty( $data['is_retired'] ) ? $now : null,
			'prefix'      => (string) ( $data['prefix'] ?? '' ),
			'suffix'      => (string) ( $data['suffix'] ?? '' ),
			'length'      => max( 8, (int) ( $data['length'] ?? 12 ) ),
			'pattern'     => (string) ( $data['pattern'] ?? 'prefix-segment' ),
			'description' => isset( $data['description'] ) ? (string) $data['description'] : '',
			'metadata'    => self::serialize_metadata( $data ),
			'created_at'  => $now,
			'updated_at'  => $now,
		);

		$wpdb->insert(
			self::table_name(),
			$payload,
			array( '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s' )
		);

		return (int) $wpdb->insert_id;
	}
	/**
	 * Finds a licence type by its associated product ID.
	 *
	 * @param string $product_id The ID of the product.
	 * @return array|null The licence type record if found, null otherwise.
	 */
	public static function find_by_product_id( string $product_id ): ?array {
		global $wpdb;

		$slug = self::normalize_slug( $product_id, '' );
		if ( '' === $slug ) {
			return null;
		}

		$row = $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM ' . self::table_name() . ' WHERE slug = %s LIMIT 1', $slug ),
			ARRAY_A
		);

		return is_array( $row ) ? self::hydrate_metadata( $row ) : null;
	}
	/**
	 * Retrieves a licence type by its ID.
	 *
	 * @param int $id The ID of the licence type.
	 * @return array|null The licence type record if found, null otherwise.
	 */
	public static function get_type( int $id ): ?array {
		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM ' . self::table_name() . ' WHERE id = %d LIMIT 1', $id ),
			ARRAY_A
		);

		return is_array( $row ) ? self::hydrate_metadata( $row ) : null;
	}
	/**
	 * Retrieves all licence types.
	 *
	 * @return array The array of all licence type records.
	 */
	public static function get_types(): array {
		global $wpdb;

		$rows = $wpdb->get_results(
			'SELECT * FROM ' . self::table_name() . ' ORDER BY created_at DESC',
			ARRAY_A
		);

		if ( ! is_array( $rows ) ) {
			return array();
		}

		return array_map( array( self::class, 'hydrate_metadata' ), $rows );
	}
	/**
	 * Updates an existing licence type.
	 *
	 * @param int   $id The ID of the licence type to update.
	 * @param array $data The data to update the licence type with.
	 * @return bool True if the update was successful, false otherwise.
	 */
	public static function update_type( int $id, array $data ): bool {
		global $wpdb;

		$record = self::get_type( $id );
		if ( null === $record ) {
			return false;
		}

		$slug = array_key_exists( 'slug', $data ) ? self::normalize_slug( (string) $data['slug'], (string) ( $data['name'] ?? ( $record['name'] ?? '' ) ) ) : (string) ( $record['slug'] ?? '' );
		if ( '' === $slug ) {
			$slug = self::normalize_slug( (string) ( $record['name'] ?? 'licence-type' ), '' );
		}

		$metadata = self::merge_metadata( $record, $data );
		$payload  = array(
			'name'        => isset( $data['name'] ) ? (string) $data['name'] : (string) ( $record['name'] ?? '' ),
			'slug'        => $slug,
			'parent_id'   => isset( $data['parent_id'] ) ? (int) $data['parent_id'] : (int) ( $record['parent_id'] ?? 0 ),
			'is_variant'  => isset( $data['is_variant'] ) ? (int) $data['is_variant'] : (int) ( $record['is_variant'] ?? 0 ),
			'is_retired'  => array_key_exists( 'is_retired', $data ) ? (int) ! empty( $data['is_retired'] ) : (int) ( $record['is_retired'] ?? 0 ),
			'retired_at'  => array_key_exists( 'is_retired', $data ) && ! empty( $data['is_retired'] ) ? ( $record['retired_at'] ?? gmdate( 'Y-m-d H:i:s' ) ) : null,
			'prefix'      => isset( $data['prefix'] ) ? (string) $data['prefix'] : (string) ( $record['prefix'] ?? '' ),
			'suffix'      => isset( $data['suffix'] ) ? (string) $data['suffix'] : (string) ( $record['suffix'] ?? '' ),
			'length'      => isset( $data['length'] ) ? max( 8, (int) $data['length'] ) : (int) ( $record['length'] ?? 12 ),
			'pattern'     => isset( $data['pattern'] ) ? (string) $data['pattern'] : (string) ( $record['pattern'] ?? 'prefix-segment' ),
			'description' => array_key_exists( 'description', $data ) ? (string) $data['description'] : (string) ( $record['description'] ?? '' ),
			'metadata'    => self::serialize_metadata( $metadata ),
			'updated_at'  => gmdate( 'Y-m-d H:i:s' ),
		);

		$updated = $wpdb->update(
			self::table_name(),
			$payload,
			array( 'id' => $id ),
			array( '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);

		return false !== $updated;
	}
	/**
	 * Retires a licence type.
	 *
	 * @param int  $id The ID of the licence type to retire.
	 * @param bool $retired Whether to retire (true) or unretire (false) the licence type.
	 * @return bool True if the update was successful, false otherwise.
	 */
	public static function retire_type( int $id, bool $retired = true ): bool {
		global $wpdb;

		$record = self::get_type( $id );
		if ( null === $record ) {
			return false;
		}

		$payload = array(
			'is_retired' => $retired ? 1 : 0,
			'retired_at' => $retired ? ( $record['retired_at'] ?? gmdate( 'Y-m-d H:i:s' ) ) : null,
			'updated_at' => gmdate( 'Y-m-d H:i:s' ),
		);

		$updated = $wpdb->update(
			self::table_name(),
			$payload,
			array( 'id' => $id ),
			array( '%d', '%s', '%s' ),
			array( '%d' )
		);

		return false !== $updated;
	}
	/**
	 * Checks if a licence type is retired.
	 *
	 * @param string|int $identifier The ID or product ID of the licence type.
	 * @return bool True if the licence type is retired, false otherwise.
	 */
	public static function is_retired( string|int $identifier ): bool {
		$record = is_int( $identifier ) ? self::get_type( $identifier ) : self::find_by_product_id( (string) $identifier );
		if ( null === $record ) {
			return false;
		}

		return ! empty( $record['is_retired'] );
	}
	/**
	 * Deletes a licence type.
	 *
	 * @param int $id The ID of the licence type to delete.
	 * @return bool True if the deletion was successful, false otherwise.
	 */
	public static function delete_type( int $id ): bool {
		global $wpdb;

		$deleted = $wpdb->delete(
			self::table_name(),
			array( 'id' => $id ),
			array( '%d' )
		);

		return false !== $deleted;
	}
	/**
	 * Normalizes a slug value.
	 *
	 * @param string $source The source string to normalize.
	 * @param string $fallback The fallback value if the source is empty.
	 * @return string The normalized slug.
	 */
	private static function normalize_slug( string $source, string $fallback = '' ): string {
		$value = trim( (string) $source );
		if ( '' === $value ) {
			$value = trim( (string) $fallback );
		}

		$value = sanitize_key( $value );
		if ( '' === $value ) {
			$value = sanitize_key( preg_replace( '/[^a-zA-Z0-9]+/', '-', $fallback ) ?? $fallback );
		}

		return $value;
	}
	/**
	 * Extract the full metadata payload for a licence type.
	 *
	 * @param array|null $record Existing record.
	 * @param array      $data New submission values.
	 * @return array<string, mixed> Merged metadata payload.
	 */
	private static function hydrate_metadata( array $record ): array {
		if ( empty( $record['metadata'] ) ) {
			return $record;
		}

		$decoded = maybe_unserialize( $record['metadata'] );
		if ( ! is_array( $decoded ) ) {
			return $record;
		}

		foreach ( $decoded as $key => $value ) {
			if ( in_array( $key, array( 'name', 'slug', 'parent_id', 'is_variant', 'is_retired', 'retired_at', 'prefix', 'suffix', 'length', 'pattern', 'description', 'created_at', 'updated_at', 'metadata' ), true ) ) {
				continue;
			}
			if ( ! array_key_exists( $key, $record ) ) {
				$record[ $key ] = $value;
			}
		}

		return $record;
	}

	private static function merge_metadata( ?array $record, array $data ): array {
		$metadata = array();
		if ( is_array( $record ) && isset( $record['metadata'] ) && '' !== (string) $record['metadata'] ) {
			$decoded = maybe_unserialize( $record['metadata'] );
			if ( is_array( $decoded ) ) {
				$metadata = $decoded;
			}
		}

		foreach ( $data as $key => $value ) {
			if ( in_array( $key, array( 'name', 'slug', 'parent_id', 'is_variant', 'is_retired', 'retired_at', 'prefix', 'suffix', 'length', 'pattern', 'description', 'created_at', 'updated_at' ), true ) ) {
				continue;
			}
			if ( is_array( $value ) ) {
				$metadata[ $key ] = array_values( $value );
				continue;
			}
			$metadata[ $key ] = $value;
		}

		return $metadata;
	}
	/**
	 * Serialize metadata for storage in the licence type table.
	 *
	 * @param array $metadata Metadata to persist.
	 * @return string Serialized metadata payload.
	 */
	private static function serialize_metadata( array $metadata ): string {
		if ( empty( $metadata ) ) {
			return '';
		}

		return maybe_serialize( $metadata );
	}
	/**
	 * Retrieves all variants of a given parent licence type.
	 *
	 * @param int $parent_id The ID of the parent licence type.
	 * @return array The array of variant licence type records.
	 */
	public static function get_variants( int $parent_id ): array {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare( 'SELECT * FROM ' . self::table_name() . ' WHERE parent_id = %d ORDER BY name ASC', $parent_id ),
			ARRAY_A
		);

		if ( ! is_array( $rows ) ) {
			return array();
		}

		return array_map( array( self::class, 'hydrate_metadata' ), $rows );
	}
	/**
	 * Generates a preview of a licence type based on its settings.
	 *
	 * @param array $settings The settings for the licence type.
	 * @return array The generated preview, including sample code and other details.
	 */
	public static function generate_preview( array $settings ): array {
		$name    = (string) ( $settings['name'] ?? 'Licence Type' );
		$prefix  = strtoupper( self::sanitize_token_part( $settings['prefix'] ?? 'LP' ) );
		$suffix  = strtoupper( self::sanitize_token_part( $settings['suffix'] ?? '' ) );
		$length  = max( 8, (int) ( $settings['length'] ?? 12 ) );
		$pattern = trim( (string) ( $settings['pattern'] ?? 'prefix-segment' ) );
		if ( '' === $pattern ) {
			$pattern = 'prefix-segment';
		}

		$segment_a_length = max( 4, (int) round( $length / 2 ) );
		$segment_b_length = max( 4, $length - $segment_a_length );
		$segment_a        = self::random_segment( $segment_a_length );
		$segment_b        = self::random_segment( $segment_b_length );

		$pattern_prefix = '' !== $prefix ? $prefix : 'LP';
		$pattern_tokens = array(
			'prefix'  => $pattern_prefix,
			'suffix'  => $suffix,
			'segment' => $segment_a,
			'name'    => $name,
		);

		$sample = $pattern;
		foreach ( $pattern_tokens as $token => $value ) {
			if ( '' !== $value ) {
				$sample = str_replace( $token, (string) $value, $sample );
			}
		}

		if ( false === strpos( $sample, '-' ) ) {
			$sample = $sample . '-' . $segment_b;
		}

		$sample = preg_replace( '/\s+/', '-', trim( $sample ) );
		$sample = strtoupper( (string) preg_replace( '/[^A-Z0-9-]/', '', (string) $sample ) );
		$sample = trim( $sample, '-' );

		if ( false === strpos( $sample, '-' ) ) {
			$sample = '' !== $prefix ? $prefix : 'LP';
		}

		$sample = preg_replace( '/-+/', '-', $sample );
		if ( '' === $sample ) {
			$sample = '' !== $prefix ? $prefix : 'LP';
		}

		return array(
			'name'     => $name,
			'pattern'  => $pattern,
			'sample'   => $sample,
			'prefix'   => $prefix,
			'suffix'   => $suffix,
			'length'   => $length,
			'variants' => self::mock_variant_list( $name ),
		);
	}
	/**
	 * Provides code examples for using the licence type manager.
	 *
	 * @return array An array of code examples, each containing a title and code snippet.
	 */
	public static function code_examples(): array {
		return array(
			array(
				'title' => 'WordPress plugin validation',
				'code'  => <<<'PHP'
					<?php
					$licence = get_option( 'licencepress_license' );
					if ( ! empty( $licence ) ) {
						$valid = LicencePress\Includes\Licence\LicenceManager::validate_license( $licence, 'wordpress-plugin-1', home_url() );
					}
					PHP,
			),
			array(
				'title' => 'Custom product check',
				'code'  => <<<'PHP'
				<?php
				$token = $_POST['license_key'] ?? '';
				$valid = LicencePress\Includes\Licence\LicenceManager::validate_license( $token, 'wordpress-plugin-1-plus', site_url() );
				PHP,
			),
			array(
				'title' => 'License summary banner',
				'code'  => <<<'PHP'
				<?php
				$summary = LicencePress\Includes\Licence\LicenceManager::summary();
				if ( ! empty( $summary['active'] ) ) {
					echo esc_html( sprintf( 'Active licenses: %d', $summary['active'] ) );
				}
				PHP,
			)
		);
	}
	/**
	 * Sanitizes a part of the license token by removing non-alphanumeric characters and converting to uppercase.
	 *
	 * @param string $value The value to sanitize.
	 * @return string The sanitized value.
	 */
	private static function sanitize_token_part( string $value ): string {
		$value = preg_replace( '/[^A-Za-z0-9]/', '', $value );

		return strtoupper( (string) $value );
	}
	/**
	 * Generates a random segment of the license token.
	 *
	 * @param int $length The length of the random segment.
	 * @return string The generated random segment.
	 */
	private static function random_segment( int $length ): string {
		$chars   = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
		$segment = '';
		$max     = strlen( $chars ) - 1;

		for ( $i = 0; $i < $length; $i++ ) {
			$random_index = function_exists( 'wp_rand' ) ? wp_rand( 0, $max ) : random_int( 0, $max );
			$segment     .= $chars[ $random_index ];
		}

		return $segment;
	}
	/**
	 * Mocks a list of license variants for a given product name.
	 *
	 * @param string $name The product name.
	 * @return array The list of mocked license variants.
	 */
	private static function mock_variant_list( string $name ): array {
		return array(
			$name . ' - Plus',
			$name . ' - Platinum',
			$name . ' - Enterprise',
		);
	}
}
