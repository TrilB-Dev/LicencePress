<?php
/**
 * Base PayPal Checkout model with Gravity Forms-style hydration helpers.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\API\Models
 */

namespace LicencePress\Includes\Plugins\PayPal\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class PayPalCommerceModel extends AbstractModel {
	public $id;

	protected $form = array();
	protected $feed = array();
	protected $submission_data = array();
	protected $entry = array();

	public function init( $form = array(), $feed = array(), $submission_data = array(), $entry = array() ) {
		$this->set_gf_data( $form, $feed, $submission_data, $entry );
		return $this;
	}

	public function set_gf_data( $form = array(), $feed = array(), $submission_data = array(), $entry = array() ) {
		$this->form = is_array( $form ) ? $form : array();
		$this->feed = is_array( $feed ) ? $feed : array();
		$this->submission_data = is_array( $submission_data ) ? $submission_data : array();
		$this->entry = is_array( $entry ) ? $entry : array();
	}

	public function get_id() {
		return $this->id;
	}

	public function set_id( $id ) {
		if ( ! empty( $id ) && is_string( $id ) ) {
			$this->id = $id;
		}
	}
}
