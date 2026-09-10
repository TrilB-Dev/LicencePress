<?php
/**
 * Settings general fields.
 *
 * @package LicencePress
 * @subpackage Admin\Manager\Settings
 * @since 1.0.0
 */
namespace LicencePress\Admin\Manager\Settings;

use LicencePress\Includes\Functions\Helpers\AlertHelper;
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

		AlertHelper::render_admin_notice(
			__( 'These defaults will be used for all newly generated licences unless a more specific value is set elsewhere.', 'licencepress' ),
			'info'
		);
		AlertHelper::render_admin_notice(
			__( 'Review the legal and licence defaults before saving; changes apply to future licence generation.', 'licencepress' ),
			'warning'
		);
		?>
		<form method="post" action="">
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
									'description' => __( 'The default Licensor name usually matches the product owner or company.', 'licencepress' ), 
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
									'validation' => array( 
										'state' => 'invalid', 
										'message' => __( 'Please enter the default licensor name.', 'licencepress' ) 
									) 
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
								'licencepress-general-default-country', 
								__( 'Country', 'licencepress' ), 
								array( 
									'description' => __( 'Select the country the default licensor is based or registered in.', 'licencepress' ), 
									'tooltip' => __( 'This is used for legal and administrative purposes.', 'licencepress' ) 
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select( 
								'licencepress_general[default_country]', 
								array( 
									'data' => array(), 
									'selected' => $values['default_country'] ?? '', 
									'id' => 'licencepress-general-default-country', 
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
								'licencepress-general-licence-prefix', 
								__( 'Licence Prefix', 'licencepress' ), 
								array( 
									'description' => __( 'Max 7 numbers and letters. Allowed: A-Z, 0-9, -, _. No spaces.', 'licencepress' ) 
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::text_input( 
								'licencepress_general[licence_prefix]', 
								(string) ( $values['licence_prefix'] ?? '' ), 
								array( 
									'id' => 'licencepress-general-licence-prefix', 
									'class' => 'w-100', 
									'pattern' => '[A-Za-z0-9_-]{1,7}' 
								) 
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-licence-usage', 
								__( 'Where will your licences be used?', 'licencepress' ), 
								array( 
									'description' => __( 'Select the environments where generated licences will be used.', 'licencepress' ) 
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_multiselect( 
								'licencepress_general[default_licence_usage][]', 
								array( 
									'data' => array( 
										'websites' => __( 'Websites', 'licencepress' ),
										'windows_software' => __( 'Windows Software', 'licencepress' ),
										'linux_software' => __( 'Linux Software', 'licencepress' ),
										'macos_software' => __( 'MacOS Software', 'licencepress' ),
										'android_devices' => __( 'Android Devices', 'licencepress' ),
										'ios_devices' => __( 'IOS Devices', 'licencepress' )
									),
									'selected' => is_array( $values['default_licence_usage'] ?? array() ) ? array_values( $values['default_licence_usage'] ?? array() ) : array( $values['default_licence_usage'] ?? array() ),
									'id' => 'licencepress-general-licence-usage',
									'live_search' => true,
									'show_tick' => true,
									'width' => '100%'
								)
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-renewal-policy-mode', 
								__( 'Licence renewal policy', 'licencepress' ), 
								array( 
									'description' => __( 'Set how licences renew by default.', 'licencepress' ) 
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select( 
								'licencepress_general[renewal_policy_mode]', 
								array( 
									'data' => array( 
										'default' => __( 'Use the LicencePress Default Renewal', 'licencepress' ), 
										'custom' => __( 'Use your own Renewal Policy', 'licencepress' ) 
									), 
									'selected' => $values['renewal_policy_mode'] ?? 'default', 
									'id' => 'licencepress-general-renewal-policy-mode', 
									'live_search' => true, 
									'width' => '100%' 
								) 
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-renewal-policy-page', 
								__( 'Renewal policy page', 'licencepress' ), 
								array( 
									'description' => __( 'Select the page users will see for licence renewal details.', 
									'licencepress' ) 
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select( 
								'licencepress_general[renewal_policy_page]', 
								array( 
									'data' => array(), 
									'selected' => $values['renewal_policy_page'] ?? '', 
									'id' => 'licencepress-general-renewal-policy-page', 
									'live_search' => true, 
									'width' => '100%' 
								) 
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-licence-pattern-type', 
								__( 'Licence pattern', 'licencepress' ), 
								array( 
									'description' => __( 'Choose the style of generated licence IDs.', 
									'licencepress' ) 
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select( 
								'licencepress_general[licence_pattern_type]', 
								array( 
									'data' => array( 
										'standard' => __( '32-char (XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX)', 'licencepress' ), 
										'custom' => __( 'Custom Pattern', 'licencepress' ) ), 
										'selected' => $pattern, 
										'id' => 'licencepress-general-licence-pattern-type', 
										'live_search' => true, 
										'width' => '100%' 
									) 
								); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-licence-pattern-format', 
								__( 'Licence pattern makeup', 'licencepress' ), 
								array( 'description' => __( 'Define the character set used in generated licence codes.', 'licencepress' ) 
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select( 
								'licencepress_general[licence_pattern_format]', 
								array( 
									'data' => array( 
										'alphanumeric' => __( 'AlphaNumeric', 'licencepress' ), 
										'letters' => __( 'Letters Only', 'licencepress' ), 
										'numbers' => __( 'Numbers Only', 'licencepress' ) 
									), 
									'selected' => $values['licence_pattern_format'] ?? 'alphanumeric', 
									'id' => 'licencepress-general-licence-pattern-format', 
									'live_search' => true, 
									'width' => '100%' 
								) 
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-exclude-ambiguous-characters', 
								__( 'Exclude ambiguous characters', 'licencepress' ), 
								array( 
									'description' => __( 'Excludes characters that look similar from the pattern: 0, O, 1, l, I', 'licencepress' ) 
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::checkbox( 
								'licencepress_general[exclude_ambiguous_characters]', 
								'1', 
								__( 'Exclude ambiguous characters', 'licencepress' ), 
								array( 
									'id' => 'licencepress-general-exclude-ambiguous-characters', 
									'checked' => ! empty( $values['exclude_ambiguous_characters'] ?? false ) 
								) 
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-pattern-letter-case', 
								__( 'Pattern letter case', 'licencepress' ), 
								array( 
									'description' => __( 'Select the casing style for alpha characters in generated licences.', 'licencepress' ) 
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select( 
								'licencepress_general[pattern_letter_case]', 
								array( 
									'data' => array( 
										'uppercase' => __( 'Uppercase', 'licencepress' ), 
										'lowercase' => __( 'Lowercase', 'licencepress' ), 
										'mixedcase' => __( 'Mixedcase', 'licencepress' ) 
									), 
									'selected' => $values['pattern_letter_case'] ?? 'uppercase', 
									'id' => 'licencepress-general-pattern-letter-case', 
									'live_search' => true, 
									'width' => '100%' 
								) 
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-pattern-separator', 
								__( 'Pattern separator', 'licencepress' ), 
								array( 
									'description' => __( 'Select the separator to use between groups of licence characters.', 'licencepress' ) 
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select( 
								'licencepress_general[pattern_separator]', 
								array( 
									'data' => array( 
										'-' => __( '-', 'licencepress' ), 
										':' => __( ':', 'licencepress' ), 
										'.' => __( '.', 'licencepress' ), 
										'none' => __( 'None', 'licencepress' ) 
									), 
									'selected' => $separator, 
									'id' => 'licencepress-general-pattern-separator', 
									'live_search' => true, 
									'width' => '100%' 
								) 
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-custom-pattern', 
								__( 'Custom Pattern', 'licencepress' ), 
								array( 
									'description' => __( 'Use X for alphanumeric, A for letters only, N for numbers only, and - for separators.', 'licencepress' ) 
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::text_input( 
								'licencepress_general[custom_pattern]', 
								(string) ( $custom ), 
								array( 
									'id' => 'licencepress-general-custom-pattern', 
									'class' => 'w-100', 
									'validation' => array( 
										'state' => 'invalid', 
										'message' => __( 'Please define the custom licence pattern.', 'licencepress' ) 
									) 
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
