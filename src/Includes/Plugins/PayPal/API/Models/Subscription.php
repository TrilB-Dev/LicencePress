<?php
/**
 * PayPal subscription model.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\API\Models
 */

namespace LicencePress\Includes\Plugins\PayPal\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Error;

class Subscription extends PayPalCommerceModel {
	public $plan;
	public $plan_id = '';
	public $start_time = '';
	public $quantity = 1;
	public $shipping_amount = array();
	public $subscriber = array();
	public $application_context = array();
	public $custom_id = '';
	public $status = 'APPROVAL_PENDING';

	public function __construct( $plan = null ) {
		if ( $plan instanceof Plan ) {
			$this->plan = $plan;
			$this->plan_id = $plan->get_id() ?: $this->plan_id;
		}
	}

	public function set_plan_id( $plan_id ) {
		$this->plan_id = is_scalar( $plan_id ) ? (string) $plan_id : '';
	}

	public function get_plan_id() {
		return $this->plan_id;
	}

	public function set_subscriber( $subscriber ) {
		$this->subscriber = is_array( $subscriber ) ? $subscriber : array();
	}

	public function set_application_context( $application_context ) {
		$this->application_context = is_array( $application_context ) ? $application_context : array();
	}

	public function set_quantity( $quantity ) {
		$this->quantity = is_numeric( $quantity ) ? (int) $quantity : 1;
	}

	public function validate() {
		if ( empty( $this->plan_id ) || mb_strlen( $this->plan_id ) < 3 ) {
			return new WP_Error( 'licencepress_invalid_subscription_plan_id', __( 'Invalid PayPal subscription plan identifier.', 'licencepress' ) );
		}

		if ( empty( $this->subscriber ) ) {
			return new WP_Error( 'licencepress_invalid_subscription_subscriber', __( 'Subscriber details are required for PayPal subscriptions.', 'licencepress' ) );
		}

		if ( empty( $this->application_context ) ) {
			return new WP_Error( 'licencepress_invalid_subscription_application_context', __( 'Application context is required for PayPal subscriptions.', 'licencepress' ) );
		}

		return $this;
	}
}
