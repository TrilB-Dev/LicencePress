<?php
/**
 * Post type definitions for LicencePress.
 * 
 * Defines the custom post types used by LicencePress.
 * @since 1.0.0
 */
namespace LicencePress\Includes\Core;

use LicencePress\Includes\Settings\Settings;
use LicencePress\Includes\Functions\Helpers\PermalinkHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PostType {
	/**
	 * Register the custom post types.
	 *
	 * @return void
	 */
	public const LICENCE_TYPE                   			 = 'licencepress_licence_type';
	/**
	 * Post type for the Wiki container.
	 * 
	 * Post type for the variants of a Wiki container.
	 * @return mixed
	 */
	public const LICENCE_TYPE_VARIANT                   	 = 'licencepress_licence_type_variant';
	/**
	 * Capability for the Wiki container post type.
	 */
	public const LICENCEPRESS_TYPE_CAPABILITY        		 = 'licencepress_licence_type';
	/**
	 * Capability for the Wiki container variant post type.
	 */
	public const LICENCEPRESS_TYPE_CAPABILITY_PLURAL 		 = 'licencepress_licence_types';
	/**
	 * Capability for the Wiki container variant post type.
	 */
	public const LICENCEPRESS_TYPE_VARIANT_CAPABILITY        = 'licencepress_licence_type_variant';
	/**
	 * Capability for the plural form of the Wiki container variant post type.
	 */
	public const LICENCEPRESS_TYPE_VARIANT_CAPABILITY_PLURAL = 'licencepress_licence_type_variants';
	/**
	 * Register the custom post types.
	 *
	 * @return void
	 */
	public function register(): void {
		register_post_type( self::LICENCE_TYPE, self::wiki_args() );
		register_post_type( self::LICENCE_TYPE_VARIANT, self::page_args() );
		add_filter( 'post_type_link', array( PermalinkHelper::class, 'filter_page_permalink' ), 10, 2 );
		PermalinkHelper::rewrite_rule();
	}
	/**
	 * Get the post type name for the public licence type variant.
	 *
	 * @return string
	 */
	public static function get_post_type_name(): string {
		return self::LICENCE_TYPE_VARIANT;
	}

	/**
	 * Get the rewrite slug for the public licence type variant pages.
	 *
	 * @return string
	 */
	public static function page_rewrite_slug(): string {
		return self::setting_slug( 'root_slug', 'wiki' );
	}

	/**
	 * Build the licence type post type definition.
	 *
	 * @return array<string, mixed> Registration arguments.
	 */
	public static function wiki_args(): array {
		return apply_filters(
			'licencepress_licence_type_post_type_args',
			array(
				'labels'          => array(
					'name'          => __( 'Licence Types', 'licencepress' ),
					'singular_name' => __( 'Licence Type', 'licencepress' ),
					'add_new_item'  => __( 'Add New Licence Type', 'licencepress' ),
					'edit_item'     => __( 'Edit Licence Type', 'licencepress' ),
				),
				'public'          => false,
				'show_ui'         => false,
				'show_in_rest'    => true,
				'supports'        => array( 'title', 'editor', 'author', 'thumbnail', 'revisions' ),
				'capability_type' => array( self::LICENCEPRESS_TYPE_CAPABILITY, self::LICENCEPRESS_TYPE_CAPABILITY_PLURAL ),
				'map_meta_cap'    => true,
			),
			self::LICENCE_TYPE
		);
	}

	/**
	 * Build the public licence type variant post type definition.
	 *
	 * @return array<string, mixed> Registration arguments.
	 */
	public static function page_args(): array {
		return apply_filters(
			'licencepress_licence_type_variant_post_type_args',
			array(
				'labels'          => array(
					'name'          => __( 'Licence Type Variants', 'licencepress' ),
					'singular_name' => __( 'Licence Type Variant', 'licencepress' ),
					'add_new_item'  => __( 'Add New Licence Type Variant', 'licencepress' ),
					'edit_item'     => __( 'Edit Licence Type Variant', 'licencepress' ),
				),
				'public'          => true,
				'show_ui'         => false,
				'show_in_rest'    => true,
				'has_archive'     => false,
				'rewrite'         => array( 'slug' => self::page_rewrite_slug() ),
				'supports'        => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'revisions', 'page-attributes' ),
				'capability_type' => array( self::LICENCEPRESS_TYPE_VARIANT_CAPABILITY, self::LICENCEPRESS_TYPE_VARIANT_CAPABILITY_PLURAL ),
				'map_meta_cap'    => true,
			),
			self::LICENCE_TYPE_VARIANT
		);
	}
	/**
	 * Get all registered post type names.
	 *
	 * @return array<string> Post type names.
	 */
	public static function get_post_type_names(): array {
		return array( self::LICENCE_TYPE, self::LICENCE_TYPE_VARIANT );
	}
	/**
	 * Get the slug for a specific setting, with a fallback.
	 *
	 * @param string $key     Setting key.
	 * @param string $fallback Fallback value.
	 *
	 * @return string
	 */
	private static function setting_slug( string $key, string $fallback ): string {
		$value = (string) Settings::get( $key, $fallback );
		if ( function_exists( 'sanitize_title' ) ) {
			$value = sanitize_title( $value );
		} else {
			$value = strtolower( preg_replace( '/[^a-z0-9]+/i', '-', $value ) );
			$value = trim( $value, '-' );
		}
		return $value !== '' ? $value : $fallback;
	}
}
