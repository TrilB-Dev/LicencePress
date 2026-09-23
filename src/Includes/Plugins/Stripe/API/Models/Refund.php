<?php
/**
 * Stripe refund model.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\Stripe\API\Models
 */

namespace LicencePress\Includes\Plugins\Stripe\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Refund extends AbstractModel {
	/**
	 * Refund ID.
	 *
	 * @var string|null
	 */
	public $id = null;

	/**
	 * Payment intent or charge ID.
	 *
	 * @var string|null
	 */
	public $payment_intent = null;

	/**
	 * Amount in cents.
	 *
	 * @var int|null
	 */
	public $amount = null;

	/**
	 * Refund status.
	 *
	 * @var string|null
	 */
	public $status = null;
}
