<?php
/**
 * Safe request-value helpers for LicencePress and extensions.
 *
 * @package LicencePress
 * @subpackage Includes\Functions\Helpers
 * @since 1.0.0
 */

namespace LicencePress\Includes\Functions\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read and sanitize values from request-like arrays.
 */
final class RequestHelper {
	/**
	 * Get a value from the $_GET superglobal.
	 *
	 * @param string $key The key to retrieve.
	 * @param mixed  $fallback The fallback value if the key does not exist.
	 * @return mixed The value from $_GET or the fallback.
	 */
	public static function get( string $key, $fallback = null ) {
		return self::value( $_GET, $key, $fallback );
	}
	/**
	 * Get a sanitized text value from the $_GET superglobal.
	 *
	 * @param string $key The key to retrieve.
	 * @param string $fallback The fallback value if the key does not exist.
	 * @return string The sanitized text value from $_GET or the fallback.
	 */
	public static function get_text( string $key, string $fallback = '' ): string {
		return self::text( $_GET, $key, $fallback );
	}
	/**
	 * Get a sanitized key value from the $_GET superglobal.
	 *
	 * @param string $key The key to retrieve.
	 * @param string $fallback The fallback value if the key does not exist.
	 * @return string The sanitized key value from $_GET or the fallback.
	 */
	public static function get_key( string $key, string $fallback = '' ): string {
		return self::key( $_GET, $key, $fallback );
	}
	/**
	 * Get a sanitized integer value from the $_GET superglobal.
	 *
	 * @param string $key The key to retrieve.
	 * @param int    $fallback The fallback value if the key does not exist.
	 * @return int The sanitized integer value from $_GET or the fallback.
	 */
	public static function get_integer( string $key, int $fallback = 0 ): int {
		return self::integer( $_GET, $key, $fallback );
	}
	/**
	 * Get a sanitized slug value from the $_GET superglobal.
	 *
	 * @param string $key The key to retrieve.
	 * @param string $fallback The fallback value if the key does not exist.
	 * @return string The sanitized slug value from $_GET or the fallback.
	 */
	public static function get_slug( string $key, string $fallback = '' ): string {
		return self::slug( $_GET, $key, $fallback );
	}
	/**
	 * Get a value from the source array.
	 *
	 * @param array $source The source array to retrieve the key from.
	 * @param string $key The key to retrieve.
	 * @param mixed $fallback The fallback value if the key does not exist.
	 * @return mixed The value from the source array or the fallback.
	 */
	public static function value( array $source, string $key, $fallback = null ) {
		return array_key_exists( $key, $source ) ? $source[ $key ] : $fallback;
	}
	/**
	 * Get a sanitized integer value from the $_GET superglobal within a specific range.
	 *
	 * @param string $key The key to retrieve.
	 * @param int    $minimum The minimum acceptable value.
	 * @param int    $maximum The maximum acceptable value.
	 * @param int    $fallback The fallback value if the key does not exist or is out of range.
	 * @return int The sanitized integer value from $_GET within the specified range or the fallback.
	 */
	public static function get_integer_range( string $key, int $minimum, int $maximum, int $fallback ): int {
		return self::integer_range( self::value( $_GET, $key, $fallback ), $minimum, $maximum, $fallback );
	}
	/**
	 * Get a sanitized text value from the $_GET superglobal.
	 *
	 * @param string $key The key to retrieve.
	 * @param array $source The source array to retrieve the key from.
	 * @param string $fallback The fallback value if the key does not exist.
	 * @return string The sanitized text value from the source array or the fallback.
	 */
	public static function text( array $source, string $key, string $fallback = '' ): string {
		return SanitizationHelper::text( self::unslash( self::value( $source, $key, $fallback ) ), $fallback );
	}
	/**
	 * Get a sanitized key value from the source array.
	 *
	 * @param string $key The key to retrieve.
	 * @param array $source The source array to retrieve the key from.
	 * @param string $fallback The fallback value if the key does not exist.
	 * @return string The sanitized key value from the source array or the fallback.
	 */
	public static function key( array $source, string $key, string $fallback = '' ): string {
		return SanitizationHelper::key( self::unslash( self::value( $source, $key, $fallback ) ), $fallback );
	}
	/**
	 * Get a sanitized slug value from the source array.
	 *
	 * @param string $key The key to retrieve.
	 * @param array $source The source array to retrieve the key from.
	 * @param string $fallback The fallback value if the key does not exist.
	 * @return string The sanitized slug value from the source array or the fallback.
	 */
	public static function slug( array $source, string $key, string $fallback = '' ): string {
		return SanitizationHelper::slug( self::unslash( self::value( $source, $key, $fallback ) ), $fallback );
	}
	/**
	 * Get a sanitized integer value from the source array.
	 *
	 * @param array $source The source array to retrieve the key from.
	 * @param string $key The key to retrieve.
	 * @param int $fallback The fallback value if the key does not exist.
	 * @return int The sanitized integer value from the source array or the fallback.
	 */
	public static function integer( array $source, string $key, int $fallback = 0 ): int {
		return SanitizationHelper::integer( self::unslash( self::value( $source, $key, $fallback ) ), $fallback );
	}
	/**
	 * Get a sanitized integer value within a specified range.
	 *
	 * @param mixed $value The value to sanitize.
	 * @param int $minimum The minimum allowed value.
	 * @param int $maximum The maximum allowed value.
	 * @param int $fallback The fallback value if the value is not within the range.
	 * @return int The sanitized integer value within the specified range or the fallback.
	 */
	public static function integer_range( $value, int $minimum, int $maximum, int $fallback ): int {
		return SanitizationHelper::integer_range( self::unslash( $value ), $minimum, $maximum, $fallback );
	}
	/**
	 * Get a sanitized array value from the source array.
	 *
	 * @param array $source The source array to retrieve the key from.
	 * @param string $key The key to retrieve.
	 * @param array $fallback The fallback value if the key does not exist.
	 * @return array The sanitized array value from the source array or the fallback.
	 */
	public static function array( array $source, string $key, array $fallback = array() ): array {
		$value = self::value( $source, $key, $fallback );
		return is_array( $value ) ? wp_unslash( $value ) : $fallback;
	}
	/**
	 * Get a sanitized boolean value from the source array.
	 *
	 * @param array $source The source array to retrieve the key from.
	 * @param string $key The key to retrieve.
	 * @param bool $fallback The fallback value if the key does not exist.
	 * @return bool The sanitized boolean value from the source array or the fallback.
	 */
	public static function boolean( array $source, string $key, bool $fallback = false ): bool {
		$value = self::value( $source, $key, null );
		if ( null === $value ) {
			return $fallback;
		}

		if ( is_bool( $value ) ) {
			return $value;
		}

		$parsed = filter_var( self::unslash( $value ), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
		return null === $parsed ? $fallback : $parsed;
	}
	/**
	 * Unslash a value if the wp_unslash function exists.
	 *
	 * @param mixed $value The value to unslash.
	 * @return mixed The unslashed value.
	 */
	private static function unslash( $value ) {
		return function_exists( 'wp_unslash' ) ? wp_unslash( $value ) : $value;
	}
}
