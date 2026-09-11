<?php
/**
 * Tokenized LicencePress permalink support.
 *
 * @package LicencePress
 */

namespace LicencePress\Includes\Functions\Helpers;

use LicencePress\Includes\Core\PostType;
use LicencePress\Includes\Core\Taxonomy;
use LicencePress\Includes\Settings\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PermalinkHelper {
	/**
	 * Meta key used to store custom permalink patterns for individual objects.
	 */
	public const OVERRIDE_META = '_licencepress_permalink';

	/**
	 * Return the list of available token definitions for permalinks.
	 *
	 * @return array<string, string> The token definitions.
	 */
	public static function token_definitions(): array {
		return array(
			'%root%'                       => __( 'The root LicencePress slug.', 'licencepress' ),
			'%root_category%'              => __( 'The licence type category path from parent to child.', 'licencepress' ),
			'%root_tags%'                  => __( 'The tags assigned to the base licence type container.', 'licencepress' ),
			'%licence_type%'               => __( 'The licence type slug.', 'licencepress' ),
			'%licence_type_category%'      => __( 'The licence type category path from parent to child.', 'licencepress' ),
			'%licence_type_tag%'           => __( 'The tags assigned to the licence type.', 'licencepress' ),
			'%licence_type_variant%'       => __( 'The licence type variant slug.', 'licencepress' ),
		);
	}

	/**
	 * Return the default permalink pattern.
	 *
	 * @return string The default permalink pattern.
	 */
	public static function default_pattern(): string {
		return '%root%/%root_category%/%licence_type%/%licence_type_category%/%licence_type_tag%/%licence_type_variant%';
	}

	/**
	 * Sanitize a permalink pattern by ensuring only allowed tokens and slugs are present.
	 *
	 * @param string $pattern The permalink pattern to sanitize.
	 * @return string The sanitized permalink pattern.
	 */
	public static function sanitize_pattern( $pattern ): string {
		$pattern  = trim( (string) $pattern );
		$allowed  = array_keys( self::token_definitions() );
		$segments = array();

		$split_segments = preg_split( '#/+#', trim( $pattern, '/' ) );
		$segments_list  = is_array( $split_segments ) ? $split_segments : array();

		foreach ( $segments_list as $segment ) {
			$segment = trim( (string) $segment );
			if ( '' === $segment ) {
				continue;
			}
			if ( in_array( $segment, $allowed, true ) ) {
				$segments[] = $segment;
				continue;
			}
			$slug = sanitize_title( $segment );
			if ( '' !== $slug ) {
				$segments[] = $slug;
			}
		}

		return implode( '/', $segments );
	}

	/**
	 * Retrieve the permalink pattern for a specific object, falling back to the default if none is set.
	 *
	 * @param int $object_id The object ID.
	 * @return string The resolved permalink pattern.
	 */
	public static function pattern_for_object( int $object_id = 0 ): string {
		$pattern = '';
		if ( $object_id > 0 ) {
			$pattern = get_post_meta( $object_id, self::OVERRIDE_META, true );
		}

		$resolved_pattern = '' !== $pattern ? $pattern : Settings::get( 'permalink', self::default_pattern() );
		$sanitized        = self::sanitize_pattern( $resolved_pattern );
		return '' !== $sanitized ? $sanitized : self::default_pattern();
	}

	/**
	 * Generate the URL for a given licence type variant.
	 *
	 * @param \WP_Post $page The licence variant post object.
	 * @return string The full URL to the variant.
	 */
	public static function page_url( \WP_Post $page ): string {
		$type_id = absint( get_post_meta( $page->ID, '_licencepress_licence_type_id', true ) );
		$type    = null;
		if ( $type_id > 0 ) {
			$type = get_post( $type_id );
		}

		$pattern = self::pattern_for_object( $type_id );
		$path    = self::expand( $pattern, $page, $type instanceof \WP_Post ? $type : null );
		return home_url( user_trailingslashit( trim( $path, '/' ) ) );
	}

	/**
	 * Expand a permalink pattern into a full path for a given licence type variant and parent licence type.
	 *
	 * @param string $pattern The permalink pattern to expand.
	 * @param \WP_Post $variant The licence type variant post object.
	 * @param \WP_Post|null $type The parent licence type post object, if available.
	 * @return string The expanded permalink path.
	 */
	public static function expand( string $pattern, \WP_Post $variant, ?\WP_Post $type = null ): string {
		$root_slug = sanitize_title( (string) Settings::get( 'root_slug', 'licence-types' ) );
		$type_name = '';
		if ( $type instanceof \WP_Post ) {
			$type_post_name = $type->post_name;
			if ( '' === $type_post_name ) {
				$type_post_name = $type->post_title;
			}
			$type_name = sanitize_title( $type_post_name );
		}

		$root_category = '';
		$root_tags     = '';
		if ( $type instanceof \WP_Post ) {
			$root_category = self::term_path( Taxonomy::CATEGORY, $type->ID );
			$root_tags     = self::term_path( Taxonomy::TAG, $type->ID );
		}

		$variant_title = $variant->post_name;
		if ( '' === $variant_title ) {
			$variant_title = $variant->post_title;
		}
		$licence_type_variant = sanitize_title( $variant_title );
		$values               = array(
			'%root%'                  => $root_slug,
			'%root_category%'         => $root_category,
			'%root_tags%'             => $root_tags,
			'%licence_type%'          => $type_name,
			'%licence_type_category%' => self::term_path( Taxonomy::CATEGORY, $variant->ID ),
			'%licence_type_tag%'      => self::term_path( Taxonomy::TAG, $variant->ID ),
			'%licence_type_variant%'  => $licence_type_variant,
		);

		$normalized = self::sanitize_pattern( $pattern );
		$path       = strtr( $normalized, $values );
		if ( false === strpos( $normalized, '%licence_type_variant%' ) ) {
			$path .= '/' . $values['%licence_type_variant%'];
		}
		return trim( preg_replace( '#/+#', '/', trim( $path, '/' ) ), '/' );
	}

	/**
	 * Register the rewrite rules and query variable for custom permalinks.
	 *
	 * @return void
	 */
	public static function rewrite_rule(): void {
		add_rewrite_rule( '^(.+?)/?$', 'index.php?licencepress_path=$matches[1]', 'top' );
		add_filter(
			'query_vars',
			static function ( array $vars ): array {
				$vars[] = 'licencepress_path';
				return $vars;
			}
		);
		add_filter( 'request', array( self::class, 'resolve_request' ) );
	}

	/**
	 * Resolve the request for a custom permalink and map it to the appropriate variant.
	 *
	 * @param array $vars The query variables.
	 * @return array The modified query variables.
	 */
	public static function resolve_request( array $vars ): array {
		$requested_path = isset( $vars['licencepress_path'] ) ? trim( urldecode( (string) $vars['licencepress_path'] ), '/' ) : '';
		if ( '' === $requested_path ) {
			return $vars;
		}

		$variants = get_posts(
			array(
				'post_type'        => PostType::LICENCE_TYPE_VARIANT,
				'post_status'      => 'publish',
				'posts_per_page'   => -1,
				'suppress_filters' => false,
			)
		);
		foreach ( $variants as $variant ) {
			if ( self::page_url_path( $variant ) === $requested_path ) {
				return array( 'p' => $variant->ID );
			}
		}

		return $vars;
	}

	/**
	 * Filter the permalink for a licence type variant to use the custom permalink structure.
	 *
	 * @param string $link The original permalink.
	 * @param \WP_Post $post The post object.
	 * @return string The filtered permalink.
	 */
	public static function filter_page_permalink( string $link, \WP_Post $post ): string {
		return $post->post_type === PostType::LICENCE_TYPE_VARIANT ? self::page_url( $post ) : $link;
	}

	/**
	 * Retrieve the URL path for a licence type variant based on its permalink pattern.
	 *
	 * @param \WP_Post $variant The licence variant post object.
	 * @return string The URL path for the variant.
	 */
	private static function page_url_path( \WP_Post $variant ): string {
		$type_id = absint( get_post_meta( $variant->ID, '_licencepress_licence_type_id', true ) );
		$type    = null;
		if ( $type_id > 0 ) {
			$type = get_post( $type_id );
		}
		return self::expand( self::pattern_for_object( $type_id ), $variant, $type instanceof \WP_Post ? $type : null );
	}

	/**
	 * Retrieve the URL path for a taxonomy term associated with a post.
	 *
	 * @param string $taxonomy The taxonomy name.
	 * @param int $post_id The post ID.
	 * @return string The URL path for the taxonomy term.
	 */
	private static function term_path( string $taxonomy, int $post_id ): string {
		$terms = get_the_terms( $post_id, $taxonomy );
		if ( ! is_array( $terms ) || empty( $terms ) ) {
			return '';
		}

		$ordered = array();
		foreach ( $terms as $term ) {
			$ancestors = is_taxonomy_hierarchical( $taxonomy ) ? array_reverse( get_ancestors( $term->term_id, $taxonomy, 'taxonomy' ) ) : array();
			foreach ( array_merge( $ancestors, array( $term->term_id ) ) as $term_id ) {
				$ancestor = get_term( $term_id, $taxonomy );
				if ( $ancestor && ! is_wp_error( $ancestor ) ) {
					$slug = $ancestor->slug;
					if ( '' === $slug ) {
						$slug = $ancestor->name;
					}
					$ordered[ $ancestor->term_id ] = sanitize_title( $slug );
				}
			}
		}

		return implode( '/', array_filter( $ordered ) );
	}
}
