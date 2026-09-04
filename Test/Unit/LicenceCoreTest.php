<?php

namespace LicencePress\Test\Unit;

use Defuse\Crypto\Key;
use LicencePress\Includes\Licence\EncryptionService;
use LicencePress\Includes\Licence\KeyManager;
use LicencePress\Includes\Licence\LicenceGenerator;
use LicencePress\Includes\Licence\LicenceManager;
use LicencePress\Includes\Licence\LicenceTypeManager;
use LicencePress\Includes\Licence\LicenceValidator;
use LicencePress\Includes\Settings\SettingsManager;
use PHPUnit\Framework\TestCase;

final class LicenceCoreTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();

        global $wpdb;
        $wpdb->tables = [];

        if ( ! defined( 'LICENCEPRESS_ENCRYPTION_KEY' ) ) {
            define( 'LICENCEPRESS_ENCRYPTION_KEY', Key::createNewRandomKey()->saveToAsciiSafeString() );
        }

        KeyManager::set_runtime_key( (string) constant( 'LICENCEPRESS_ENCRYPTION_KEY' ) );
    }

    public function test_missing_settings_table_does_not_query_before_install(): void {
        $guard = new class() {
            public string $prefix = 'wp_';

            public function prepare( $query, ...$args ) {
                return sprintf( $query, ...$args );
            }

            public function get_var( $query ) {
                if ( stripos( (string) $query, 'SHOW TABLES LIKE' ) !== false ) {
                    return null;
                }
                throw new \RuntimeException( 'Settings table should not be queried before installation.' );
            }

            public function get_results( $query, $output = ARRAY_A ) {
                throw new \RuntimeException( 'Settings table should not be queried before installation.' );
            }
        };

        global $wpdb;
        $previous = $wpdb;
        $wpdb = $guard;

        try {
            $this->assertSame( [], SettingsManager::get_all() );
            $this->assertNull( SettingsManager::get_group( 'general' ) );
        } finally {
            $wpdb = $previous;
        }
    }

    public function test_generates_and_validates_a_license(): void {
        $product_id = 'core-pro-' . uniqid( '', true );
        $site_url = 'https://example.com';
        $record = LicenceGenerator::generate( $product_id, 'customer-42', 30, $site_url, [ 'pro' ] );

        $this->assertMatchesRegularExpression( '/^LP-[A-F0-9]+$/', $record['token'] );
        $this->assertIsString( $record['payload_encrypted'] );
        $this->assertTrue( LicenceValidator::validate( $record['token'], $product_id, $site_url, $record ) );
    }

    public function test_encryption_service_round_trips_values(): void {
        $plaintext = 'sensitive-license-data';
        $ciphertext = EncryptionService::encrypt( $plaintext );

        $this->assertNotSame( $plaintext, $ciphertext );
        $this->assertSame( $plaintext, EncryptionService::decrypt( $ciphertext ) );
    }

    public function test_manager_creates_and_revokes_a_license(): void {
        $product_id = 'core-pro-' . uniqid( '', true );
        $record = LicenceManager::create_license( $product_id, 'customer-77', 14, 'https://example.com', [ 'pro', 'support' ] );

        $this->assertSame( 'active', $record['status'] );
        $this->assertNotEmpty( $record['token'] );
        $this->assertTrue( LicenceManager::validate_license( $record['token'], $product_id, 'https://example.com' ) );
        $this->assertNotEmpty( LicenceManager::list_for_customer( 'customer-77' ) );
        $this->assertTrue( LicenceManager::revoke_license( $record['token'] ) );
    }

    public function test_licence_type_preview_builds_a_sample_key(): void {
        $preview = LicenceTypeManager::generate_preview( [
            'name' => 'WordPress Plugin 1',
            'prefix' => 'WPP',
            'suffix' => 'PRO',
            'length' => 16,
            'pattern' => 'prefix-segment-segment',
        ] );

        $this->assertArrayHasKey( 'sample', $preview );
        $this->assertMatchesRegularExpression( '/^WPP-[A-Z0-9]{8}-[A-Z0-9]{8}$/', $preview['sample'] );
    }

    public function test_licence_type_crud_flow_persists_updates_and_removes_records(): void {
        $created = LicenceTypeManager::create_type( [
            'name' => 'WordPress Plugin CRUD',
            'prefix' => 'WPC',
            'suffix' => 'CRD',
            'length' => 14,
            'pattern' => 'prefix-segment',
            'description' => 'Initial description',
        ] );

        $this->assertGreaterThan( 0, $created );

        $loaded = LicenceTypeManager::get_type( $created );
        $this->assertNotNull( $loaded );
        $this->assertSame( 'WordPress Plugin CRUD', $loaded['name'] );

        $updated = LicenceTypeManager::update_type( $created, [
            'name' => 'WordPress Plugin CRUD Updated',
            'description' => 'Updated description',
        ] );

        $this->assertTrue( $updated );
        $this->assertSame( 'WordPress Plugin CRUD Updated', LicenceTypeManager::get_type( $created )['name'] );

        $deleted = LicenceTypeManager::delete_type( $created );
        $this->assertTrue( $deleted );
        $this->assertNull( LicenceTypeManager::get_type( $created ) );
    }

    public function test_retired_licence_types_block_new_issues_but_keep_existing_licences_valid(): void {
        $product_id = 'retired-product-' . uniqid( '', true );
        $type_id = LicenceTypeManager::create_type( [
            'name' => 'Retired Product',
            'slug' => $product_id,
            'prefix' => 'RTP',
            'suffix' => 'RET',
            'length' => 12,
            'pattern' => 'prefix-segment',
        ] );

        $issued = LicenceManager::create_license( $product_id, 'customer-retired', 30, 'https://example.com', [ 'support' ] );
        $this->assertNotEmpty( $issued['token'] );
        $this->assertTrue( LicenceManager::validate_license( $issued['token'], $product_id, 'https://example.com' ) );

        $this->assertTrue( LicenceTypeManager::retire_type( $type_id, true ) );
        $this->assertTrue( LicenceTypeManager::is_retired( $product_id ) );
        $this->assertTrue( LicenceManager::validate_license( $issued['token'], $product_id, 'https://example.com' ) );

        $this->expectException( \InvalidArgumentException::class );
        LicenceManager::create_license( $product_id, 'customer-retired-2', 30, 'https://example.com', [ 'support' ] );
    }
}
