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
    /**
	 * The PayPal plan associated with the subscription.
	 *
	 * @since 1.0.0
	 */
	public $plan;
    /**
	 * The PayPal plan ID associated with the subscription.
	 *
	 * @since 1.0.0
	 */
	public $plan_id = '';
    /**
	 * The start time of the PayPal subscription.
	 *
	 * @since 1.0.0
	 */
	public $start_time = '';
    /**
	 * The quantity of the PayPal subscription.
	 *
	 * @since 1.0.0
	 */
	public $quantity = 1;
    /**
	 * The shipping amount of the PayPal subscription.
	 *
	 * @since 1.0.0
	 */
	public $shipping_amount = array();
    /**
	 * The subscriber details of the PayPal subscription.
	 *
	 * @since 1.0.0
	 */
	public $subscriber = array();
    /**
	 * The application context of the PayPal subscription.
	 *
	 * @since 1.0.0
	 */
	public $application_context = array();
    /**
	 * The custom ID of the PayPal subscription.
	 *
	 * @since 1.0.0
	 */
	public $custom_id = '';
    /**
	 * The status of the PayPal subscription.
	 *
	 * @since 1.0.0
	 */
	public $status = 'APPROVAL_PENDING';
    /**
     * Constructs a new PayPal subscription instance.
     *
     * @param Plan|null $plan The PayPal plan associated with the subscription.
     * @since 1.0.0
     */
	public function __construct( $plan = null ) {
		if ( $plan instanceof Plan ) {
			$this->plan = $plan;
			$this->plan_id = $plan->get_id() ?: $this->plan_id;
		}
	}
    /**
	 * Sets the plan ID of the PayPal subscription.
	 *
	 * @param string $plan_id The PayPal plan ID.
	 * @since 1.0.0
	 */
	public function set_plan_id( $plan_id ) {
		$this->plan_id = is_scalar( $plan_id ) ? (string) $plan_id : '';
	}

    /**
	 * Gets the plan ID of the PayPal subscription.
	 *
	 * @return string The PayPal plan ID.
	 * @since 1.0.0
	 */
	public function get_plan_id() {
		return $this->plan_id;
	}
    /**
	 * Sets the subscriber details of the PayPal subscription.
	 *
	 * @param array $subscriber The subscriber details.
	 * @since 1.0.0
	 */
	public function set_subscriber( $subscriber ) {
		$this->subscriber = is_array( $subscriber ) ? $subscriber : array();
	}
    /**
	 * Sets the application context of the PayPal subscription.
	 *
	 * @param array $application_context The application context.
	 * @since 1.0.0
	 */
	public function set_application_context( $application_context ) {
		$this->application_context = is_array( $application_context ) ? $application_context : array();
	}
    /**
	 * Sets the quantity of the PayPal subscription.
	 *
	 * @param int $quantity The quantity.
	 * @since 1.0.0
	 */
	public function set_quantity( $quantity ) {
		$this->quantity = is_numeric( $quantity ) ? (int) $quantity : 1;
	}
    /**
	 * Validates the PayPal subscription.
	 *
	 * @return WP_Error|$this Returns WP_Error if validation fails, otherwise returns the subscription instance.
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
