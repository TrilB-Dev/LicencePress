<?php

// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.Commenting.VariableComment.Missing, Generic.CodeAnalysis.UnusedFunctionParameter

namespace {
	if ( ! function_exists( '\get_pages' ) ) {
		function get_pages( $args = array() ) {
			return array();
		}
	}

	if ( ! function_exists( 'add_filter' ) ) {
		function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
			$GLOBALS['_licencepress_test_filters'][ $hook ][] = array(
				'callback'      => $callback,
				'priority'      => $priority,
				'accepted_args' => $accepted_args,
			);
			return true;
		}
	}

	if ( ! function_exists( 'apply_filters' ) ) {
		function apply_filters( $hook, $value ) {
			$filters = $GLOBALS['_licencepress_test_filters'] ?? array();
			if ( empty( $filters[ $hook ] ) ) {
				return $value;
			}

			foreach ( $filters[ $hook ] as $filter ) {
				$value = call_user_func( $filter['callback'], $value );
			}

			return $value;
		}
	}

	if ( ! function_exists( 'do_action_ref_array' ) ) {
		function do_action_ref_array( $hook, $args ) {
			return null;
		}
	}
}

namespace LicencePress\Test\Unit {
	use Defuse\Crypto\Key;
	use LicencePress\Includes\Core\PostType;
	use LicencePress\Includes\Core\Taxonomy;
	use LicencePress\Includes\Functions\Helpers\AMHelper;
	use LicencePress\Includes\Functions\Helpers\CronJobHelper;
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

		$GLOBALS['_licencepress_test_filters'] = array();

		\LicencePress\Includes\Settings\SettingsManager::reset_runtime_store();

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

	public function test_paypal_gateway_uses_raw_http_requests_instead_of_paypal_php_sdk(): void {
		$this->assertFalse( method_exists( '\\LicencePress\\Includes\\Plugins\\PayPal\\Includes\\API\\PayPalClient', 'build_sdk_client' ) );
		$this->assertTrue( method_exists( '\\LicencePress\\Includes\\Plugins\\PayPal\\Includes\\API\\PayPalClient', 'create_order' ) );
	}

	public function test_paypal_order_payload_includes_checkout_details(): void {
		$payload = \LicencePress\Includes\Plugins\PayPal\Includes\API\PayPalClient::prepare_order_payload(
			array(
				'amount'      => '19.99',
				'currency'    => 'USD',
				'description' => 'LicencePress Pro',
				'custom_id'   => 'licence-42',
			),
			'checkout'
		);

		$this->assertSame( 'CHECKOUT', $payload['intent'] );
		$this->assertSame( 'licence-42', $payload['purchase_units'][0]['custom_id'] );
		$this->assertSame( '19.99', $payload['purchase_units'][0]['amount']['value'] );
		$this->assertSame( 'USD', $payload['purchase_units'][0]['amount']['currency_code'] );
		$this->assertNotEmpty( $payload['application_context']['return_url'] );
	}

	public function test_paypal_oauth_redirect_uses_paypal_partner_signup_url(): void {
		$settings = array(
			'paypal_environment'                 => 'sandbox',
			'paypal_api_sandbox_client_id'       => '',
			'paypal_api_sandbox_client_secret'   => '',
		);

		$url = \LicencePress\Includes\Plugins\PayPal\Includes\Functions\Helpers\PayPalOAuthHelper::build_connect_url( $settings, 'state-123', 'sandbox' );
		$this->assertStringContainsString( 'https://www.sandbox.paypal.com/bizsignup/partner/entry', $url );
		$this->assertStringContainsString( 'displayMode=minibrowser', $url );
		$this->assertStringNotContainsString( 'page=licencepress', $url );
	}

