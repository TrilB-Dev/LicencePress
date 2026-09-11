<?php
/**
 * Licence generation helper for LicencePress.
 *
 * @package LicencePress
 */

namespace LicencePress\Includes\Functions\Helpers;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LicenceHelper {
	/**
	 * Generate a licence from the default settings and any per-licence overrides.
	 *
	 * @param array<string, mixed> $default_settings Default licence settings.
	 * @param array<string, mixed> $licence_settings Per-licence settings that can override each default.
	 * @param array<string, mixed> $context Extra generation context, including database persistence options.
	 * @return array<string, mixed>
	 */
	public static function generate( array $default_settings, array $licence_settings, array $context = array() ): array {
		$settings = self::resolve_settings( $default_settings, $licence_settings );
		$prefix   = self::string_value( $settings, 'licence_prefix', '' );
		$pattern  = self::pattern_template( $settings );
		$table    = self::table_name( $context );
		$attempts = 0;
		$licence  = '';
		$record   = array();

		while ( $attempts < 25 ) {
			$body    = self::build_pattern_body( $pattern, $settings );
			$licence = '' !== $prefix ? trim( $prefix . '-' . $body, '-' ) : $body;

			if ( self::is_unique_licence( $table, $licence, $settings ) ) {
				break;
			}

			$attempts++;
		}

		if ( '' === $licence || $attempts >= 25 ) {
			throw new \RuntimeException( 'Unable to generate a unique licence after multiple attempts.' );
		}

		$encrypted = self::encrypt_value( $licence, $settings );
		$record    = array(
			'licence_type_id'          => isset( $context['licence_type_id'] ) ? (int) $context['licence_type_id'] : 0,
			'licence_type_variant_id'  => isset( $context['licence_type_variant_id'] ) ? (int) $context['licence_type_variant_id'] : 0,
			'user_id'                 => isset( $context['user_id'] ) ? (int) $context['user_id'] : 0,
			'creation_date'           => gmdate( 'Y-m-d H:i:s' ),
			'licence_status'          => isset( $context['licence_status'] ) ? (int) $context['licence_status'] : 2,
			'licence_use'             => isset( $context['licence_use'] ) ? self::encode_licence_use( $context['licence_use'] ) : '[]',
			'licence'                 => $encrypted,
			'licence_prefix'          => $prefix,
			'licence_pattern'         => $pattern,
			'licensor_name'           => self::string_value( $settings, 'licensor_name', '' ),
			'licensor_country'        => self::string_value( $settings, 'licensor_country', '' ),
			'licence_pattern_type'    => self::string_value( $settings, 'licence_pattern_type', 'standard' ),
			'licence_pattern_format'  => self::string_value( $settings, 'licence_pattern_format', 'alphanumeric' ),
			'licence_pattern_separator' => self::string_value( $settings, 'licence_pattern_separator', '-' ),
		);

		if ( ! empty( $context['save_to_database'] ) || ! array_key_exists( 'save_to_database', $context ) ) {
			self::save_record( $table, $record );
		}

		return array(
			'licence'             => $licence,
			'encrypted_licence'   => $encrypted,
			'raw_licence'        => $licence,
			'prefix'             => $prefix,
			'pattern'            => $pattern,
			'key'                => self::base_key(),
			'settings'           => $settings,
			'table'              => $table,
			'record'             => $record,
			'unique'             => true,
			'licence_type_id'    => $record['licence_type_id'],
			'licence_type_variant_id' => $record['licence_type_variant_id'],
			'user_id'            => $record['user_id'],
		);
	}

	/**
	 * Decrypt a stored licence value.
	 *
	 * @param string $ciphertext Encrypted value stored in the database.
	 * @param array<string, mixed> $settings Settings used while encrypting the value.
	 * @return string|null The decrypted licence code.
	 */
	public static function decrypt( string $ciphertext, array $settings = array() ): ?string {
		if ( '' === $ciphertext ) {
			return '';
		}

		$key = self::load_key( self::base_key() );
		if ( null === $key ) {
			return null;
		}

		try {
			$json = Crypto::decrypt( $ciphertext, $key );
			$data = json_decode( $json, true );
			if ( is_array( $data ) && isset( $data['licence'] ) ) {
				return (string) $data['licence'];
			}

			return $json;
		} catch ( \Throwable $exception ) {
			return null;
		}
	}

