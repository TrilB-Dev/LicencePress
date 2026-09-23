<?php
/**
 * PayPal payment disputes model.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\API\Models
 */

namespace LicencePress\Includes\Plugins\PayPal\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Disputes {
	/**
	 * PayPal disputes model.
	 *
     * @var array The list of disputes.
	 * @since 1.0.0
	 */
	public array $disputes = array();
    /**
	 * Set the list of disputes.
	 *
	 * @param array $disputes The list of disputes.
	 * @return self The Disputes model instance.
	 * @since 1.0.0
	 */
	public function set_disputes( array $disputes ): self {
		$this->disputes = array_values( $disputes );
		return $this;
	}
	/**
	 * Get the list of disputes.
	 *
	 * @return array The list of disputes.
	 * @since 1.0.0
	 */
	public function get_disputes(): array {
		return $this->disputes;
	}
	/**
	 * Add a dispute to the list of disputes.
	 *
	 * @param array $dispute The dispute to add.
	 * @return self The Disputes model instance.
	 * @since 1.0.0
	 */
	public function add_dispute( array $dispute ): self {
		if ( ! empty( $dispute ) ) {
			$this->disputes[] = $dispute;
		}

		return $this;
	}
	/**
	 * Convert the disputes model to a payload array.
	 *
	 * @return array The payload array.
	 * @since 1.0.0
	 */
	public function to_payload(): array {
		return array(
			'disputes' => $this->disputes,
		);
	}
	/**
	 * Validate the disputes model.
	 *
	 * @return bool True if the disputes model is valid, false otherwise.
	 * @since 1.0.0
	 */
	public function validate(): bool {
		foreach ( $this->disputes as $dispute ) {
			if ( ! is_array( $dispute ) || empty( $dispute ) ) {
				return false;
			}
			if ( empty( $dispute['id'] ) || empty( $dispute['status'] ) ) {
				return false;
			}
		}

		return true;
	}
}