<?php
/**
 * WordPress query helpers for LicencePress and extensions.
 *
 * @package LicencePress\Includes\Functions\Helpers
 * @since 1.0.0
 */
namespace LicencePress\Includes\Functions\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provide access to the current query and reusable post queries.
 */
final class QueryHelper {
	/**
	 * Get the current global WP_Query instance.
	 *
	 * @return \WP_Query|null The current WP_Query instance or null if not available.
	 */
	public static function current(): ?\WP_Query {
		global $wp_query;
		return isset( $wp_query ) && $wp_query instanceof \WP_Query ? $wp_query : null;
	}
	/**
	 * Get a new WP_Query instance with the specified arguments.
	 *
	 * @param array $args The arguments for the WP_Query.
	 * @return \WP_Query The new WP_Query instance.
	 */
	public static function posts( array $args = array() ): \WP_Query {
		return new \WP_Query( $args );
	}
}
