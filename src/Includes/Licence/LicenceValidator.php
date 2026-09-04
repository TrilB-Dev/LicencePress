<?php
/**
 * Validates a generated licence record.
 *
 * @package LicencePress
 */

namespace LicencePress\Includes\Licence;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LicenceValidator {
	/**
	 * Validate that a submitted licence matches the expected product and site.
	 *
	 * @param string                    $token Submitted licence token.
	 * @param string                    $product_id Required product identifier.
	 * @param string|null               $site_url Optional matching site URL.
	 * @param array<string, mixed>|null $record Existing stored record.
	 * @return bool
	 */
	public static function validate( string $token, string $product_id, ?string $site_url = null, ?array $record = null ): bool {
		$runtime_key = KeyManager::runtime_key();
		if ( null === $runtime_key || '' === $runtime_key ) {
			return false;
		}

		$normalized_token = strtoupper( trim( $token ) );
		if ( '' === $normalized_token ) {
			return false;
		}

		if ( null === $record ) {
			return false;
		}

		if ( ! isset( $record['payload_encrypted'] ) || ! is_string( $record['payload_encrypted'] ) ) {
			return false;
		}

		if ( ! isset( $record['payload'] ) || ! is_array( $record['payload'] ) ) {
			return false;
		}

		$payload             = $record['payload'];
		$expected_token_hash = hash_hmac( 'sha256', $normalized_token, $runtime_key );
		if ( ( $record['token_hash'] ?? null ) !== $expected_token_hash ) {
			return false;
		}

		if ( ( $payload['product_id'] ?? null ) !== $product_id ) {
			return false;
		}

		$expires_at = (int) ( $payload['expires_at'] ?? 0 );
		if ( $expires_at > 0 && time() > $expires_at ) {
			return false;
		}

		if ( null !== $site_url && ! empty( $payload['site_hash'] ) ) {
			$expected_site_hash = hash( 'sha256', self::normalize_domain( $site_url ) );
			if ( (string) $payload['site_hash'] !== $expected_site_hash ) {
				return false;
			}
		}

		$decrypted = EncryptionService::decrypt( $record['payload_encrypted'] );
		if ( null === $decrypted ) {
			return false;
		}

		$decoded = json_decode( $decrypted, true );
		if ( ! is_array( $decoded ) ) {
			return false;
		}

		if ( ( $decoded['product_id'] ?? null ) !== $product_id ) {
			return false;
		}

		return true;
	}

	private static function normalize_domain( string $value ): string {
		$host = wp_parse_url( $value, PHP_URL_HOST );
		if ( is_string( $host ) && '' !== $host ) {
			$value = $host;
		}

		$value = trim( strtolower( $value ) );

		if ( '' === $value ) {
			return '';
		}

		return preg_replace( '/^www\./', '', $value ) ?? $value;
	}
}
