<?php
/**
 * Settings general fields.
 *
 * @package LicencePress
 * @subpackage Admin\Manager\Settings
 * @since 1.0.0
 */
namespace LicencePress\Admin\Manager\Settings;

use LicencePress\Includes\Functions\Helpers\FormFieldHelper;
use LicencePress\Includes\Functions\Helpers\SanitizationHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SettingsGeneral {
	/**
	 * Render the general settings fields.
	 *
	 * @param array $values The current values for the settings fields.
	 */
	public function render( array $values ): void {
		$licensor_type = $values['licensor_type'] ?? 'individual';
		$usage       = $values['licence_usage'] ?? array();
		$usage       = is_array( $usage ) ? $usage : array( $usage );
		$pattern     = $values['licence_pattern_type'] ?? 'standard';
		$custom      = $values['custom_pattern'] ?? '';
		$separator   = $values['pattern_separator'] ?? '-';
		$renewal_licence_pages = array( '' => __( 'Select a page', 'licencepress' ) );
		if ( function_exists( 'get_pages' ) ) {
			foreach ( get_pages( array( 'sort_column' => 'post_title', 'sort_order' => 'ASC' ) ) as $page ) {
				$renewal_licence_pages[ (string) $page->ID ] = $page->post_title;
			}
		}

		?>
		<form method="post" action="" class="licencepress-settings-form">
			<?php echo FormFieldHelper::input( 
				'action', 
				'licencepress_save_general_settings', 
				array( 
					'type' => 'hidden' 
				) 
			); ?>
			<?php echo FormFieldHelper::input( 
				'licencepress_general_nonce', 
				wp_create_nonce( 'licencepress_general' ), 
				array( 
					'type' => 'hidden' 
				) 
			); ?>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-default-licensor-name', 
								__( 'Name of default Licensor', 'licencepress' ), 
								array( 
									'description' => __( 'The default Licensor name usually matches the product owner or company registered name.', 'licencepress' ), 
									'tooltip' => __( 'This value is also used as part of the encryption process for generated licences.', 'licencepress' ) 
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::text_input( 
								'licencepress_general[default_licensor_name]', 
								(string) ( $values['default_licensor_name'] ?? '' ), 
								array( 
										'id' => 'licencepress-general-default-licensor-name',
										'class' => 'w-100',
										'data-licencepress-validate' => true,
										'data-licencepress-required' => true,
									)
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-default-licensor-type', 
								__( 'Default licensor type', 'licencepress' ), 
								array( 
									'description' => __( 'Please choose the legal form of the default licensor.', 'licencepress' ), 
									'tooltip' => __( 'This is used for legal and administrative defaults.', 'licencepress' ) 
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::button_group( 
								'licencepress_general[default_licensor_type]', 
								array( 
									'individual' => __( 'Individual', 'licencepress' ), 
									'group' => __( 'Group', 'licencepress' ), 
									'company' => __( 'Company', 'licencepress' ), 
									'organization' => __( 'Organization', 'licencepress' ) 
								), 
								(string) ( $values['default_licensor_type'] ?? '' ), 
								array( 
									'id' => 'licencepress-general-default-licensor-type', 
									'type' => 'radio' 
								) 
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-default-licensor-country', 
								__( 'Country', 'licencepress' ), 
								array( 
									'description' => __( 'Select the country the default licensor is based or registered in.', 'licencepress' ), 
									'tooltip' => __( 'This is used for legal and administrative purposes.', 'licencepress' ) 
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select( 
								'licencepress_general[default_licensor_country]', 
								array( 
									'data' => array(), 
									'selected' => $values['default_licensor_country'] ?? '', 
									'id' => 'licencepress-general-default-licensor-country', 
									'live_search' => true, 
									'width' => '100%', 
									'bscd_type' => 'country', 
									'bscd_group' => true, 
									'bscd_flags' => true 
								) 
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-currency', 
								__( 'Currency', 'licencepress' ), 
								array( 
									'description' => __( 'Choose the default currency for generated licence values.', 'licencepress' ) 
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select( 
								'licencepress_general[currency]', 
								array( 
									'data' => array( 
										'GBP' => 'GBP - British Pound', 
										'USD' => 'USD - US Dollar', 
										'EUR' => 'EUR - Euro', 
										'AUD' => 'AUD - Australian Dollar', 
										'CAD' => 'CAD - Canadian Dollar' 
									), 
									'selected' => $values['currency'] ?? 'GBP', 
									'id' => 'licencepress-general-currency', 
									'live_search' => true, 
									'width' => '100%' 
								) 
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-default-licence-prefix', 
								__( 'Licence Prefix', 'licencepress' ), 
								array( 
									'description' => __( 'Max 7 numbers and letters. Allowed: A-Z, 0-9, -, _. No spaces.', 'licencepress' ) 
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::input_group( 
								FormFieldHelper::text_input( 
									'licencepress_general[default_licence_prefix]', 
									(string) ( $values['default_licence_prefix'] ?? '' ), 
									array( 
										'id' => 'licencepress-general-default-licence-prefix', 
										'class' => 'form-control',
										'pattern' => '[A-Za-z0-9_-]{1,7}' 
									) 
								) . '<span class="input-group-text" aria-label="Example licence prefix format">XXXXX-XXXXX-XXXXX-XXXXX-XXXXX</span>',
								array(
									'class' => 'w-100'
								)
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-licence-platform', 
								__( 'Where will your licences be used?', 'licencepress' ), 
								array( 
									'description' => __( 'Select the environments where generated licences will be used.', 'licencepress' ) 
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_multiselect( 
								'licencepress_general[default_licence_platform][]', 
								array( 
									'data' => array( 
										'website' => __( 'Website', 'licencepress' ),
										'windows_software' => __( 'Windows Software', 'licencepress' ),
										'linux_software' => __( 'Linux Software', 'licencepress' ),
										'macos_software' => __( 'MacOS Software', 'licencepress' ),
										'android_devices' => __( 'Android Devices', 'licencepress' ),
										'ios_devices' => __( 'IOS Devices', 'licencepress' )
									),
									'selected' => is_array( $values['default_licence_platform'] ?? array() ) ? array_values( $values['default_licence_platform'] ?? array() ) : array( $values['default_licence_platform'] ?? array() ),
									'id' => 'licencepress-general-licence-platform',
									'live_search' => false,
									'show_tick' => true,
									'width' => '100%'
								)
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-default-licence-renewal-policy-mode', 
								__( 'Licence renewal policy', 'licencepress' ), 
								array( 
									'description' => __( 'Set where customers can find your licence renewal policy.', 'licencepress' ) 
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select( 
								'licencepress_general[default_renewal_policy_mode]', 
								array( 
									'data' => array( 
										'default' => __( 'Use the LicencePress\'s Default Renewal Policy', 'licencepress' ), 
										'custom' => __( 'Use your own Licence Renewal Policy', 'licencepress' ) 
									), 
									'selected' => $values['default_renewal_policy_mode'] ?? 'default', 
									'id' => 'licencepress-general-default-licence-renewal-policy-mode', 
									'live_search' => false, 
									'width' => '100%' 
								) 
							); ?>
						</td>
					</tr>
					<tr id="licencepress-custom-renewal-row">
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-custom-licence-renewal-policy-page', 
								__( 'Licence Renewal policy page', 'licencepress' ), 
								array( 
									'description' => __( 'Select the page users will find your custom Licence Renewal Policy.', 'licencepress' ) 
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select( 
								'licencepress_general[custom_licence_renewal_policy_page]', 
								array( 
									'data' => $renewal_licence_pages,
									'selected' => (string) ( $values['custom_licence_renewal_policy_page'] ?? '' ),
									'id' => 'licencepress-general-custom-licence-renewal-policy-page',
									'live_search' => true,
									'width' => '100%'
								) 
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-default-licence-pattern-type', 
								__( 'Licence pattern', 'licencepress' ), 
								array( 
									'description' => __( 'Choose the style of generated licence IDs.', 
									'licencepress' ) 
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select( 
								'licencepress_general[default_licence_pattern_type]', 
								array( 
									'data' => array( 
										'32-char' => __( '32-char (XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX)', 'licencepress' ),
										'25-char' => __( '25-char (XXXXX-XXXXX-XXXXX-XXXXX-XXXXX)', 'licencepress' ),
										'16-char' => __( '16-char (XXXX-XXXX-XXXX-XXXX)', 'licencepress' ),
										'12-char' => __( '12-char (XXXX-XXXX-XXXX)', 'licencepress' ),
										'8-char' => __( '8-char (XXXX-XXXX)', 'licencepress' ),
										'custom' => __( 'Custom Pattern', 'licencepress' ) 
									), 
										'selected' => $values['default_licence_pattern_type'] ?? '32-char', 
										'id' => 'licencepress-general-default-licence-pattern-type', 
										'live_search' => false, 
										'width' => '100%' 
									) 
								); ?>
						</td>
					</tr>
					<tr id="licencepress-default-custom-pattern-row">
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-default-custom-licence-pattern', 
								__( 'Default Custom Licence Pattern', 'licencepress' ), 
								array( 
									'description' => __( 'Use X for alphanumeric, A for letters only, N for numbers only, and you can use any of the following seperators (-, _, |, :, ., <, >) for separators.', 'licencepress' ),
									'tooltip' => __( 'This custom pattern defines the default format for licence codes generated by LicencePress.', 'licencepress' )
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::text_input( 
								'licencepress_general[default_custom_licence_pattern]', 
								(string) ( $custom ), 
								array( 
										'id' => 'licencepress-general-default-custom-licence-pattern',
										'class' => 'w-100',
										'data-licencepress-validate' => true,
										'data-licencepress-required' => true,
									)
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-default-exclude-ambiguous-characters', 
								__( 'Exclude ambiguous characters', 'licencepress' ), 
								array( 
									'description' => __( 'Select any characters you want omitted from generated licence codes.', 'licencepress' ) 
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::floating( 
								FormFieldHelper::bootstrap_multiselect( 
									'licencepress_general[default_exclude_ambiguous_characters][]', 
									array(
										'data' => array(
											'0' => '0',
											'O' => 'O',
											'1' => '1',
											'i' => 'i',
											'l' => 'l',
											'I' => 'I',
											'5' => '5',
											'S' => 'S',
											's' => 's',
										),
										'selected' => array_values( array_filter( (array) ( $values['default_exclude_ambiguous_characters'] ?? array() ), 'is_scalar' ) ),
										'id' => 'licencepress-general-default-exclude-ambiguous-characters',
										'live_search' => true,
										'show_selected_tags' => true,
										'selected_items_style' => 'tags',
										'selected_text_format' => 'count',
										'width' => '100%',
										'show_tick' => true,
									),
								),
								__( 'Characters to ignore', 'licencepress' ),
								array(
									'for' => 'licencepress-general-default-exclude-ambiguous-characters',
									'class' => 'w-100'
								)
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-default-licence-pattern-format', 
								__( 'Licence pattern makeup', 'licencepress' ), 
								array( 'description' => __( 'Define the character set used in generated licence codes.', 'licencepress' ) 
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select( 
								'licencepress_general[default_licence_pattern_format]', 
								array( 
									'data' => array( 
										'alphanumeric' => __( 'AlphaNumeric', 'licencepress' ), 
										'letters' => __( 'Letters Only', 'licencepress' ), 
										'numbers' => __( 'Numbers Only', 'licencepress' ) 
									), 
									'selected' => $values['default_licence_pattern_format'] ?? 'alphanumeric', 
									'id' => 'licencepress-general-default-licence-pattern-format', 
									'live_search' => true, 
									'width' => '100%' 
								) 
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-default-licence-pattern-letter-case', 
								__( 'Pattern letter case', 'licencepress' ), 
								array( 
									'description' => __( 'Select the casing style for alpha characters in generated licences.', 'licencepress' ) 
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select( 
								'licencepress_general[default_licence_pattern_letter_case]', 
								array( 
									'data' => array( 
										'uppercase' => __( 'Uppercase', 'licencepress' ), 
										'lowercase' => __( 'Lowercase', 'licencepress' ), 
										'mixedcase' => __( 'Mixedcase', 'licencepress' ) 
									), 
									'selected' => $values['default_licence_pattern_letter_case'] ?? 'uppercase', 
									'id' => 'licencepress-general-default-licence-pattern-letter-case', 
									'live_search' => true, 
									'width' => '100%' 
								) 
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-default-licence-pattern-separator', 
								__( 'Pattern separator', 'licencepress' ), 
								array( 
									'description' => __( 'Select the separator to use between groups of licence characters.', 'licencepress' ) 
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select( 
								'licencepress_general[default_licence_pattern_separator]', 
								array( 
									'data' => array( 
										'-' => __( '-', 'licencepress' ),
										'_' => __( '_', 'licencepress' ),
										'|' => __( '|', 'licencepress' ),
										'<' => __( '<', 'licencepress' ), 
										'>' => __( '>', 'licencepress' ), 
										':' => __( ':', 'licencepress' ), 
										'.' => __( '.', 'licencepress' ), 
										'none' => __( 'None', 'licencepress' ) 
									), 
									'selected' => $values['default_licence_pattern_separator'] ?? '-', 
									'id' => 'licencepress-general-default-licence-pattern-separator', 
									'live_search' => true, 
									'width' => '100%' 
								) 
							); ?>
						</td>
					</tr>
				</tbody>
			</table>
			<div class="d-flex justify-content-end mt-3">
				<?php echo FormFieldHelper::button( 
					__( 'Save', 'licencepress' ), 
					array( 
						'type' => 'submit', 
						'class' => 'btn-primary' 
					) 
				); ?>
			</div>
		</form>
		<?php
	}
}





