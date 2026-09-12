<?php
/**
 * Settings-related admin functions for LicencePress.
 *
 * @package LicencePress
 * @subpackage Includes\Functions\Admin
 * @since 1.0.0
 */
namespace LicencePress\Includes\Functions\Admin;

use LicencePress\Includes\Functions\Helpers\PermalinkHelper;
use LicencePress\Includes\Settings\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class FunctionsSettings {
	/**
	 * Plugin functions used to collect provider-backed settings pages.
	 *
	 * @var FunctionsPlugins
	 */
	private FunctionsPlugins $plugin_functions;
	/**
	 * Constructor for the FunctionsSettings class.
	 *
	 * @param FunctionsPlugins $plugin_functions The plugin functions instance.
	 */
	public function __construct( FunctionsPlugins $plugin_functions ) {
		$this->plugin_functions = $plugin_functions;
	}

	/**
	 * Register LicencePress and provider-backed plugin settings.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting( 'licencepress_settings', 'licencepress_general', array( 'sanitize_callback' => array( $this, 'sanitize_general' ) ) );
		register_setting( 'licencepress_settings', 'licencepress_billing', array( 'sanitize_callback' => array( $this, 'sanitize_billing' ) ) );
		register_setting( 'licencepress_settings', 'licencepress_billing_invoice', array( 'sanitize_callback' => array( $this, 'sanitize_billing_invoice' ) ) );
		register_setting( 'licencepress_settings', 'licencepress_access', array( 'sanitize_callback' => array( $this, 'sanitize_access' ) ) );
		register_setting( 'licencepress_settings', 'licencepress_tools', array( 'sanitize_callback' => array( $this, 'sanitize_tools' ) ) );

		if ( 'POST' === strtoupper( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			$this->handle_direct_post();
		}

		foreach ( $this->plugin_functions->plugin_settings_pages() as $page ) {
			register_setting(
				'licencepress_settings',
				'licencepress_' . $page['slug'],
				array( 'sanitize_callback' => $page['provider']->sanitize_settings( ... ) )
			);
		}
	}
	
	/**
	 * Process direct form submissions for billing settings.
	 *
	 * @return void
	 */
	private function handle_direct_post(): void {
		$action = wp_unslash( $_POST['action'] ?? '' );
		if ( 'licencepress_save_billing_settings' === $action ) {
			if ( ! current_user_can( 'licencepress_settings_general_edit' ) ) {
				wp_die( esc_html__( 'You are not allowed to save LicencePress billing settings.', 'licencepress' ), 403 );
			}
			check_admin_referer( 'licencepress_billing_general', 'licencepress_billing_general_nonce' );
			$input = isset( $_POST['licencepress_billing'] ) && is_array( $_POST['licencepress_billing'] ) ? wp_unslash( $_POST['licencepress_billing'] ) : array();
			$this->sanitize_billing( $input );
			wp_safe_redirect( admin_url( 'admin.php?page=licencepress-settings&tab=billing' ) );
			exit;
		}

		if ( 'licencepress_save_billing_invoice_settings' === $action ) {
			if ( ! current_user_can( 'licencepress_settings_general_edit' ) ) {
				wp_die( esc_html__( 'You are not allowed to save LicencePress billing settings.', 'licencepress' ), 403 );
			}
			check_admin_referer( 'licencepress_billing_invoice', 'licencepress_billing_invoice_nonce' );
			$input = isset( $_POST['licencepress_billing'] ) && is_array( $_POST['licencepress_billing'] ) ? wp_unslash( $_POST['licencepress_billing'] ) : array();
			$this->sanitize_billing_invoice( $input );
			wp_safe_redirect( admin_url( 'admin.php?page=licencepress-settings&tab=billing&billing_tab=invoice' ) );
			exit;
		}
	}
	/**
	 * Sanitize the billing settings input.
	 *
	 * @param array<string, mixed> $input The input data to sanitize.
	 * @return array<string, mixed> The sanitized billing settings.
	 */
	public function sanitize_billing( $input ): array {
		if ( ! current_user_can( 'licencepress_settings_general_edit' ) ) {
			return (array) Settings::get_group( 'billing', array() );
		}

		$input = is_array( $input ) ? $input : array();
		$billing = array(
			'billing_name'      => sanitize_text_field( $input['billing_name'] ?? '' ),
			'billing_address_1' => sanitize_text_field( $input['billing_address_1'] ?? '' ),
			'billing_address_2' => sanitize_text_field( $input['billing_address_2'] ?? '' ),
			'town'              => sanitize_text_field( $input['town'] ?? '' ),
			'county_state'      => sanitize_text_field( $input['county_state'] ?? '' ),
			'country'           => sanitize_text_field( $input['country'] ?? '' ),
			'vat_number'        => sanitize_text_field( $input['vat_number'] ?? '' ),
			'email_address'     => sanitize_email( $input['email_address'] ?? '' ),
			'phone_number'      => sanitize_text_field( $input['phone_number'] ?? '' ),
			'invoice_prefix'    => sanitize_text_field( $input['invoice_prefix'] ?? 'INV-' ),
			'invoice_logo'      => sanitize_text_field( $input['invoice_logo'] ?? '' ),
		);

		foreach ( $billing as $key => $value ) {
			Settings::set( $key, $value );
		}

		return $billing;
	}

	/**
	 * Sanitize the invoice settings input.
	 *
	 * @param array<string, mixed> $input The input data to sanitize.
	 * @return array<string, mixed> The sanitized invoice settings.
	 */
	public function sanitize_billing_invoice( $input ): array {
		if ( ! current_user_can( 'licencepress_settings_general_edit' ) ) {
			return (array) Settings::get_group( 'billing', array() );
		}

		$input = is_array( $input ) ? $input : array();
		$invoice = array(
			'invoice_style' => wp_kses_post( $input['invoice_style'] ?? '' ),
		);

		foreach ( $invoice as $key => $value ) {
			Settings::set( $key, $value );
		}

		return $invoice;
	}
	/**
	 * Sanitize the general settings input.
	 *
	 * @param array<string, mixed> $input The input data to sanitize.
	 * @return array<string, mixed> The sanitized general settings.
	 */
	public function sanitize_general( $input ): array {
		if ( ! current_user_can( 'licencepress_settings_general_edit' ) ) {
			return (array) Settings::get_group( Settings::GENERAL, array() );
		}

		$input = is_array( $input ) ? $input : array();

		$allowed_entity_types = array( 'company', 'organization', 'group', 'individual' );
		$allowed_renewal_modes = array( 'default', 'custom' );
		$allowed_pattern_types = array( 'standard', 'custom' );
		$allowed_patterns = array( 'alphanumeric', 'letters', 'numbers' );
		$allowed_cases = array( 'uppercase', 'lowercase', 'mixedcase' );
		$allowed_separators = array( '-', ':', '.', 'none' );
		$allowed_ambiguous_chars = array( '0', 'O', '1', 'i', 'l', 'I' );

		$ambiguous_characters = is_array( $input['default_exclude_ambiguous_characters'] ?? null ) ? (array) $input['default_exclude_ambiguous_characters'] : ( is_array( $input['exclude_ambiguous_characters'] ?? null ) ? (array) $input['exclude_ambiguous_characters'] : array() );
		$ambiguous_characters = array_values( array_unique( array_intersect( $allowed_ambiguous_chars, array_map( 'strval', $ambiguous_characters ) ) ) );

		$general = array(
			'entity_type'                        => in_array( $input['entity_type'] ?? 'individual', $allowed_entity_types, true ) ? sanitize_key( $input['entity_type'] ?? 'individual' ) : 'individual',
			'licence_name'                       => sanitize_text_field( $input['licence_name'] ?? '' ),
			'country'                            => sanitize_text_field( $input['country'] ?? '' ),
			'currency'                           => sanitize_text_field( $input['currency'] ?? '' ),
			'licence_prefix'                     => preg_match( '/^[A-Za-z0-9_-]{1,7}$/', (string) ( $input['licence_prefix'] ?? '' ) ) ? sanitize_text_field( $input['licence_prefix'] ?? '' ) : '',
			'licence_usage'                      => array_values( array_unique( array_filter( array_map( 'sanitize_key', is_array( $input['licence_usage'] ?? array() ) ? $input['licence_usage'] : array( $input['licence_usage'] ?? '' ) ) ) ) ),
			'renewal_policy_mode'                => in_array( $input['renewal_policy_mode'] ?? 'default', $allowed_renewal_modes, true ) ? sanitize_key( $input['renewal_policy_mode'] ?? 'default' ) : 'default',
			'renewal_policy_page'                => absint( $input['renewal_policy_page'] ?? 0 ),
			'licence_pattern_type'               => in_array( $input['licence_pattern_type'] ?? 'standard', $allowed_pattern_types, true ) ? sanitize_key( $input['licence_pattern_type'] ?? 'standard' ) : 'standard',
			'licence_pattern_format'             => in_array( (string) ( $input['licence_pattern_format'] ?? 'alphanumeric' ), $allowed_patterns, true ) ? sanitize_key( (string) ( $input['licence_pattern_format'] ?? 'alphanumeric' ) ) : 'alphanumeric',
			'exclude_ambiguous_characters'      => $ambiguous_characters,
			'default_exclude_ambiguous_characters' => $ambiguous_characters,
			'pattern_letter_case'                => in_array( (string) ( $input['pattern_letter_case'] ?? 'uppercase' ), $allowed_cases, true ) ? sanitize_key( (string) ( $input['pattern_letter_case'] ?? 'uppercase' ) ) : 'uppercase',
			'pattern_separator'                  => in_array( (string) ( $input['pattern_separator'] ?? '-' ), $allowed_separators, true ) ? (string) $input['pattern_separator'] : '-',
			'custom_pattern'                     => sanitize_text_field( $input['custom_pattern'] ?? '' ),
		);

		if ( 'custom' === $general['licence_pattern_type'] && ! preg_match( '/[XA]/i', $general['custom_pattern'] ) ) {
			$general['pattern_letter_case'] = '';
		}

		foreach ( $general as $key => $value ) {
			$input[ $key ] = $value;
			Settings::set( $key, $value );
		}

		return $input;
	}
	/**
	 * Sanitize the access settings input.
	 *
	 * @param array<string, mixed> $input The input data to sanitize.
	 * @return array<string, mixed> The sanitized access settings.
	 */
	public function sanitize_access( $input ): array {
		if ( ! current_user_can( 'licencepress_settings_access_edit' ) ) {
			return (array) Settings::get_group( Settings::ACCESS, array() );
		}
		$input   = is_array( $input ) ? $input : array();
		$allowed = array( 'manage_options', 'edit_posts', 'publish_posts' );
		foreach ( array( 'create_licence_types', 'write_licence_type_variants', 'view_analytics', 'manage_plugins' ) as $key ) {
			$values        = is_array( $input[ $key ] ?? null ) ? $input[ $key ] : array( $input[ $key ] ?? 'manage_options' );
			$values        = array_values( array_unique( array_intersect( $allowed, array_map( 'sanitize_key', $values ) ) ) );
			$input[ $key ] = empty( $values ) ? array( 'manage_options' ) : $values;
			Settings::set( $key, $input[ $key ] );
		}
		return $input;
	}

	/**
	 * Sanitize the tools settings input.
	 *
	 * @param array<string, mixed> $input The input data to sanitize.
	 * @return array<string, mixed> The sanitized tools settings.
	 */
	public function sanitize_tools( $input ): array {
		$input = is_array( $input ) ? $input : array();
		foreach ( array( 'debug_logging', 'console_logging' ) as $key ) {
			$input[ $key ] = ! empty( $input[ $key ] );
			Settings::set( $key, $input[ $key ] );
		}
		return $input;
	}
}
