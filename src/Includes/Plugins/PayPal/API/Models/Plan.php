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

class Plan extends PayPalCommerceModel {
    /**
	 * The associated PayPal product.
	 *
	 * @since 1.0.0
	 */
	private $product = null;
    /**
	 * The associated PayPal product ID.
	 *
	 * @since 1.0.0
	 */
	public $product_id = '';
    /**
	 * The name of the PayPal plan.
	 *
	 * @since 1.0.0
	 */
	public $name = '';
    /**
	 * The status of the PayPal plan.
	 *
	 * @since 1.0.0
	 */
	public $status = 'ACTIVE';
    /**
	 * The description of the PayPal plan.
	 *
	 * @since 1.0.0
	 */
	public $description = '';
    /**
	 * The billing cycles of the PayPal plan.
	 *
	 * @since 1.0.0
	 */
	public $billing_cycles = array();
    /**
	 * The payment preferences of the PayPal plan.
	 *
	 * @since 1.0.0
	 */
	public $payment_preferences = array();
    /**
	 * The taxes of the PayPal plan.
	 *
	 * @since 1.0.0
	 */
	public $taxes = array();
    /**
	 * Whether the quantity is supported for the PayPal plan.
	 *
	 * @since 1.0.0
	 */
	public $quantity_supported = true;
    /**
	 * Constructs a new PayPal plan instance.
	 *
	 * @param Product|null $product The associated PayPal product.
	 * @since 1.0.0
	 */
	public function __construct( $product = null ) {
		if ( $product instanceof Product ) {
			$this->product = $product;
			$this->product_id = $product->get_id() ?: $this->product_id;
		}
	}
    /**
	 * Sets the associated PayPal product ID.
	 *
	 * @param string $product_id The PayPal product ID.
	 * @since 1.0.0
	 */
	public function set_product_id( $product_id ) {
		$this->product_id = is_scalar( $product_id ) ? (string) $product_id : '';
	}
    /**
	 * Gets the associated PayPal product ID.
	 *
	 * @return string The PayPal product ID.
	 * @since 1.0.0
	 */
	public function get_product_id() {
		return $this->product_id;
	}

    /**
	 * Sets the name of the PayPal plan.
	 *
	 * @param string $name The PayPal plan name.
	 * @since 1.0.0
	 */
	public function set_name( $name ) {
		$this->name = is_scalar( $name ) ? (string) $name : '';
	}

    /**
	 * Gets the name of the PayPal plan.
	 *
	 * @return string The PayPal plan name.
	 * @since 1.0.0
	 */
	public function get_name() {
		return $this->name;
	}

    /**
	 * Sets the billing cycles of the PayPal plan.
	 *
	 * @param array $billing_cycles The PayPal plan billing cycles.
	 * @since 1.0.0
	 */
	public function set_billing_cycles( $billing_cycles ) {
		$this->billing_cycles = is_array( $billing_cycles ) ? $billing_cycles : array();
	}

    /**
	 * Gets the billing cycles of the PayPal plan.
	 *
	 * @return array The PayPal plan billing cycles.
	 * @since 1.0.0
	 */
	public function get_billing_cycles() {
		return $this->billing_cycles;
	}

    /**
	 * Sets the payment preferences of the PayPal plan.
	 *
	 * @param array $payment_preferences The PayPal plan payment preferences.
	 * @since 1.0.0
	 */
	public function set_payment_preferences( $payment_preferences ) {
		$this->payment_preferences = is_array( $payment_preferences ) ? $payment_preferences : array();
	}

    /**
	 * Gets the payment preferences of the PayPal plan.
	 *
	 * @return array The PayPal plan payment preferences.
	 * @since 1.0.0
	 */
	public function get_payment_preferences() {
		return $this->payment_preferences;
	}
    /**
	 * Validates the PayPal plan.
	 *
	 * @return WP_Error|self Returns WP_Error if validation fails, otherwise returns the plan instance.
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
