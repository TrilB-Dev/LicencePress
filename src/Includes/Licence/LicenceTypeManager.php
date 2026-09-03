<?php

namespace LicencePress\Includes\Licence;

use LicencePress\Includes\Core\WP\Database;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class LicenceTypeManager {
    public static function register_schema(): void {
        Database::register_table( 'licence_type', static function ( string $table_name, string $charset ) {
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
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY parent_id (parent_id),
                KEY is_variant (is_variant),
                KEY is_retired (is_retired),
                KEY slug (slug),
                KEY name (name)
            ) {$charset};";
        } );
    }

    public static function table_name(): string {
        return Database::table_name( 'licence_type' );
    }

    public static function create_type( array $data ): int {
        global $wpdb;

        $now = gmdate( 'Y-m-d H:i:s' );
        $slug = self::normalize_slug( (string) ( $data['slug'] ?? '' ), (string) ( $data['name'] ?? '' ) );
        $payload = [
            'name' => (string) ( $data['name'] ?? '' ),
            'slug' => $slug,
            'parent_id' => ! empty( $data['parent_id'] ) ? (int) $data['parent_id'] : 0,
            'is_variant' => ! empty( $data['is_variant'] ) ? 1 : 0,
            'is_retired' => ! empty( $data['is_retired'] ) ? 1 : 0,
            'retired_at' => ! empty( $data['is_retired'] ) ? $now : null,
            'prefix' => (string) ( $data['prefix'] ?? '' ),
            'suffix' => (string) ( $data['suffix'] ?? '' ),
            'length' => max( 8, (int) ( $data['length'] ?? 12 ) ),
            'pattern' => (string) ( $data['pattern'] ?? 'prefix-segment' ),
            'description' => isset( $data['description'] ) ? (string) $data['description'] : '',
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $wpdb->insert(
            self::table_name(),
            $payload,
            [ '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s' ]
        );

        return (int) $wpdb->insert_id;
    }

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

        return is_array( $row ) ? $row : null;
    }

    public static function get_type( int $id ): ?array {
        global $wpdb;

        $row = $wpdb->get_row(
            $wpdb->prepare( 'SELECT * FROM ' . self::table_name() . ' WHERE id = %d LIMIT 1', $id ),
            ARRAY_A
        );

        return is_array( $row ) ? $row : null;
    }

    public static function get_types(): array {
        global $wpdb;

        $rows = $wpdb->get_results(
            'SELECT * FROM ' . self::table_name() . ' ORDER BY created_at DESC',
            ARRAY_A
        );

        return is_array( $rows ) ? $rows : [];
    }

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

        $payload = [
            'name' => isset( $data['name'] ) ? (string) $data['name'] : (string) ( $record['name'] ?? '' ),
            'slug' => $slug,
            'parent_id' => isset( $data['parent_id'] ) ? (int) $data['parent_id'] : (int) ( $record['parent_id'] ?? 0 ),
            'is_variant' => isset( $data['is_variant'] ) ? (int) $data['is_variant'] : (int) ( $record['is_variant'] ?? 0 ),
            'is_retired' => array_key_exists( 'is_retired', $data ) ? (int) ! empty( $data['is_retired'] ) : (int) ( $record['is_retired'] ?? 0 ),
            'retired_at' => array_key_exists( 'is_retired', $data ) && ! empty( $data['is_retired'] ) ? ( $record['retired_at'] ?? gmdate( 'Y-m-d H:i:s' ) ) : null,
            'prefix' => isset( $data['prefix'] ) ? (string) $data['prefix'] : (string) ( $record['prefix'] ?? '' ),
            'suffix' => isset( $data['suffix'] ) ? (string) $data['suffix'] : (string) ( $record['suffix'] ?? '' ),
            'length' => isset( $data['length'] ) ? max( 8, (int) $data['length'] ) : (int) ( $record['length'] ?? 12 ),
            'pattern' => isset( $data['pattern'] ) ? (string) $data['pattern'] : (string) ( $record['pattern'] ?? 'prefix-segment' ),
            'description' => array_key_exists( 'description', $data ) ? (string) $data['description'] : (string) ( $record['description'] ?? '' ),
            'updated_at' => gmdate( 'Y-m-d H:i:s' ),
        ];

        $updated = $wpdb->update(
            self::table_name(),
            $payload,
            [ 'id' => $id ],
            [ '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s' ],
            [ '%d' ]
        );

        return false !== $updated;
    }

    public static function retire_type( int $id, bool $retired = true ): bool {
        global $wpdb;

        $record = self::get_type( $id );
        if ( null === $record ) {
            return false;
        }

        $payload = [
            'is_retired' => $retired ? 1 : 0,
            'retired_at' => $retired ? ( $record['retired_at'] ?? gmdate( 'Y-m-d H:i:s' ) ) : null,
            'updated_at' => gmdate( 'Y-m-d H:i:s' ),
        ];

        $updated = $wpdb->update(
            self::table_name(),
            $payload,
            [ 'id' => $id ],
            [ '%d', '%s', '%s' ],
            [ '%d' ]
        );

        return false !== $updated;
    }

    public static function is_retired( string|int $identifier ): bool {
        $record = is_int( $identifier ) ? self::get_type( $identifier ) : self::find_by_product_id( (string) $identifier );
        if ( null === $record ) {
            return false;
        }

        return ! empty( $record['is_retired'] );
    }

    public static function delete_type( int $id ): bool {
        global $wpdb;

        $deleted = $wpdb->delete(
            self::table_name(),
            [ 'id' => $id ],
            [ '%d' ]
        );

        return false !== $deleted;
    }

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

    public static function get_variants( int $parent_id ): array {
        global $wpdb;

        $rows = $wpdb->get_results(
            $wpdb->prepare( 'SELECT * FROM ' . self::table_name() . ' WHERE parent_id = %d ORDER BY name ASC', $parent_id ),
            ARRAY_A
        );

        return is_array( $rows ) ? $rows : [];
    }

    public static function generate_preview( array $settings ): array {
        $name = (string) ( $settings['name'] ?? 'Licence Type' );
        $prefix = strtoupper( self::sanitize_token_part( $settings['prefix'] ?? 'LP' ) );
        $suffix = strtoupper( self::sanitize_token_part( $settings['suffix'] ?? '' ) );
        $length = max( 8, (int) ( $settings['length'] ?? 12 ) );
        $pattern = trim( (string) ( $settings['pattern'] ?? 'prefix-segment' ) );
        if ( '' === $pattern ) {
            $pattern = 'prefix-segment';
        }

        $segment_a_length = max( 4, (int) round( $length / 2 ) );
        $segment_b_length = max( 4, $length - $segment_a_length );
        $segment_a = self::random_segment( $segment_a_length );
        $segment_b = self::random_segment( $segment_b_length );

        $pattern_tokens = [
            'prefix' => $prefix ?: 'LP',
            'suffix' => $suffix,
            'segment' => $segment_a,
            'name' => $name,
        ];

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
            $sample = $prefix ?: 'LP';
        }

        $sample = preg_replace( '/-+/', '-', $sample );
        if ( '' === $sample ) {
            $sample = $prefix ?: 'LP';
        }

        return [
            'name' => $name,
            'pattern' => $pattern,
            'sample' => $sample,
            'prefix' => $prefix,
            'suffix' => $suffix,
            'length' => $length,
            'variants' => self::mock_variant_list( $name ),
        ];
    }

    public static function code_examples(): array {
        return [
            [
                'title' => 'WordPress plugin validation',
                'code' => <<<'PHP'
<?php
$licence = get_option( 'licencepress_license' );
if ( ! empty( $licence ) ) {
    $valid = LicencePress\Includes\Licence\LicenceManager::validate_license( $licence, 'wordpress-plugin-1', home_url() );
}
PHP,
            ],
            [
                'title' => 'Custom product check',
                'code' => <<<'PHP'
<?php
$token = $_POST['license_key'] ?? '';
$valid = LicencePress\Includes\Licence\LicenceManager::validate_license( $token, 'wordpress-plugin-1-plus', site_url() );
PHP,
            ],
            [
                'title' => 'License summary banner',
                'code' => <<<'PHP'
<?php
$summary = LicencePress\Includes\Licence\LicenceManager::summary();
if ( ! empty( $summary['active'] ) ) {
    echo esc_html( sprintf( 'Active licenses: %d', $summary['active'] ) );
}
PHP,
            ],
        ];
    }

    private static function sanitize_token_part( string $value ): string {
        $value = preg_replace( '/[^A-Za-z0-9]/', '', $value );

        return strtoupper( (string) $value );
    }

    private static function random_segment( int $length ): string {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $segment = '';
        $max = strlen( $chars ) - 1;

        for ( $i = 0; $i < $length; $i++ ) {
            $random_index = function_exists( 'wp_rand' ) ? wp_rand( 0, $max ) : random_int( 0, $max );
            $segment .= $chars[ $random_index ];
        }

        return $segment;
    }

    private static function mock_variant_list( string $name ): array {
        return [
            $name . ' - Plus',
            $name . ' - Platinum',
            $name . ' - Enterprise',
        ];
    }
}
