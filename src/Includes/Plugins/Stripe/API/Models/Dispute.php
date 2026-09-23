<?php
/**
 * Stripe dispute model.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\Stripe\API\Models
 */

namespace LicencePress\Includes\Plugins\Stripe\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Dispute extends AbstractModel {
	/**
	 * Dispute ID.
	 *
	 * @var string|null
	 */
	public $id = null;

	/**
	 * Charge ID.
	 *
	 * @var string|null
	 */
	public $charge = null;

	/**
	 * Dispute status.
	 *
	 * @var string|null
	 */
	public $status = null;

	/**
	 * Dispute reason.
	 *
	 * @var string|null
	 */
	public $reason = null;

	/**
	 * Dispute amount.
	 *
	 * @var int|null
	 */
	public $amount = null;

	/**
	 * Dispute currency.
	 *
	 * @var string|null
	 */
	public $currency = null;

	/**
	 * Dispute evidence payload.
	 *
	 * @var array
	 */
	public $evidence = array();
}
