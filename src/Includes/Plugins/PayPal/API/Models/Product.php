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

class Product extends Checkout {
	/**
	 * The name of the product.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $name = '';
	/**
	 * The description of the product.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $description = '';
	/**
	 * The type of the product.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $type = 'INVALID_TYPE';
	/**
	 * The category of the product.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $category = '';
	/**
	 * The image URL of the product.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $image_url = '';
	/**
	 * The home URL of the product.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $home_url = '';

	/**
	 * Set the product name.
	 *
	 * @param string $name The product name.
	 * @return void
	 * @since 1.0.0
	 */
	public function set_name( $name ) {
		$this->name = is_scalar( $name ) ? (string) $name : '';
	}

	/**
	 * Get the product name.
	 *
	 * @return string The product name.
	 * @since 1.0.0
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Set the home URL of the product.
	 *
	 * @param string $home_url The home URL of the product.
	 * @return void
	 * @since 1.0.0
	 */
	public function set_home_url( $home_url ) {
		$this->home_url = is_scalar( $home_url ) ? (string) $home_url : '';
	}

	/**
	 * Get the home URL of the product.
	 *
	 * @return string The home URL of the product.
	 * @since 1.0.0
	 */
	public function get_home_url() {
		return $this->home_url;
	}
	/**
	 * Set the category of the product.
	 *
	 * @param string $category The category of the product.
	 * @return void
	 * @since 1.0.0
	 */
	public function set_category( $category ) {
		$this->category = is_scalar( $category ) ? (string) $category : '';
	}

	/**
	 * Get the category of the product.
	 *
	 * @return string The category of the product.
	 * @since 1.0.0
	 */
	public function get_category() {
		return $this->category;
	}

	/**
	 * Set the image URL of the product.
	 *
	 * @param string $image_url The image URL of the product.
	 * @return void
	 * @since 1.0.0
	 */
	public function set_image_url( $image_url ) {
		$this->image_url = is_scalar( $image_url ) ? (string) $image_url : '';
	}

	/**
	 * Get the image URL of the product.
	 *
	 * @return string The image URL of the product.
	 * @since 1.0.0
	 */
	public function get_image_url() {
		return $this->image_url;
	}
	/**
	 * Set the description of the product.
	 *
	 * @param string $description The description of the product.
	 * @return void
	 * @since 1.0.0
	 */
	public function set_description( $description ) {
		$this->description = is_scalar( $description ) ? (string) $description : '';
	}

	/**
	 * Get the description of the product.
	 *
	 * @return string The description of the product.
	 * @since 1.0.0
	 */
	public function get_description() {
		return $this->description;
	}
	/**
	 * Set the type of the product.
	 *
	 * @param string $type The type of the product.
	 * @return void
	 * @since 1.0.0
	 */
	public function set_type( $type ) {
		$this->type = strtoupper( (string) $type );
	}

	/**
	 * Get the type of the product.
	 *
	 * @return string The type of the product.
	 * @since 1.0.0
	 */
	public function get_type() {
		return $this->type;
	}
	/**
	 * Convert the product object to a payload array suitable for API requests.
	 *
	 * @return array The payload array.
	 * @since 1.0.0
	 */
	public function to_payload(): array {
		$payload = array(
			'name' => $this->name,
			'description' => $this->description,
			'type' => $this->type,
		);

		if ( '' !== $this->category ) {
			$payload['category'] = $this->category;
		}
		if ( '' !== $this->image_url ) {
			$payload['image_url'] = $this->image_url;
		}
		if ( '' !== $this->home_url ) {
			$payload['home_url'] = $this->home_url;
		}

		return $payload;
	}
	/**
	 * Validate the product object.
	 *
	 * @return WP_Error|self Returns WP_Error if validation fails, or the product object if validation succeeds.
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
