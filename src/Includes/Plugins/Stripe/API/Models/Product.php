<?php
/**
 * Stripe product model.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\Stripe\API\Models
 */

namespace LicencePress\Includes\Plugins\Stripe\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Product extends AbstractModel {
	/**
	 * Product ID.
	 *
	 * @var string|null
	 */
	public $id = null;

	/**
	 * Product name.
	 *
	 * @var string|null
	 */
	public $name = null;

	/**
	 * Product description.
	 *
	 * @var string|null
	 */
	public $description = null;
}
