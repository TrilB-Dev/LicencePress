<?php
/**
 * Content formatting and measurement helpers for LicencePress.
 *
 * @package LicencePress
 * @subpackage Includes\Functions\Helpers
 * @since 1.0.0
 */

namespace LicencePress\Includes\Functions\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


final class ContentHelper {
	/**
	 * Convert content to plain text by stripping tags and shortcodes.
	 *
	 * @param mixed $content The content to convert.
	 * @return string The plain text content.
	 */
	public static function plain_text( $content ): string {
		$content = is_scalar( $content ) ? (string) $content : '';
		return wp_strip_all_tags( strip_shortcodes( $content ) );
	}

	/**
	 * Count the number of words in the content.
	 *
	 * @param mixed $content The content to count words in.
	 * @return int The word count.
	 */
	public static function word_count( $content ): int {
		$plain_text = trim( self::plain_text( $content ) );
		return '' === $plain_text ? 0 : str_word_count( wp_check_invalid_utf8( $plain_text ) );
	}

	/**
	 * Estimate the reading time for the content.
	 *
	 * @param mixed $content The content to estimate reading time for.
	 * @param int $words_per_minute The reading speed in words per minute.
	 * @return int The estimated reading time in minutes.
	 */
	public static function reading_time( $content, int $words_per_minute = 200 ): int {
		$words_per_minute = max( 1, $words_per_minute );
		return max( 1, (int) ceil( self::word_count( $content ) / $words_per_minute ) );
	}

	/**
	 * Generate an excerpt from the content.
	 *
	 * @param mixed $content The content to generate an excerpt from.
	 * @param int $words The number of words for the excerpt.
	 * @return string The generated excerpt.
	 */
	public static function excerpt( $content, int $words = 30 ): string {
		return wp_trim_words( self::plain_text( $content ), max( 1, $words ) );
	}

	/**
	 * Generate a sanitized ID for a heading.
	 *
	 * @param mixed $heading The heading text.
	 * @param string $fallback The fallback ID if the heading is empty or invalid.
	 * @return string The sanitized heading ID.
	 */
	public static function heading_id( $heading, string $fallback = 'section' ): string {
		return SanitizationHelper::slug( $heading, $fallback );
	}
}
