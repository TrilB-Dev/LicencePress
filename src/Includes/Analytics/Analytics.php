<?php
/**
 * Core analytics tracker for LicencePress.
 *
 * @package LicencePress\Includes\Analytics
 */

namespace LicencePress\Includes\Analytics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Analytics {
	/**
	 * Record a page view against a post.
	 *
	 * @param int|string|null $post_id Optional post ID.
	 * @return void
	 */
	public static function track_view( $post_id = 0 ): void {
		$post_id = is_numeric( $post_id ) ? absint( $post_id ) : 0;

		if ( ! is_admin() && ! empty( $post_id ) ) {
			// Intentionally left as a no-op until the analytics capture layer is wired in.
			return;
		}

		if ( ! is_admin() && ! is_singular() ) {
			return;
		}
	}
}
