<?php

namespace LicencePress\Test\Unit;

use LicencePress\Includes\Plugins\PayPal\API\Models\Disputes;
use LicencePress\Includes\Plugins\PayPal\API\Models\Invoicing;
use LicencePress\Includes\Plugins\PayPal\API\Models\PaymentMethods;
use LicencePress\Includes\Plugins\PayPal\API\Models\Transactions;
use PHPUnit\Framework\TestCase;

final class PayPalModelsTest extends TestCase {
	public function test_transaction_model_serializes_payload(): void {
		$transactions = new Transactions();
		$transactions->add_transaction(
			array(
				'id' => 'txn_123',
				'status' => 'COMPLETED',
				'amount' => array(
					'value' => '25.00',
					'currency_code' => 'USD',
				),
			)
		);

		$this->assertCount( 1, $transactions->get_transactions() );
		$this->assertSame( 'txn_123', $transactions->to_payload()['transactions'][0]['id'] );
	}

	public function test_invoicing_model_builds_invoice_payload(): void {
		$invoicing = new Invoicing();
		$invoicing->create_invoice(
			array(
				'merchant_info' => array( 'email' => 'merchant@example.com' ),
				'billing_info' => array(
					array(
						'email' => 'buyer@example.com',
					),
				),
				'items' => array(
					array(
						'name' => 'Licence',
						'quantity' => '1',
						'unit_price' => array( 'currency' => 'USD', 'value' => '20.00' ),
					),
				),
			)
		);

		$payload = $invoicing->to_payload();
		$this->assertSame( 'merchant@example.com', $payload['invoices'][0]['merchant_info']['email'] );
		$this->assertSame( 'Licence', $payload['invoices'][0]['items'][0]['name'] );
	}

	public function test_payment_methods_model_tracks_supported_methods(): void {
		$methods = new PaymentMethods();
		$methods->add_method(
			array(
				'customer_type' => 'PAYMENT_METHOD',
				'method' => 'paypal',
				'supported' => true,
			)
		);

		$this->assertTrue( $methods->to_payload()['payment_methods'][0]['supported'] );
	}

	public function test_disputes_model_tracks_and_validates_dispute_data(): void {
		$disputes = new Disputes();
		$disputes->add_dispute(
			array(
				'id' => 'PP-123',
				'reason' => 'MERCHANDISE_NOT_RECEIVED',
				'status' => 'OPEN',
			)
		);

		$this->assertTrue( $disputes->validate() );
		$this->assertSame( 'OPEN', $disputes->to_payload()['disputes'][0]['status'] );
	}
}
