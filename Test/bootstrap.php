<?php

// phpcs:disable WordPress.Files.FileName, WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize, WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize, WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.json_encode_json_encode, WordPress.WP.AlternativeFunctions.parse_url_parse_url, WordPress.WP.AlternativeFunctions.strip_tags_strip_tags, WordPress.WP.GlobalVariablesOverride.Prohibited, Generic.CodeAnalysis.UnusedFunctionParameter, PEAR.NamingConventions.ValidClassName, Squiz.Commenting.VariableComment, Squiz.Commenting.ClassComment, Squiz.Commenting.FileComment, Squiz.Classes.ClassNamePrefix
// phpcs:disable Generic.Files.OneObjectStructurePerFile, Universal.Files.SeparateFunctionsFromOO.Mixed

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . DIRECTORY_SEPARATOR );
}

if ( ! defined( 'LICENCEPRESS_URL' ) ) {
	define( 'LICENCEPRESS_URL', 'https://example.com/wp-content/plugins/licencepress/' );
}

if ( ! defined( 'LICENCEPRESS_ASSETS_URL' ) ) {
	define( 'LICENCEPRESS_ASSETS_URL', LICENCEPRESS_URL . 'src/Assets' );
}

if ( ! defined( 'LICENCEPRESS_VERSION' ) ) {
	define( 'LICENCEPRESS_VERSION', '1.0.0' );
}

if ( ! defined( 'LICENCEPRESS_FILE' ) ) {
	define( 'LICENCEPRESS_FILE', dirname( __DIR__ ) . '/licencepress.php' );
}

if ( ! defined( 'LICENCEPRESS_PLUGINS_URL' ) ) {
	define( 'LICENCEPRESS_PLUGINS_URL', LICENCEPRESS_URL . 'src/Includes/Plugins' );
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

if ( ! function_exists( 'sanitize_title' ) ) {
	function sanitize_title( $title ) {
		$title = strtolower( (string) $title );
		$title = preg_replace( '/[^a-z0-9_\-]+/', '-', $title );
		$title = preg_replace( '/-+/', '-', $title );
		return trim( $title, '-' );
	}
}

if ( ! function_exists( 'sanitize_html_class' ) ) {
	function sanitize_html_class( $class ) {
		if ( is_array( $class ) ) {
			$class = implode( ' ', $class );
		}
		$class = strtolower( (string) $class );
		$class = preg_replace( '/[^a-z0-9_\-\s]+/', '', $class );
		$class = preg_replace( '/\s+/', '-', trim( $class ) );
		return trim( $class, '-' );
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

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = null ) {
		return (string) $text;
	}
}

if ( ! function_exists( '_e' ) ) {
	function _e( $text, $domain = null ) {
		echo __( $text, $domain );
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( $text, $domain = null ) {
		return __( $text, $domain );
	}
}

if ( ! function_exists( 'esc_html_e' ) ) {
	function esc_html_e( $text, $domain = null ) {
		echo esc_html__( $text, $domain );
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return is_scalar( $text ) ? htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ) : '';
	}
}

if ( ! function_exists( 'esc_attr__' ) ) {
	function esc_attr__( $text, $domain = null ) {
		return __( $text, $domain );
	}
}

if ( ! function_exists( 'esc_attr_e' ) ) {
	function esc_attr_e( $text, $domain = null ) {
		echo esc_attr__( $text, $domain );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return is_scalar( $text ) ? htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ) : '';
	}
}

if ( ! function_exists( 'esc_textarea' ) ) {
	function esc_textarea( $text ) {
		return is_scalar( $text ) ? htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ) : '';
	}
}

if ( ! function_exists( 'checked' ) ) {
	function checked( $checked, $current ) {
		if ( $checked == $current ) {
			echo ' checked="checked"';
		}
	}
}

if ( ! function_exists( 'selected' ) ) {
	function selected( $selected, $current ) {
		if ( $selected == $current ) {
			echo ' selected="selected"';
		}
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $url ) {
		return is_string( $url ) ? esc_url_raw( $url ) : '';
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
	function esc_url_raw( $url ) {
		return is_string( $url ) ? trim( $url ) : '';
	}
}

if ( ! function_exists( 'admin_url' ) ) {
	function admin_url( $path = '' ) {
		$path = ltrim( (string) $path, '/' );
		return 'https://example.com/wp-admin/' . ( '' !== $path ? $path : '' );
	}
}

if ( ! function_exists( 'current_user_can' ) ) {
	function current_user_can( $capability ) {
		return true;
	}
}

if ( ! function_exists( 'get_role' ) ) {
	function get_role( $role ) {
		return new class() {
			public function has_cap( $capability ) {
				return true;
			}

			public function add_cap( $capability ) {
				return true;
			}
		};
	}
}

if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $value ) {
		return $value;
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $hook, $value ) {
		unset( $hook );
		return $value;
	}
}

