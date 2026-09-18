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
    /**
	 * The name of the PayPal product.
	 *
	 * @since 1.0.0
	 */
	public $name = '';
    /**
	 * The description of the PayPal product.
	 *
	 * @since 1.0.0
	 */
	public $description = '';
    /**
	 * The type of the PayPal product.
	 *
	 * @since 1.0.0
	 */
	public $type = 'INVALID_TYPE';
    /**
	 * The category of the PayPal product.
	 *
	 * @since 1.0.0
	 */
	public $category = '';
    /**
	 * The image URL of the PayPal product.
	 *
	 * @since 1.0.0
	 */
	public $image_url = '';
    /**
	 * The home URL of the PayPal product.
	 *
	 * @since 1.0.0
	 */
	public $home_url = '';
    /**
	 * Sets the name of the PayPal product.
	 *
	 * @param string $name The PayPal product name.
	 * @since 1.0.0
	 */
	public function set_name( $name ) {
		$this->name = is_scalar( $name ) ? (string) $name : '';
	}

    /**
	 * Gets the name of the PayPal product.
	 *
	 * @return string The PayPal product name.
	 * @since 1.0.0
	 */
	public function get_name() {
		return $this->name;
	}

    /**
	 * Sets the type of the PayPal product.
	 *
	 * @param string $type The PayPal product type.
	 * @since 1.0.0
	 */
	public function set_type( $type ) {
		$this->type = strtoupper( (string) $type );
	}

    /**
	 * Gets the type of the PayPal product.
	 *
	 * @return string The PayPal product type.
	 * @since 1.0.0
	 */
	public function get_type() {
		return $this->type;
	}

    /**
	 * Sets the description of the PayPal product.
	 *
	 * @param string $description The PayPal product description.
	 * @since 1.0.0
	 */
	public function set_description( $description ) {
		$this->description = is_scalar( $description ) ? (string) $description : '';
	}

    /**
	 * Gets the description of the PayPal product.
	 *
	 * @return string The PayPal product description.
	 * @since 1.0.0
	 */
	public function get_description() {
		return $this->description;
	}
    /**
     * Validates the PayPal product.
     *
     * @return WP_Error|self Returns WP_Error if validation fails, otherwise returns the product instance.
     * @since 1.0.0
     */
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
