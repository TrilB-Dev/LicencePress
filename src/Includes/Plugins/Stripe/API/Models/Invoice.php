<?php
/**
 * Stripe invoice model.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\Stripe\API\Models
 */

namespace LicencePress\Includes\Plugins\Stripe\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Invoice extends AbstractModel {
	/**
	 * Invoice ID.
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
	 * Invoice status.
	 *
	 * @var string|null
	 */
	public $status = null;

	/**
	 * Total in cents.
	 *
	 * @var int|null
	 */
	public $total = null;
}
