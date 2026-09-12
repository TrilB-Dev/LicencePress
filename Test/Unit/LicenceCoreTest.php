<?php

// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.Commenting.VariableComment.Missing, Generic.CodeAnalysis.UnusedFunctionParameter

namespace LicencePress\Test\Unit;

use Defuse\Crypto\Key;
use LicencePress\Includes\Core\PostType;
use LicencePress\Includes\Core\Taxonomy;
use LicencePress\Includes\Functions\Helpers\LicenceHelper;
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
		$wpdb->tables = array();

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
		$wpdb     = $guard;

		try {
			$this->assertSame( array(), SettingsManager::get_all() );
			$this->assertNull( SettingsManager::get_group( 'general' ) );
		} finally {
			$wpdb = $previous;
		}
	}

	public function test_licencepress_post_types_and_taxonomies_use_current_names(): void {
		$this->assertSame( 'lp_licence_type', PostType::LICENCE_TYPE );
		$this->assertSame( 'lp_licence_variant', PostType::LICENCE_TYPE_VARIANT );
		$this->assertLessThanOrEqual( 20, strlen( PostType::LICENCE_TYPE ) );
		$this->assertLessThanOrEqual( 20, strlen( PostType::LICENCE_TYPE_VARIANT ) );
		$this->assertSame( 'licence_type_categories', Taxonomy::CATEGORY );
		$this->assertSame( 'licence_type_tags', Taxonomy::TAG );
		$this->assertSame( 'licence-types', PostType::page_rewrite_slug() );
	}

	public function test_generates_and_validates_a_license(): void {
		$product_id = 'core-pro-' . uniqid( '', true );
		$site_url   = 'https://example.com';
		$record     = LicenceGenerator::generate( $product_id, 'customer-42', 30, $site_url, array( 'pro' ) );

		$this->assertMatchesRegularExpression( '/^LP-[A-F0-9]+$/', $record['token'] );
		$this->assertIsString( $record['payload_encrypted'] );
		$this->assertTrue( LicenceValidator::validate( $record['token'], $product_id, $site_url, $record ) );
	}

	public function test_encryption_service_round_trips_values(): void {
		$plaintext  = 'sensitive-license-data';
		$ciphertext = EncryptionService::encrypt( $plaintext );

		$this->assertNotSame( $plaintext, $ciphertext );
		$this->assertSame( $plaintext, EncryptionService::decrypt( $ciphertext ) );
	}

	public function test_licence_helper_respects_default_settings_and_per_licence_overrides(): void {
		$default_settings = array(
			'licence_prefix'                     => 'LP',
			'licence_pattern_type'               => 'custom',
			'custom_licence_pattern'             => 'XX-XX-XX',
			'licence_pattern_format'             => 'alphanumeric',
			'licence_pattern_letter_case'        => 'uppercase',
			'licence_pattern_separator'          => '-',
			'exclude_ambiguous_characters'      => array( '0', 'O', '1', 'i', 'l', 'I' ),
			'licensor_name'                      => 'Acme Ltd',
			'licensor_country'                   => 'United Kingdom',
		);

		$licence_settings = array(
			'use_default_licence_prefix'        => false,
			'licence_prefix'                    => 'DEV',
			'use_default_licence_pattern_type' => false,
			'licence_pattern_type'              => 'custom',
			'custom_licence_pattern'            => 'AN-NN-AN',
			'use_default_exclude_ambiguous_characters' => false,
			'exclude_ambiguous_characters'      => array( '0', '1' ),
			'use_default_licence_pattern_format' => false,
			'licence_pattern_format'            => 'alphanumeric',
			'use_default_licence_pattern_letter_case' => false,
			'licence_pattern_letter_case'       => 'uppercase',
			'use_default_licence_pattern_separator' => false,
			'licence_pattern_separator'         => '-',
			'use_default_licensor_name'         => false,
			'licensor_name'                     => 'Northwind',
			'use_default_licensor_country'      => false,
			'licensor_country'                  => 'France',
		);

		$result = LicenceHelper::generate( $default_settings, $licence_settings, array( 'table_name' => 'wp_licencepress_licence' ) );

		$this->assertNotEmpty( $result['licence'] );
		$this->assertStringStartsWith( 'DEV-', $result['licence'] );
		$this->assertMatchesRegularExpression( '/^DEV-[A-Z0-9-]+$/', $result['licence'] );
		$this->assertNotSame( '', $result['encrypted_licence'] );
		$this->assertSame( 'Northwind', $result['settings']['licensor_name'] );
		$this->assertSame( 'France', $result['settings']['licensor_country'] );
		$this->assertStringNotContainsString( '0', $result['licence'] );
	}

	public function test_manager_creates_and_revokes_a_license(): void {
		$product_id = 'core-pro-' . uniqid( '', true );
		$record     = LicenceManager::create_license( $product_id, 'customer-77', 14, 'https://example.com', array( 'pro', 'support' ) );

		$this->assertSame( 'active', $record['status'] );
		$this->assertNotEmpty( $record['token'] );
		$this->assertTrue( LicenceManager::validate_license( $record['token'], $product_id, 'https://example.com' ) );
		$this->assertNotEmpty( LicenceManager::list_for_customer( 'customer-77' ) );
		$this->assertTrue( LicenceManager::revoke_license( $record['token'] ) );
	}

	public function test_licence_type_preview_builds_a_sample_key(): void {
		$preview = LicenceTypeManager::generate_preview(
			array(
				'name'    => 'WordPress Plugin 1',
				'prefix'  => 'WPP',
				'suffix'  => 'PRO',
				'length'  => 16,
				'pattern' => 'prefix-segment-segment',
			)
		);

		$this->assertArrayHasKey( 'sample', $preview );
		$this->assertMatchesRegularExpression( '/^WPP-[A-Z0-9]{8}-[A-Z0-9]{8}$/', $preview['sample'] );
	}

	public function test_admin_dashboard_assets_use_real_compiled_bundle_names(): void {
		if ( ! defined( 'LICENCEPRESS_URL' ) ) {
			define( 'LICENCEPRESS_URL', 'https://example.com/wp-content/plugins/licencepress/' );
		}

		$manager = new class() extends \LicencePress\Admin\Manager\Manager {
			public function expose_assets( string $bundle ): array {
				return $this->assets( $bundle );
			}
		};

		$assets = $manager->expose_assets( 'dashboard' );

		$this->assertNotEmpty( $assets['scripts'] );
		$this->assertStringContainsString( 'admin.dashboard.js', $assets['scripts'][0]['src'] );
		$this->assertStringContainsString( 'admin.ui.css', $assets['styles'][0]['src'] );
		$this->assertStringNotContainsString( 'admin.dashboard.css', $assets['styles'][0]['src'] );
	}

	public function test_licence_type_crud_flow_persists_updates_and_removes_records(): void {
		$created = LicenceTypeManager::create_type(
			array(
				'name'        => 'WordPress Plugin CRUD',
				'prefix'      => 'WPC',
				'suffix'      => 'CRD',
				'length'      => 14,
				'pattern'     => 'prefix-segment',
				'description' => 'Initial description',
			)
		);

		$this->assertGreaterThan( 0, $created );

		$loaded = LicenceTypeManager::get_type( $created );
		$this->assertNotNull( $loaded );
		$this->assertSame( 'WordPress Plugin CRUD', $loaded['name'] );

		$updated = LicenceTypeManager::update_type(
			$created,
			array(
				'name'        => 'WordPress Plugin CRUD Updated',
				'description' => 'Updated description',
			)
		);

		$this->assertTrue( $updated );
		$this->assertSame( 'WordPress Plugin CRUD Updated', LicenceTypeManager::get_type( $created )['name'] );

		$deleted = LicenceTypeManager::delete_type( $created );
		$this->assertTrue( $deleted );
		$this->assertNull( LicenceTypeManager::get_type( $created ) );
	}

	public function test_licence_type_extended_configuration_persists_in_metadata(): void {
		$config = array(
			'name'           => 'Extended Licence Type',
			'slug'           => 'extended-licence-type',
			'prefix'         => 'EXT',
			'length'         => 18,
			'pattern'        => 'prefix-segment-segment',
			'licensor_name'  => 'Acme Studio',
			'renewal_window' => 45,
			'licence_platforms' => array( 'website', 'windows_software' ),
			'capability_groups' => array(
				array(
					'name' => 'Product Access',
					'capabilities' => array( 'feature_access', 'support_priority' ),
				),
			),
		);

		$id = LicenceTypeManager::create_type( $config );
		$this->assertGreaterThan( 0, $id );

		$loaded = LicenceTypeManager::get_type( $id );
		$this->assertNotNull( $loaded );
		$this->assertNotEmpty( $loaded['metadata'] );
		$this->assertSame( 'Acme Studio', maybe_unserialize( $loaded['metadata'] )['licensor_name'] ?? '' );
		$this->assertSame( 45, (int) ( maybe_unserialize( $loaded['metadata'] )['renewal_window'] ?? 0 ) );

		$updated = LicenceTypeManager::update_type(
			$id,
			array(
				'renewal_window' => 60,
				'licence_platforms' => array( 'website', 'ios_devices' ),
			)
		);

		$this->assertTrue( $updated );
		$metadata = maybe_unserialize( LicenceTypeManager::get_type( $id )['metadata'] );
		$this->assertSame( 60, (int) ( $metadata['renewal_window'] ?? 0 ) );
		$this->assertContains( 'ios_devices', $metadata['licence_platforms'] ?? array() );
	}

	public function test_licence_type_metadata_is_hydrated_on_load_for_editing(): void {
		$id = LicenceTypeManager::create_type(
			array(
				'name'           => 'Hydrated Licence Type',
				'slug'           => 'hydrated-licence-type',
				'prefix'         => 'HDT',
				'length'         => 16,
				'pattern'        => 'prefix-segment-segment',
				'renewal_window' => 45,
				'licence_platforms' => array( 'website', 'windows_software' ),
				'capability_groups' => array(
					array(
						'name'        => 'Product Access',
						'capabilities' => array( 'feature_access', 'updates' ),
					),
				),
			)
		);

		$loaded = LicenceTypeManager::get_type( $id );
		$this->assertNotNull( $loaded );
		$this->assertSame( 45, (int) ( $loaded['renewal_window'] ?? 0 ) );
		$this->assertContains( 'windows_software', $loaded['licence_platforms'] ?? array() );
		$this->assertSame( 'Product Access', ( $loaded['capability_groups'][0]['name'] ?? '' ) );
	}

	public function test_retired_licence_types_block_new_issues_but_keep_existing_licences_valid(): void {
		$product_id = 'retired-product-' . uniqid( '', true );
		$type_id    = LicenceTypeManager::create_type(
			array(
				'name'    => 'Retired Product',
				'slug'    => $product_id,
				'prefix'  => 'RTP',
				'suffix'  => 'RET',
				'length'  => 12,
				'pattern' => 'prefix-segment',
			)
		);

		$issued = LicenceManager::create_license( $product_id, 'customer-retired', 30, 'https://example.com', array( 'support' ) );
		$this->assertNotEmpty( $issued['token'] );
		$this->assertTrue( LicenceManager::validate_license( $issued['token'], $product_id, 'https://example.com' ) );

		$this->assertTrue( LicenceTypeManager::retire_type( $type_id, true ) );
		$this->assertTrue( LicenceTypeManager::is_retired( $product_id ) );
		$this->assertTrue( LicenceManager::validate_license( $issued['token'], $product_id, 'https://example.com' ) );

		$this->expectException( \InvalidArgumentException::class );
		LicenceManager::create_license( $product_id, 'customer-retired-2', 30, 'https://example.com', array( 'support' ) );
	}
}
