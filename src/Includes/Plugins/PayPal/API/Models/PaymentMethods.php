<?php
/**
 * PayPal wallet and payment method model.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\API\Models
 */

namespace LicencePress\Includes\Plugins\PayPal\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PaymentMethods {
	/**
	 * PayPal payment methods model.
	 *
	 * @var array The list of payment methods.
	 * @since 1.0.0
	 */
	public array $payment_methods = array();

	/**
	 * Set the list of payment methods.
	 *
	 * @param array $payment_methods The list of payment methods.
	 * @return self The PaymentMethods model instance.
	 * @since 1.0.0
	 */
	public function set_payment_methods( array $payment_methods ): self {
		$this->payment_methods = array_values( $payment_methods );
		return $this;
	}

	/**
	 * Get the list of payment methods.
	 *
	 * @return array The list of payment methods.
	 * @since 1.0.0
	 */
	public function get_payment_methods(): array {
		return $this->payment_methods;
	}

	/**
	 * Add a payment method to the list of payment methods.
	 *
	 * @param array $method The payment method to add.
	 * @return self The PaymentMethods model instance.
	 * @since 1.0.0
	 */
	public function add_method( array $method ): self {
		if ( ! empty( $method ) ) {
			$this->payment_methods[] = $method;
		}

		return $this;
	}

	/**
	 * Convert the payment methods model to a payload array.
	 *
	 * @return array The payload array.
	 * @since 1.0.0
	 */
	public function to_payload(): array {
		return array(
			'payment_methods' => $this->payment_methods,
		);
	}

	/**
	 * Validate the payment methods model.
	 *
	 * @return bool True if the payment methods model is valid, false otherwise.
	 * @since 1.0.0
	 */
	public function validate(): bool {
		foreach ( $this->payment_methods as $method ) {
			if ( ! is_array( $method ) || empty( $method ) ) {
				return false;
			}
		}

		return true;
	}
}