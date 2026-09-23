<?php
/**
 * PayPal invoicing model.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\API\Models
 */

namespace LicencePress\Includes\Plugins\PayPal\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Invoicing {
	/**
	 * PayPal invoicing model.
	 *
	 * @var array The list of invoices.
	 * @since 1.0.0
	 */
	public array $invoices = array();

	/**
	 * Set the list of invoices.
	 *
	 * @param array $invoices The list of invoices.
	 * @return self The Invoicing model instance.
	 * @since 1.0.0
	 */
	public function set_invoices( array $invoices ): self {
		$this->invoices = array_values( $invoices );
		return $this;
	}

	/**
	 * Get the list of invoices.
	 *
	 * @return array The list of invoices.
	 * @since 1.0.0
	 */
	public function get_invoices(): array {
		return $this->invoices;
	}

	/**
	 * Add a new invoice to the list of invoices.
	 *
	 * @param array $invoice The invoice to add.
	 * @return self The Invoicing model instance.
	 * @since 1.0.0
	 */
	public function create_invoice( array $invoice ): self {
		if ( ! empty( $invoice ) ) {
			$this->invoices[] = $invoice;
		}

		return $this;
	}

	/**
	 * Convert the invoicing model to a payload array.
	 *
	 * @return array The payload array.
	 * @since 1.0.0
	 */
	public function to_payload(): array {
		return array(
			'invoices' => $this->invoices,
		);
	}

	/**
	 * Validate the invoicing model.
	 *
	 * @return bool True if the invoicing model is valid, false otherwise.
	 * @since 1.0.0
	 */
	public function validate(): bool {
		foreach ( $this->invoices as $invoice ) {
			if ( ! is_array( $invoice ) || empty( $invoice ) ) {
				return false;
			}
		}

		return true;
	}
}