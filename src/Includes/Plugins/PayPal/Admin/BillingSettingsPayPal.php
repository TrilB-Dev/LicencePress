<?php

namespace LicencePress\Includes\Plugins\PayPal\Admin;

use LicencePress\Includes\Functions\Helpers\FormFieldHelper;
use LicencePress\Includes\Settings\Settings as CoreSettings;
use LicencePress\Includes\Plugins\PayPal\Includes\Settings\Settings as PayPalSettings;

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
		$group = 'paypal';
		$values = array_merge( CoreSettings::get_group( $group, array() ) ?? array(), $values );
		$environment = sanitize_key( (string) ( $values['paypal_environment'] ?? 'sandbox' ) );
		$environment = in_array( $environment, array( 'sandbox', 'live' ), true ) ? $environment : 'sandbox';
		$connection_status = PayPalSettings::is_oauth_connected( $environment ) ? __( 'Connected', 'licencepress' ) : __( 'Not connected', 'licencepress' );
		$fields = array(
			array(
				'key'     => 'paypal_checkout_enabled',
				'label'   => __( 'Enable checkout', 'licencepress' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			array(
				'key'     => 'paypal_environment',
				'label'   => __( 'PayPal environment', 'licencepress' ),
				'type'    => 'select',
				'options' => array(
					'sandbox' => __( 'Sandbox', 'licencepress' ),
					'live'    => __( 'Live', 'licencepress' ),
				),
				'default' => 'sandbox',
			),
			array(
				'key'     => 'paypal_currency',
				'label'   => __( 'Default currency', 'licencepress' ),
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
				'label'   => __( 'Enable subscriptions', 'licencepress' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			array(
				'key'         => 'paypal_api_live_client_id',
				'label'       => __( 'Live client ID', 'licencepress' ),
				'type'        => 'text',
				'description' => __( 'Your live PayPal app client ID.', 'licencepress' ),
				'default'     => '',
			),
			array(
				'key'         => 'paypal_api_live_client_secret',
				'label'       => __( 'Live client secret', 'licencepress' ),
				'type'        => 'password',
				'description' => __( 'Your live PayPal app secret. It is encrypted before storage.', 'licencepress' ),
				'default'     => '',
			),
			array(
				'key'         => 'paypal_api_sandbox_client_id',
				'label'       => __( 'Sandbox client ID', 'licencepress' ),
				'type'        => 'text',
				'description' => __( 'Your sandbox PayPal app client ID.', 'licencepress' ),
				'default'     => '',
			),
			array(
				'key'         => 'paypal_api_sandbox_client_secret',
				'label'       => __( 'Sandbox client secret', 'licencepress' ),
				'type'        => 'password',
				'description' => __( 'Your sandbox PayPal app secret. It is encrypted before storage.', 'licencepress' ),
				'default'     => '',
			),
		);

		$feature_sections = array(
			array(
				'label' => __( 'Checkout & wallets', 'licencepress' ),
				'items' => array(
					'paypal_feature_one_time_payments'    => __( 'One-time payments', 'licencepress' ),
					'paypal_feature_saved_payment_methods' => __( 'Saved payment methods', 'licencepress' ),
					'paypal_feature_apple_pay'            => __( 'Apple Pay', 'licencepress' ),
					'paypal_feature_google_pay'           => __( 'Google Pay', 'licencepress' ),
					'paypal_feature_advanced_cards'       => __( 'Advanced credit & debit cards', 'licencepress' ),
					'paypal_feature_fastlane'             => __( 'Fastlane', 'licencepress' ),
				),
			),
			array(
				'label' => __( 'Subscriptions & billing', 'licencepress' ),
				'items' => array(
					'paypal_feature_subscription_billing' => __( 'Subscription billing', 'licencepress' ),
					'paypal_feature_invoicing'            => __( 'Invoicing', 'licencepress' ),
					'paypal_feature_refunds'              => __( 'Refunds', 'licencepress' ),
					'paypal_feature_transaction_search'   => __( 'Transaction searches', 'licencepress' ),
					'paypal_feature_customer_disputes'    => __( 'Customer disputes', 'licencepress' ),
					'paypal_feature_ic_plus'              => __( 'IC+', 'licencepress' ),
				),
			),
		);
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
			<div class="card mb-4" style="max-width: 980px;">
				<div class="card-body">
					<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
						<div>
							<h3 class="h5 mb-1"><?php esc_html_e( 'Connection & environment', 'licencepress' ); ?></h3>
							<p class="text-muted mb-0"><?php esc_html_e( 'Configure the PayPal REST app credentials and choose the mode used for billing operations.', 'licencepress' ); ?></p>
						</div>
						<span class="badge bg-<?php echo esc_attr( PayPalSettings::is_oauth_connected( $environment ) ? 'success' : 'secondary' ); ?>"><?php echo esc_html( $connection_status ); ?></span>
					</div>
					<div class="row g-3 align-items-end">
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
							<div class="col-md-6">
								<div class="mb-3">
									<?php echo FormFieldHelper::label( 'licencepress-' . $key, (string) ( $field['label'] ?? $key ), array( 'description' => (string) ( $field['description'] ?? '' ) ) ); ?>
									<?php if ( 'select' === $type ) : ?>
										<?php echo FormFieldHelper::select( $name, (array) ( $field['options'] ?? array() ), $field_value, array( 'id' => 'licencepress-' . $key, 'class' => 'form-select' ) ); ?>
									<?php elseif ( in_array( $type, array( 'text', 'password' ), true ) ) : ?>
										<?php echo FormFieldHelper::input(
											$name,
											is_scalar( $field_value ) ? (string) $field_value : '',
											array(
												'id'    => 'licencepress-' . $key,
												'type'  => $type,
												'class' => 'w-100',
											)
										); ?>
									<?php else : ?>
										<?php echo FormFieldHelper::checkbox( $name, '1', '', array( 'id' => 'licencepress-' . $key, 'checked' => ! empty( $field_value ) ) ); ?>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<div class="card mb-4" style="max-width: 980px;">
				<div class="card-body">
					<h3 class="h5 mb-3"><?php esc_html_e( 'PayPal API features', 'licencepress' ); ?></h3>
					<?php foreach ( $feature_sections as $section ) : ?>
						<div class="mb-4">
							<h4 class="h6 mb-3"><?php echo esc_html( $section['label'] ?? '' ); ?></h4>
							<div class="row g-3">
								<?php foreach ( $section['items'] as $key => $label ) : ?>
									<?php $name = 'licencepress_' . sanitize_key( (string) $group ) . '[' . sanitize_key( (string) $key ) . ']'; ?>
									<div class="col-md-6">
										<div class="form-check form-switch p-0">
											<?php echo FormFieldHelper::checkbox( $name, '1', '', array( 'id' => 'licencepress-' . sanitize_key( (string) $key ), 'checked' => ! empty( $values[ sanitize_key( (string) $key ) ] ) ) ); ?>
											<?php echo FormFieldHelper::label( 'licencepress-' . sanitize_key( (string) $key ), (string) $label ); ?>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="d-flex justify-content-end">
				<button class="btn btn-primary" type="submit"><?php esc_html_e( 'Save PayPal settings', 'licencepress' ); ?></button>
			</div>
		</form>
		<?php
	}
}