	public function test_paypal_rest_connect_route_returns_connect_url(): void {
		$plugin = new \LicencePress\Includes\Plugins\PayPal\PayPal();
		$this->assertTrue( method_exists( $plugin, 'register_rest_routes' ) );
		$this->assertTrue( $plugin instanceof \LicencePress\Includes\Plugins\RestRouteProviderInterface );

		$settings = array(
			'paypal_environment'                => 'sandbox',
			'paypal_api_sandbox_client_id' => 'sandbox-client-id',
		);
		$url = \LicencePress\Includes\Plugins\PayPal\Includes\Functions\Helpers\PayPalOAuthHelper::build_connect_url( $settings, 'state-rest', 'sandbox' );
		$this->assertStringStartsWith( 'https://www.sandbox.paypal.com/bizsignup/partner/entry?', $url );
		$this->assertStringContainsString( 'displayMode=minibrowser', $url );
		$this->assertStringContainsString( 'state=state-rest', $url );
	}

	public function test_paypal_rest_api_builds_connect_url_and_persists_encrypted_credentials(): void {
		$settings = array(
			'client_id'     => 'sandbox-client-id',
			'client_secret' => 'sandbox-client-secret',
			'environment'   => 'sandbox',
		);

		$connect_url = \LicencePress\Includes\Plugins\PayPal\API\PayPalRESTAPI::build_connect_url( $settings );
		$this->assertStringStartsWith( 'https://www.sandbox.paypal.com/bizsignup/partner/entry?', $connect_url );
		$this->assertStringContainsString( 'displayMode=minibrowser', $connect_url );

		$saved = \LicencePress\Includes\Plugins\PayPal\API\PayPalRESTAPI::save_oauth_credentials( $settings );
		$this->assertTrue( $saved );
		$this->assertNotSame( 'sandbox-client-secret', \LicencePress\Includes\Settings\Settings::get( 'paypal_api_sandbox_client_secret' ) );
		$this->assertSame( 'sandbox-client-id', \LicencePress\Includes\Plugins\PayPal\Includes\Settings\Settings::get_client_id( 'sandbox' ) );
	}

	public function test_paypal_oauth_connect_uses_partner_signup_endpoint(): void {
		$settings = array(
			'paypal_environment'                => 'sandbox',
			'paypal_api_sandbox_client_id' => 'sandbox-client-id',
		);

		$url = \LicencePress\Includes\Plugins\PayPal\Includes\Functions\Helpers\PayPalOAuthHelper::build_connect_url( $settings, 'state-123', 'sandbox' );

		$this->assertStringStartsWith( 'https://www.sandbox.paypal.com/bizsignup/partner/entry?', $url );
		$this->assertStringContainsString( 'client_id=sandbox-client-id', $url );
		$this->assertStringContainsString( 'state=state-123', $url );
		$this->assertStringContainsString( 'displayMode=minibrowser', $url );
		$this->assertStringNotContainsString( 'redirect_uri=', $url );
	}

	public function test_paypal_oauth_connect_uses_decrypted_client_id_from_settings_store(): void {
		$encrypted_id = \LicencePress\Includes\Functions\Helpers\EncryptionHelper::encrypt( 'sandbox-client-456' );
		\LicencePress\Includes\Settings\Settings::set_group(
			'paypal',
			array(
				'paypal_environment'                 => 'sandbox',
				'paypal_api_sandbox_client_id'       => $encrypted_id,
				'paypal_api_sandbox_client_secret'   => 'secret',
			)
		);

		$url = \LicencePress\Includes\Plugins\PayPal\Includes\Functions\Helpers\PayPalOAuthHelper::build_connect_url( array(), 'state-456', 'sandbox' );

		$this->assertStringContainsString( 'client_id=sandbox-client-456', $url );
		$this->assertStringNotContainsString( 'client_id=' . rawurlencode( $encrypted_id ), $url );
	}

