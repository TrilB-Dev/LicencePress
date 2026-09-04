<?php
/**
 * Handles sending emails through WordPress and resolving template overrides.
 *
 * @package LicencePress
 */

namespace LicencePress\Includes\Email;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Email {
	/**
	 * Send an email via the standard WordPress mail pipeline so MSPress or other
	 * mailer integrations can capture and forward the message.
	 *
	 * @param string|array<int, string> $to Recipient address or list.
	 * @param string                    $subject Email subject.
	 * @param string                    $message Email body.
	 * @param array<string, string>     $headers Optional headers.
	 * @param array<int, string>        $attachments Optional attachments.
	 * @return bool
	 */
	public static function send( $to, string $subject, string $message, array $headers = array(), array $attachments = array() ): bool {
		if ( empty( $to ) ) {
			return false;
		}

		$recipients = is_array( $to ) ? array_filter( array_map( 'trim', $to ) ) : array( trim( (string) $to ) );
		if ( empty( $recipients ) ) {
			return false;
		}

		$prepared_headers = self::prepare_headers( $headers );
		if ( ! self::has_html_content( $message ) ) {
			$prepared_headers[] = 'Content-Type: text/plain; charset=UTF-8';
		} else {
			$prepared_headers[] = 'Content-Type: text/html; charset=UTF-8';
		}

		return wp_mail( implode( ',', $recipients ), $subject, $message, $prepared_headers, $attachments );
	}

	/**
	 * Instantiate an email template for a given template path or type.
	 *
	 * Theme overrides are supported by placing a matching file in:
	 * wp-content/themes/<theme>/LicencePressEmails/Templates/Admin/<TemplateName>.php
	 *
	 * @param string               $type Template identifier, e.g. `Admin/LicenceTypeCreated`.
	 * @param array<string, mixed> $context Template context.
	 * @return object|null
	 */
	public static function template( string $type, array $context = array() ) {
		$class_name = self::resolve_template_class( $type );
		if ( '' !== $class_name && class_exists( $class_name ) ) {
			return new $class_name( $context );
		}

		$override_class = self::resolve_theme_override_class( $type );
		$override_path  = self::theme_override_path( $type );

		if ( '' !== $override_path && file_exists( $override_path ) ) {
			require_once $override_path;
		}

		if ( '' !== $override_class && class_exists( $override_class ) ) {
			return new $override_class( $context );
		}

		return null;
	}

	/**
	 * Render and send a template through the WordPress mail pipeline.
	 *
	 * @param string|array<int, string> $to Recipient(s).
	 * @param string                    $type Template identifier.
	 * @param array<string, mixed>      $context Template context.
	 * @param array<string, string>     $headers Extra headers.
	 * @param array<int, string>        $attachments Attachments.
	 * @return bool
	 */
	public static function send_template( $to, string $type, array $context = array(), array $headers = array(), array $attachments = array() ): bool {
		$template = self::template( $type, $context );
		if ( null === $template || ! method_exists( $template, 'render' ) ) {
			return false;
		}

		$message = (string) $template->render( $context );
		if ( method_exists( $template, 'subject' ) ) {
			$subject = (string) $template->subject();
		} else {
			$subject = 'LPEmail-Admin';
		}

		return self::send( $to, $subject, $message, $headers, $attachments );
	}

	/**
	 * Resolve a template class path using the plugin template namespace.
	 *
	 * @param string $type Template path or type identifier.
	 * @return string
	 */
	private static function resolve_template_class( string $type ): string {
		$type = trim( $type, '\/' );
		$split = preg_split( '/[\\\/]+/', $type );
		if ( false === $split ) {
			$split = array();
		}
		$segments = array_values( array_filter( $split, 'strlen' ) );

		if ( empty( $segments ) ) {
			return '';
		}

		$class_name = ucfirst( (string) array_pop( $segments ) );
		$namespace  = '\\LicencePress\\Includes\\Email\\Templates';

		if ( ! empty( $segments ) ) {
			$namespace .= '\\' . implode( '\\', array_map( 'ucfirst', $segments ) );
		}

		return $namespace . '\\' . $class_name;
	}

	/**
	 * Resolve a theme override class name for copied templates.
	 *
	 * @param string $type Template path or identifier.
	 * @return string
	 */
	private static function resolve_theme_override_class( string $type ): string {
		$type = trim( $type, '\/' );
		$split = preg_split( '/[\\\/]+/', $type );
		if ( false === $split ) {
			$split = array();
		}
		$segments = array_values( array_filter( $split, 'strlen' ) );

		if ( empty( $segments ) ) {
			return '';
		}

		$class_name = ucfirst( (string) array_pop( $segments ) );
		$namespace  = '\\LicencePressEmails\\Templates';

		if ( ! empty( $segments ) ) {
			$namespace .= '\\' . implode( '\\', array_map( 'ucfirst', $segments ) );
		}

		return $namespace . '\\' . $class_name;
	}

	/**
	 * Load a copied template from the active theme override path.
	 *
	 * @param string $type Template identifier.
	 * @return string
	 */
	public static function theme_override_path( string $type ): string {
		$type     = trim( $type, '\/' );
		$split = preg_split( '/[\\\/]+/', $type );
		if ( false === $split ) {
			$split = array();
		}
		$segments = array_values( array_filter( $split, 'strlen' ) );

		if ( empty( $segments ) ) {
			return '';
		}

		$file_name = array_pop( $segments );
		$path      = implode( '/', $segments );

		$theme_roots = array();
		if ( function_exists( 'get_stylesheet_directory' ) ) {
			$theme_roots[] = get_stylesheet_directory();
		}
		if ( function_exists( 'get_template_directory' ) ) {
			$theme_roots[] = get_template_directory();
		}

		foreach ( array_unique( $theme_roots ) as $theme_root ) {
			$candidate = trailingslashit( $theme_root ) . 'LicencePressEmails/Templates';
			if ( '' !== $path ) {
				$candidate .= '/' . $path;
			}
			$candidate .= '/' . $file_name . '.php';

			if ( file_exists( $candidate ) ) {
				return $candidate;
			}
		}

		return '';
	}

	/**
	 * Normalize headers before sending.
	 *
	 * @param array<string, string> $headers Raw headers.
	 * @return array<int, string>
	 */
	private static function prepare_headers( array $headers ): array {
		$prepared = array();

		foreach ( $headers as $key => $value ) {
			if ( is_string( $key ) && is_string( $value ) ) {
				$prepared[] = trim( $key ) . ': ' . trim( $value );
				continue;
			}

			if ( is_string( $value ) ) {
				$prepared[] = trim( $value );
			}
		}

		return $prepared;
	}

	/**
	 * Check whether the email payload looks like HTML output.
	 *
	 * @param string $message Email message body.
	 * @return bool
	 */
	private static function has_html_content( string $message ): bool {
		return false !== stripos( $message, '<html' )
			|| false !== stripos( $message, '<body' )
			|| false !== stripos( $message, '<div' )
			|| false !== strpos( $message, '<' );
	}
}
