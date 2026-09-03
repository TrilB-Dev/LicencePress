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
    public const OVERRIDE_META = '_licencepress_permalink';

    public static function token_definitions(): array {
        return [
            '%root%' => __( 'The root LicencePress slug.', 'licencepress' ),
            '%root_category%' => __( 'The Wiki categories, from parent to child.', 'licencepress' ),
            '%root_tags%' => __( 'The tags assigned to the Wiki container.', 'licencepress' ),
            '%wiki%' => __( 'The Wiki slug.', 'licencepress' ),
            '%wiki_category%' => __( 'The Wiki page categories, from parent to child.', 'licencepress' ),
            '%wiki_tag%' => __( 'The tags assigned to the Wiki page.', 'licencepress' ),
            '%wiki_page%' => __( 'The Wiki page slug.', 'licencepress' ),
        ];
    }

    public static function default_pattern(): string {
        return '%root%/%root_category%/%wiki%/%wiki_category%/%wiki_tag%/%wiki_page%';
    }

    public static function sanitize_pattern( $pattern ): string {
        $pattern = trim( (string) $pattern );
        $allowed = array_keys( self::token_definitions() );
        $segments = [];

        foreach ( preg_split( '#/+#', trim( $pattern, '/' ) ) ?: [] as $segment ) {
            $segment = trim( $segment );
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

    public static function pattern_for_object( int $object_id = 0 ): string {
        $pattern = $object_id > 0 ? get_post_meta( $object_id, self::OVERRIDE_META, true ) : '';
        return self::sanitize_pattern( $pattern ?: Settings::get( 'permalink', self::default_pattern() ) ) ?: self::default_pattern();
    }

    public static function page_url( \WP_Post $page ): string {
        $wiki_id = absint( get_post_meta( $page->ID, '_licencepress_wiki_id', true ) );
        $wiki = $wiki_id > 0 ? get_post( $wiki_id ) : null;
        $pattern = self::pattern_for_object( $wiki_id );
        $path = self::expand( $pattern, $page, $wiki instanceof \WP_Post ? $wiki : null );
        return home_url( user_trailingslashit( trim( $path, '/' ) ) );
    }

    public static function expand( string $pattern, \WP_Post $page, ?\WP_Post $wiki = null ): string {
        $values = [
            '%root%' => sanitize_title( (string) Settings::get( 'root_slug', 'wiki' ) ),
            '%root_category%' => $wiki ? self::term_path( Taxonomy::CATEGORY, $wiki->ID ) : '',
            '%root_tags%' => $wiki ? self::term_path( Taxonomy::TAG, $wiki->ID ) : '',
            '%wiki%' => $wiki ? sanitize_title( $wiki->post_name ?: $wiki->post_title ) : '',
            '%wiki_category%' => self::term_path( Taxonomy::CATEGORY, $page->ID ),
            '%wiki_tag%' => self::term_path( Taxonomy::TAG, $page->ID ),
            '%wiki_page%' => sanitize_title( $page->post_name ?: $page->post_title ),
        ];

        $normalized = self::sanitize_pattern( $pattern );
        $path = strtr( $normalized, $values );
        if ( ! str_contains( $normalized, '%wiki_page%' ) ) {
            $path .= '/' . $values['%wiki_page%'];
        }
        return trim( preg_replace( '#/+#', '/', trim( $path, '/' ) ), '/' );
    }

    public static function rewrite_rule(): void {
        add_rewrite_rule( '^(.+?)/?$', 'index.php?licencepress_path=$matches[1]', 'top' );
        add_filter( 'query_vars', static function ( array $vars ): array {
            $vars[] = 'licencepress_path';
            return $vars;
        } );
        add_filter( 'request', [ self::class, 'resolve_request' ] );
    }

    public static function resolve_request( array $vars ): array {
        $requested_path = isset( $vars['licencepress_path'] ) ? trim( urldecode( (string) $vars['licencepress_path'] ), '/' ) : '';
        if ( '' === $requested_path ) {
            return $vars;
        }

        $pages = get_posts( [
            'post_type' => PostType::PAGE,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'suppress_filters' => false,
        ] );
        foreach ( $pages as $page ) {
            if ( self::page_url_path( $page ) === $requested_path ) {
                return [ 'p' => $page->ID ];
            }
        }

        return $vars;
    }

    public static function filter_page_permalink( string $link, \WP_Post $post ): string {
        return $post->post_type === PostType::PAGE ? self::page_url( $post ) : $link;
    }

    private static function page_url_path( \WP_Post $page ): string {
        $wiki_id = absint( get_post_meta( $page->ID, '_licencepress_wiki_id', true ) );
        $wiki = $wiki_id > 0 ? get_post( $wiki_id ) : null;
        return self::expand( self::pattern_for_object( $wiki_id ), $page, $wiki instanceof \WP_Post ? $wiki : null );
    }

    private static function term_path( string $taxonomy, int $post_id ): string {
        $terms = get_the_terms( $post_id, $taxonomy );
        if ( ! is_array( $terms ) || empty( $terms ) ) {
            return '';
        }

        $ordered = [];
        foreach ( $terms as $term ) {
            $ancestors = is_taxonomy_hierarchical( $taxonomy ) ? array_reverse( get_ancestors( $term->term_id, $taxonomy, 'taxonomy' ) ) : [];
            foreach ( array_merge( $ancestors, [ $term->term_id ] ) as $term_id ) {
                $ancestor = get_term( $term_id, $taxonomy );
                if ( $ancestor && ! is_wp_error( $ancestor ) ) {
                    $ordered[ $ancestor->term_id ] = sanitize_title( $ancestor->slug ?: $ancestor->name );
                }
            }
        }

        return implode( '/', array_filter( $ordered ) );
    }
}