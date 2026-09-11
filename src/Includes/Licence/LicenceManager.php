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
	/**
	 * Initializes the licence manager by ensuring the key manager is configured
	 * and registering the necessary schemas for licences and licence types.
	 *
	 * @since 1.0.0
	 */
	public static function initialize(): void {
		KeyManager::ensure_configured();
		LicenceRepository::register_schema();
		LicenceTypeManager::register_schema();
	}
	/**
	 * Creates a new licence for a given product and customer.
	 *
	 * @param string      $product_id The ID of the product.
	 * @param string      $customer_id The ID of the customer.
	 * @param int         $days The number of days the licence is valid for.
	 * @param string|null $site_url The URL of the site where the licence will be used.
	 * @param array       $features The features associated with the licence.
	 * @return array The created licence record.
	 * @since 1.0.0
	 * @throws \InvalidArgumentException If the licence type is retired.
	 */
	public static function create_license(
		string $product_id,
		string $customer_id,
		int $days,
		?string $site_url = null,
		array $features = array()
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

		$row_id = LicenceRepository::create(
			array(
				'product_id'        => $product_id,
				'customer_id'       => $customer_id,
				'token_hash'        => $record['token_hash'],
				'site_hash'         => $record['payload']['site_hash'] ?? '',
				'payload_encrypted' => $record['payload_encrypted'],
				'payload'           => $record['payload'],
				'status'            => 'active',
				'issued_at'         => $record['payload']['issued_at'],
				'expires_at'        => $record['payload']['expires_at'],
			)
		);

		$record['id']     = $row_id;
		$record['status'] = 'active';

		return $record;
	}
	/**
	 * Validates a licence token for a given product and site URL.
	 *
	 * @since 1.0.0
	 * @param string      $token The licence token.
	 * @param string      $product_id The ID of the product.
	 * @param string|null $site_url The URL of the site where the licence is used.
	 * @return bool True if the licence is valid, false otherwise.
	 */
	public static function validate_license( string $token, string $product_id, ?string $site_url = null ): bool {
		$record = LicenceRepository::find_by_token( $token );
		if ( null === $record ) {
			return false;
		}

		return LicenceValidator::validate( $token, $product_id, $site_url, $record );
	}

	/**
	 * Revokes a licence token.
	 *
	 * @since 1.0.0
	 * @param string $token The licence token to revoke.
	 * @return bool True if the licence was successfully revoked, false otherwise.
	 */
	public static function revoke_license( string $token ): bool {
		return LicenceRepository::revoke( $token );
	}

	/**
	 * Finds a licence by its token.
	 *
	 * @since 1.0.0
	 * @param string $token The licence token.
	 * @return array|null The licence record if found, null otherwise.
	 */
	public static function find_license( string $token ): ?array {
		return LicenceRepository::find_by_token( $token );
	}

	/**
	 * Lists all licences for a given customer.
	 *
	 * @since 1.0.0
	 * @param string $customer_id The ID of the customer.
	 * @return array The array of licences for the customer.
	 */
	public static function list_for_customer( string $customer_id ): array {
		self::initialize();
		return LicenceRepository::list_by_customer( $customer_id );
	}

	/**
	 * Retrieves a summary of licences.
	 *
	 * @since 1.0.0
	 * @return array The summary of licences, including counts of active, expiring soon, revoked, and total customers.
	 */
	public static function summary(): array {
		self::initialize();

		return array(
			'active'        => LicenceRepository::count_active(),
			'expiring_soon' => LicenceRepository::count_expiring_soon(),
			'revoked'       => LicenceRepository::count_revoked(),
			'customers'     => LicenceRepository::count_customers(),
		);
	}
}
