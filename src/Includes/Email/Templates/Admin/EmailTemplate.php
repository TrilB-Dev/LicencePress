<?php
/**
 * Base email template for admin notifications.
 *
 * @package LicencePress
 */

namespace LicencePress\Includes\Email\Templates\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

abstract class EmailTemplate {
    /**
     * Email context data.
     *
     * @var array<string, mixed>
     */
    protected array $context = [];

    /**
     * Template identifier used for the default subject prefix.
     */
    protected string $template_name = '';

    public function __construct( array $context = [] ) {
        $this->context = $context;
    }

    /**
     * Return the default email subject.
     */
    public function subject(): string {
        $template_name = $this->template_name ?: 'Email';
        return sprintf( 'LPEmail-Admin-%s', $template_name );
    }

    /**
     * Build the rendered email HTML body.
     *
     * @param array<string, mixed> $context Context variables for the template.
     * @return string
     */
    abstract public function render( array $context = [] ): string;

    /**
     * Resolve a contextual value with a fallback.
     *
     * @param string $key Value key.
     * @param mixed  $fallback Fallback if the key is missing.
     * @return mixed
     */
    protected function value( string $key, $fallback = '' ) {
        if ( array_key_exists( $key, $this->context ) ) {
            return $this->context[ $key ];
        }

        return $fallback;
    }

    /**
     * Return a safe escaped string for email content.
     *
     * @param mixed $value Raw value.
     * @return string
     */
    protected function text( $value ): string {
        if ( is_scalar( $value ) ) {
            return (string) $value;
        }

        if ( is_array( $value ) ) {
            return wp_json_encode( $value );
        }

        return '';
    }

    /**
     * Resolve placeholders in a string like {{ name }}.
     *
     * @param string $content Template string.
     * @param array<string, mixed> $context Replacement context.
     * @return string
     */
    protected function interpolate( string $content, array $context = [] ): string {
        foreach ( $context as $key => $value ) {
            $content = str_replace( '{{ ' . $key . ' }}', (string) $value, $content );
            $content = str_replace( '{{' . $key . '}}', (string) $value, $content );
            $content = str_replace( '{' . $key . '}', (string) $value, $content );
        }

        return $content;
    }

    /**
     * Wrap rendered HTML in a consistent email shell.
     *
     * @param string $content Inner HTML.
     * @return string
     */
    protected function wrap( string $content ): string {
        $brand = __( 'LicencePress', 'licencepress' );

        return sprintf(
            '<div style="font-family: Arial, Helvetica, sans-serif; line-height: 1.6; color: #1f2937; background: #f9fafb; padding: 24px;">
                <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
                    <div style="background: #111827; color: #ffffff; padding: 18px 24px; font-weight: 700; letter-spacing: 0.03em;">%1$s</div>
                    <div style="padding: 24px;">%2$s</div>
                </div>
            </div>',
            esc_html( $brand ),
            $content
        );
    }
}
