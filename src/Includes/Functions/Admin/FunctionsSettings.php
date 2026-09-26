<?php
/**
 * Settings-related admin functions for LicencePress.
 *
 * @package LicencePress
 * @subpackage Includes\Functions\Admin
 * @since 1.0.0
 */
namespace LicencePress\Includes\Functions\Admin;

use LicencePress\Includes\Functions\Helpers\LoaderHelper;
use LicencePress\Includes\Functions\Helpers\PermalinkHelper;
use LicencePress\Includes\Plugins\Plugins;
use LicencePress\Includes\Plugins\SettingsPageProviderInterface;
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
	 * Register the WordPress admin-post actions used by the settings forms.
	 *
	 * @param LoaderHelper $loader Loader instance used to register hooks.
	 * @return void
	 */
	public function register_admin_post_hooks( LoaderHelper $loader ): void {
		$loader->register_component(
			$this,
			array(
				array(
					'type'     => 'action',
					'hook'     => 'admin_post_licencepress_save_general_settings',
					'callback' => 'handle_general_save',
				),
				array(
					'type'     => 'action',
					'hook'     => 'admin_post_licencepress_save_billing_settings',
					'callback' => 'handle_billing_save',
				),
				array(
					'type'     => 'action',
					'hook'     => 'admin_post_licencepress_save_billing_invoice_settings',
					'callback' => 'handle_billing_invoice_save',
				),
			)
		)->run();
	}

	/**
	 * Save settings using the direct admin POST flow used by the custom table store.
	 *
	 * @return void
	 */
	public function save_settings(): void {
		$action = isset( $_POST['action'] ) ? sanitize_key( wp_unslash( $_POST['action'] ) ) : '';
		if ( '' === $action ) {
			return;
		}

		if ( 'licencepress_save_general_settings' === $action ) {
			$this->handle_general_save();
		}

		if ( 'licencepress_save_billing_settings' === $action ) {
			$this->handle_billing_save();
		}

		if ( 'licencepress_save_billing_invoice_settings' === $action ) {
			$this->handle_billing_invoice_save();
		}
	}

	/**
	 * Validate a save nonce against the standard WordPress field and the legacy plugin-specific field.
	 *
	 * @param string $action Nonce action.
	 * @param string $legacy_field_name Legacy field name to accept for compatibility.
	 * @return void
	 */
	private function validate_save_nonce( string $action, string $legacy_field_name = '' ): void {
		if ( isset( $_REQUEST['_wpnonce'] ) ) {
			check_admin_referer( $action, '_wpnonce' );
			return;
		}

		if ( '' !== $legacy_field_name && isset( $_REQUEST[ $legacy_field_name ] ) ) {
			check_admin_referer( $action, $legacy_field_name );
			return;
		}

		check_admin_referer( $action );
	}

	/**
	 * Handle a general settings save request.
	 *
	 * @return void
	 */
	public function handle_general_save(): void {
		if ( ! $this->can_manage_general_settings() ) {
			wp_die( esc_html__( 'You are not allowed to save LicencePress general settings.', 'licencepress' ), 403 );
		}

		$this->validate_save_nonce( 'licencepress_save_general_settings', 'licencepress_general_nonce' );
		$input = isset( $_POST['licencepress_general'] ) && is_array( $_POST['licencepress_general'] ) ? wp_unslash( $_POST['licencepress_general'] ) : array();
		$this->sanitize_general( $input );
		wp_safe_redirect( admin_url( 'admin.php?page=licencepress&group=settings&tab=general' ) );
		exit;
	}

	/**
	 * Handle a billing settings save request.
	 *
	 * @return void
	 */
	public function handle_billing_save(): void {
		if ( ! $this->can_manage_billing_settings( 'licencepress_paypal_manage' ) && ! $this->can_manage_billing_settings( 'licencepress_stripe_manage' ) ) {
			wp_die( esc_html__( 'You are not allowed to save LicencePress billing settings.', 'licencepress' ), 403 );
		}

		$this->validate_save_nonce( 'licencepress_billing_general', 'licencepress_billing_general_nonce' );
		$input = isset( $_POST['licencepress_billing'] ) && is_array( $_POST['licencepress_billing'] ) ? wp_unslash( $_POST['licencepress_billing'] ) : array();
		if ( ! empty( $input ) ) {
			$this->sanitize_billing( $input );
		}

		$paypal_input = isset( $_POST['licencepress_paypal'] ) && is_array( $_POST['licencepress_paypal'] ) ? wp_unslash( $_POST['licencepress_paypal'] ) : array();
		if ( ! empty( $paypal_input ) ) {
			$plugin = Plugins::get_instance()->get_registered_plugins()['licencepress-paypal'] ?? null;
			if ( $plugin instanceof SettingsPageProviderInterface ) {
				$plugin->sanitize_settings( $paypal_input );
			}
		}

		$billing_tab = ! empty( $paypal_input ) ? 'bt=paypal' : 'bt=general';
		wp_safe_redirect( admin_url( 'admin.php?page=licencepress&group=settings&tab=billing&' . $billing_tab ) );
		exit;
	}

	/**
	 * Handle a billing invoice settings save request.
	 *
	 * @return void
	 */
	public function handle_billing_invoice_save(): void {
		if ( ! $this->can_manage_billing_settings( 'licencepress_paypal_manage' ) && ! $this->can_manage_billing_settings( 'licencepress_stripe_manage' ) ) {
			wp_die( esc_html__( 'You are not allowed to save LicencePress billing settings.', 'licencepress' ), 403 );
		}

		$this->validate_save_nonce( 'licencepress_billing_invoice', 'licencepress_billing_invoice_nonce' );
		$input = isset( $_POST['licencepress_billing'] ) && is_array( $_POST['licencepress_billing'] ) ? wp_unslash( $_POST['licencepress_billing'] ) : array();
		$this->sanitize_billing_invoice( $input );
		wp_safe_redirect( admin_url( 'admin.php?page=licencepress&group=settings&tab=billing&bt=invoice' ) );
		exit;
	}

	/**
	 * Process direct form submissions for access settings.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function can_manage_general_settings(): bool {
		return current_user_can( 'manage_options' ) || current_user_can( 'licencepress_settings_general_edit' );
	}
	/**
	 * Process direct form submissions for billing settings.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function can_manage_billing_settings( string $provider_capability = 'licencepress_settings_general_edit' ): bool {
		return current_user_can( 'manage_options' )
			|| current_user_can( 'licencepress_settings_general_edit' )
			|| current_user_can( $provider_capability );
	}
	/**
	 * Handle direct POST requests for LicencePress settings.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function handle_direct_post(): void {
		$this->save_settings();
	}
	/**
	 * Sanitize the billing settings input.
	 *
	 * @param array<string, mixed> $input The input data to sanitize.
	 * @return array<string, mixed> The sanitized billing settings.
	 * @since 1.0.0
	 */
	public function sanitize_billing( $input ): array {
		if ( ! $this->can_manage_general_settings() ) {
			return (array) Settings::get_group( 'billing', array() );
		}

		$input = is_array( $input ) ? $input : array();
		$billing = array(
			'billing_name'          => sanitize_text_field( $input['billing_name'] ?? '' ),
			'billing_address_1'     => sanitize_text_field( $input['billing_address_1'] ?? '' ),
			'billing_address_2'     => sanitize_text_field( $input['billing_address_2'] ?? '' ),
			'town'                  => sanitize_text_field( $input['town'] ?? '' ),
			'country'               => sanitize_text_field( $input['country'] ?? '' ),
			'uk_county'             => sanitize_text_field( $input['uk_county'] ?? '' ),
			'us_state'              => sanitize_text_field( $input['us_state'] ?? '' ),
			'other_county_state'    => sanitize_text_field( $input['other_county_state'] ?? '' ),
			'currency'              => sanitize_text_field( $input['currency'] ?? 'GBP' ),
			'custom_currency_code'  => sanitize_text_field( $input['custom_currency_code'] ?? '' ),
			'custom_currency_symbol'=> sanitize_text_field( $input['custom_currency_symbol'] ?? '' ),
			'charge_vat'            => ! empty( $input['charge_vat'] ) ? true : false,
			'vat_label'             => sanitize_text_field( $input['vat_label'] ?? 'VAT' ),
			'vat_percentage'        => sanitize_text_field( $input['vat_percentage'] ?? '20' ),
			'vat_number'            => sanitize_text_field( $input['vat_number'] ?? '' ),
			'email_address'         => sanitize_email( $input['email_address'] ?? '' ),
			'phone_country_code'    => sanitize_text_field( $input['phone_country_code'] ?? '' ),
			'phone_number'          => sanitize_text_field( $input['phone_number'] ?? '' ),
			'prefix'                => sanitize_text_field( $input['prefix'] ?? '' ),
			'invoice_suffix'        => sanitize_text_field( $input['invoice_suffix'] ?? '' ),
			'receipt_suffix'        => sanitize_text_field( $input['receipt_suffix'] ?? '' ),
			'invoice_logo'          => sanitize_text_field( $input['invoice_logo'] ?? '' ),
		);

		foreach ( $billing as $key => $value ) {
			Settings::set( $key, $value );
		}

		update_option( 'licencepress_billing', $billing );

		return $billing;
	}

	/**
	 * Sanitize the invoice settings input.
	 *
	 * @param array<string, mixed> $input The input data to sanitize.
	 * @return array<string, mixed> The sanitized invoice settings.
	 * @since 1.0.0
	 */
	public function sanitize_billing_invoice( $input ): array {
		if ( ! $this->can_manage_general_settings() ) {
			return (array) Settings::get_group( 'billing', array() );
		}

		$input = is_array( $input ) ? $input : array();
		$invoice = array(
			'invoice_style' => wp_kses_post( $input['invoice_style'] ?? '' ),
		);

		foreach ( $invoice as $key => $value ) {
			Settings::set( $key, $value );
		}

		update_option( 'licencepress_billing_invoice', $invoice );

		return $invoice;
	}
	/**
	 * Sanitize the general settings input.
	 *
	 * @param array<string, mixed> $input The input data to sanitize.
	 * @return array<string, mixed> The sanitized general settings.
	 * @since 1.0.0
	 */
	public function sanitize_general( $input ): array {
		if ( ! $this->can_manage_general_settings() ) {
			return (array) Settings::get_group( Settings::GENERAL, array() );
		}

		$input = is_array( $input ) ? $input : array();

		$allowed_default_licensor_type = array( 'company', 'organization', 'group', 'individual' );
		$allowed_default_renewal_policy_mode = array( 'default', 'custom' );
		$allowed_default_licence_pattern_type = array( 'standard', 'custom' );
		$allowed_default_licence_pattern_format = array( 'alphanumeric', 'letters', 'numbers' );
		$allowed_default_licence_pattern_letter_case = array( 'uppercase', 'lowercase', 'mixedcase' );
		$allowed_default_licence_pattern_separator = array( '-', '_', '|', '<', '>', ':', '.', 'none' );
		$allowed_default_ambiguous_chars = array( '0', 'O', '1', 'i', 'l', 'I', 's', 'S', '5' );

		$default_ambiguous_characters = is_array( $input['default_exclude_ambiguous_characters'] ?? null ) ? (array) $input['default_exclude_ambiguous_characters'] : array();
		$default_ambiguous_characters = array_values( array_unique( array_intersect( $allowed_default_ambiguous_chars, array_map( 'strval', $default_ambiguous_characters ) ) ) );

		$default_licensor_type = $input['default_licensor_type'] ?? 'individual';
		$default_licensor_name = $input['default_licensor_name'] ?? '';
		$default_licensor_country = $input['default_licensor_country'] ?? '';
		$default_licence_prefix = $input['default_licence_prefix'] ?? '';
		$default_licence_platform = $input['default_licence_platform'] ?? array();
		$default_renewal_policy_mode = $input['default_renewal_policy_mode'] ?? 'default';
		$default_custom_licence_renewal_policy_page = $input['default_custom_licence_renewal_policy_page'] ?? 0;
		$default_licence_pattern_type = $input['default_licence_pattern_type'] ?? 'standard';
		$default_custom_licence_pattern = $input['default_custom_licence_pattern'] ?? '';
		$default_licence_pattern_format = $input['default_licence_pattern_format'] ?? 'alphanumeric';
		$default_licence_pattern_letter_case = $input['default_licence_pattern_letter_case'] ?? 'uppercase';
		$default_licence_pattern_separator = $input['default_licence_pattern_separator'] ?? '-';

		$default_custom_licence_renewal_policy_page = max( 0, intval( $default_custom_licence_renewal_policy_page ) );

		$general = array(
			'default_licensor_type'                       => in_array( $default_licensor_type, $allowed_default_licensor_type, true ) ? sanitize_key( $default_licensor_type ) : 'individual',
			'default_licensor_name'                       => sanitize_text_field( $default_licensor_name ),
			'default_licensor_country'                    => sanitize_text_field( $default_licensor_country ),
			'default_licence_prefix'                      => preg_match( '/^[A-Za-z0-9_-]{1,7}$/', (string) $default_licence_prefix ) ? sanitize_text_field( $default_licence_prefix ) : '',
			'default_licence_platform'                    => array_values( array_unique( array_filter( array_map( 'sanitize_key', is_array( $default_licence_platform ) ? $default_licence_platform : array( $default_licence_platform ) ) ) ) ),
			'default_renewal_policy_mode'                 => in_array( $default_renewal_policy_mode, $allowed_default_renewal_policy_mode, true ) ? sanitize_key( $default_renewal_policy_mode ) : 'default',
			'default_custom_licence_renewal_policy_page'  => $default_custom_licence_renewal_policy_page,
			'default_licence_pattern_type'                => in_array( $default_licence_pattern_type, $allowed_default_licence_pattern_type, true ) ? sanitize_key( $default_licence_pattern_type ) : 'standard',
			'default_custom_licence_pattern'              => sanitize_text_field( $default_custom_licence_pattern ),
			'default_licence_pattern_format'              => in_array( (string) $default_licence_pattern_format, $allowed_default_licence_pattern_format, true ) ? sanitize_key( (string) $default_licence_pattern_format ) : 'alphanumeric',
			'default_exclude_ambiguous_characters'        => $default_ambiguous_characters,
			'default_licence_pattern_letter_case'         => in_array( (string) $default_licence_pattern_letter_case, $allowed_default_licence_pattern_letter_case, true ) ? sanitize_key( (string) $default_licence_pattern_letter_case ) : 'uppercase',
			'default_licence_pattern_separator'           => in_array( (string) $default_licence_pattern_separator, $allowed_default_licence_pattern_separator, true ) ? (string) $default_licence_pattern_separator : '-',
		);

		if ( 'custom' === $general['default_licence_pattern_type'] && ! preg_match( '/[XA]/i', $general['default_custom_licence_pattern'] ) ) {
			$general['default_licence_pattern_letter_case'] = '';
		}

		foreach ( $general as $key => $value ) {
			$input[ $key ] = $value;
			Settings::set( $key, $value );
		}

		Settings::set_group( Settings::GENERAL, $general );
		update_option( 'licencepress_general', $general );

		return $general;
	}
	/**
	 * Sanitize the access settings input.
	 *
	 * @param array<string, mixed> $input The input data to sanitize.
	 * @return array<string, mixed> The sanitized access settings.
	 * @since 1.0.0
	 */
	public function sanitize_access( $input ): array {
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'licencepress_settings_access_edit' ) ) {
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

		update_option( 'licencepress_access', $input );

		return $input;
	}

	/**
	 * Sanitize the tools settings input.
	 *
	 * @param array<string, mixed> $input The input data to sanitize.
	 * @return array<string, mixed> The sanitized tools settings.
	 * @since 1.0.0
	 */
	public function sanitize_tools( $input ): array {
		$input = is_array( $input ) ? $input : array();
		foreach ( array( 'debug_logging', 'console_logging', 'remove_all_data_on_uninstall', 'uninstall_3rd_party_plugins' ) as $key ) {
			$input[ $key ] = ! empty( $input[ $key ] );
			Settings::set( $key, $input[ $key ] );
		}

		update_option( 'licencepress_tools', $input );

		return $input;
	}
}