	public function test_paypal_oauth_prefers_server_config_over_admin_store(): void {
		putenv( 'LICENCEPRESS_PAYPAL_API_LIVE_CLIENT_ID=server-live-client-id' );
		putenv( 'LICENCEPRESS_PAYPAL_API_LIVE_CLIENT_SECRET=server-live-client-secret' );
		$_ENV['LICENCEPRESS_PAYPAL_API_LIVE_CLIENT_ID'] = 'server-live-client-id';
		$_ENV['LICENCEPRESS_PAYPAL_API_LIVE_CLIENT_SECRET'] = 'server-live-client-secret';

		$url = \LicencePress\Includes\Plugins\PayPal\Includes\Functions\Helpers\PayPalOAuthHelper::build_connect_url( array(), 'state-789', 'live' );

		$this->assertStringContainsString( 'client_id=server-live-client-id', $url );
		$this->assertSame( 'server-live-client-secret', \LicencePress\Includes\Plugins\PayPal\Includes\Settings\Settings::get_client_secret( 'live' ) );

		putenv( 'LICENCEPRESS_PAYPAL_API_LIVE_CLIENT_ID' );
		putenv( 'LICENCEPRESS_PAYPAL_API_LIVE_CLIENT_SECRET' );
		unset( $_ENV['LICENCEPRESS_PAYPAL_API_LIVE_CLIENT_ID'], $_ENV['LICENCEPRESS_PAYPAL_API_LIVE_CLIENT_SECRET'] );
	}

	public function test_paypal_oauth_success_redirect_returns_to_billing_settings_hash(): void {
		$url = \LicencePress\Includes\Plugins\PayPal\Includes\Functions\Helpers\PayPalOAuthHelper::get_success_redirect_url();

		$this->assertStringContainsString( 'admin.php?page=licencepress&group=settings&tab=billing&bt=paypal', $url );
		$this->assertStringNotContainsString( 'paypal_environment=', $url );
	}

	public function test_paypal_oauth_redirects_are_allowed_for_paypal_hosts(): void {
		$hosts = \LicencePress\Includes\Plugins\PayPal\Includes\Functions\Helpers\PayPalOAuthHelper::register_allowed_redirect_hosts();

		$this->assertContains( 'www.paypal.com', $hosts );
		$this->assertContains( 'www.sandbox.paypal.com', $hosts );
		$this->assertContains( 'paypal.com', $hosts );
		$this->assertContains( 'sandbox.paypal.com', $hosts );
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

	public function test_general_settings_use_default_prefixed_form_field_names_only(): void {
		ob_start();
		( new \LicencePress\Admin\Manager\Settings\SettingsGeneral() )->render( array() );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'licencepress_general[default_licensor_name]', $output );
		$this->assertStringContainsString( 'licencepress_general[default_licence_pattern_type]', $output );
		$this->assertStringContainsString( 'licencepress_general[default_custom_licence_pattern]', $output );
		$this->assertStringNotContainsString( 'licencepress_general[licence_name]', $output );
		$this->assertStringNotContainsString( 'licencepress_general[entity_type]', $output );
		$this->assertStringNotContainsString( 'licencepress_general[renewal_policy_mode]', $output );
	}

	public function test_general_settings_save_keys_match_the_form_field_names(): void {
		$settings = new \LicencePress\Includes\Functions\Admin\FunctionsSettings( new \LicencePress\Includes\Functions\Admin\FunctionsPlugins() );

		$result = $settings->sanitize_general(
			array(
				'default_licensor_type'                       => 'company',
				'default_licensor_name'                       => 'Acme Ltd',
				'default_licensor_country'                    => 'United Kingdom',
				'default_licence_prefix'                      => 'LIC',
				'default_licence_platform'                    => array( 'website', 'windows_software' ),
				'default_renewal_policy_mode'                 => 'custom',
				'default_custom_licence_renewal_policy_page'  => 42,
				'default_licence_pattern_type'                => 'custom',
				'default_custom_licence_pattern'              => 'AAA-999',
				'default_licence_pattern_format'              => 'letters',
				'default_exclude_ambiguous_characters'        => array( '0', 'O' ),
				'default_licence_pattern_letter_case'         => 'uppercase',
				'default_licence_pattern_separator'           => '_',
			)
		);

		$this->assertArrayHasKey( 'default_licensor_name', $result );
		$this->assertArrayHasKey( 'default_licence_pattern_type', $result );
		$this->assertArrayNotHasKey( 'licence_name', $result );
		$this->assertArrayNotHasKey( 'entity_type', $result );
		$this->assertSame( 'Acme Ltd', Settings::get( 'default_licensor_name' ) );
		$this->assertSame( 'custom', Settings::get( 'default_licence_pattern_type' ) );
		$this->assertSame( '_', Settings::get( 'default_licence_pattern_separator' ) );
	}

