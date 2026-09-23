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

class Subscription extends Checkout {
	/**
	 * The plan object associated with the subscription.
	 *
	 * @var Plan|null
	 * @since 1.0.0
	 */
	public $plan;
	/**
	 * The plan ID associated with the subscription.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $plan_id = '';
	/**
	 * The start time of the subscription.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $start_time = '';
	/**
	 * The quantity of the subscription.
	 *
	 * @var int
	 * @since 1.0.0
	 */
	public $quantity = 1;
	/**
	 * The shipping amount for the subscription.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	public $shipping_amount = array();
	/**
	 * The subscriber details for the subscription.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	public $subscriber = array();
	/**
	 * The application context for the subscription.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	public $application_context = array();
	/**
	 * The custom ID for the subscription.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $custom_id = '';
	/**
	 * The status of the subscription.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $status = 'APPROVAL_PENDING';
	/**
	 * Constructor for the subscription object.
	 *
	 * @param Plan|null $plan The plan object associated with the subscription.
	 * @since 1.0.0
	 */
	public function __construct( $plan = null ) {
		if ( $plan instanceof Plan ) {
			$this->plan = $plan;
			$this->plan_id = $plan->get_id() ?: $this->plan_id;
		}
	}
	/**
	 * Set the custom ID for the subscription.
	 *
	 * @param string $custom_id The custom ID for the subscription.
	 * @return void
	 * @since 1.0.0
	 */
	public function set_custom_id( $custom_id ) {
		$this->custom_id = is_scalar( $custom_id ) ? (string) $custom_id : '';
	}

	/**
	 * Get the custom ID for the subscription.
	 *
	 * @return string The custom ID for the subscription.
	 * @since 1.0.0
	 */
	public function get_custom_id() {
		return $this->custom_id;
	}
	/**
	 * Set the plan ID for the subscription.
	 *
	 * @param string $plan_id The plan ID for the subscription.
	 * @return void
	 * @since 1.0.0
	 */
	public function set_plan_id( $plan_id ) {
		$this->plan_id = is_scalar( $plan_id ) ? (string) $plan_id : '';
	}

	/**
	 * Get the plan ID for the subscription.
	 *
	 * @return string The plan ID for the subscription.
	 * @since 1.0.0
	 */
	public function get_plan_id() {
		return $this->plan_id;
	}

	/**
	 * Set the subscriber details for the subscription.
	 *
	 * @param array $subscriber The subscriber details for the subscription.
	 * @return void
	 * @since 1.0.0
	 */
	public function set_subscriber( $subscriber ) {
		$this->subscriber = is_array( $subscriber ) ? $subscriber : array();
	}

	/**
	 * Set the application context for the subscription.
	 *
	 * @param array $application_context The application context for the subscription.
	 * @return void
	 * @since 1.0.0
	 */
	public function set_application_context( $application_context ) {
		$this->application_context = is_array( $application_context ) ? $application_context : array();
	}

	/**
	 * Set the quantity for the subscription.
	 *
	 * @param int $quantity The quantity for the subscription.
	 * @return void
	 * @since 1.0.0
	 */
	public function set_quantity( $quantity ) {
		$this->quantity = is_numeric( $quantity ) ? (int) $quantity : 1;
	}
	/**
	 * Get the quantity for the subscription.
	 *
	 * @return int The quantity for the subscription.
	 * @since 1.0.0
	 */
	public function get_quantity() {
		return $this->quantity;
	}
	/**
	 * Get the subscriber details for the subscription.
	 *
	 * @return array The subscriber details for the subscription.
	 * @since 1.0.0
	 */
	public function get_subscriber() {
		return $this->subscriber;
	}

	/**
	 * Get the application context for the subscription.
	 *
	 * @return array The application context for the subscription.
	 * @since 1.0.0
	 */
	public function get_application_context() {
		return $this->application_context;
	}
	/**
	 * Get the start time for the subscription.
	 *
	 * @return string The start time for the subscription.
	 * @since 1.0.0
	 */
	public function get_start_time() {
		return $this->start_time;
	}
	/**
	 * Set the start time for the subscription.
	 *
	 * @param string $start_time The start time for the subscription.
	 * @return void
	 * @since 1.0.0
	 */
	public function set_start_time( $start_time ) {
		$this->start_time = is_scalar( $start_time ) ? (string) $start_time : '';
	}
	/**
	 * Get the shipping amount for the subscription.
	 *
	 * @return array The shipping amount for the subscription.
	 * @since 1.0.0
	 */
	public function get_shipping_amount() {
		return $this->shipping_amount;
	}

	/**
	 * Set the shipping amount for the subscription.
	 *
	 * @param array $shipping_amount The shipping amount for the subscription.
	 * @return void
	 * @since 1.0.0
	 */
	public function set_shipping_amount( $shipping_amount ) {
		$this->shipping_amount = is_array( $shipping_amount ) ? $shipping_amount : array();
	}

	/**
	 * Get the status for the subscription.
	 *
	 * @return string The status for the subscription.
	 * @since 1.0.0
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Set the status for the subscription.
	 *
	 * @param string $status The status for the subscription.
	 * @return void
	 * @since 1.0.0
	 */
	public function set_status( $status ) {
		$this->status = is_scalar( $status ) ? (string) $status : '';
	}
	/**
	 * Get the payload for the subscription.
	 *
	 * @return array The payload for the subscription.
	 * @since 1.0.0
	 */
	public function to_payload(): array {
		$payload = array(
			'plan_id' => $this->plan_id,
			'start_time' => $this->start_time,
			'quantity' => $this->quantity,
			'subscriber' => $this->subscriber,
			'application_context' => $this->application_context,
		);

		if ( '' !== $this->custom_id ) {
			$payload['custom_id'] = $this->custom_id;
		}
		if ( ! empty( $this->shipping_amount ) ) {
			$payload['shipping_amount'] = $this->shipping_amount;
		}
		if ( '' !== $this->status ) {
			$payload['status'] = $this->status;
		}

		return $payload;
	}
	/**
	 * Validate the subscription data.
	 *
	 * @return WP_Error|self Returns WP_Error if validation fails, otherwise returns the subscription instance.
	 * @since 1.0.0
	 */
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