	/**
	 * Resolve the effective settings by applying each default override toggle.
	 *
	 * @param array<string, mixed> $defaults Default settings.
	 * @param array<string, mixed> $overrides Per-licence settings.
	 * @return array<string, mixed>
	 */
	private static function resolve_settings( array $defaults, array $overrides ): array {
		$resolved = array();
		$keys     = array(
			'licence_prefix',
			'licence_pattern_type',
			'custom_licence_pattern',
			'licence_pattern_format',
			'licence_pattern_letter_case',
			'licence_pattern_separator',
			'exclude_ambiguous_characters',
			'licensor_name',
			'licensor_country',
		);

		foreach ( $keys as $key ) {
			$use_default_key = 'use_default_' . $key;
			if ( isset( $overrides[ $use_default_key ] ) && ! empty( $overrides[ $use_default_key ] ) ) {
				$resolved[ $key ] = isset( $defaults[ $key ] ) ? $defaults[ $key ] : self::default_value_for( $key );
				continue;
			}

			if ( array_key_exists( $key, $overrides ) ) {
				$resolved[ $key ] = $overrides[ $key ];
				continue;
			}

			$resolved[ $key ] = isset( $defaults[ $key ] ) ? $defaults[ $key ] : self::default_value_for( $key );
		}

		$resolved['licence_pattern_type'] = self::sanitize_pattern_type( (string) ( $resolved['licence_pattern_type'] ?? 'standard' ) );
		$resolved['licence_pattern_format'] = self::sanitize_pattern_format( (string) ( $resolved['licence_pattern_format'] ?? 'alphanumeric' ) );
		$resolved['licence_pattern_letter_case'] = self::sanitize_case( (string) ( $resolved['licence_pattern_letter_case'] ?? 'uppercase' ) );
		$resolved['licence_pattern_separator'] = self::sanitize_separator( (string) ( $resolved['licence_pattern_separator'] ?? '-' ) );
		$resolved['exclude_ambiguous_characters'] = self::normalize_excluded_chars( $resolved['exclude_ambiguous_characters'] ?? array() );

		return $resolved;
	}

	/**
	 * Return the default template for a selected pattern type.
	 *
	 * @param array<string, mixed> $settings Resolved settings.
	 * @return string
	 */
	private static function pattern_template( array $settings ): string {
		$pattern_type = self::sanitize_pattern_type( (string) ( $settings['licence_pattern_type'] ?? 'standard' ) );
		$custom       = trim( (string) ( $settings['custom_licence_pattern'] ?? '' ) );

		if ( 'custom' === $pattern_type && '' !== $custom ) {
			return $custom;
		}

		$map = array(
			'32-char' => 'XXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
			'25-char' => 'XXXXX-XXXXX-XXXXX-XXXXX-XXXXX',
			'16-char' => 'XXXX-XXXX-XXXX-XXXX',
			'12-char' => 'XXXX-XXXX-XXXX',
			'8-char'  => 'XXXX-XXXX',
			'standard' => 'XXXX-XXXX-XXXX',
		);

		$template = $map[ $pattern_type ] ?? $map['standard'];
		$template = str_replace( 'X', 'X', $template );
		return $template;
	}

	/**
	 * Build the character body from the selected pattern template.
	 *
	 * @param string                $pattern Pattern template.
	 * @param array<string, mixed> $settings Settings used to build the key.
	 * @return string
	 */
	private static function build_pattern_body( string $pattern, array $settings ): string {
		$separator = self::sanitize_separator( (string) ( $settings['licence_pattern_separator'] ?? '-' ) );
		$case      = self::sanitize_case( (string) ( $settings['licence_pattern_letter_case'] ?? 'uppercase' ) );
		$format    = self::sanitize_pattern_format( (string) ( $settings['licence_pattern_format'] ?? 'alphanumeric' ) );
		$excluded  = self::normalize_excluded_chars( $settings['exclude_ambiguous_characters'] ?? array() );
		$body      = '';
		$length    = strlen( $pattern );

		for ( $i = 0; $i < $length; $i++ ) {
			$char = $pattern[ $i ];
			if ( in_array( $char, array( '-', '_', '|', ':', '.', '<', '>' ), true ) ) {
				$body .= $char;
				continue;
			}

			if ( 'A' === strtoupper( $char ) ) {
				$body .= self::random_character( 'letters', $case, $excluded );
				continue;
			}

			if ( 'N' === strtoupper( $char ) ) {
				$body .= self::random_character( 'numbers', $case, $excluded );
				continue;
			}

			if ( 'X' === strtoupper( $char ) ) {
				$body .= self::random_character( $format, $case, $excluded );
				continue;
			}

			if ( in_array( $char, array( ' ', '\t', '\n' ), true ) ) {
				continue;
			}

			$body .= $char;
		}

		if ( '' !== $separator && 'none' !== strtolower( (string) $separator ) ) {
			$body = preg_replace( '/\s+/', '', $body );
		}

		return trim( $body );
	}

