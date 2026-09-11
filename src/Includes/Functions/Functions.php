<?php
/**
 * LicencePress - Functions
 *
 * Shared utilities used by the LicencePress content model and REST routes.
 *
 * @package LicencePress
 * @subpackage Includes\Functions
 * @since 1.0.0
 */

namespace LicencePress\Includes\Functions;

use LicencePress\Includes\Functions\Helpers\PostHelper;
use LicencePress\Includes\Functions\Helpers\SanitizationHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backwards-compatible facade for common LicencePress utility operations.
 *
 * New code may use the focused helper classes directly. This facade remains
 * useful to extensions that need one stable entry point for LicencePress data.
 */
final class Functions {
	/**
	 * Default status for LicencePress content.
	 */
	public const DEFAULT_STATUS = 'publish';
	/**
	 * Allowed statuses for LicencePress content.
	 */
	public const ALLOWED_STATUSES = array( 'publish', 'draft', 'private' );
	/**
	 * Sanitizes a LicencePress payload array for safe use.
	 *
	 * @param array $payload The payload to sanitize.
	 * @return array The sanitized payload.
	 */
	public static function sanitize_payload( array $payload ): array {
		$status = SanitizationHelper::key( $payload['status'] ?? self::DEFAULT_STATUS );

		return array(
			'title'      => SanitizationHelper::text( $payload['title'] ?? '' ),
			'content'    => self::sanitize_content( $payload['content'] ?? '' ),
			'excerpt'    => SanitizationHelper::text( $payload['excerpt'] ?? '' ),
			'status'     => SanitizationHelper::one_of( $status, self::ALLOWED_STATUSES, self::DEFAULT_STATUS ),
			'post_id'    => SanitizationHelper::integer( $payload['post_id'] ?? 0 ),
			'categories' => self::normalize_terms( $payload['categories'] ?? array() ),
			'tags'       => self::normalize_terms( $payload['tags'] ?? array() ),
		);
	}
	/**
	 * Normalizes an array of terms (categories or tags) for safe use.
	 *
	 * @param mixed $terms The terms to normalize.
	 * @return array The normalized terms.
	 */
	public static function normalize_terms( $terms ): array {
		return SanitizationHelper::terms( $terms );
	}
	/**
	 * Checks if a given post is a supported LicencePress content post.
	 *
	 * @param mixed $post The post to check.
	 * @return bool True if the post matches the LicencePress content model, false otherwise.
	 */
	public static function is_post( $post ): bool {
		return self::is_page( $post );
	}
	/**
	 * Checks if a given post is a LicencePress content object.
	 *
	 * @param mixed $post The post to check.
	 * @return bool True if the post is a LicencePress content object, false otherwise.
	 */
	public static function is( $post ): bool {
		return PostHelper::is( $post );
	}
	/**
	 * Checks if a given post is a LicencePress variant page.
	 *
	 * @param mixed $post The post to check.
	 * @return bool True if the post is a LicencePress variant page, false otherwise.
	 */
	public static function is_page( $post ): bool {
		return PostHelper::is_page( $post );
	}
	/**
	 * Checks if a given post is content owned by the LicencePress model.
	 *
	 * @param mixed $post The post to check.
	 * @return bool True if the post is a LicencePress content object, false otherwise.
	 */
	public static function is_content( $post ): bool {
		return self::is( $post ) || self::is_page( $post );
	}
	/**
	 * Returns a standardized REST response array.
	 *
	 * @param bool   $success Indicates if the operation was successful.
	 * @param string $message A message describing the result.
	 * @param array  $data    Additional data to include in the response.
	 * @return array The standardized REST response.
	 */
	public static function rest_response( bool $success, string $message = '', array $data = array() ): array {
		return array(
			'success' => $success,
			'message' => $message,
			'data'    => $data,
		);
	}
	/**
	 * Sanitizes a string for safe use in HTML output.
	 *
	 * @param string $string The string to sanitize.
	 * @return string The sanitized string.
	 */
	private static function sanitize_content( $content ): string {
		return is_scalar( $content ) ? wp_kses_post( (string) $content ) : '';
	}
}
