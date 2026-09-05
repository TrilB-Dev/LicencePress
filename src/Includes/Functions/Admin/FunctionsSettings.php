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
		register_setting( 'licencepress_settings', 'licencepress_layout', array( 'sanitize_callback' => array( $this, 'sanitize_layout' ) ) );
		register_setting( 'licencepress_settings', 'licencepress_access', array( 'sanitize_callback' => array( $this, 'sanitize_access' ) ) );
		register_setting( 'licencepress_settings', 'licencepress_tools', array( 'sanitize_callback' => array( $this, 'sanitize_tools' ) ) );

		foreach ( $this->plugin_functions->plugin_settings_pages() as $page ) {
			register_setting(
				'licencepress_settings',
				'licencepress_' . $page['slug'],
				array( 'sanitize_callback' => $page['provider']->sanitize_settings( ... ) )
			);
		}
	}

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

		$general = array(
			'entity_type'                 => in_array( $input['entity_type'] ?? 'individual', $allowed_entity_types, true ) ? sanitize_key( $input['entity_type'] ?? 'individual' ) : 'individual',
			'licence_name'                => sanitize_text_field( $input['licence_name'] ?? '' ),
			'country'                     => sanitize_text_field( $input['country'] ?? '' ),
			'currency'                    => sanitize_text_field( $input['currency'] ?? '' ),
			'licence_prefix'              => preg_match( '/^[A-Za-z0-9_-]{1,7}$/', (string) ( $input['licence_prefix'] ?? '' ) ) ? sanitize_text_field( $input['licence_prefix'] ?? '' ) : '',
			'licence_usage'               => array_values( array_unique( array_filter( array_map( 'sanitize_key', is_array( $input['licence_usage'] ?? array() ) ? $input['licence_usage'] : array( $input['licence_usage'] ?? '' ) ) ) ) ),
			'renewal_policy_mode'         => in_array( $input['renewal_policy_mode'] ?? 'default', $allowed_renewal_modes, true ) ? sanitize_key( $input['renewal_policy_mode'] ?? 'default' ) : 'default',
			'renewal_policy_page'         => absint( $input['renewal_policy_page'] ?? 0 ),
			'licence_pattern_type'        => in_array( $input['licence_pattern_type'] ?? 'standard', $allowed_pattern_types, true ) ? sanitize_key( $input['licence_pattern_type'] ?? 'standard' ) : 'standard',
			'licence_pattern_format'      => in_array( (string) ( $input['licence_pattern_format'] ?? 'alphanumeric' ), $allowed_patterns, true ) ? sanitize_key( (string) ( $input['licence_pattern_format'] ?? 'alphanumeric' ) ) : 'alphanumeric',
			'exclude_ambiguous_characters' => ! empty( $input['exclude_ambiguous_characters'] ),
			'pattern_letter_case'         => in_array( (string) ( $input['pattern_letter_case'] ?? 'uppercase' ), $allowed_cases, true ) ? sanitize_key( (string) ( $input['pattern_letter_case'] ?? 'uppercase' ) ) : 'uppercase',
			'pattern_separator'           => in_array( (string) ( $input['pattern_separator'] ?? '-' ), $allowed_separators, true ) ? (string) $input['pattern_separator'] : '-',
			'custom_pattern'              => sanitize_text_field( $input['custom_pattern'] ?? '' ),
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

	public function sanitize_layout( $input ): array {
		if ( ! current_user_can( 'licencepress_settings_layout_edit' ) ) {
			return (array) Settings::get_group( Settings::LAYOUT, array() );
		}
		$input   = is_array( $input ) ? $input : array();
		$section = sanitize_key( $input['layout_section'] ?? 'general' );
		unset( $input['layout_section'] );
		$section_keys = array(
			'general' => array( 'show_search', 'show_breadcrumbs', 'show_sidebar' ),
			'search'  => array( 'show_search', 'search_placeholder', 'search_button_text', 'search_scope', 'search_no_results_message', 'search_results_count', 'search_min_chars', 'search_live_results' ),
			'sidebar' => array( 'show_sidebar', 'sidebar_position', 'sidebar_width', 'sidebar_sticky', 'sidebar_show_categories', 'sidebar_show_category_count', 'sidebar_expand_categories', 'sidebar_show_page_count' ),
			'page'    => array( 'page_show_title', 'show_breadcrumbs', 'page_show_toc', 'page_toc_position', 'toc_min_level', 'toc_max_level', 'show_last_updated', 'show_author', 'show_reading_time', 'reading_time_wpm', 'show_feedback', 'page_show_navigation', 'show_related_pages', 'related_pages_count' ),
		);
		$active_keys  = $section_keys[ $section ] ?? array_merge( ...array_values( $section_keys ) );
		foreach ( array( 'show_search', 'show_toc', 'show_breadcrumbs', 'show_last_updated', 'show_author', 'show_reading_time', 'show_feedback', 'show_related_pages', 'search_live_results', 'show_sidebar', 'sidebar_sticky', 'sidebar_show_categories', 'sidebar_show_category_count', 'sidebar_expand_categories', 'sidebar_show_page_count', 'page_show_title', 'page_show_toc', 'page_show_navigation' ) as $key ) {
			if ( ! in_array( $key, $active_keys, true ) ) {
				continue;
			}
			$value         = ! empty( $input[ $key ] );
			$input[ $key ] = $value;
			Settings::set( $key, $value );
		}
		foreach ( array( 'search_placeholder', 'search_button_text', 'search_no_results_message' ) as $key ) {
			if ( ! in_array( $key, $active_keys, true ) ) {
				continue;
			}
			$input[ $key ] = sanitize_text_field( $input[ $key ] ?? '' );
			Settings::set( $key, $input[ $key ] );
		}
		if ( in_array( 'search_scope', $active_keys, true ) ) {
			$input['search_scope'] = in_array( $input['search_scope'] ?? '', array( 'all', 'title', 'content' ), true ) ? $input['search_scope'] : 'all';
			Settings::set( 'search_scope', $input['search_scope'] );
		}
		if ( in_array( 'sidebar_position', $active_keys, true ) ) {
			$input['sidebar_position'] = in_array( $input['sidebar_position'] ?? '', array( 'left', 'right' ), true ) ? $input['sidebar_position'] : 'left';
			Settings::set( 'sidebar_position', $input['sidebar_position'] );
		}
		if ( in_array( 'page_toc_position', $active_keys, true ) ) {
			$input['page_toc_position'] = in_array( $input['page_toc_position'] ?? '', array( 'sidebar', 'content' ), true ) ? $input['page_toc_position'] : 'sidebar';
			Settings::set( 'page_toc_position', $input['page_toc_position'] );
		}
		foreach ( array(
			'related_pages_count'  => array( 1, 12 ),
			'search_results_count' => array( 1, 50 ),
			'search_min_chars'     => array( 1, 5 ),
			'sidebar_width'        => array( 180, 480 ),
			'toc_min_level'        => array( 1, 5 ),
			'toc_max_level'        => array( 2, 6 ),
			'reading_time_wpm'     => array( 100, 400 ),
		) as $key => [ $minimum, $maximum ] ) {
			if ( ! in_array( $key, $active_keys, true ) ) {
				continue;
			}
			$input[ $key ] = max( $minimum, min( $maximum, absint( $input[ $key ] ?? $minimum ) ) );
			Settings::set( $key, $input[ $key ] );
		}
		return $input;
	}

	public function sanitize_access( $input ): array {
		if ( ! current_user_can( 'licencepress_settings_access_edit' ) ) {
			return (array) Settings::get_group( Settings::ACCESS, array() );
		}
		$input   = is_array( $input ) ? $input : array();
		$allowed = array( 'manage_options', 'edit_posts', 'publish_posts' );
		foreach ( array( 'create_wikis', 'write_pages', 'view_analytics', 'manage_plugins' ) as $key ) {
			$values        = is_array( $input[ $key ] ?? null ) ? $input[ $key ] : array( $input[ $key ] ?? 'manage_options' );
			$values        = array_values( array_unique( array_intersect( $allowed, array_map( 'sanitize_key', $values ) ) ) );
			$input[ $key ] = empty( $values ) ? array( 'manage_options' ) : $values;
			Settings::set( $key, $input[ $key ] );
		}
		return $input;
	}

	public function sanitize_tools( $input ): array {
		$input = is_array( $input ) ? $input : array();
		foreach ( array( 'debug_logging', 'console_logging' ) as $key ) {
			$input[ $key ] = ! empty( $input[ $key ] );
			Settings::set( $key, $input[ $key ] );
		}
		return $input;
	}
}
