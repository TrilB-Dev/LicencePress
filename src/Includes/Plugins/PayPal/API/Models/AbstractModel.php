<?php
/**
 * Base PayPal model used for API payloads and hydration.
 *
 * @package LicencePress
 * @subpackage Includes\Plugins\PayPal\API\Models
 */

namespace LicencePress\Includes\Plugins\PayPal\API\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class AbstractModel {
	public function load( $props ) {
		if ( empty( $props ) ) {
			return $this;
		}

		if ( is_wp_error( $props ) ) {
			return $this;
		}

		if ( ! is_array( $props ) ) {
			return $this;
		}

		foreach ( $props as $prop => $value ) {
			if ( property_exists( $this, $prop ) ) {
				$this->set_property( $prop, $value );
			}
		}

		return $this;
	}

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
