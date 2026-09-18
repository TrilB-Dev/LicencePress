<?php
/**
 * PayPal product model.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\API\Models
 */

namespace LicencePress\Includes\Plugins\PayPal\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Error;

class Product extends PayPalCommerceModel {
	public $name = '';
	public $description = '';
	public $type = 'INVALID_TYPE';
	public $category = '';
	public $image_url = '';
	public $home_url = '';

	public function set_name( $name ) {
		$this->name = is_scalar( $name ) ? (string) $name : '';
	}

	public function get_name() {
		return $this->name;
	}

	public function set_type( $type ) {
		$this->type = strtoupper( (string) $type );
	}

	public function get_type() {
		return $this->type;
	}

	public function set_description( $description ) {
		$this->description = is_scalar( $description ) ? (string) $description : '';
	}

	public function get_description() {
		return $this->description;
	}

	public function validate() {
		if ( ! $this->get_name() || mb_strlen( $this->get_name() ) > 127 ) {
			return new WP_Error( 'licencepress_invalid_product_name', __( 'Invalid PayPal product name.', 'licencepress' ) );
		}

		if ( ! in_array( $this->get_type(), array( 'PHYSICAL', 'DIGITAL', 'SERVICE' ), true ) ) {
			return new WP_Error( 'licencepress_invalid_product_type', __( 'Invalid PayPal product type.', 'licencepress' ) );
		}

		if ( $this->get_description() && mb_strlen( $this->get_description() ) > 256 ) {
			return new WP_Error( 'licencepress_invalid_product_description', __( 'Invalid PayPal product description.', 'licencepress' ) );
		}

		return $this;
	}
}
