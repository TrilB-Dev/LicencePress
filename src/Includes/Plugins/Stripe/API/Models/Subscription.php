<?php
/**
 * Stripe subscription model.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\Stripe\API\Models
 */

namespace LicencePress\Includes\Plugins\Stripe\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Subscription extends AbstractModel {
	/**
	 * Stripe subscription ID.
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
	 * Stripe status.
	 *
	 * @var string|null
	 */
	public $status = null;

	/**
	 * Subscription items array.
	 *
	 * @var array
	 */
	public $items = array();

	/**
	 * Amount in cents or amount values.
	 *
	 * @var array
	 */
	public $metadata = array();

	/**
	 * Whether the subscription is set to cancel at the end of the current period.
	 *
	 * @var bool|null
	 */
	public $cancel_at_period_end = null;

	/**
	 * Current period start timestamp.
	 *
	 * @var int|null
	 */
	public $current_period_start = null;

	/**
	 * Current period end timestamp.
	 *
	 * @var int|null
	 */
	public $current_period_end = null;

	/**
	 * Convert the model into a payload.
	 *
	 * @return array The payload array.
	 */
	public function to_payload(): array {
		return $this->to_array();
	}
}
