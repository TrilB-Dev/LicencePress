<?php
/**
 * Encryption service for LicencePress licence data.
 *
 * @package LicencePress
 */

namespace LicencePress\Includes\Licence;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class EncryptionService {
	/**
	 * Encrypt a value using the runtime key.
	 *
	 * @param string $value The value to encrypt.
	 * @return string|null The encrypted value or null on failure.
	 */
	public static function encrypt( string $value ): ?string {
		if ( '' === $value ) {
			return '';
		}

		$key = self::load_key();
		if ( null === $key ) {
			return null;
		}

		try {
			return Crypto::encrypt( $value, $key );
		} catch ( \Throwable $exception ) {
			return null;
		}
	}

	/**
	 * Decrypt a value using the runtime key.
	 *
	 * @param string|null $value The value to decrypt.
	 * @return string|null The decrypted value or null on failure.
	 */
	public static function decrypt( ?string $value ): ?string {
		if ( null === $value || '' === $value ) {
			return $value ?? '';
		}

		$key = self::load_key();
		if ( null === $key ) {
			return null;
		}

		try {
			return Crypto::decrypt( $value, $key );
		} catch ( \Throwable $exception ) {
			return null;
		}
	}

	/**
	 * Load the runtime encryption key.
	 *
	 * @return Key|null The loaded key or null on failure.
	 */
	public static function load_key(): ?Key {
		$runtime_key = KeyManager::runtime_key();
		if ( null === $runtime_key || '' === $runtime_key ) {
			return null;
		}

		try {
			return Key::loadFromAsciiSafeString( $runtime_key );
		} catch ( \Throwable $exception ) {
			return null;
		}
	}
}
