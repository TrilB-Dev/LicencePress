<?php
/**
 * Base Stripe model used for API payloads and hydration.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\Stripe\API\Models
 */

namespace LicencePress\Includes\Plugins\Stripe\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class AbstractModel {
	/**
	 * Loads the model with the given properties.
	 *
	 * @param array $props The properties to load into the model.
	 * @return $this The current model instance.
	 */
	public function load( $props ) {
		if ( empty( $props ) || ! is_array( $props ) ) {
			return $this;
		}

		foreach ( $props as $prop => $value ) {
			if ( property_exists( $this, $prop ) ) {
				$this->set_property( $prop, $value );
			}
		}

		return $this;
	}

	/**
	 * Sets a property on the model, using a setter method if available.
	 *
	 * @param string $prop  The property name.
	 * @param mixed  $value The value to set.
	 * @return void
	 */
	protected function set_property( $prop, $value ) {
		$method = 'set_' . str_replace( '-', '_', $prop );
		if ( method_exists( $this, $method ) ) {
			$this->{$method}( $value );
			return;
		}

		if ( property_exists( $this, $prop ) ) {
			$this->{$prop} = $value;
		}
	}

	/**
	 * Converts the model to an associative array, excluding null values.
	 *
	 * @return array The model represented as an associative array.
	 */
	public function to_array() {
		$array = get_object_vars( $this );
		foreach ( $array as $key => $value ) {
			if ( null === $value ) {
				unset( $array[ $key ] );
			}
		}

		return $array;
	}
}
