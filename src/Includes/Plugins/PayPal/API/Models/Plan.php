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
	private $product = null;

	public $product_id = '';
	public $name = '';
	public $status = 'ACTIVE';
	public $description = '';
	public $billing_cycles = array();
	public $payment_preferences = array();
	public $taxes = array();
	public $quantity_supported = true;

	public function __construct( $product = null ) {
		if ( $product instanceof Product ) {
			$this->product = $product;
			$this->product_id = $product->get_id() ?: $this->product_id;
		}
	}

	public function set_product_id( $product_id ) {
		$this->product_id = is_scalar( $product_id ) ? (string) $product_id : '';
	}

	public function get_product_id() {
		return $this->product_id;
	}

	public function set_name( $name ) {
		$this->name = is_scalar( $name ) ? (string) $name : '';
	}

	public function get_name() {
		return $this->name;
	}

	public function set_billing_cycles( $billing_cycles ) {
		$this->billing_cycles = is_array( $billing_cycles ) ? $billing_cycles : array();
	}

	public function get_billing_cycles() {
		return $this->billing_cycles;
	}

	public function set_payment_preferences( $payment_preferences ) {
		$this->payment_preferences = is_array( $payment_preferences ) ? $payment_preferences : array();
	}

	public function get_payment_preferences() {
		return $this->payment_preferences;
	}

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
