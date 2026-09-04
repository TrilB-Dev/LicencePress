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
	/** @var string */
	protected string $template_name = 'LicenceTypeRetired';

	public function render( array $context = array() ): string {
		$values = array_merge( $this->context, $context );
		$name   = $this->text( $values['name'] ?? __( 'Licence type', 'licencepress' ) );
		if ( ! empty( $values['retired'] ) ) {
			$message = __( 'retired', 'licencepress' );
		} else {
			$message = __( 'reactivated', 'licencepress' );
		}

		/* translators: %s is the licence type status label. */
		$status_message = sprintf( __( 'This licence type has been %s.', 'licencepress' ), $message );

		$html = '<h2 style="margin: 0 0 12px;">' . esc_html__( 'Licence type status changed', 'licencepress' ) . '</h2>
            <p style="margin: 0 0 12px;">' . esc_html( $status_message ) . '</p>
            <p style="margin: 0;"><strong>' . esc_html__( 'Name', 'licencepress' ) . ':</strong> ' . esc_html( $name ) . '</p>';

		return $this->wrap( $html );
	}
}
