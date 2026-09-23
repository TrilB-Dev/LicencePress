<?php
/**
 * Stripe price model.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\Stripe\API\Models
 */

namespace LicencePress\Includes\Plugins\Stripe\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Price extends AbstractModel {
	/**
	 * Product ID.
	 *
	 * @var string|null
	 */
	public $product = null;

	/**
	 * Unit amount.
	 *
	 * @var int|null
	 */
	public $unit_amount = null;

	/**
	 * Currency code.
	 *
	 * @var string|null
	 */
	public $currency = null;

	/**
	 * Billing interval.
	 *
	 * @var string|null
	 */
	public $recurring = null;
}
