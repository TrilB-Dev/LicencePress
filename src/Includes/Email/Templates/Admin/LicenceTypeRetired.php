<?php
/**
 * Admin email template for retired licence types.
 *
 * @package LicencePress
 */

namespace LicencePress\Includes\Email\Templates\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class LicenceTypeRetired extends EmailTemplate {
    protected string $template_name = 'LicenceTypeRetired';

    public function render( array $context = [] ): string {
        $values = array_merge( $this->context, $context );
        $name = $this->text( $values['name'] ?? __( 'Licence type', 'licencepress' ) );
        $message = ! empty( $values['retired'] ) ? __( 'retired', 'licencepress' ) : __( 'reactivated', 'licencepress' );

        $html = '<h2 style="margin: 0 0 12px;">' . esc_html__( 'Licence type status changed', 'licencepress' ) . '</h2>
            <p style="margin: 0 0 12px;">' . esc_html( sprintf( __( 'This licence type has been %s.', 'licencepress' ), $message ) ) . '</p>
            <p style="margin: 0;"><strong>' . esc_html__( 'Name', 'licencepress' ) . ':</strong> ' . esc_html( $name ) . '</p>';

        return $this->wrap( $html );
    }
}
