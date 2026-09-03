<?php
/**
 * Settings general fields.
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
        $fields = [
            'default_licence_lifetime' => [
                'label' => __( 'Default licence lifetime', 'licencepress' ),
                'description' => __( 'Length of time a new licence remains valid before expiry.', 'licencepress' ),
                'tooltip' => __( 'Use this as the default when issuing new licences.', 'licencepress' ),
            ],
            'renewal_policy' => [
                'label' => __( 'Renewal policy', 'licencepress' ),
                'description' => __( 'Choose how new licences are renewed or extended.', 'licencepress' ),
                'tooltip' => __( 'The policy is used by your billing and compliance workflow.', 'licencepress' ),
            ],
            'default_product' => [
                'label' => __( 'Default product', 'licencepress' ),
                'description' => __( 'The primary product assigned to new licences.', 'licencepress' ),
                'tooltip' => __( 'Every new licence can inherit this product unless overridden.', 'licencepress' ),
            ],
            'validation_domain_mode' => [
                'label' => __( 'Validation domain mode', 'licencepress' ),
                'description' => __( 'Controls how licences are bound to the customer install domain.', 'licencepress' ),
                'tooltip' => __( 'This protects against unauthorised licence reuse across domains.', 'licencepress' ),
            ],
            'allow_token_export' => [
                'label' => __( 'Allow token export', 'licencepress' ),
                'description' => __( 'Allow privileged staff to export active licence data.', 'licencepress' ),
                'tooltip' => __( 'Exports should remain encrypted and password-protected.', 'licencepress' ),
                'type' => 'checkbox',
                'default' => true,
            ],
            'require_approval' => [
                'label' => __( 'Require approval for new licences', 'licencepress' ),
                'description' => __( 'Require a second approval step before issuing a licence.', 'licencepress' ),
                'tooltip' => __( 'Use this to tighten your internal approval workflow.', 'licencepress' ),
                'type' => 'checkbox',
                'default' => false,
            ],
        ];

        foreach ( $fields as $key => $field ) {
            $key = SanitizationHelper::key( $key );
            $id = 'licencepress-' . $key;
            $name = 'licencepress_general[' . $key . ']';
            $value = $values[ $key ] ?? $field['default'] ?? '';
            ?>
            <tr>
                <th scope="row"><?php echo FormFieldHelper::label( $id, $field['label'], $field ); ?></th>
                <td>
                    <?php
                    if ( 'checkbox' === ( $field['type'] ?? '' ) ) {
                        echo FormFieldHelper::checkbox(
                            $name,
                            '1',
                            $field['label'],
                            [
                                'id' => $id,
                                'checked' => ! empty( $value ),
                            ]
                        );
                    } else {
                        echo FormFieldHelper::text_input(
                            $name,
                            is_scalar( $value ) ? (string) $value : '',
                            [ 'id' => $id ]
                        );
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
    }
}
