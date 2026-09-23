<?php

namespace LicencePress\Includes\Plugins\Stripe\Admin;

use LicencePress\Includes\Functions\Helpers\FormFieldHelper;
use LicencePress\Includes\Settings\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class BillingSettingsStripe {
	/**
	 * Register the billing tab with the shared billing settings page.
	 *
	 * @param array<string, mixed> $tabs Existing tabs.
	 * @return array<string, mixed> Updated tabs.
	 */
	public function register_billing_tab( array $tabs ): array {
		$tabs['stripe'] = array(
			'label'    => __( 'Stripe', 'licencepress' ),
			'callback' => array( $this, 'render_stripe_tab' ),
		);

		return $tabs;
	}

	/**
	 * Render the Stripe billing configuration tab.
	 *
	 * @param array<string, mixed> $values Current values.
	 * @return void
	 */
	public function render_stripe_tab( array $values = array() ): void {
		$group = 'stripe';
		$values = array_merge( Settings::get_group( $group, array() ) ?? array(), $values );
		$environment = sanitize_key( (string) ( $values['stripe_environment'] ?? 'sandbox' ) );
		$environment = in_array( $environment, array( 'sandbox', 'live' ), true ) ? $environment : 'sandbox';
		$connection_status = ! empty( $values[ 'stripe_api_' . $environment . '_connected' ] ) ? __( 'Connected', 'licencepress' ) : __( 'Not connected', 'licencepress' );
		$fields = array(
			array(
				'key'     => 'stripe_checkout_enabled',
				'label'   => __( 'Enable checkout', 'licencepress' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			array(
				'key'     => 'stripe_environment',
				'label'   => __( 'Stripe environment', 'licencepress' ),
				'type'    => 'select',
				'options' => array(
					'sandbox' => __( 'Sandbox', 'licencepress' ),
					'live'    => __( 'Live', 'licencepress' ),
				),
				'default' => 'sandbox',
			),
			array(
				'key'     => 'stripe_currency',
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
				'key'     => 'stripe_subscriptions_enabled',
				'label'   => __( 'Enable subscriptions', 'licencepress' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			array(
				'key'         => 'stripe_api_live_secret_key',
				'label'       => __( 'Live secret key', 'licencepress' ),
				'type'        => 'password',
				'description' => __( 'Your live Stripe secret key. It is encrypted before storage.', 'licencepress' ),
				'default'     => '',
			),
			array(
				'key'         => 'stripe_api_sandbox_secret_key',
				'label'       => __( 'Sandbox secret key', 'licencepress' ),
				'type'        => 'password',
				'description' => __( 'Your sandbox Stripe secret key. It is encrypted before storage.', 'licencepress' ),
				'default'     => '',
			),
		);

		$feature_sections = array(
			array(
				'label' => __( 'Checkout & wallets', 'licencepress' ),
				'items' => array(
					'stripe_feature_one_time_payments'    => __( 'One-time payments', 'licencepress' ),
					'stripe_feature_saved_payment_methods' => __( 'Saved payment methods', 'licencepress' ),
					'stripe_feature_apple_pay'            => __( 'Apple Pay', 'licencepress' ),
					'stripe_feature_google_pay'           => __( 'Google Pay', 'licencepress' ),
					'stripe_feature_advanced_cards'       => __( 'Advanced credit & debit cards', 'licencepress' ),
					'stripe_feature_fastlane'             => __( 'Fastlane', 'licencepress' ),
				),
			),
			array(
				'label' => __( 'Subscriptions & billing', 'licencepress' ),
				'items' => array(
					'stripe_feature_subscription_billing' => __( 'Subscription billing', 'licencepress' ),
					'stripe_feature_invoicing'            => __( 'Invoicing', 'licencepress' ),
					'stripe_feature_refunds'              => __( 'Refunds', 'licencepress' ),
					'stripe_feature_transaction_search'   => __( 'Transaction searches', 'licencepress' ),
					'stripe_feature_customer_disputes'    => __( 'Customer disputes', 'licencepress' ),
					'stripe_feature_ic_plus'              => __( 'IC+', 'licencepress' ),
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
							<p class="text-muted mb-0"><?php esc_html_e( 'Configure the Stripe REST app credentials and choose the mode used for billing operations.', 'licencepress' ); ?></p>
						</div>
						<span class="badge bg-<?php echo esc_attr( ! empty( $values[ 'stripe_api_' . $environment . '_connected' ] ) ? 'success' : 'secondary' ); ?>"><?php echo esc_html( $connection_status ); ?></span>
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
					<h3 class="h5 mb-3"><?php esc_html_e( 'Stripe API features', 'licencepress' ); ?></h3>
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
				<button class="btn btn-primary" type="submit"><?php esc_html_e( 'Save Stripe settings', 'licencepress' ); ?></button>
			</div>
		</form>
		<?php
	}
}
