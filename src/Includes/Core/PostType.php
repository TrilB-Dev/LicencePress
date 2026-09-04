<?php

namespace LicencePress\Includes\Core;

use LicencePress\Includes\Settings\Settings;
use LicencePress\Includes\Functions\Helpers\PermalinkHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PostType {
	public const WIKI                   = 'licencepress';
	public const PAGE                   = 'licencepress';
	public const WIKI_CAPABILITY        = 'licencepress_wiki';
	public const WIKI_CAPABILITY_PLURAL = 'licencepress_wikis';
	public const PAGE_CAPABILITY        = 'licencepress_page';
	public const PAGE_CAPABILITY_PLURAL = 'licencepress_pages';

	public function register(): void {
		register_post_type( self::WIKI, self::wiki_args() );
		register_post_type( self::PAGE, self::page_args() );
		add_filter( 'post_type_link', array( PermalinkHelper::class, 'filter_page_permalink' ), 10, 2 );
		PermalinkHelper::rewrite_rule();
	}

	public static function get_post_type_name(): string {
		return self::PAGE;
	}

	public static function page_rewrite_slug(): string {
		return self::setting_slug( 'root_slug', 'wiki' );
	}

	/**
	 * Build the Wiki container post type definition.
	 *
	 * @return array<string, mixed> Registration arguments.
	 */
	public static function wiki_args(): array {
		return apply_filters(
			'licencepress_wiki_post_type_args',
			array(
				'labels'          => array(
					'name'          => __( 'Wikis', 'licencepress' ),
					'singular_name' => __( 'Wiki', 'licencepress' ),
					'add_new_item'  => __( 'Add New Wiki', 'licencepress' ),
					'edit_item'     => __( 'Edit Wiki', 'licencepress' ),
				),
				'public'          => false,
				'show_ui'         => false,
				'show_in_rest'    => true,
				'supports'        => array( 'title', 'editor', 'author', 'thumbnail', 'revisions' ),
				'capability_type' => array( self::WIKI_CAPABILITY, self::WIKI_CAPABILITY_PLURAL ),
				'map_meta_cap'    => true,
			),
			self::WIKI
		);
	}

	/**
	 * Build the public Wiki page post type definition.
	 *
	 * @return array<string, mixed> Registration arguments.
	 */
	public static function page_args(): array {
		return apply_filters(
			'licencepress_page_post_type_args',
			array(
				'labels'          => array(
					'name'          => __( 'Wiki Pages', 'licencepress' ),
					'singular_name' => __( 'Wiki Page', 'licencepress' ),
					'add_new_item'  => __( 'Add New Wiki Page', 'licencepress' ),
					'edit_item'     => __( 'Edit Wiki Page', 'licencepress' ),
				),
				'public'          => true,
				'show_ui'         => false,
				'show_in_rest'    => true,
				'has_archive'     => false,
				'rewrite'         => array( 'slug' => self::page_rewrite_slug() ),
				'supports'        => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'revisions', 'page-attributes' ),
				'capability_type' => array( self::PAGE_CAPABILITY, self::PAGE_CAPABILITY_PLURAL ),
				'map_meta_cap'    => true,
			),
			self::PAGE
		);
	}

	public static function get_post_type_names(): array {
		return array( self::WIKI, self::PAGE );
	}

	private static function setting_slug( string $key, string $fallback ): string {
		$value = sanitize_title( (string) Settings::get( $key, $fallback ) );
		return $value !== '' ? $value : $fallback;
	}
}
