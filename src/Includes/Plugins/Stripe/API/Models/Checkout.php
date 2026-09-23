<?php
/**
 * Base Stripe Checkout model with hydration helpers.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\Stripe\API\Models
 */

namespace LicencePress\Includes\Plugins\Stripe\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Checkout extends AbstractModel {
	/**
	 * Stripe object ID.
	 *
	 * @var string|null
	 */
	public $id;

	/**
	 * Payment form data.
	 *
	 * @var array
	 */
	protected $form = array();

	/**
	 * Payment feed data.
	 *
	 * @var array
	 */
	protected $feed = array();

	/**
	 * Submission data.
	 *
	 * @var array
	 */
	protected $submission_data = array();

	/**
	 * Entry data.
	 *
	 * @var array
	 */
	protected $entry = array();

	/**
	 * Initializes the Stripe checkout model with form data.
	 *
	 * @param array $form The form data.
	 * @param array $feed The feed data.
	 * @param array $submission_data The submission data.
	 * @param array $entry The entry data.
	 * @return $this The current model instance.
	 */
	public function init( $form = array(), $feed = array(), $submission_data = array(), $entry = array() ) {
		$this->set_gf_data( $form, $feed, $submission_data, $entry );
		return $this;
	}

	/**
	 * Sets the form data for the Stripe checkout model.
	 *
	 * @param array $form The form data.
	 * @param array $feed The feed data.
	 * @param array $submission_data The submission data.
	 * @param array $entry The entry data.
	 * @return void
	 */
	public function set_gf_data( $form = array(), $feed = array(), $submission_data = array(), $entry = array() ) {
		$this->form = is_array( $form ) ? $form : array();
		$this->feed = is_array( $feed ) ? $feed : array();
		$this->submission_data = is_array( $submission_data ) ? $submission_data : array();
		$this->entry = is_array( $entry ) ? $entry : array();
	}

	/**
	 * Gets the ID of the model.
	 *
	 * @return string|null The model ID.
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Sets the ID of the model.
	 *
	 * @param string $id The model ID.
	 * @return void
	 */
	public function set_id( $id ) {
		if ( ! empty( $id ) && is_string( $id ) ) {
			$this->id = $id;
		}
	}
}
