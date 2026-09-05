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
use LicencePress\Includes\Functions\Helpers\PermalinkHelper;
use LicencePress\Includes\Functions\Helpers\SanitizationHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SettingsGeneral {
	public function render( array $values ): void {
		$entity_type = $values['entity_type'] ?? 'individual';
		$usage       = $values['licence_usage'] ?? array();
		$usage       = is_array( $usage ) ? $usage : array( $usage );
		$pattern     = $values['licence_pattern_type'] ?? 'standard';
		$custom      = $values['custom_pattern'] ?? '';
		$separator   = $values['pattern_separator'] ?? '-';
		$fields      = array(
			'entity_type' => array(
				'label' => __( 'Are you a company, Organization, Group or Individual?', 'licencepress' ),
				'type'  => 'radio_group',
				'items' => array(
					'company'     => __( 'Company', 'licencepress' ),
					'organization'=> __( 'Organization', 'licencepress' ),
					'group'       => __( 'Group', 'licencepress' ),
					'individual'  => __( 'Individual', 'licencepress' ),
				),
			),
			'licence_name' => array(
				'label' => __( 'Name of Licence', 'licencepress' ),
				'help'  => __( 'This will also be part of the licence encryption.', 'licencepress' ),
			),
			'country' => array(
				'label' => __( 'Country', 'licencepress' ),
				'type'  => 'select',
				'items' => array(
					'gb' => 'United Kingdom',
					'us' => 'United States',
					'ca' => 'Canada',
					'au' => 'Australia',
					'de' => 'Germany',
					'fr' => 'France',
				),
			),
			'currency' => array(
				'label' => __( 'Currency', 'licencepress' ),
				'type'  => 'select',
				'items' => array(
					'GBP' => 'GBP - British Pound',
					'USD' => 'USD - US Dollar',
					'EUR' => 'EUR - Euro',
					'AUD' => 'AUD - Australian Dollar',
					'CAD' => 'CAD - Canadian Dollar',
				),
			),
			'licence_prefix' => array(
				'label' => __( 'Licence Prefix', 'licencepress' ),
				'help'  => __( 'Max 7 numbers and letters. Allowed: A-Z, 0-9, -, _. No spaces.', 'licencepress' ),
			),
			'licence_usage' => array(
				'label' => __( 'Where will your licences be used?', 'licencepress' ),
				'type'  => 'multiselect',
				'items' => array(
					'websites'         => __( 'Websites', 'licencepress' ),
					'windows_software' => __( 'Windows Software', 'licencepress' ),
					'linux_software'   => __( 'Linux Software', 'licencepress' ),
					'macos_software'   => __( 'MacOS Software', 'licencepress' ),
					'android_devices'  => __( 'Android Devices', 'licencepress' ),
					'ios_devices'      => __( 'IOS Devices', 'licencepress' ),
				),
			),
			'renewal_policy_mode' => array(
				'label' => __( 'Licence renewal policy', 'licencepress' ),
				'type'  => 'select',
				'items' => array(
					'default' => __( 'Use the LicencePress Default Renewal', 'licencepress' ),
					'custom'  => __( 'Use your own Renewal Policy', 'licencepress' ),
				),
			),
			'renewal_policy_page' => array(
				'label' => __( 'Renewal policy page', 'licencepress' ),
				'type'  => 'select',
				'items' => array(),
			),
			'licence_pattern_type' => array(
				'label' => __( 'Licence pattern', 'licencepress' ),
				'type'  => 'select',
				'items' => array(
					'standard' => __( '32-char (XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX)', 'licencepress' ),
					'custom'   => __( 'Custom Pattern', 'licencepress' ),
				),
			),
			'licence_pattern_format' => array(
				'label' => __( 'Licence pattern makeup', 'licencepress' ),
				'type'  => 'select',
				'items' => array(
					'alphanumeric' => __( 'AlphaNumeric', 'licencepress' ),
					'letters'      => __( 'Letters Only', 'licencepress' ),
					'numbers'      => __( 'Numbers Only', 'licencepress' ),
				),
			),
			'exclude_ambiguous_characters' => array(
				'label' => __( 'Exclude ambiguous characters', 'licencepress' ),
				'type'  => 'checkbox',
				'help'  => __( 'Excludes characters that look similar from the pattern: 0, O, 1, l, I', 'licencepress' ),
			),
			'pattern_letter_case' => array(
				'label' => __( 'Pattern letter case', 'licencepress' ),
				'type'  => 'select',
				'items' => array(
					'uppercase' => __( 'Uppercase', 'licencepress' ),
					'lowercase' => __( 'Lowercase', 'licencepress' ),
					'mixedcase' => __( 'Mixedcase', 'licencepress' ),
				),
			),
			'pattern_separator' => array(
				'label' => __( 'Pattern separator', 'licencepress' ),
				'type'  => 'select',
				'items' => array(
					'-'    => __( '-', 'licencepress' ),
					':'    => __( ':', 'licencepress' ),
					'.'    => __( '.', 'licencepress' ),
					'none' => __( 'None', 'licencepress' ),
				),
			),
			'custom_pattern' => array(
				'label' => __( 'Custom Pattern', 'licencepress' ),
				'help'  => __( 'Create your own pattern using the following placeholders: X = alphanumeric, A = letters only, N = numbers only, - for your separators.', 'licencepress' ),
			),
		);

		foreach ( $fields as $key => $field ) {
			$key   = SanitizationHelper::key( $key );
			$id    = 'licencepress-general-' . $key;
			$name  = 'licencepress_general[' . $key . ']';
			$value = $values[ $key ] ?? '';
			$mode  = in_array( $key, array( 'licence_pattern_format', 'exclude_ambiguous_characters', 'pattern_letter_case', 'pattern_separator', 'custom_pattern' ), true ) ? 'custom' : 'standard';
			if ( in_array( $key, array( 'custom_pattern', 'exclude_ambiguous_characters', 'pattern_letter_case', 'pattern_separator' ), true ) ) {
				$mode = 'custom';
			}
			if ( in_array( $key, array( 'entity_type', 'licence_name', 'country', 'currency', 'licence_prefix', 'licence_usage', 'renewal_policy_mode', 'renewal_policy_page', 'licence_pattern_type' ), true ) ) {
				$mode = 'standard';
			}
			?>
			<tr data-licencepress-pattern-mode="<?php echo esc_attr( $mode ); ?>">
				<th scope="row"><?php echo FormFieldHelper::label( $id, $field['label'], $field ); ?></th>
				<td>
					<?php
					switch ( $field['type'] ?? 'text' ) {
						case 'radio_group':
							echo FormFieldHelper::button_group(
								$name,
								$field['items'],
								(string) $entity_type,
								array(
									'id'   => $id,
									'type' => 'radio',
								)
							);
							break;
						case 'checkbox':
							echo FormFieldHelper::checkbox(
								$name,
								'1',
								$field['label'],
								array(
									'id'      => $id,
									'checked' => ! empty( $value ),
								)
							);
							break;
						case 'multiselect':
							echo FormFieldHelper::bootstrap_multiselect(
								$name,
								array(
									'data'    => $field['items'],
									'selected'=> is_array( $value ) ? array_values( $value ) : array( $value ),
									'live_search' => true,
									'show_tick' => true,
									'width' => '100%',
									'id' => $id,
								)
							);
							break;
						case 'select':
							echo FormFieldHelper::bootstrap_select(
								$name,
								array(
									'data'    => $field['items'],
									'selected'=> $value,
									'live_search' => true,
									'width' => '100%',
									'id' => $id,
								)
							);
							break;
						default:
							echo FormFieldHelper::text_input(
								$name,
								is_scalar( $value ) ? (string) $value : '',
								array(
									'id'    => $id,
									'pattern' => ( 'licence_prefix' === $key ? '[A-Za-z0-9_-]{1,7}' : null ),
								)
							);
							break;
					}
					?>
				</td>
			</tr>
			<?php
		}
	}
}
