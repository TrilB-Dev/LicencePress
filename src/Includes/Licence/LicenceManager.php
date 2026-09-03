<?php
/**
 * High-level licence management for LicencePress.
 *
 * @package LicencePress
 */

namespace LicencePress\Includes\Licence;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class LicenceManager {
    public static function initialize(): void {
        KeyManager::ensure_configured();
        LicenceRepository::register_schema();
        LicenceTypeManager::register_schema();
    }

    public static function create_license(
        string $product_id,
        string $customer_id,
        int $days,
        ?string $site_url = null,
        array $features = []
    ): array {
        self::initialize();

        $type = LicenceTypeManager::find_by_product_id( $product_id );
        if ( is_array( $type ) && ! empty( $type['is_retired'] ) ) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The licence type "%s" is retired and cannot issue new licences.',
                    $product_id
                )
            );
        }

        $record = LicenceGenerator::generate( $product_id, $customer_id, $days, $site_url, $features );

        $row_id = LicenceRepository::create( [
            'product_id' => $product_id,
            'customer_id' => $customer_id,
            'token_hash' => $record['token_hash'],
            'site_hash' => $record['payload']['site_hash'] ?? '',
            'payload_encrypted' => $record['payload_encrypted'],
            'payload' => $record['payload'],
            'status' => 'active',
            'issued_at' => $record['payload']['issued_at'],
            'expires_at' => $record['payload']['expires_at'],
        ] );

        $record['id'] = $row_id;
        $record['status'] = 'active';

        return $record;
    }

    public static function validate_license( string $token, string $product_id, ?string $site_url = null ): bool {
        $record = LicenceRepository::find_by_token( $token );
        if ( null === $record ) {
            return false;
        }

        return LicenceValidator::validate( $token, $product_id, $site_url, $record );
    }

    public static function revoke_license( string $token ): bool {
        return LicenceRepository::revoke( $token );
    }

    public static function find_license( string $token ): ?array {
        return LicenceRepository::find_by_token( $token );
    }

    public static function list_for_customer( string $customer_id ): array {
        self::initialize();
        return LicenceRepository::list_by_customer( $customer_id );
    }

    public static function summary(): array {
        self::initialize();

        return [
            'active' => LicenceRepository::count_active(),
            'expiring_soon' => LicenceRepository::count_expiring_soon(),
            'revoked' => LicenceRepository::count_revoked(),
            'customers' => LicenceRepository::count_customers(),
        ];
    }
}