	/**
	 * Generate a single random character.
	 *
	 * @param string               $kind Character pool kind.
	 * @param string               $case Preferred case.
	 * @param array<int, string> $excluded Excluded characters.
	 * @return string
	 */
	private static function random_character( string $kind, string $case, array $excluded ): string {
		$letters = array( 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z' );
		$lower   = array_map( 'strtolower', $letters );
		$numbers = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
		$pool    = array();

		if ( 'numbers' === $kind ) {
			$pool = $numbers;
		} elseif ( 'letters' === $kind ) {
			$pool = 'uppercase' === $case ? $letters : ( 'lowercase' === $case ? $lower : array_merge( $letters, $lower ) );
		} else {
			$pool = array_merge( $letters, $lower, $numbers );
		}

		$pool = array_values( array_filter( $pool, static fn ( $value ) => ! in_array( (string) $value, $excluded, true ) ) );
		if ( empty( $pool ) ) {
			$pool = array( 'X', 'A', '1' );
		}

		$index = function_exists( 'wp_rand' ) ? wp_rand( 0, count( $pool ) - 1 ) : random_int( 0, count( $pool ) - 1 );
		return (string) $pool[ $index ];
	}

	/**
	 * Check whether the generated licence is unique in the database.
	 *
	 * @param string $table_name Table name to query.
	 * @param string $plain_value Raw licence to test.
	 * @param array<string, mixed> $settings Resolved settings.
	 * @return bool
	 */
	private static function is_unique_licence( string $table_name, string $plain_value, array $settings ): bool {
		if ( '' === $table_name ) {
			return true;
		}

		global $wpdb;
		if ( ! isset( $wpdb ) || ! is_object( $wpdb ) || ! method_exists( $wpdb, 'prepare' ) || ! method_exists( $wpdb, 'get_var' ) ) {
			return true;
		}

		$encrypted = self::encrypt_value( $plain_value, $settings );
		$query     = $wpdb->prepare( 'SELECT id FROM ' . $table_name . ' WHERE licence = %s LIMIT 1', $encrypted );
		$result    = $wpdb->get_var( $query );

		return null === $result || '' === (string) $result;
	}

	/**
	 * Save the encrypted licence record to the database.
	 *
	 * @param string $table_name Table name to insert into.
	 * @param array<string, mixed> $record Database record payload.
	 * @return int
	 */
	private static function save_record( string $table_name, array $record ): int {
		global $wpdb;
		if ( ! isset( $wpdb ) || ! is_object( $wpdb ) || ! method_exists( $wpdb, 'insert' ) ) {
			return 0;
		}

		$wpdb->insert(
			$table_name,
			array(
				'licence_type_id'         => (int) ( $record['licence_type_id'] ?? 0 ),
				'licence_type_variant_id' => (int) ( $record['licence_type_variant_id'] ?? 0 ),
				'user_id'                => (int) ( $record['user_id'] ?? 0 ),
				'creation_date'          => (string) ( $record['creation_date'] ?? gmdate( 'Y-m-d H:i:s' ) ),
				'licence_status'         => (int) ( $record['licence_status'] ?? 2 ),
				'licence_use'            => (string) ( $record['licence_use'] ?? '[]' ),
				'licence'                => (string) ( $record['licence'] ?? '' ),
			),
			array( '%d', '%d', '%d', '%s', '%d', '%s', '%s' )
		);

		return (int) $wpdb->insert_id;
	}

	/**
	 * Return the configured table name.
	 *
	 * @param array<string, mixed> $context Context data.
	 * @return string
	 */
	private static function table_name( array $context ): string {
		if ( ! empty( $context['table_name'] ) ) {
			return (string) $context['table_name'];
		}

		return 'licencepress_licence';
	}

	/**
	 * Encrypt a licence payload using the configured runtime key and metadata.
	 *
	 * @param string                $licence Value to encrypt.
	 * @param array<string, mixed> $settings Resolved settings.
	 * @return string
	 */
	private static function encrypt_value( string $licence, array $settings ): string {
		$key = self::load_key( self::base_key() );
		if ( null === $key ) {
			throw new \RuntimeException( 'LicencePress encryption key is not configured.' );
		}

		$payload = array(
			'licence'          => $licence,
			'licensor_name'    => self::string_value( $settings, 'licensor_name', '' ),
			'licensor_country' => self::string_value( $settings, 'licensor_country', '' ),
		);

		try {
			return Crypto::encrypt( wp_json_encode( $payload ), $key );
		} catch ( \Throwable $exception ) {
			throw new \RuntimeException( 'Unable to encrypt the generated licence.', 0, $exception );
		}
	}

	/**
	 * Load the Defuse key from the runtime key constant.
	 *
	 * @param string|null $value Runtime key string.
	 * @return Key|null
	 */
	private static function load_key( ?string $value ): ?Key {
		if ( null === $value || '' === $value || 'placeholder-key-for-intelephense' === $value ) {
			return null;
		}

		try {
			return Key::loadFromAsciiSafeString( $value );
		} catch ( \Throwable $exception ) {
			return null;
		}
	}

	/**
	 * Return the base runtime key constant.
	 *
	 * @return string|null
	 */
	private static function base_key(): ?string {
		if ( ! defined( 'LICENCEPRESS_ENCRYPTION_KEY' ) ) {
			return null;
		}

		return (string) constant( 'LICENCEPRESS_ENCRYPTION_KEY' );
	}

	/**
	 * Get a string value from an array and fall back safely.
	 *
	 * @param array<string, mixed> $values Values to inspect.
	 * @param string               $key Key to read.
	 * @param string               $default Default value.
	 * @return string
	 */
	private static function string_value( array $values, string $key, string $default = '' ): string {
		$value = $values[ $key ] ?? $default;
		return is_scalar( $value ) ? (string) $value : $default;
	}

	/**
	 * Normalize excluded characters into a safe array of strings.
	 *
	 * @param mixed $value Input value.
	 * @return array<int, string>
	 */
	private static function normalize_excluded_chars( $value ): array {
		if ( is_string( $value ) ) {
			$value = preg_split( '//', $value, -1, PREG_SPLIT_NO_EMPTY );
		}

		if ( ! is_array( $value ) ) {
			return array();
		}

		$chars = array();
		foreach ( $value as $item ) {
			$char = (string) $item;
			if ( '' !== $char ) {
				$chars[] = $char;
			}
		}

		return array_values( array_unique( $chars ) );
	}

	/**
	 * Encode arbitrary licence use data for database storage.
	 *
	 * @param mixed $value Value to store.
	 * @return string
	 */
	private static function encode_licence_use( $value ): string {
		if ( is_string( $value ) ) {
			return $value;
		}

		if ( is_array( $value ) ) {
			return wp_json_encode( $value );
		}

		return '[]';
	}

	/**
	 * Normalize allowed pattern type strings.
	 *
	 * @param string $value Pattern type.
	 * @return string
	 */
	private static function sanitize_pattern_type( string $value ): string {
		$value = strtolower( trim( $value ) );
		if ( in_array( $value, array( 'custom', '32-char', '25-char', '16-char', '12-char', '8-char', 'standard' ), true ) ) {
			return $value;
		}

		return 'standard';
	}

	/**
	 * Normalize pattern format strings.
	 *
	 * @param string $value Pattern format.
	 * @return string
	 */
	private static function sanitize_pattern_format( string $value ): string {
		$value = strtolower( trim( $value ) );
		if ( in_array( $value, array( 'alphanumeric', 'letters', 'numbers' ), true ) ) {
			return $value;
		}

		return 'alphanumeric';
	}

	/**
	 * Normalize letter case strings.
	 *
	 * @param string $value Case value.
	 * @return string
	 */
	private static function sanitize_case( string $value ): string {
		$value = strtolower( trim( $value ) );
		if ( in_array( $value, array( 'uppercase', 'lowercase', 'mixedcase' ), true ) ) {
			return $value;
		}

		return 'uppercase';
	}

	/**
	 * Normalize separator strings.
	 *
	 * @param string $value Separator value.
	 * @return string
	 */
	private static function sanitize_separator( string $value ): string {
		$value = (string) $value;
		if ( in_array( $value, array( '-', '_', '|', ':', '.', '<', '>', 'none' ), true ) ) {
			return $value;
		}

		return '-';
	}

	/**
	 * Return a fallback default for a setting key.
	 *
	 * @param string $key Setting key.
	 * @return mixed
	 */
	private static function default_value_for( string $key ) {
		$defaults = array(
			'licence_prefix'                     => '',
			'licence_pattern_type'               => 'standard',
			'custom_licence_pattern'             => 'XXXX-XXXX-XXXX',
			'licence_pattern_format'             => 'alphanumeric',
			'licence_pattern_letter_case'        => 'uppercase',
			'licence_pattern_separator'          => '-',
			'exclude_ambiguous_characters'      => array( '0', 'O', '1', 'i', 'l', 'I' ),
			'licensor_name'                      => '',
			'licensor_country'                  => '',
		);

		return $defaults[ $key ] ?? '';
	}
}