if ( ! function_exists( 'wp_create_nonce' ) ) {
	function wp_create_nonce( $action = '' ) {
		return 'nonce-' . sanitize_key( (string) $action );
	}
}

if ( ! function_exists( 'wp_script_is' ) ) {
	function wp_script_is( $handle, $list = 'enqueued' ) {
		return false;
	}
}

if ( ! function_exists( 'wp_style_is' ) ) {
	function wp_style_is( $handle, $list = 'enqueued' ) {
		return false;
	}
}

if ( ! function_exists( 'wp_enqueue_style' ) ) {
	function wp_enqueue_style( $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) {
		return true;
	}
}

if ( ! function_exists( 'wp_enqueue_script' ) ) {
	function wp_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
		return true;
	}
}

if ( ! function_exists( 'plugin_basename' ) ) {
	function plugin_basename( $file ) {
		return basename( (string) $file );
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

// phpcs:ignore Generic.Files.OneObjectStructurePerFile
if ( ! class_exists( 'wpdb' ) ) {
	class wpdb {
		public string $prefix = 'wp_';
		public int $insert_id = 0;
		public array $tables  = array();

		public function prepare( $query, ...$args ) {
			$formatted = $query;
			foreach ( $args as $arg ) {
				$replacement = is_numeric( $arg ) ? (string) $arg : "'" . addslashes( (string) $arg ) . "'";
				$formatted   = preg_replace( '/%s|%d/', $replacement, $formatted, 1 );
			}
			return $formatted;
		}

		public function get_row( $query, $output = ARRAY_A ) {
			$rows = $this->get_results( $query, $output );
			return is_array( $rows ) && ! empty( $rows ) ? $rows[0] : null;
		}

		public function get_var( $query ) {
			$matches = array();
			if ( preg_match( '/FROM\s+`?([A-Za-z0-9_]+)`?/i', $query, $matches ) ) {
				$table = $matches[1];
				$rows  = $this->tables[ $table ] ?? array();
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
			$matches = array();
			if ( ! preg_match( '/FROM\s+`?([A-Za-z0-9_]+)`?/i', $query, $matches ) ) {
				return array();
			}

			$table = $matches[1];
			$rows  = $this->tables[ $table ] ?? array();
			if ( empty( $rows ) ) {
				return array();
			}

			if ( stripos( $query, 'WHERE' ) !== false && stripos( $query, 'token_hash' ) !== false ) {
				preg_match( "/token_hash\s*=\s*'([^']+)'/i", $query, $matches );
				$expected = $matches[1] ?? '';
				foreach ( $rows as $row ) {
					if ( ( $row['token_hash'] ?? '' ) === $expected ) {
						return array( $row );
					}
				}
				return array();
			}

			if ( stripos( $query, 'WHERE' ) !== false && stripos( $query, 'customer_id' ) !== false ) {
				preg_match( "/customer_id\s*=\s*'([^']+)'/i", $query, $matches );
				$expected = $matches[1] ?? '';
				$filtered = array();
				foreach ( $rows as $row ) {
					if ( ( $row['customer_id'] ?? '' ) === $expected ) {
						$filtered[] = $row;
					}
				}
				return $filtered;
			}

			return $rows;
		}

		public function insert( $table, $data, $format = array() ) {
			$rows   = $this->tables[ $table ] ?? array();
			$id     = count( $rows ) + 1;
			$record = array( 'id' => $id );
			foreach ( $data as $key => $value ) {
				$record[ $key ] = $value;
			}
			$rows[]                 = $record;
			$this->tables[ $table ] = $rows;
			$this->insert_id        = $id;
			return $id;
		}

		public function update( $table, $data, $where, $format = array(), $where_format = array() ) {
			$rows = $this->tables[ $table ] ?? array();
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

		public function replace( $table, $data, $format = array() ) {
			$rows  = $this->tables[ $table ] ?? array();
			$found = false;
			foreach ( $rows as $index => $row ) {
				if ( ( $row['setting_group'] ?? '' ) === ( $data['setting_group'] ?? '' ) ) {
					$rows[ $index ] = array_merge( $row, $data );
					$found          = true;
					break;
				}
			}
			if ( ! $found ) {
				$rows[] = $data;
			}
			$this->tables[ $table ] = $rows;
			return 1;
		}

		public function delete( $table, $where, $where_format = array() ) {
			$rows     = $this->tables[ $table ] ?? array();
			$filtered = array();
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
