<?php
/**
 * Stripe payment method model.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\Stripe\API\Models
 */

namespace LicencePress\Includes\Plugins\Stripe\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PaymentMethod extends AbstractModel {
	/**
	 * Payment method ID.
	 *
	 * @var string|null
	 */
	public $id = null;

	/**
	 * Customer ID.
	 *
	 * @var string|null
	 */
	public $customer = null;

	/**
	 * Payment method type.
	 *
	 * @var string|null
	 */
	public $type = null;

	/**
	 * Card details.
	 *
	 * @var array
	 */
	public $card = array();

	/**
	 * Billing details for the payment method.
	 *
	 * @var array
	 */
	public $billing_details = array();

	/**
	 * Wallet details for wallets such as Apple Pay or Google Pay.
	 *
	 * @var array
	 */
	public $wallet = array();

	/**
	 * Payment method metadata.
	 *
	 * @var array
	 */
	public $metadata = array();
}
