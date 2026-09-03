<?php

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . DIRECTORY_SEPARATOR );
}

if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}

if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $key ) {
		$key = strtolower( (string) $key );
		$key = preg_replace( '/[^a-z0-9_\-]+/', '', $key );
		return (string) $key;
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $value ) {
		if ( is_array( $value ) ) {
			return '';
		}
		return trim( strip_tags( (string) $value ) );
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
	function esc_url_raw( $url ) {
		return is_string( $url ) ? trim( $url ) : '';
	}
}

if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $value ) {
		return $value;
	}
}

if ( ! function_exists( 'current_time' ) ) {
	function current_time( $type = 'timestamp', $gmt = 0 ) {
		if ( 'mysql' === $type ) {
			return gmdate( 'Y-m-d H:i:s' );
		}
		return time();
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data ) {
		return json_encode( $data );
	}
}

if ( ! function_exists( 'wp_parse_url' ) ) {
	function wp_parse_url( $url, $component = -1 ) {
		return parse_url( $url, $component );
	}
}

if ( ! function_exists( 'maybe_serialize' ) ) {
	function maybe_serialize( $data ) {
		return serialize( $data );
	}
}

if ( ! function_exists( 'maybe_unserialize' ) ) {
	function maybe_unserialize( $data ) {
		if ( is_scalar( $data ) ) {
			$unserialized = @unserialize( (string) $data );
			return false === $unserialized ? $data : $unserialized;
		}
		return $data;
	}
}

if ( ! function_exists( 'dbDelta' ) ) {
	function dbDelta( $sql ) {
		return true;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	function update_option( $option, $value ) {
		return true;
	}
}

if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 1 );
}

if ( ! class_exists( 'wpdb' ) ) {
	class wpdb {
		public string $prefix = 'wp_';
		public int $insert_id = 0;
		public array $tables = [];

		public function prepare( $query, ...$args ) {
			$formatted = $query;
			foreach ( $args as $arg ) {
				$replacement = is_numeric( $arg ) ? (string) $arg : "'" . addslashes( (string) $arg ) . "'";
				$formatted = preg_replace( '/%s|%d/', $replacement, $formatted, 1 );
			}
			return $formatted;
		}

		public function get_row( $query, $output = ARRAY_A ) {
			$rows = $this->get_results( $query, $output );
			return is_array( $rows ) && ! empty( $rows ) ? $rows[0] : null;
		}

		public function get_var( $query ) {
			$matches = [];
			if ( preg_match( '/FROM\s+`?([A-Za-z0-9_]+)`?/i', $query, $matches ) ) {
				$table = $matches[1];
				$rows = $this->tables[ $table ] ?? [];
				if ( empty( $rows ) ) {
					return null;
				}
				foreach ( $rows as $row ) {
					if ( isset( $row['setting_group'] ) ) {
						return $row['setting_value'];
					}
				}
			}
			return null;
		}

		public function get_results( $query, $output = ARRAY_A ) {
			$matches = [];
			if ( ! preg_match( '/FROM\s+`?([A-Za-z0-9_]+)`?/i', $query, $matches ) ) {
				return [];
			}

			$table = $matches[1];
			$rows  = $this->tables[ $table ] ?? [];
			if ( empty( $rows ) ) {
				return [];
			}

			if ( stripos( $query, 'WHERE' ) !== false && stripos( $query, 'token_hash' ) !== false ) {
				preg_match( "/token_hash\s*=\s*'([^']+)'/i", $query, $matches );
				$expected = $matches[1] ?? '';
				foreach ( $rows as $row ) {
					if ( ( $row['token_hash'] ?? '' ) === $expected ) {
						return [ $row ];
					}
				}
				return [];
			}

			if ( stripos( $query, 'WHERE' ) !== false && stripos( $query, 'customer_id' ) !== false ) {
				preg_match( "/customer_id\s*=\s*'([^']+)'/i", $query, $matches );
				$expected = $matches[1] ?? '';
				$filtered = [];
				foreach ( $rows as $row ) {
					if ( ( $row['customer_id'] ?? '' ) === $expected ) {
						$filtered[] = $row;
					}
				}
				return $filtered;
			}

			return $rows;
		}

		public function insert( $table, $data, $format = [] ) {
			$rows = $this->tables[ $table ] ?? [];
			$id = count( $rows ) + 1;
			$record = [ 'id' => $id ];
			foreach ( $data as $key => $value ) {
				$record[ $key ] = $value;
			}
			$rows[] = $record;
			$this->tables[ $table ] = $rows;
			$this->insert_id = $id;
			return $id;
		}

		public function update( $table, $data, $where, $format = [], $where_format = [] ) {
			$rows = $this->tables[ $table ] ?? [];
			foreach ( $rows as $index => $row ) {
				if ( (int) ( $where['id'] ?? 0 ) === (int) ( $row['id'] ?? 0 ) ) {
					foreach ( $data as $key => $value ) {
						$rows[ $index ][ $key ] = $value;
					}
					$this->tables[ $table ] = $rows;
					return 1;
				}
			}
			return 0;
		}

		public function replace( $table, $data, $format = [] ) {
			$rows = $this->tables[ $table ] ?? [];
			$found = false;
			foreach ( $rows as $index => $row ) {
				if ( ( $row['setting_group'] ?? '' ) === ( $data['setting_group'] ?? '' ) ) {
					$rows[ $index ] = array_merge( $row, $data );
					$found = true;
					break;
				}
			}
			if ( ! $found ) {
				$rows[] = $data;
			}
			$this->tables[ $table ] = $rows;
			return 1;
		}

		public function delete( $table, $where, $where_format = [] ) {
			$rows = $this->tables[ $table ] ?? [];
			$filtered = [];
			foreach ( $rows as $row ) {
				if ( ( $where['setting_group'] ?? '' ) !== ( $row['setting_group'] ?? '' ) ) {
					$filtered[] = $row;
				}
			}
			$this->tables[ $table ] = $filtered;
			return 1;
		}
	}

	global $wpdb;
	$wpdb = new wpdb();
}

require_once dirname( __DIR__ ) . '/vendor/autoload.php';