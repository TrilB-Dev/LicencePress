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
	 * Normalize the current values for the default-prefixed general settings fields.
	 *
	 * @param array<string, mixed> $values The incoming values.
	 * @return array<string, mixed> The normalized values.
	 */
	private function normalize_values( array $values ): array {
		return $values;
	}

	/**
	 * Render the general settings fields.
	 *
	 * @param array $values The current values for the settings fields.
	 */
	public function render( array $values ): void {
		$values = $this->normalize_values( $values );

		$licensor_type = $values['default_licensor_type'] ?? 'individual';
		$usage         = $values['default_licence_usage'] ?? array();
		$usage         = is_array( $usage ) ? $usage : array( $usage );
		$pattern       = $values['default_licence_pattern_type'] ?? 'standard';
		$custom        = $values['default_custom_licence_pattern'] ?? '';
		$separator     = $values['default_licence_pattern_separator'] ?? '-';
		$renewal_licence_pages = array( '' => __( 'Select a page', 'licencepress' ) );
		if ( function_exists( 'get_pages' ) ) {
			foreach ( get_pages( array( 'sort_column' => 'post_title', 'sort_order' => 'ASC' ) ) as $page ) {
				$renewal_licence_pages[ (string) $page->ID ] = $page->post_title;
			}
		}

		?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="licencepress-settings-form">
			<input type="hidden" name="action" value="licencepress_save_general_settings" />
			<input type="hidden" name="licencepress_tab" value="general" />
			<?php wp_nonce_field( 'licencepress_save_general_settings' ); ?>
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
								__( 'Default Licensor Country', 'licencepress' ), 
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
							'licencepress-general-default-licence-prefix', 
								__( 'Default Licence Prefix', 'licencepress' ), 
								array( 
									'description' => __( 'Max 7 numbers and letters. Allowed: A-Z, 0-9, -, _. No spaces.', 'licencepress' ),
									'tooltip' => __( 'This is the default prefix that will be added to the beginning of all generated licence keys via LicencePress.', 'licencepress' )
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
							'licencepress-general-default-licence-platform', 
								__( 'Default Licence Platforms', 'licencepress' ), 
								array( 
									'description' => __( 'Select the environments where generated licences will be used.', 'licencepress' ),
									'tooltip' => __( 'These are the default environments where your licences will be used. This will allow for the storage of relevant verification data for each applicable environment.', 'licencepress' )
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
									'id' => 'licencepress-general-default-licence-platform',
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
								__( 'Default Licence renewal policy', 'licencepress' ), 
								array( 
									'description' => __( 'Set whether to use the default LicencePress licence renewal policy or your own custom policy.', 'licencepress' ),
									'tooltip' => __( 'LicencePress comes with a default licence renewal policy that you can use, or you can specify your own custom policy.', 'licencepress' )
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
								'licencepress-general-default-custom-licence-renewal-policy-page', 
								__( 'Default Custom Licence Renewal policy page', 'licencepress' ), 
								array( 
									'description' => __( 'Select the page users will find your custom Licence Renewal Policy.', 'licencepress' ),
									'tooltip' => __( 'This setting allows you to specify the page where your custom licence renewal policy is located.', 'licencepress' )
								) 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select( 
								'licencepress_general[default_custom_licence_renewal_policy_page]', 
								array( 
									'data' => $renewal_licence_pages,
									'selected' => (string) ( $values['default_custom_licence_renewal_policy_page'] ?? '' ),
									'id' => 'licencepress-general-default-custom-licence-renewal-policy-page',
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
								__( 'Default Licence pattern', 'licencepress' ), 
								array( 
									'description' => __( 'Choose the default licence pattern for generated licence codes.', 'licencepress' ),
									'tooltip' => __( 'The Licence Pattern defines the format of the generated licence codes.', 'licencepress' )
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
					<tr id="licencepress-default-custom-pattern-row" data-licencepress-pattern-mode="custom">
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
								__( 'Default Excluded ambiguous characters', 'licencepress' ), 
								array( 
									'description' => __( 'Select the characters you want omitted from generated licence codes.', 'licencepress' ),
									'tooltip' => __( 'These characters will be excluded from all generated licence codes, maintaining clarity and avoiding confusion between characters.', 'licencepress' )
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
								__( 'Default Characters to ignore', 'licencepress' ),
								array(
									'for' => 'licencepress-general-default-exclude-ambiguous-characters',
									'class' => 'w-100'
								)
							); ?>
						</td>
					</tr>
					<tr data-licencepress-pattern-mode="standard" id="licencepress-default-pattern-format-row">
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-default-licence-pattern-format', 
								__( 'Default Licence pattern makeup', 'licencepress' ), 
								array( 
									'description' => __( 'Define the character set used in generated licence codes.', 'licencepress' ),
									'tooltip' => __( 'This setting determines which types of characters (letters, numbers, or both) will be used in the generated licence codes.', 'licencepress' )
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
					<tr data-licencepress-pattern-mode="standard" id="licencepress-default-pattern-letter-case-row">
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-default-licence-pattern-letter-case', 
								__( 'Default Pattern letter case', 'licencepress' ), 
								array( 
									'description' => __( 'Select the casing style for alpha characters in generated licences.', 'licencepress' ),
									'tooltip' => __( 'This setting determines whether the letters in the generated licence codes will be uppercase, lowercase, or mixed case.', 'licencepress' )
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
					<tr data-licencepress-pattern-mode="standard" id="licencepress-default-pattern-separator-row">
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'licencepress-general-default-licence-pattern-separator', 
								__( 'Default Pattern separator', 'licencepress' ), 
								array( 
									'description' => __( 'Select the separator to use between groups of licence characters.', 'licencepress' ),
									'tooltip' => __( 'This setting determines the character that will be used to separate different groups of characters in the generated licence codes.', 'licencepress' )
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





