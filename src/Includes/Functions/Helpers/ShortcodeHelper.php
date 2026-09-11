<?php

namespace LicencePress\Includes\Functions\Helpers;

use LicencePress\Includes\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Convenience methods for defining LicencePress shortcodes.
 */
final class ShortcodeHelper {
	/**
	 * Create a shortcode definition for a plugin shortcode list.
	 *
	 * @param array<string, mixed> $metadata Optional descriptor metadata.
	 * @return array<string, mixed>
	 */
	public static function define( string $tag, callable $callback, array $attributes = array(), array $metadata = array() ): array {
		return array_merge(
			array(
				'tag'         => $tag,
				'callback'    => $callback,
				'attributes'  => $attributes,
				'description' => '',
				'category'    => '',
				'enclosing'   => false,
				'tinymce'     => false,
			),
			$metadata
		);
	}

	/**
	 * Register a single shortcode definition.
	 *
	 * @param array<string, mixed> $definition The shortcode definition.
	 * @param bool $replace Optional. Whether to replace an existing shortcode with the same tag. Default false.
	 * @return bool True if the shortcode was registered successfully, false otherwise.
	 */
	public static function register( array $definition, bool $replace = false ): bool {
		return Includes::get_instance()->core()->shortcodes()->register( $definition, $replace );
	}

	/**
	 * Register multiple shortcode definitions at once.
	 *
	 * @param array<int, array<string, mixed>> $definitions The shortcode definitions.
	 * @param bool $replace Optional. Whether to replace existing shortcodes with the same tags. Default false.
	 * @return array<int, string> The tags of the successfully registered shortcodes.
	 */
	public static function register_many( array $definitions, bool $replace = false ): array {
		return Includes::get_instance()->core()->shortcodes()->register_many( $definitions, $replace );
	}
}
