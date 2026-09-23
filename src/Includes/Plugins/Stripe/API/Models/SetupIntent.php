<?php
/**
 * Stripe setup intent model.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\Stripe\API\Models
 */

namespace LicencePress\Includes\Plugins\Stripe\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SetupIntent extends AbstractModel {
	/**
	 * Setup intent ID.
	 *
	 * @var string|null
	 */
	public $id = null;

	/**
	 * The customer ID.
	 *
	 * @var string|null
	 */
	public $customer = null;

	/**
	 * The payment method to attach,
	 * if already known.
	 *
	 * @var string|null
	 */
	public $payment_method = null;

	/**
	 * Supported payment method types for the setup flow.
	 *
	 * @var array
	 */
	public $payment_method_types = array();

	/**
	 * Whether the setup should be confirmed.
	 *
	 * @var bool|null
	 */
	public $confirm = null;

	/**
	 * Whether Stripe should automatically collect a payment method.
	 *
	 * @var bool|null
	 */
	public $automatic_payment_methods = null;

	/**
	 * Setup intent status.
	 *
	 * @var string|null
	 */
	public $status = null;

	/**
	 * The payload to send to Stripe.
	 *
	 * @return array The normalized payload.
	 */
	public function to_payload(): array {
		return $this->to_array();
	}
}
