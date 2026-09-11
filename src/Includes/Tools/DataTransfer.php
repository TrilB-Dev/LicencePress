<?php

namespace LicencePress\Includes\Tools;

use LicencePress\Includes\Core\PostType;
use LicencePress\Includes\Core\Taxonomy;
use LicencePress\Includes\Functions\Helpers\SanitizationHelper;
use LicencePress\Includes\Functions\Helpers\PostHelper;
use LicencePress\Includes\Functions\Helpers\QueryHelper;
use LicencePress\Includes\Functions\Helpers\TaxonomyHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DataTransfer {
	public const VERSION = 1;

	public static function export(): array {
		$data  = array(
			'version'    => self::VERSION,
			'licence_types' => array(),
			'variants'      => array(),
			'categories'    => array(),
			'tags'          => array(),
		);
		$query = QueryHelper::posts(
			array(
				'post_type'      => array( PostType::LICENCE_TYPE, PostType::LICENCE_TYPE_VARIANT ),
				'post_status'    => 'any',
				'posts_per_page' => -1,
			)
		);
		while ( $query->have_posts() ) {
			$query->the_post();
			$post = PostHelper::current();
			if ( ! $post ) {
				continue;
			}

			$item = array(
				'id'         => $post->ID,
				'title'      => $post->post_title,
				'content'    => $post->post_content,
				'excerpt'    => $post->post_excerpt,
				'status'     => $post->post_status,
				'licence_type_id' => absint( get_post_meta( $post->ID, '_licencepress_licence_type_id', true ) ),
				'categories' => TaxonomyHelper::names( TaxonomyHelper::terms( Taxonomy::CATEGORY, $post->ID ) ),
				'tags'       => TaxonomyHelper::names( TaxonomyHelper::terms( Taxonomy::TAG, $post->ID ) ),
			);
			$data[ $post->post_type === PostType::LICENCE_TYPE ? 'licence_types' : 'variants' ][] = $item;
		}
		wp_reset_postdata();
		foreach ( array(
			'categories' => Taxonomy::CATEGORY,
			'tags'       => Taxonomy::TAG,
		) as $key => $taxonomy ) {
			$terms = TaxonomyHelper::terms( $taxonomy );
			foreach ( $terms as $term ) {
				$data[ $key ][] = array(
					'name'        => $term->name,
					'slug'        => $term->slug,
					'description' => $term->description,
				);
			}
		}
		return $data;
	}

	public static function export_json( int $flags = 0 ): string {
		$json = wp_json_encode( self::export(), $flags );
		return is_string( $json ) ? $json : '';
	}

	public static function validate( $data ): array {
		$errors = array();
		if ( ! is_array( $data ) ) {
			return array(
				'valid'  => false,
				'errors' => array( __( 'The import data must be an object.', 'licencepress' ) ),
			);
		}
		if ( absint( $data['version'] ?? 0 ) !== self::VERSION ) {
			$errors[] = __( 'This LicencePress export version is not supported.', 'licencepress' );
		}
		foreach ( array( 'licence_types', 'variants', 'categories', 'tags' ) as $key ) {
			if ( isset( $data[ $key ] ) && ! is_array( $data[ $key ] ) ) {
				/* translators: %s is the name of the export section. */
				$errors[] = sprintf( esc_html__( 'The %s export section must be an array.', 'licencepress' ), $key );
			}
		}

		return array(
			'valid'  => empty( $errors ),
			'errors' => $errors,
		);
	}

	public static function import( array $data ): array|\WP_Error {
		$validation = self::validate( $data );
		if ( ! $validation['valid'] ) {
			return new \WP_Error( 'invalid_import', implode( ' ', $validation['errors'] ) );
		}
		$wiki_map = array();
		$result   = array(
			'wikis'      => 0,
			'pages'      => 0,
			'categories' => 0,
			'tags'       => 0,
			'errors'     => array(),
		);
		foreach ( (array) ( $data['licence_types'] ?? array() ) as $licence_type ) {
			if ( ! is_array( $licence_type ) ) {
				$result['errors'][] = __( 'A licence type entry was skipped because it was invalid.', 'licencepress' );
				continue;
			}
			$id = wp_insert_post(
				array(
					'post_type'    => PostType::LICENCE_TYPE,
					'post_title'   => SanitizationHelper::text( $licence_type['title'] ?? '' ),
					'post_content' => self::content( $licence_type['content'] ?? '' ),
					'post_status'  => self::status( $licence_type['status'] ?? 'draft' ),
				),
				true
			);
			if ( ! is_wp_error( $id ) ) {
				$wiki_map[ absint( $licence_type['id'] ?? 0 ) ] = (int) $id;
				++$result['licence_types'];
			} else {
				$result['errors'][] = $id->get_error_message();
			}
		}
		foreach ( (array) ( $data['variants'] ?? array() ) as $variant ) {
			if ( ! is_array( $variant ) ) {
				$result['errors'][] = __( 'A variant entry was skipped because it was invalid.', 'licencepress' );
				continue;
			}
			$id = wp_insert_post(
				array(
					'post_type'    => PostType::LICENCE_TYPE_VARIANT,
					'post_title'   => SanitizationHelper::text( $variant['title'] ?? '' ),
					'post_content' => self::content( $variant['content'] ?? '' ),
					'post_excerpt' => SanitizationHelper::text( $variant['excerpt'] ?? '' ),
					'post_status'  => self::status( $variant['status'] ?? 'draft' ),
				),
				true
			);
			if ( ! is_wp_error( $id ) ) {
				++$result['variants'];
				if ( ! empty( $wiki_map[ absint( $variant['licence_type_id'] ?? 0 ) ] ) ) {
					update_post_meta( (int) $id, '_licencepress_licence_type_id', $wiki_map[ absint( $variant['licence_type_id'] ?? 0 ) ] );
				}
				self::set_terms( (int) $id, $variant['categories'] ?? array(), Taxonomy::CATEGORY );
				self::set_terms( (int) $id, $variant['tags'] ?? array(), Taxonomy::TAG );
			} else {
				$result['errors'][] = $id->get_error_message();
			}
		}
		$result['categories'] = self::import_terms( (array) ( $data['categories'] ?? array() ), Taxonomy::CATEGORY );
		$result['tags']       = self::import_terms( (array) ( $data['tags'] ?? array() ), Taxonomy::TAG );
		return $result;
	}

	private static function import_terms( array $terms, string $taxonomy ): int {
		$count = 0;
		foreach ( $terms as $term ) {
			if ( ! is_array( $term ) || empty( $term['name'] ) ) {
				continue;
			}
			$inserted = wp_insert_term(
				SanitizationHelper::text( $term['name'] ),
				$taxonomy,
				array(
					'slug'        => SanitizationHelper::slug( $term['slug'] ?? '' ),
					'description' => SanitizationHelper::textarea( $term['description'] ?? '' ),
				)
			);
			if ( ! is_wp_error( $inserted ) ) {
				++$count;
			}
		}
		return $count;
	}

	private static function set_terms( int $post_id, $terms, string $taxonomy ): void {
		wp_set_post_terms( $post_id, TaxonomyHelper::names( $terms ), $taxonomy, false );
	}

	private static function status( $status ): string {
		if ( ! is_scalar( $status ) ) {
			return 'draft';
		}

		$status = sanitize_key( (string) $status );
		return in_array( $status, array( 'publish', 'draft', 'private' ), true ) ? $status : 'draft';
	}

	private static function content( $content ): string {
		return is_scalar( $content ) ? wp_kses_post( (string) $content ) : '';
	}
}
