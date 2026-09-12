<?php

// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.Commenting.VariableComment.Missing, Generic.CodeAnalysis.UnusedFunctionParameter

namespace LicencePress\Test\Unit;

if ( ! function_exists( '\get_pages' ) ) {
	function get_pages( $args = array() ) {
		return array();
	}
}

use Defuse\Crypto\Key;
use LicencePress\Includes\Core\PostType;
use LicencePress\Includes\Core\Taxonomy;
use LicencePress\Includes\Functions\Helpers\AMHelper;
use LicencePress\Includes\Functions\Helpers\LicenceHelper;
use LicencePress\Includes\Licence\EncryptionService;
use LicencePress\Includes\Licence\KeyManager;
use LicencePress\Includes\Licence\LicenceGenerator;
use LicencePress\Includes\Licence\LicenceManager;
use LicencePress\Includes\Licence\LicenceTypeManager;
use LicencePress\Includes\Licence\LicenceValidator;
use LicencePress\Includes\Settings\Settings;
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

	public function test_sidebar_links_keep_the_explicit_licencepress_route(): void {
		$method = new \ReflectionMethod( '\\LicencePress\\Admin\\Manager\\UI\\Sidebar', 'item_link' );
		$method->setAccessible( true );

		$link = $method->invoke( null, 'general', array( 'link' => 'licencepress&group=settings&tab=general' ) );
		$this->assertSame( 'licencepress&group=settings&tab=general', $link );

		$page_method = new \ReflectionMethod( '\\LicencePress\\Admin\\Manager\\UI\\Sidebar', 'item_page' );
		$page_method->setAccessible( true );
		$this->assertSame( 'licencepress', $page_method->invoke( null, 'licencepress&group=settings&tab=general' ) );
	}

	public function test_admin_route_slugs_keep_the_explicit_query_string(): void {
		$menu = AMHelper::define( 'Overview', 'licencepress&group=licences&tab=overview', 'dashicons-admin-generic', 'licencepress' );
		$this->assertSame( 'licencepress&group=licences&tab=overview', $menu['slug'] );

		$method = new \ReflectionMethod( '\\LicencePress\\Includes\\Functions\\Admin\\FunctionsSidebar', 'menu_page_slug' );
		$method->setAccessible( true );
		$this->assertSame( 'licencepress&group=licences&tab=overview', $method->invoke( null, 'licencepress&group=licences&tab=overview' ) );
	}

	public function test_general_settings_do_not_render_invalid_state_on_first_load(): void {
		ob_start();
		( new \LicencePress\Admin\Manager\Settings\SettingsGeneral() )->render( array() );
		$output = ob_get_clean();

		$this->assertStringNotContainsString( 'Please enter the default licensor name.', $output );
		$this->assertStringNotContainsString( 'Please define the custom licence pattern.', $output );
		$this->assertStringNotContainsString( 'is-invalid', $output );
	}

	public function test_admin_settings_reinitializes_bootstrap_selects_after_tab_reload(): void {
		$script = file_get_contents( dirname( __DIR__, 2 ) . '/src/Assets/js/admin.settings.js' );
		$this->assertIsString( $script );
		$this->assertStringContainsString( 'initializeBootstrapSelects', $script );
		$this->assertStringContainsString( 'window.licencepressBootstrapSelect?.initialize', $script );
		$this->assertStringContainsString( 'panel.innerHTML = response.data.html;', $script );
	}

	public function test_billing_settings_are_saved_to_the_shared_settings_store(): void {
		$settings = new \LicencePress\Includes\Functions\Admin\FunctionsSettings( new \LicencePress\Includes\Functions\Admin\FunctionsPlugins() );
		$general = $settings->sanitize_billing(
			array(
				'billing_name'      => 'Acme Ltd',
				'billing_address_1' => '12 Market Street',
				'town'              => 'London',
				'country'           => 'United Kingdom',
				'email_address'     => 'billing@acme.example',
				'phone_number'      => '+44 20 1234 5678',
				'invoice_prefix'    => 'INV-',
			)
		);
		$invoice = $settings->sanitize_billing_invoice(
			array(
				'invoice_style' => '<p>Thank you for your order.</p>',
			)
		);

		$this->assertSame( 'Acme Ltd', $general['billing_name'] );
		$this->assertSame( 'billing@acme.example', Settings::get( 'email_address' ) );
		$this->assertSame( '<p>Thank you for your order.</p>', $invoice['invoice_style'] );
		$this->assertSame( '<p>Thank you for your order.</p>', Settings::get( 'invoice_style' ) );
	}

	public function test_ambiguous_character_multiselect_shows_visible_tags(): void {
		ob_start();
		( new \LicencePress\Admin\Manager\Settings\SettingsGeneral() )->render(
			array(
				'default_exclude_ambiguous_characters' => array( '0', '1', 'O' ),
			)
		);
		$output = ob_get_clean();

		$this->assertStringContainsString( 'data-live-search="true"', $output );
		$this->assertStringContainsString( 'data-show-selected-tags="true"', $output );
		$this->assertStringContainsString( 'data-selected-items-style="tags"', $output );
	}

	public function test_licence_dashboard_routes_licence_tabs_to_their_pages(): void {
		$admin = ( new \ReflectionClass( \LicencePress\Admin\Admin::class ) )->newInstanceWithoutConstructor();
		$manager = new \LicencePress\Admin\Manager\Licences\LicencesManager();

		$property = new \ReflectionProperty( \LicencePress\Admin\Admin::class, 'licences_manager' );
		$property->setAccessible( true );
		$property->setValue( $admin, $manager );

		$_GET['group'] = 'licences';
		$_GET['tab']   = 'manage-types';
		ob_start();
		$admin->render_dashboard();
		$output = ob_get_clean();
		$this->assertStringContainsString( 'Manage Licence Types', $output );

		$_GET['group'] = 'licences';
		$_GET['tab']   = 'add-type';
		ob_start();
		$admin->render_dashboard();
		$output = ob_get_clean();
		$this->assertStringContainsString( 'Add Licence Type', $output );
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

	public function test_customer_roles_and_customer_metadata_are_registered(): void {
		if ( ! function_exists( 'get_role' ) ) {
			function get_role( $role ) {
				return $GLOBALS['licencepress_test_roles'][ $role ] ?? null;
			}
		}

		if ( ! function_exists( 'add_role' ) ) {
			function add_role( $role, $name, $capabilities = array() ) {
				$GLOBALS['licencepress_test_roles'][ $role ] = (object) array(
					'name'        => $name,
					'capabilities' => $capabilities,
				);
				return $GLOBALS['licencepress_test_roles'][ $role ];
			}
		}

		$GLOBALS['licencepress_test_roles'] = array();
		\LicencePress\Includes\Core\CustomerRoles::install();

		$this->assertNotNull( get_role( \LicencePress\Includes\Core\CustomerRoles::CUSTOMER_ROLE ) );
		$this->assertNotNull( get_role( \LicencePress\Includes\Core\CustomerRoles::INTERNAL_CUSTOMER_ROLE ) );
		$this->assertContains( 'title', \LicencePress\Admin\Manager\Customer\CustomerManager::meta_keys() );
		$this->assertContains( 'payment_method', \LicencePress\Admin\Manager\Customer\CustomerManager::meta_keys() );
	}

	public function test_customer_overview_loads_real_customer_users(): void {
		if ( ! function_exists( 'get_users' ) ) {
			function get_users( $args = array() ) {
				return $GLOBALS['licencepress_test_users'] ?? array();
			}
		}

		if ( ! function_exists( 'get_user_meta' ) ) {
			function get_user_meta( $user_id, $key = '', $single = false ) {
				$meta = $GLOBALS['licencepress_test_user_meta'][ (int) $user_id ] ?? array();
				if ( '' === $key ) {
					return $meta;
				}
				return $meta[ $key ] ?? ( $single ? '' : array() );
			}
		}

		$GLOBALS['licencepress_test_users'] = array(
			(object) array(
				'ID'            => 21,
				'display_name'  => 'Northwind Studio',
				'user_email'    => 'hello@northwind.dev',
				'roles'         => array( \LicencePress\Includes\Core\CustomerRoles::CUSTOMER_ROLE ),
			),
			(object) array(
				'ID'            => 22,
				'display_name'  => 'Westgate Labs',
				'user_email'    => 'ops@westgate.dev',
				'roles'         => array( \LicencePress\Includes\Core\CustomerRoles::INTERNAL_CUSTOMER_ROLE ),
			),
		);
		$GLOBALS['licencepress_test_user_meta'] = array(
			21 => array(
				'company_name' => 'Northwind Studio',
				'primary_contact_name' => 'Lena Morris',
				'primary_contact_email' => 'lena@northwind.dev',
				'customer_type' => 'Customer',
			),
			22 => array(
				'company_name' => 'Westgate Labs',
				'primary_contact_name' => 'Cameron Bell',
				'primary_contact_email' => 'cameron@westgate.dev',
				'customer_type' => 'Internal Customer',
			),
		);

		$profiles = \LicencePress\Admin\Manager\Customer\CustomerManager::customer_profiles();

		$this->assertCount( 2, $profiles );
		$this->assertSame( 'Northwind Studio', $profiles[0]['name'] );
		$this->assertSame( 'Customer', $profiles[0]['type'] );
		$this->assertSame( 'Lena Morris', $profiles[0]['contact'] );
		$this->assertSame( 'Westgate Labs', $profiles[1]['name'] );
		$this->assertSame( 'Internal Customer', $profiles[1]['type'] );
	}

	public function test_customer_detail_record_exposes_user_metadata(): void {
		$GLOBALS['licencepress_test_users'] = array(
			(object) array(
				'ID'            => 81,
				'display_name'  => 'Acme Cloud',
				'user_email'    => 'billing@acmecloud.dev',
				'roles'         => array( \LicencePress\Includes\Core\CustomerRoles::CUSTOMER_ROLE ),
			),
		);
		$GLOBALS['licencepress_test_user_meta'] = array(
			81 => array(
				'customer_type' => 'Customer',
				'company_name' => 'Acme Cloud',
				'primary_contact_name' => 'Nina Price',
				'primary_contact_email' => 'nina@acmecloud.dev',
				'payment_method' => 'invoice',
				'account_status' => 'active',
			),
		);

		$record = \LicencePress\Admin\Manager\Customer\CustomerManager::get_customer( 81 );
		$this->assertNotNull( $record );
		$this->assertSame( 81, (int) ( $record['id'] ?? 0 ) );
		$this->assertSame( 'Acme Cloud', $record['company_name'] );
		$this->assertSame( 'Nina Price', $record['primary_contact_name'] );
		$this->assertSame( 'invoice', $record['payment_method'] );
	}

	public function test_customer_save_persists_metadata_for_real_user_records(): void {
		$GLOBALS['licencepress_test_users'] = array(
			(object) array(
				'ID'            => 91,
				'display_name'  => 'Delta Valley',
				'user_email'    => 'billing@deltavalley.dev',
				'roles'         => array( \LicencePress\Includes\Core\CustomerRoles::CUSTOMER_ROLE ),
			),
		);
		$GLOBALS['licencepress_test_user_meta'] = array(
			91 => array(
				'company_name' => 'Delta Valley',
				'customer_type' => 'Customer',
				'account_status' => 'active',
			),
		);

		$updated = \LicencePress\Admin\Manager\Customer\CustomerManager::save_customer(
			91,
			array(
				'company_name' => 'Delta Valley Ltd',
				'customer_type' => 'Internal Customer',
				'primary_contact_name' => 'A. Singh',
				'primary_contact_email' => 'a.singh@deltavalley.dev',
				'payment_method' => 'card',
				'account_status' => 'trial',
			)
		);

		$this->assertTrue( $updated );
		$this->assertSame( 'Delta Valley Ltd', $GLOBALS['licencepress_test_user_meta'][91]['company_name'] );
		$this->assertSame( 'Internal Customer', $GLOBALS['licencepress_test_user_meta'][91]['customer_type'] );
		$this->assertSame( 'trial', $GLOBALS['licencepress_test_user_meta'][91]['account_status'] );
	}

	public function test_customer_detail_renders_manage_licence_actions(): void {
		$GLOBALS['licencepress_test_users'] = array(
			(object) array(
				'ID'            => 141,
				'display_name'  => 'Delta Works',
				'user_email'    => 'ops@deltaworks.dev',
				'roles'         => array( \LicencePress\Includes\Core\CustomerRoles::CUSTOMER_ROLE ),
			),
		);
		$GLOBALS['licencepress_test_user_meta'] = array(
			141 => array(
				'company_name' => 'Delta Works',
				'customer_type' => 'Customer',
				'account_status' => 'active',
			),
		);
		$GLOBALS['licencepress_test_licences'] = array(
			array(
				'id' => 2001,
				'customer_id' => '141',
				'product_id' => 'delta-pro',
				'token' => 'LP-TEST-REVOKE-1',
				'status' => 'active',
				'expires_at' => '2030-12-31 00:00:00',
			),
		);

		ob_start();
		( new \LicencePress\Admin\Manager\Customer\CustomerOverview() )->render( 141 );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'data-customer-action="issue-licence"', $output );
		$this->assertStringContainsString( 'data-customer-action="revoke-licence"', $output );
		$this->assertStringContainsString( 'data-customer-id="141"', $output );
		$this->assertStringContainsString( 'data-licence-token="LP-TEST-REVOKE-1"', $output );
	}

	public function test_customer_detail_loads_real_licence_records_for_customer(): void {
		$GLOBALS['licencepress_test_users'] = array(
			(object) array(
				'ID'            => 111,
				'display_name'  => 'Blue Peak',
				'user_email'    => 'ops@bluepeak.dev',
				'roles'         => array( \LicencePress\Includes\Core\CustomerRoles::CUSTOMER_ROLE ),
			),
		);
		$GLOBALS['licencepress_test_user_meta'] = array(
			111 => array(
				'company_name' => 'Blue Peak',
				'customer_type' => 'Customer',
				'account_status' => 'active',
			),
		);
		$GLOBALS['licencepress_test_licences'] = array(
			array(
				'id' => 1001,
				'customer_id' => '111',
				'product_id' => 'core-pro',
				'status' => 'active',
				'expires_at' => '2030-12-31 00:00:00',
			),
			array(
				'id' => 1002,
				'customer_id' => '111',
				'product_id' => 'studio-suite',
				'status' => 'expiring',
				'expires_at' => '2026-09-20 00:00:00',
			),
		);

		$licences = \LicencePress\Admin\Manager\Customer\CustomerManager::customer_licences( 111 );

		$this->assertCount( 2, $licences );
		$this->assertSame( 'core-pro', $licences[0]['product_id'] );
		$this->assertSame( 'active', $licences[0]['status'] );
		$this->assertSame( 'studio-suite', $licences[1]['product_id'] );
		$this->assertSame( 'expiring', $licences[1]['status'] );
	}

	public function test_customer_can_issue_and_revoke_licences(): void {
		$GLOBALS['licencepress_test_users'] = array(
			(object) array(
				'ID'            => 121,
				'display_name'  => 'Apex Studio',
				'user_email'    => 'ops@apexstudio.dev',
				'roles'         => array( \LicencePress\Includes\Core\CustomerRoles::CUSTOMER_ROLE ),
			),
		);
		$GLOBALS['licencepress_test_licences'] = array();

		$issued = \LicencePress\Admin\Manager\Customer\CustomerManager::issue_customer_licence(
			121,
			array(
				'product_id' => 'pro-suite',
				'days' => 30,
				'site_url' => 'https://apexstudio.dev',
				'features' => array( 'support', 'updates' ),
			)
		);

		$this->assertNotNull( $issued );
		$this->assertSame( 'pro-suite', $issued['product_id'] );
		$this->assertSame( 'active', $issued['status'] );
		$this->assertCount( 1, \LicencePress\Admin\Manager\Customer\CustomerManager::customer_licences( 121 ) );

		$this->assertTrue( \LicencePress\Admin\Manager\Customer\CustomerManager::revoke_customer_licence( 121, $issued['token'] ) );
		$this->assertSame( 'revoked', \LicencePress\Admin\Manager\Customer\CustomerManager::customer_licences( 121 )[0]['status'] );
	}

	public function test_customer_dashboard_and_checkout_render_customer_ui_sections(): void {
		$GLOBALS['licencepress_test_users'] = array(
			(object) array(
				'ID'            => 151,
				'display_name'  => 'Aurora Labs',
				'user_email'    => 'team@auroralabs.dev',
				'roles'         => array( \LicencePress\Includes\Core\CustomerRoles::CUSTOMER_ROLE ),
			),
		);
		$GLOBALS['licencepress_test_user_meta'] = array(
			151 => array(
				'company_name' => 'Aurora Labs',
				'customer_type' => 'Customer',
				'account_status' => 'active',
			),
		);

		ob_start();
		( new \LicencePress\Admin\Manager\Customer\CustomerDashboard() )->render();
		$dashboard_html = ob_get_clean();

		$this->assertStringContainsString( 'Customer Directory', $dashboard_html );
		$this->assertStringContainsString( 'Aurora Labs', $dashboard_html );

		ob_start();
		( new \LicencePress\Admin\Manager\Customer\CustomerCheckout() )->render( 151 );
		$checkout_html = ob_get_clean();

		$this->assertStringContainsString( 'Customer checkout', $checkout_html );
		$this->assertStringContainsString( 'Checkout summary', $checkout_html );
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
