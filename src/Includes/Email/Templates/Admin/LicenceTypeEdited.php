<?php
/**
 * Admin email template for edited licence types.
 *
 * @package LicencePress
 */

namespace LicencePress\Includes\Email\Templates\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LicenceTypeEdited extends EmailTemplate {
	/** @var string */
	protected string $template_name = 'LicenceTypeEdited';

	public function render( array $context = array() ): string {
		$values      = array_merge( $this->context, $context );
		$name        = $this->text( $values['name'] ?? __( 'Licence type', 'licencepress' ) );
		$description = $this->text( $values['description'] ?? '' );

		$html = '<h2 style="margin: 0 0 12px;">' . esc_html__( 'Licence type updated', 'licencepress' ) . '</h2>
            <p style="margin: 0 0 12px;">' . esc_html__( 'A LicencePress licence type has been edited.', 'licencepress' ) . '</p>
            <p style="margin: 0 0 12px;"><strong>' . esc_html__( 'Name', 'licencepress' ) . ':</strong> ' . esc_html( $name ) . '</p>
            <p style="margin: 0;">' . esc_html( $description ) . '</p>';

		return $this->wrap( $html );
	}
}
