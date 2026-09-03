<?php
/**
 * Licence key management for LicencePress.
 *
 * @package LicencePress
 */

namespace LicencePress\Includes\Licence;

use Defuse\Crypto\Key;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class KeyManager {
    private static ?string $runtime_key = null;

    public static function set_runtime_key( ?string $key ): void {
        self::$runtime_key = $key;
    }

    public static function runtime_key(): ?string {
        if ( null !== self::$runtime_key ) {
            return self::$runtime_key;
        }

        if ( defined( 'LICENCEPRESS_ENCRYPTION_KEY' ) ) {
            self::$runtime_key = (string) constant( 'LICENCEPRESS_ENCRYPTION_KEY' );
            return self::$runtime_key;
        }

        return null;
    }

    public static function ensure_configured(): bool {
        if ( null !== self::runtime_key() ) {
            return true;
        }

        $config_path = self::config_path();
        if ( null === $config_path || ! is_readable( $config_path ) || ! is_writable( $config_path ) ) {
            return false;
        }

        $config_content = file_get_contents( $config_path );
        if ( false === $config_content ) {
            return false;
        }

        if ( preg_match( '/define\s*\(\s*[\'\"]LICENCEPRESS_ENCRYPTION_KEY[\'\"]\s*,/i', $config_content ) ) {
            return true;
        }

        try {
            $key = Key::createNewRandomKey()->saveToAsciiSafeString();
        } catch ( \Throwable $exception ) {
            return false;
        }

        $new_line = "define( 'LICENCEPRESS_ENCRYPTION_KEY', '" . addslashes( $key ) . "' );" . PHP_EOL;
        $marker = '/* That\'s all, stop editing!';
        $marker_position = strpos( $config_content, $marker );

        if ( false !== $marker_position ) {
            $updated_content = substr_replace( $config_content, $new_line, $marker_position, 0 );
        } else {
            $settings_load = "require_once ABSPATH . 'wp-settings.php';";
            $settings_position = strpos( $config_content, $settings_load );
            if ( false === $settings_position ) {
                return false;
            }
            $updated_content = substr_replace( $config_content, $new_line, $settings_position, 0 );
        }

        $handle = fopen( $config_path, 'c+' );
        if ( false === $handle || ! flock( $handle, LOCK_EX ) ) {
            if ( is_resource( $handle ) ) {
                fclose( $handle );
            }
            return false;
        }

        $success = ftruncate( $handle, 0 ) && rewind( $handle ) && false !== fwrite( $handle, $updated_content );
        fflush( $handle );
        flock( $handle, LOCK_UN );
        fclose( $handle );

        if ( ! $success ) {
            return false;
        }

        if ( ! defined( 'LICENCEPRESS_ENCRYPTION_KEY' ) ) {
            define( 'LICENCEPRESS_ENCRYPTION_KEY', $key );
        }

        self::$runtime_key = $key;
        return true;
    }

    private static function config_path(): ?string {
        $abspath = defined( 'ABSPATH' ) ? (string) ABSPATH : null;
        if ( null === $abspath ) {
            return null;
        }

        $paths = [
            $abspath . 'wp-config.php',
            dirname( $abspath ) . '/wp-config.php',
        ];

        foreach ( $paths as $path ) {
            if ( file_exists( $path ) ) {
                return $path;
            }
        }

        return null;
    }
}
