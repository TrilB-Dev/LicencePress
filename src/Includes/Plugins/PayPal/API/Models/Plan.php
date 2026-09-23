<?php
/**
 * PayPal plan model.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\API\Models
 */

namespace LicencePress\Includes\Plugins\PayPal\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Error;

class Plan extends Checkout {
	/**
	 * PayPal plan model.
	 *
	 * @var Product|null The associated product.
	 * @since 1.0.0
	 */
	private $product = null;
	/**
	 * The product ID associated with the plan.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $product_id = '';
	/**
	 * The name of the plan.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $name = '';
	/**
	 * The status of the plan.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $status = 'ACTIVE';
	/**
	 * The description of the plan.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $description = '';
	/**
	 * The billing cycles of the plan.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	public $billing_cycles = array();
	/**
	 * The payment preferences of the plan.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	public $payment_preferences = array();
	/**
	 * The taxes of the plan.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	public $taxes = array();
	/**
	 * Whether the plan supports quantity.
	 *
	 * @var bool
	 * @since 1.0.0
	 */
	public $quantity_supported = true;
	/**
	 * The constructor for the Plan model.
	 *
	 * @param Product|null $product The associated product.
	 * @since 1.0.0
	 */
	public function __construct( $product = null ) {
		if ( $product instanceof Product ) {
			$this->product = $product;
			$this->product_id = $product->get_id() ?: $this->product_id;
		}
	}
	/**
	 * Set the product ID associated with the plan.
	 *
	 * @param string $product_id The product ID.
	 * @return void
	 * @since 1.0.0
	 */
	public function set_product_id( $product_id ) {
		$this->product_id = is_scalar( $product_id ) ? (string) $product_id : '';
	}

	/**
	 * Get the product ID associated with the plan.
	 *
	 * @return string The product ID.
	 * @since 1.0.0
	 */
	public function get_product_id() {
		return $this->product_id;
	}

	/**
	 * Set the name of the plan.
	 *
	 * @param string $name The name of the plan.
	 * @return void
	 * @since 1.0.0
	 */
	public function set_name( $name ) {
		$this->name = is_scalar( $name ) ? (string) $name : '';
	}

	/**
	 * Get the name of the plan.
	 *
	 * @return string The name of the plan.
	 * @since 1.0.0
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Set the billing cycles of the plan.
	 *
	 * @param array $billing_cycles The billing cycles of the plan.
	 * @return void
	 * @since 1.0.0
	 */
	public function set_billing_cycles( $billing_cycles ) {
		$this->billing_cycles = is_array( $billing_cycles ) ? $billing_cycles : array();
	}

	/**
	 * Get the billing cycles of the plan.
	 *
	 * @return array The billing cycles of the plan.
	 * @since 1.0.0
	 */
	public function get_billing_cycles() {
		return $this->billing_cycles;
	}

	/**
	 * Set the payment preferences of the plan.
	 *
	 * @param array $payment_preferences The payment preferences of the plan.
	 * @return void
	 * @since 1.0.0
	 */
	public function set_payment_preferences( $payment_preferences ) {
		$this->payment_preferences = is_array( $payment_preferences ) ? $payment_preferences : array();
	}

	/**
	 * Get the payment preferences of the plan.
	 *
	 * @return array The payment preferences of the plan.
	 * @since 1.0.0
	 */
	public function get_payment_preferences() {
		return $this->payment_preferences;
	}
	/**
	 * Convert the plan object to a payload array suitable for API requests.
	 *
	 * @return array The payload array.
	 * @since 1.0.0
	 */
	public function to_payload(): array {
		$payload = array(
			'name' => $this->name,
			'product_id' => $this->product_id,
			'status' => $this->status,
			'description' => $this->description,
			'billing_cycles' => $this->billing_cycles,
			'payment_preferences' => $this->payment_preferences,
		);

		if ( ! empty( $this->taxes ) ) {
			$payload['taxes'] = $this->taxes;
		}
		if ( false !== $this->quantity_supported ) {
			$payload['quantity_supported'] = (bool) $this->quantity_supported;
		}

		return $payload;
	}
	/**
	 * Validate the plan object.
	 *
	 * @return WP_Error|self Returns WP_Error if validation fails, or the plan object if validation succeeds.
	 * @since 1.0.0
	 */
	public function validate() {
		if ( ! $this->get_name() || mb_strlen( $this->get_name() ) > 127 ) {
			return new WP_Error( 'licencepress_invalid_plan_name', __( 'Invalid PayPal plan name.', 'licencepress' ) );
		}

		if ( ! $this->get_product_id() || mb_strlen( $this->get_product_id() ) < 6 ) {
			return new WP_Error( 'licencepress_invalid_plan_product_id', __( 'Invalid PayPal plan product identifier.', 'licencepress' ) );
		}

		if ( empty( $this->billing_cycles ) || ! is_array( $this->billing_cycles ) ) {
			return new WP_Error( 'licencepress_invalid_plan_billing_cycles', __( 'Invalid PayPal plan billing cycles.', 'licencepress' ) );
		}

		if ( empty( $this->payment_preferences ) || ! is_array( $this->payment_preferences ) ) {
			return new WP_Error( 'licencepress_invalid_plan_payment_preferences', __( 'Invalid PayPal plan payment preferences.', 'licencepress' ) );
		}

		return $this;
	}
}
