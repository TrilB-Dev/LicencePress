<?php
/**
 * Base PayPal Checkout model with Gravity Forms-style hydration helpers.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\API\Models
 * @since 1.0.0
 */

namespace LicencePress\Includes\Plugins\PayPal\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class PayPalCommerceModel extends AbstractModel {
	/**
	 * PayPal Commerce model ID.
	 *
	 * @since 1.0.0
	 */
	public $id;
	/**
	 * PayPal Commerce model form data.
	 *
	 * @since 1.0.0
	 */
	protected $form = array();
	/**
	 * PayPal Commerce model feed data.
	 *
	 * @since 1.0.0
	 */
	protected $feed = array();
	/**
	 * PayPal Commerce model submission data.
	 *
	 * @since 1.0.0
	 */
	protected $submission_data = array();
	/**
	 * PayPal Commerce model entry data.
	 *
	 * @since 1.0.0
	 */
	protected $entry = array();
	/**
	 * Initializes the PayPal Commerce model with Gravity Forms data.
	 *
	 * @param array $form The form data.
	 * @param array $feed The feed data.
	 * @param array $submission_data The submission data.
	 * @param array $entry The entry data.
	 * @return $this The current model instance.
	 * @since 1.0.0
	 */
	public function init( $form = array(), $feed = array(), $submission_data = array(), $entry = array() ) {
		$this->set_gf_data( $form, $feed, $submission_data, $entry );
		return $this;
	}

	/**
	 * Sets the Gravity Forms data for the PayPal Commerce model.
	 *
	 * @param array $form The form data.
	 * @param array $feed The feed data.
	 * @param array $submission_data The submission data.
	 * @param array $entry The entry data.
	 * @return void
	 * @since 1.0.0
	 */
	public function set_gf_data( $form = array(), $feed = array(), $submission_data = array(), $entry = array() ) {
		$this->form = is_array( $form ) ? $form : array();
		$this->feed = is_array( $feed ) ? $feed : array();
		$this->submission_data = is_array( $submission_data ) ? $submission_data : array();
		$this->entry = is_array( $entry ) ? $entry : array();
	}
	/**
	 * Gets the ID of the PayPal Commerce model.
	 *
	 * @return string|null The model ID.
	 * @since 1.0.0
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Sets the ID of the PayPal Commerce model.
	 *
	 * @param string $id The model ID.
	 * @return void
	 * @since 1.0.0
	 */
	public function set_id( $id ) {
		if ( ! empty( $id ) && is_string( $id ) ) {
			$this->id = $id;
		}
	}
}
