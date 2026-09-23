<?php
/**
 * PayPal transaction search model.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\API\Models
 */

namespace LicencePress\Includes\Plugins\PayPal\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Transactions {
	/**
	 * PayPal transactions model.
	 *
	 * @since 1.0.0
	 */
	public array $transactions = array();
    /**
	 * Set the transactions for the model.
	 *
	 * @param array $transactions The transactions to set.
	 * @return self
	 * @since 1.0.0
	 */
	public function set_transactions( array $transactions ): self {
		$this->transactions = array_values( $transactions );
		return $this;
	}
    /**
	 * Get the transactions for the model.
	 *
	 * @return array The transactions.
	 * @since 1.0.0
	 */
	public function get_transactions(): array {
		return $this->transactions;
	}
    /**
	 * Add a transaction to the model.
	 *
	 * @param array $transaction The transaction to add.
	 * @return self
	 * @since 1.0.0
	 */
	public function add_transaction( array $transaction ): self {
		if ( ! empty( $transaction ) ) {
			$this->transactions[] = $transaction;
		}

		return $this;
	}
    /**
	 * Get the payload for the model.
	 *
	 * @return array The payload.
	 * @since 1.0.0
	 */
	public function to_payload(): array {
		return array(
			'transactions' => $this->transactions,
		);
	}
    /**
	 * Validate the transactions in the model.
	 *
	 * @return bool True if all transactions are valid, false otherwise.
	 * @since 1.0.0
	 */
	public function validate(): bool {
		foreach ( $this->transactions as $transaction ) {
			if ( ! is_array( $transaction ) || empty( $transaction ) ) {
				return false;
			}
		}

		return true;
	}
}