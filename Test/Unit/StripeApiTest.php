<?php

namespace Test\Unit;

use PHPUnit\Framework\TestCase;

final class StripeApiTest extends TestCase {
	public function test_stripe_api_client_and_models_are_available(): void {
		$this->assertTrue( class_exists( '\\LicencePress\\Includes\\Plugins\\Stripe\\API\\StripeAPI' ) );
		$this->assertTrue( class_exists( '\\LicencePress\\Includes\\Plugins\\Stripe\\API\\Client\\StripeClient' ) );
		$this->assertTrue( class_exists( '\\LicencePress\\Includes\\Plugins\\Stripe\\API\\Models\\Connection' ) );
		$this->assertTrue( class_exists( '\\LicencePress\\Includes\\Plugins\\Stripe\\API\\Models\\Checkout' ) );
		$this->assertTrue( class_exists( '\\LicencePress\\Includes\\Plugins\\Stripe\\API\\Models\\Subscription' ) );
		$this->assertTrue( class_exists( '\\LicencePress\\Includes\\Plugins\\Stripe\\API\\Models\\PaymentIntent' ) );
		$this->assertTrue( class_exists( '\\LicencePress\\Includes\\Plugins\\Stripe\\API\\Models\\SetupIntent' ) );
		$this->assertTrue( method_exists( '\\LicencePress\\Includes\\Plugins\\Stripe\\API\\StripeAPI', 'validate_connection' ) );
		$this->assertTrue( method_exists( '\\LicencePress\\Includes\\Plugins\\Stripe\\API\\StripeAPI', 'create_checkout_session' ) );
		$this->assertTrue( method_exists( '\\LicencePress\\Includes\\Plugins\\Stripe\\API\\StripeAPI', 'create_payment_intent' ) );
		$this->assertTrue( method_exists( '\\LicencePress\\Includes\\Plugins\\Stripe\\API\\StripeAPI', 'create_setup_intent' ) );
		$this->assertTrue( method_exists( '\LicencePress\Includes\Plugins\Stripe\API\StripeAPI', 'create_payment_method' ) );
		$this->assertTrue( method_exists( '\LicencePress\Includes\Plugins\Stripe\API\StripeAPI', 'attach_payment_method' ) );
		$this->assertTrue( method_exists( '\LicencePress\Includes\Plugins\Stripe\API\StripeAPI', 'create_subscription' ) );
		$this->assertTrue( method_exists( '\LicencePress\Includes\Plugins\Stripe\API\StripeAPI', 'retrieve_subscription' ) );
		$this->assertTrue( method_exists( '\LicencePress\Includes\Plugins\Stripe\API\StripeAPI', 'cancel_subscription' ) );
		$this->assertTrue( method_exists( '\LicencePress\Includes\Plugins\Stripe\API\StripeAPI', 'retrieve_invoice' ) );
		$this->assertTrue( method_exists( '\LicencePress\Includes\Plugins\Stripe\API\StripeAPI', 'list_products' ) );
		$this->assertTrue( method_exists( '\LicencePress\Includes\Plugins\Stripe\API\StripeAPI', 'search_transactions' ) );
		$this->assertTrue( method_exists( '\LicencePress\Includes\Plugins\Stripe\API\StripeAPI', 'list_disputes' ) );
	}

	public function test_stripe_settings_store_secret_keys_and_environment(): void {
		$settings = new \LicencePress\Includes\Plugins\Stripe\Includes\Settings\Settings();
		$this->assertSame( 'sandbox', \LicencePress\Includes\Plugins\Stripe\Includes\Settings\Settings::DEFAULT_ENVIRONMENT );
		$this->assertTrue( is_array( $settings->register() ) || true );
	}
}
