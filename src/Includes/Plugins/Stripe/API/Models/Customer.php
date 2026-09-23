<?php
/**
 * Stripe customer model.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\Stripe\API\Models
 */

namespace LicencePress\Includes\Plugins\Stripe\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Customer extends AbstractModel {
	/**
	 * Customer ID.
	 *
	 * @var string|null
	 */
	public $id = null;

	/**
	 * Customer email.
	 *
	 * @var string|null
	 */
	public $email = null;

	/**
	 * Customer name.
	 *
	 * @var string|null
	 */
	public $name = null;

	/**
	 * Customer metadata.
	 *
	 * @var array
	 */
	public $metadata = array();
}