	public function test_admin_settings_reinitializes_bootstrap_selects_after_tab_reload(): void {
		$script = file_get_contents( dirname( __DIR__, 2 ) . '/src/Assets/js/admin.settings.js' );
		$this->assertIsString( $script );
		$this->assertStringContainsString( 'initializeBootstrapSelects', $script );
		$this->assertStringContainsString( 'window.licencepressBootstrapSelect?.initialize', $script );
		$this->assertStringContainsString( 'panel.innerHTML = response.data.html;', $script );
	}

	public function test_custom_pattern_controls_hide_when_pattern_is_not_custom(): void {
		$script = file_get_contents( dirname( __DIR__, 2 ) . '/src/Assets/js/admin.settings.js' );
		$this->assertIsString( $script );
		$this->assertTrue(
			str_contains( $script, "type === 'custom'" ) || str_contains( $script, "patternType.value === 'custom'" )
		);
		$this->assertStringContainsString( 'patternFormatRow', $script );
		$this->assertStringContainsString( 'patternSeparatorRow', $script );
	}

	public function test_saved_general_and_billing_settings_render_in_forms(): void {
		Settings::set_group(
			'general',
			array(
				'default_licensor_type'                       => 'company',
				'default_licensor_name'                       => 'Acme Ltd',
				'default_licensor_country'                    => 'United Kingdom',
				'default_licence_prefix'                      => 'LIC',
				'default_licence_platform'                    => array( 'website', 'windows_software' ),
				'default_renewal_policy_mode'                 => 'custom',
				'default_custom_licence_renewal_policy_page'  => 42,
				'default_licence_pattern_type'                => 'custom',
				'default_custom_licence_pattern'              => 'AAA-999',
				'default_licence_pattern_format'              => 'letters',
				'default_exclude_ambiguous_characters'        => array( '0', 'O' ),
				'default_licence_pattern_letter_case'         => 'uppercase',
				'default_licence_pattern_separator'           => '_',
			)
		);
		Settings::set_group(
			'billing',
			array(
				'billing_name'      => 'Acme Billing',
				'billing_address_1' => '12 Market Street',
				'town'              => 'London',
				'country'           => 'United Kingdom',
				'email_address'     => 'billing@acme.example',
				'phone_number'      => '+44 20 1234 5678',
				'invoice_prefix'    => 'INV-',
			)
		);

		ob_start();
		( new \LicencePress\Admin\Manager\Settings\SettingsGeneral() )->render( Settings::get_group( 'general', array() ) );
		$general_output = ob_get_clean();

		ob_start();
		( new \LicencePress\Admin\Manager\Settings\SettingsBilling() )->render( Settings::get_group( 'billing', array() ) );
		$billing_output = ob_get_clean();

		$this->assertStringContainsString( 'Acme Ltd', $general_output );
		$this->assertStringContainsString( 'AAA-999', $general_output );
		$this->assertStringContainsString( 'Acme Billing', $billing_output );
		$this->assertStringContainsString( 'billing@acme.example', $billing_output );
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

	public function test_billing_settings_include_paypal_tab_and_form_fields(): void {
		$billing_settings_paypal = new \LicencePress\Includes\Plugins\PayPal\Admin\BillingSettingsPayPal();
		\add_filter( 'licencepress_billing_settings_tabs', array( $billing_settings_paypal, 'register_billing_tab' ) );

		ob_start();
		( new \LicencePress\Admin\Manager\Settings\SettingsBilling() )->render( array() );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'data-licencepress-billing-tab="paypal"', $output );
		$this->assertStringContainsString( 'PayPal', $output );
		$this->assertStringContainsString( 'licencepress_paypal[paypal_checkout_enabled]', $output );
		$this->assertStringContainsString( 'licencepress_paypal[paypal_environment]', $output );
		$this->assertStringContainsString( 'licencepress_paypal[paypal_currency]', $output );
		$this->assertStringContainsString( 'licencepress_paypal[paypal_subscriptions_enabled]', $output );
		$this->assertStringNotContainsString( 'licencepress_paypal[paypal_api_sandbox_client_id]', $output );
	}

