<?php
/**
 * Safe maintenance operations for LicencePress.
 *
 * @package LicencePress
 * @subpackage Includes\Tools
 * @since 1.0.0
 */

namespace LicencePress\Includes\Tools;

use LicencePress\Includes\Functions\Helpers\SanitizationHelper;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Maintenance {
    public static function flush_rewrites(): bool {
        flush_rewrite_rules();
        return true;
    }

    public static function clear_cache( string $group = '' ): bool {
        if ( '' !== $group && function_exists( 'wp_cache_flush_group' ) ) {
            return (bool) wp_cache_flush_group( SanitizationHelper::key( $group ) );
        }

        return (bool) wp_cache_flush();
    }

    public static function rebuild(): array {
        return [
            'rewrites_flushed' => self::flush_rewrites(),
            'cache_cleared' => self::clear_cache(),
        ];
    }
}