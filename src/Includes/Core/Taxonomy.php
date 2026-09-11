<?php

namespace LicencePress\Includes\Core;

use LicencePress\Includes\Settings\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Taxonomy {
	public const CATEGORY = 'licence_type_categories';
	public const TAG      = 'licence_type_tags';

	public function register(): void {
		register_taxonomy( self::CATEGORY, array( PostType::LICENCE_TYPE, PostType::LICENCE_TYPE_VARIANT ), self::category_args() );
		register_taxonomy( self::TAG, array( PostType::LICENCE_TYPE, PostType::LICENCE_TYPE_VARIANT ), self::tag_args() );
	}

	/**
	 * Build the hierarchical licence type category taxonomy definition.
	 *
	 * @return array<string, mixed> Registration arguments.
	 */
	public static function category_args(): array {
		return apply_filters(
			'licencepress_licence_type_categories_taxonomy_args',
			array(
				'labels'       => array(
					'name'          => __( 'Licence Type Categories', 'licencepress' ),
					'singular_name' => __( 'Licence Type Category', 'licencepress' ),
				),
				'hierarchical' => true,
				'public'       => true,
				'show_ui'      => false,
				'show_in_rest' => true,
				'rewrite'      => array( 'slug' => self::setting_slug( 'category_slug', 'licence-type-category' ) ),
			),
			self::CATEGORY
		);
	}

	/**
	 * Build the non-hierarchical licence type tag taxonomy definition.
	 *
	 * @return array<string, mixed> Registration arguments.
	 */
	public static function tag_args(): array {
		return apply_filters(
			'licencepress_licence_type_tags_taxonomy_args',
			array(
				'labels'       => array(
					'name'          => __( 'Licence Type Tags', 'licencepress' ),
					'singular_name' => __( 'Licence Type Tag', 'licencepress' ),
				),
				'hierarchical' => false,
				'public'       => true,
				'show_ui'      => false,
				'show_in_rest' => true,
				'rewrite'      => array( 'slug' => self::setting_slug( 'tag_slug', 'licence-type-tag' ) ),
			),
			self::TAG
		);
	}

	public static function get_taxonomy_names(): array {
		return array( self::CATEGORY, self::TAG );
	}

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
