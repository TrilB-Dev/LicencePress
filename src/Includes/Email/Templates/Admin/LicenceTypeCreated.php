<?php
/**
 * Admin email template for newly created licence types.
 *
 * @package LicencePress
 */

namespace LicencePress\Includes\Email\Templates\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LicenceTypeCreated extends EmailTemplate {
	/** @var string */
	protected string $template_name = 'LicenceTypeCreated';

	public function render( array $context = array() ): string {
		$values      = array_merge( $this->context, $context );
		$name        = $this->text( $values['name'] ?? __( 'Licence type', 'licencepress' ) );
		$slug        = $this->text( $values['slug'] ?? '' );
		$description = $this->text( $values['description'] ?? '' );

		$html = '<h2 style="margin: 0 0 12px;">' . esc_html__( 'Licence type created', 'licencepress' ) . '</h2>
            <p style="margin: 0 0 12px;">' . esc_html__( 'A new licence type has been created in LicencePress.', 'licencepress' ) . '</p>
            <ul style="margin: 0 0 12px; padding-left: 20px;">
                <li><strong>' . esc_html__( 'Name', 'licencepress' ) . ':</strong> ' . esc_html( $name ) . '</li>
                <li><strong>' . esc_html__( 'Slug', 'licencepress' ) . ':</strong> ' . esc_html( $slug ) . '</li>
            </ul>
            <p style="margin: 0;">' . esc_html( $description ) . '</p>';

		return $this->wrap( $html );
	}
}
