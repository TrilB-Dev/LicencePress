<?php
/**
 * Generates secure licence records for LicencePress.
 *
 * @package LicencePress
 */

namespace LicencePress\Includes\Licence;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'DAY_IN_SECONDS' ) ) {
    define( 'DAY_IN_SECONDS', 86400 );
}

final class LicenceGenerator {
    /**
     * Create a safe licence record.
     *
     * @param string $product_id Product identifier.
     * @param string $customer_id Customer identifier.
     * @param int $days Licence validity in days.
     * @param string|null $site_url Optional host for binding the licence to a site.
     * @param array<int, string> $features Feature names included in the licence.
     * @return array<string, mixed>
     */
    public static function generate(
        string $product_id,
        string $customer_id,
        int $days,
        ?string $site_url = null,
        array $features = []
    ): array {
        $token = 'LP-' . strtoupper( bin2hex( random_bytes( 16 ) ) );
        $issued_at = time();
        $expires_at = $issued_at + ( max( 1, $days ) * DAY_IN_SECONDS );

        $payload = [
            'version' => 1,
            'product_id' => $product_id,
            'customer_id' => $customer_id,
            'issued_at' => $issued_at,
            'expires_at' => $expires_at,
            'site_url' => $site_url ? self::normalize_domain( $site_url ) : null,
            'site_hash' => $site_url ? hash( 'sha256', self::normalize_domain( $site_url ) ) : null,
            'features' => array_values( array_unique( $features ) ),
        ];

        $json = wp_json_encode( $payload );
        if ( ! is_string( $json ) ) {
            throw new \RuntimeException( 'Unable to encode the licence payload.' );
        }

        $runtime_key = KeyManager::runtime_key();
        if ( null === $runtime_key || '' === $runtime_key ) {
            throw new \RuntimeException( 'LicencePress encryption key is not configured.' );
        }

        $encrypted = EncryptionService::encrypt( $json );
        if ( null === $encrypted ) {
            throw new \RuntimeException( 'Unable to encrypt the licence payload.' );
        }

        return [
            'token' => $token,
            'token_hash' => hash_hmac( 'sha256', $token, $runtime_key ),
            'payload_encrypted' => $encrypted,
            'payload' => $payload,
            'expires_at' => $expires_at,
        ];
    }

    private static function normalize_domain( string $value ): string {
        $value = trim( strtolower( wp_parse_url( $value, PHP_URL_HOST ) ?: $value ) );

        if ( '' === $value ) {
            return '';
        }

        return preg_replace( '/^www\./', '', $value ) ?? $value;
    }
}
