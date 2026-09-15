<?php

namespace LicencePress\Includes\Plugins\PayPal\Admin;

use LicencePress\Includes\Functions\Helpers\FormFieldHelper;
use LicencePress\Includes\Settings\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class BillingSettingsPayPal {
	/**
	 * Register the billing tab with the shared billing settings page.
	 *
	 * @param array<string, mixed> $tabs Existing tabs.
	 * @return array<string, mixed> Updated tabs.
	 */
	public function register_billing_tab( array $tabs ): array {
		$tabs['paypal'] = array(
			'label'    => __( 'PayPal', 'licencepress' ),
			'callback' => array( $this, 'render_paypal_tab' ),
		);

		return $tabs;
	}

	/**
	 * Render the PayPal billing configuration tab.
	 *
	 * @param array<string, mixed> $values Current values.
	 * @return void
	 */
	public function render_paypal_tab( array $values = array() ): void {
		$group  = 'paypal';
		$fields = array(
			array(
				'key'     => 'paypal_checkout_enabled',
				'label'   => __( 'Enable PayPal Payment Gateway', 'licencepress' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			array(
				'key'     => 'paypal_environment',
				'label'   => __( 'PayPal Environment', 'licencepress' ),
				'type'    => 'select',
				'options' => array(
					'sandbox' => __( 'Sandbox', 'licencepress' ),
					'live'    => __( 'Live', 'licencepress' ),
				),
				'default' => 'sandbox',
			),
			array(
				'key'     => 'paypal_currency',
				'label'   => __( 'PayPal Currency', 'licencepress' ),
				'type'    => 'select',
				'options' => array(
					'USD' => 'USD',
					'EUR' => 'EUR',
					'GBP' => 'GBP',
					'AUD' => 'AUD',
					'CAD' => 'CAD',
					'JPY' => 'JPY',
				),
				'default' => 'USD',
			),
			array(
				'key'     => 'paypal_subscriptions_enabled',
				'label'   => __( 'Enable Subscriptions', 'licencepress' ),
				'type'    => 'checkbox',
				'default' => false,
			),
		);

		$values = Settings::get_group( $group, array() ) ?? array();
		?>
		<form method="post" action="" class="licencepress-settings-form">
			<?php wp_nonce_field( 'licencepress_billing_general', 'licencepress_billing_general_nonce' ); ?>
			<?php echo FormFieldHelper::input(
				'action',
				'licencepress_save_billing_settings',
				array(
					'type' => 'hidden',
				)
			); ?>
			<?php foreach ( $fields as $field ) : ?>
				<?php
				$key = isset( $field['key'] ) ? sanitize_key( (string) $field['key'] ) : '';
				if ( '' === $key ) {
					continue;
				}
				$name = 'licencepress_' . sanitize_key( (string) $group ) . '[' . $key . ']';
				$field_value = $values[ $key ] ?? ( $field['default'] ?? '' );
				$type = sanitize_key( (string) ( $field['type'] ?? 'checkbox' ), 'checkbox' );
				?>
				<div class="mb-3">
					<?php echo FormFieldHelper::label(
						'licencepress-' . $key,
						(string) ( $field['label'] ?? $key ),
						array(
							'description' => (string) ( $field['description'] ?? '' ),
						)
					); ?>
					<?php if ( 'select' === $type ) : ?>
						<?php echo FormFieldHelper::select(
							$name,
							(array) ( $field['options'] ?? array() ),
							$field_value,
							array( 'id' => 'licencepress-' . $key )
						); ?>
					<?php elseif ( 'text' === $type ) : ?>
						<?php echo FormFieldHelper::input(
							$name,
							is_scalar( $field_value ) ? (string) $field_value : '',
							array(
								'id'   => 'licencepress-' . $key,
								'type' => 'text',
							)
						); ?>
					<?php elseif ( 'custom' === $type ) : ?>
						<?php $render = $field['render'] ?? null; if ( is_callable( $render ) ) { call_user_func( $render, $field_value, $name, 'licencepress-' . $key ); } ?>
					<?php else : ?>
						<?php echo FormFieldHelper::checkbox(
							$name,
							'1',
							'',
							array(
								'id'      => 'licencepress-' . $key,
								'checked' => ! empty( $field_value ),
							)
						); ?>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
			<button class="btn btn-primary" type="submit"><?php esc_html_e( 'Save PayPal settings', 'licencepress' ); ?></button>
		</form>
		<?php
	}
}
