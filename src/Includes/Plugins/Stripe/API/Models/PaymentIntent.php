<?php
/**
 * Stripe payment intent model.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\Stripe\API\Models
 */

namespace LicencePress\Includes\Plugins\Stripe\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PaymentIntent extends AbstractModel {
	/**
	 * Payment intent ID.
	 *
	 * @var string|null
	 */
	public $id = null;

	/**
	 * The Stripe customer ID.
	 *
	 * @var string|null
	 */
	public $customer = null;

	/**
	 * Amount in cents.
	 *
	 * @var int|null
	 */
	public $amount = null;

	/**
	 * Currency code.
	 *
	 * @var string|null
	 */
	public $currency = null;

	/**
	 * Payment method ID.
	 *
	 * @var string|null
	 */
	public $payment_method = null;

	/**
	 * Supported payment method types for the intent.
	 *
	 * @var array
	 */
	public $payment_method_types = array();

	/**
	 * Whether the intent should be confirmed automatically.
	 *
	 * @var bool|null
	 */
	public $automatic_payment_methods = null;

	/**
	 * Confirmation status.
	 *
	 * @var bool|null
	 */
	public $confirm = null;

	/**
	 * Payment intent status.
	 *
	 * @var string|null
	 */
	public $status = null;

	/**
	 * Intent description.
	 *
	 * @var string|null
	 */
	public $description = null;

	/**
	 * The payload to send to Stripe.
	 *
	 * @return array The normalized payload.
	 */
	public function to_payload(): array {
		return $this->to_array();
	}
}
