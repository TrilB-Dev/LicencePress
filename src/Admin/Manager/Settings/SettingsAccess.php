<?php
/**
 * Settings access restriction fields.
 *
 * @package TrilBDev
 * @subpackage Admin\Manager\Settings
 */
namespace LicencePress\Admin\Manager\Settings;

use LicencePress\Includes\Functions\Helpers\FormFieldHelper;
use LicencePress\Includes\Functions\Helpers\SanitizationHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SettingsAccess {
    public function render( array $values ): void {
        $fields = [
            'issue_licences' => [
                'label' => __( 'Who can issue licences?', 'licencepress' ),
                'description' => __( 'Minimum capability required to issue new licence records.', 'licencepress' ),
                'tooltip' => __( 'Only trusted administrators or licence managers should issue keys.', 'licencepress' ),
            ],
            'revoke_licences' => [
                'label' => __( 'Who can revoke licences?', 'licencepress' ),
                'description' => __( 'Minimum capability required to revoke or disable active licences.', 'licencepress' ),
                'tooltip' => __( 'Revocations are security-sensitive and should be tightly controlled.', 'licencepress' ),
            ],
            'export_data' => [
                'label' => __( 'Who can export licence data?', 'licencepress' ),
                'description' => __( 'Minimum capability required to export licence records and backups.', 'licencepress' ),
                'tooltip' => __( 'Exports should require password protection and strong security checks.', 'licencepress' ),
            ],
            'review_security' => [
                'label' => __( 'Who can review security logs?', 'licencepress' ),
                'description' => __( 'Minimum capability required to inspect validation and audit activity.', 'licencepress' ),
                'tooltip' => __( 'Use an administrator-level role for security and compliance review.', 'licencepress' ),
            ],
        ];

        foreach ( $fields as $key => $field ) {
            $key = SanitizationHelper::key( $key );
            $id = 'licencepress-access-' . $key;
            $name = 'licencepress_access[' . $key . ']';
            $options = [
                [ 'value' => 'manage_options', 'label' => __( 'Administrators', 'licencepress' ) ],
                [ 'value' => 'edit_posts', 'label' => __( 'Editors', 'licencepress' ) ],
                [ 'value' => 'publish_posts', 'label' => __( 'Authors', 'licencepress' ) ],
            ];
            $current = $values[ $key ] ?? 'manage_options';
            $current = is_array( $current ) ? $current : [ $current ];
            $current = array_values( array_filter( array_map( 'sanitize_key', $current ) ) );
            $selected = [];
            foreach ( $options as $option ) {
                if ( in_array( $option['value'], $current, true ) ) {
                    $selected[] = $option;
                }
            }
            if ( empty( $selected ) ) {
                $selected[] = $options[0];
            }

            echo '<tr><th scope="row">' . FormFieldHelper::label( $id, $field['label'], $field ) . '</th><td>' . FormFieldHelper::select( $name, $options, $selected[0]['value'], [ 'id' => $id ] ) . '</td></tr>';
        }
    }
}
