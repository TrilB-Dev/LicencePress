<?php
/**
 * Post identity and LicencePress post-type helpers.
 *
 * @package LicencePress
 * @subpackage Includes\Functions\Helpers
 * @since 1.0.0
 */

namespace LicencePress\Includes\Functions\Helpers;

use LicencePress\Includes\Core\PostType;
use LicencePress\Includes\Functions\Helpers\QueryHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provide null-safe post checks shared by admin, API, and frontend code.
 */
final class PostHelper {
	/**
	 * Get the current global WP_Post instance.
	 *
	 * @return \WP_Post|null The current WP_Post instance or null if not available.
	 */
	public static function current(): ?\WP_Post {
		$query = QueryHelper::current();
		return $query instanceof \WP_Query && $query->post instanceof \WP_Post ? $query->post : null;
	}

	/**
	 * Get the ID of the current global WP_Post instance.
	 *
	 * @return int The ID of the current WP_Post instance or 0 if not available.
	 */
	public static function current_id(): int {
		$current = self::current();
		return $current instanceof \WP_Post ? absint( $current->ID ) : 0;
	}

	/**
	 * Get the post type of the current global WP_Post instance.
	 *
	 * @return string The post type of the current WP_Post instance or an empty string if not available.
	 */
	public static function current_type(): string {
		$current = self::current();
		return $current instanceof \WP_Post ? (string) $current->post_type : '';
	}

	/**
	 * Get a WP_Post instance by ID, object, or the current global post.
	 *
	 * @param int|\WP_Post|null $post The post ID, WP_Post instance, or null for the current post.
	 * @return \WP_Post|null The WP_Post instance or null if not available.
	 */
	public static function get( $post = null ): ?\WP_Post {
		if ( $post instanceof \WP_Post ) {
			return $post;
		}

		if ( is_numeric( $post ) && absint( $post ) > 0 ) {
			$post = get_post( absint( $post ) );
		} elseif ( null === $post ) {
			$post = get_post();
		}

		return $post instanceof \WP_Post ? $post : null;
	}

	/**
	 * Get the ID of a WP_Post instance by ID or object.
	 *
	 * @param int|\WP_Post|null $post The post ID, WP_Post instance, or null for the current post.
	 * @return int The ID of the WP_Post instance or 0 if not available.
	 */
	public static function id( $post = null ): int {
		$post = self::get( $post );
		return $post instanceof \WP_Post ? absint( $post->ID ) : 0;
	}

	/**
	 * Check if a post is any LicencePress post type.
	 *
	 * @param mixed $post The post ID, WP_Post instance, or null.
	 * @return bool True if the post matches a supported LicencePress custom post type.
	 */
	public static function is( $post ): bool {
		return self::is_licence_type( $post ) || self::is_licence_type_variant( $post );
	}

	/**
	 * Check if a post is specifically a licence type variant page.
	 *
	 * @param mixed $post The post ID, WP_Post instance, or null.
	 * @return bool True if the post is a variant page.
	 */
	public static function is_page( $post ): bool {
		return self::is_licence_type_variant( $post );
	}

	/**
	 * Check if a WP_Post instance is of a specific post type.
	 *
	 * @param mixed  $post The post ID, WP_Post instance, or null.
	 * @param string $post_type The post type to check against.
	 * @return bool True if the post is of the specified post type, false otherwise.
	 */
	public static function is_type( $post, string $post_type ): bool {
		$post = self::get( $post );
		return $post instanceof \WP_Post && $post->post_type === $post_type;
	}

	/**
	 * Check if a post is a LicencePress licence type.
	 *
	 * @param mixed $licence_type The post ID, WP_Post instance, or null.
	 * @return bool True if the post is a licence type, false otherwise.
	 */
	public static function is_licence_type( $licence_type ): bool {
		return self::is_type( $licence_type, PostType::LICENCE_TYPE );
	}

	/**
	 * Check if a post is a LicencePress licence type variant.
	 *
	 * @param mixed $variant The post ID, WP_Post instance, or null.
	 * @return bool True if the post is a variant, false otherwise.
	 */
	public static function is_licence_type_variant( $variant ): bool {
		return self::is_type( $variant, PostType::LICENCE_TYPE_VARIANT );
	}

	/**
	 * Backwards-compatible check for a legacy licence post type.
	 *
	 * LicencePress does not register a standalone licence post type; licences live in the
	 * dedicated database table instead.
	 *
	 * @param mixed $licence The post ID, WP_Post instance, or null.
	 * @return bool Always false for the current architecture.
	 */
	public static function is_licence( $licence ): bool {
		return false;
	}

	/**
	 * Get the permalink of a WP_Post instance by ID or object.
	 *
	 * @param int|\WP_Post|null $post The post ID, WP_Post instance, or null for the current post.
	 * @return string The permalink of the WP_Post instance or an empty string if not available.
	 */
	public static function permalink( $post = null ): string {
		$post_id = self::id( $post );
		return $post_id > 0 ? (string) get_permalink( $post_id ) : '';
	}
}