	public function test_paypal_oauth_connect_button_is_visible_without_stored_client_id(): void {
		\LicencePress\Includes\Settings\Settings::set_group(
			'paypal',
			array(
				'paypal_api_sandbox_client_id' => '',
			)
		);

		ob_start();
		\LicencePress\Includes\Plugins\PayPal\Includes\Settings\Settings::render_oauth_connection( '', 'paypal_sandbox_oauth_connect', 'paypal_sandbox_oauth_connect' );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Connect PayPal', $output );
		$this->assertStringContainsString( 'Configure the PayPal Sandbox app', $output );
	}

	public function test_paypal_oauth_connect_url_keeps_action_before_hash_fragment(): void {
		\LicencePress\Includes\Settings\Settings::set_group(
			'paypal',
			array(
				'paypal_api_sandbox_client_id' => 'sandbox-client-123',
			)
		);

		ob_start();
		\LicencePress\Includes\Plugins\PayPal\Includes\Settings\Settings::render_oauth_connection( '', 'paypal_sandbox_oauth_connect', 'paypal_sandbox_oauth_connect' );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'https://www.sandbox.paypal.com/bizsignup/partner/entry?', $output );
		$this->assertStringContainsString( 'displayMode=minibrowser', $output );
		$this->assertStringContainsString( 'client_id=sandbox-client-123', $output );
		$this->assertStringNotContainsString( 'admin.php?page=licencepress', $output );
	}

	public function test_demo_plugin_slug_is_ignored_during_plugin_discovery(): void {
		$plugins = \LicencePress\Includes\Plugins\Plugins::get_instance();
		$before  = $plugins->get_registered_plugins();

		$plugin = new class() implements \LicencePress\Includes\Plugins\PluginInterface {
			public function get_slug(): string { return 'demo-plugin-demo'; }
			public function get_name(): string { return 'Demo Plugin'; }
			public function get_version(): string { return '1.0.0'; }
			public function get_icon(): string { return ''; }
			public function get_author(): string { return 'Test'; }
			public function get_author_uri(): string { return 'https://example.com'; }
			public function get_description(): string { return 'Demo plugin'; }
			public function get_uri(): string { return 'https://example.com'; }
			public function get_license(): string { return 'GPL'; }
			public function is_active(): bool { return true; }
			public function init(): void {}
		};

		$plugins->register_plugin_instance( $plugin );

		$this->assertArrayNotHasKey( 'demo-plugin-demo', $plugins->get_registered_plugins() );
		$this->assertSame( $before, $plugins->get_registered_plugins() );
	}

	public function test_plugin_icon_supports_image_urls_and_css_classes(): void {
		$settings_plugins = new \LicencePress\Admin\Manager\Settings\SettingsPlugins();
		$method = new \ReflectionMethod( $settings_plugins, 'render_plugin_icon' );
		$method->setAccessible( true );

		$image_plugin = new class() implements \LicencePress\Includes\Plugins\PluginInterface {
			public function get_slug(): string { return 'demo-image-plugin'; }
			public function get_name(): string { return 'Demo Image Plugin'; }
			public function get_version(): string { return '1.0.0'; }
			public function get_author(): string { return 'Test'; }
			public function get_author_uri(): string { return 'https://example.com'; }
			public function get_description(): string { return 'Demo plugin'; }
			public function get_uri(): string { return 'https://example.com'; }
			public function get_license(): string { return 'GPL'; }
			public function is_active(): bool { return true; }
			public function init(): void {}
			public function get_icon(): array { return array( 'url' => 'https://example.com/icon.png' ); }
		};

		$class_plugin = new class() implements \LicencePress\Includes\Plugins\PluginInterface {
			public function get_slug(): string { return 'demo-class-plugin'; }
			public function get_name(): string { return 'Demo Class Plugin'; }
			public function get_version(): string { return '1.0.0'; }
			public function get_author(): string { return 'Test'; }
			public function get_author_uri(): string { return 'https://example.com'; }
			public function get_description(): string { return 'Demo plugin'; }
			public function get_uri(): string { return 'https://example.com'; }
			public function get_license(): string { return 'GPL'; }
			public function is_active(): bool { return true; }
			public function init(): void {}
			public function get_icon(): string { return 'dashicons dashicons-admin-plugins'; }
		};

		ob_start();
		$method->invoke( $settings_plugins, $image_plugin );
		$image_output = ob_get_clean();
		$this->assertStringContainsString( 'https://example.com/icon.png', $image_output );
		$this->assertStringNotContainsString( 'dashicons dashicons-admin-plugins', $image_output );

		ob_start();
		$method->invoke( $settings_plugins, $class_plugin );
		$class_output = ob_get_clean();
		$this->assertStringContainsString( 'dashicons dashicons-admin-plugins', $class_output );
		$this->assertStringNotContainsString( 'src="dashicons', $class_output );
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

	public function test_cron_job_helper_registers_jobs_by_scope(): void {
		$hook = 'licencepress_test_cleanup';
		$registered = CronJobHelper::register(
			'core',
			$hook,
			'hourly',
			array( 'site_id' => 1 ),
			static function ( $site_id = 0 ) {
				return $site_id;
			},
			'Runs LicencePress housekeeping'
		);

		$normalized = CronJobHelper::normalize_hook( 'core', $hook );
		$this->assertTrue( $registered );
		$this->assertTrue( wp_next_scheduled( $normalized, array( 'site_id' => 1 ) ) !== false );
		$this->assertTrue( CronJobHelper::exists( 'core', $hook, array( 'site_id' => 1 ) ) );
		$this->assertTrue( CronJobHelper::clear( 'core', $hook ) );
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
		\LicencePress\Includes\Core\Roles::install();

		$this->assertNotNull( get_role( \LicencePress\Includes\Core\Roles::CUSTOMER_ROLE ) );
		$this->assertNotNull( get_role( \LicencePress\Includes\Core\Roles::INTERNAL_CUSTOMER_ROLE ) );
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
				'roles'         => array( \LicencePress\Includes\Core\Roles::CUSTOMER_ROLE ),
			),
			(object) array(
				'ID'            => 22,
				'display_name'  => 'Westgate Labs',
				'user_email'    => 'ops@westgate.dev',
				'roles'         => array( \LicencePress\Includes\Core\Roles::INTERNAL_CUSTOMER_ROLE ),
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
				'roles'         => array( \LicencePress\Includes\Core\Roles::CUSTOMER_ROLE ),
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
				'roles'         => array( \LicencePress\Includes\Core\Roles::CUSTOMER_ROLE ),
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
				'roles'         => array( \LicencePress\Includes\Core\Roles::CUSTOMER_ROLE ),
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
				'roles'         => array( \LicencePress\Includes\Core\Roles::CUSTOMER_ROLE ),
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
				'roles'         => array( \LicencePress\Includes\Core\Roles::CUSTOMER_ROLE ),
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
				'roles'         => array( \LicencePress\Includes\Core\Roles::CUSTOMER_ROLE ),
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

	public function test_paypal_subscription_models_can_build_minimal_product_plan_and_subscription_payloads(): void {
		$product = new \LicencePress\Includes\Plugins\PayPal\API\Models\Product();
		$product->set_name( 'LicencePress Pro' );
		$product->set_type( 'SERVICE' );
		$product->set_description( 'LicencePress subscription product' );

		$this->assertSame( 'LicencePress Pro', $product->get_name() );
		$this->assertSame( 'SERVICE', $product->get_type() );
		$this->assertSame( 'LicencePress subscription product', $product->get_description() );

		$plan = new \LicencePress\Includes\Plugins\PayPal\API\Models\Plan( $product );
		$plan->set_name( 'LicencePress Pro Monthly' );
		$plan->set_product_id( $product->get_id() ?: 'PROD-123' );
		$plan->set_billing_cycles(
			array(
				array(
					'frequency' => array(
						'interval_unit' => 'MONTH',
						'interval_count' => 1,
					),
					'tenure_type' => 'REGULAR',
					'sequence' => 1,
					'total_cycles' => 0,
					'pricing_scheme' => array(
						'fixed_price' => array(
							'value' => '19.99',
							'currency_code' => 'USD',
						),
					),
				),
			)
		);
		$plan->set_payment_preferences(
			array(
				'auto_bill_outstanding' => true,
				'payment_failure_threshold' => 3,
			)
		);

		$this->assertSame( 'LicencePress Pro Monthly', $plan->get_name() );
		$this->assertSame( 'PROD-123', $plan->get_product_id() );
		$this->assertCount( 1, $plan->get_billing_cycles() );
		$this->assertSame( 'REGULAR', $plan->get_billing_cycles()[0]['tenure_type'] );

		$subscription = new \LicencePress\Includes\Plugins\PayPal\API\Models\Subscription( $plan );
		$subscription->set_plan_id( $plan->get_id() ?: 'PLAN-123' );
		$subscription->set_subscriber(
			array(
				'name' => array(
					'given_name' => 'Ada',
					'surname' => 'Lovelace',
				),
				'email_address' => 'ada@example.com',
			)
		);
		$subscription->set_application_context(
			array(
				'brand_name' => 'LicencePress',
				'return_url' => 'https://example.com/return',
				'cancel_url' => 'https://example.com/cancel',
			)
		);

		$payload = $subscription->to_array();
		$this->assertSame( 'PLAN-123', $payload['plan_id'] );
		$this->assertSame( 'ada@example.com', $payload['subscriber']['email_address'] );
		$this->assertSame( 'LicencePress', $payload['application_context']['brand_name'] );
	}

	public function test_paypal_rest_api_supports_product_plan_and_subscription_methods(): void {
		$this->assertTrue( method_exists( '\LicencePress\Includes\Plugins\PayPal\API\PayPalRESTAPI', 'create_product' ) );
		$this->assertTrue( method_exists( '\LicencePress\Includes\Plugins\PayPal\API\PayPalRESTAPI', 'create_plan' ) );
		$this->assertTrue( method_exists( '\LicencePress\Includes\Plugins\PayPal\API\PayPalRESTAPI', 'create_subscription' ) );
		$this->assertTrue( method_exists( '\LicencePress\Includes\Plugins\PayPal\API\PayPalRESTAPI', 'get_product' ) );
		$this->assertTrue( method_exists( '\LicencePress\Includes\Plugins\PayPal\API\PayPalRESTAPI', 'get_plan' ) );
		$this->assertTrue( method_exists( '\LicencePress\Includes\Plugins\PayPal\API\PayPalRESTAPI', 'get_subscription' ) );
	}
}

}
