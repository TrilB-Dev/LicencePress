<?php
/**
 * Billing settings interface for LicencePress.
 *
 * @package LicencePress
 * @subpackage Admin\Manager\Settings
 * @since 1.0.0
 */
namespace LicencePress\Admin\Manager\Settings;

use LicencePress\Includes\Functions\Helpers\FormFieldHelper;
use LicencePress\Includes\Functions\Helpers\RequestHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class SettingsBilling extends SettingsManager {
	/**
	 * Render the billing settings page with horizontal tabs.
	 *
	 * @param array<string, mixed> $values Existing values.
	 * @return void
	 */
	public function render( array $values = array() ): void {
		$values = wp_parse_args(
			$values,
			array(
				'billing_name'       => '',
				'billing_address_1'  => '',
				'billing_address_2'  => '',
				'town'               => '',
				'county_state'       => '',
				'country'            => '',
				'vat_number'         => '',
				'email_address'      => '',
				'phone_number'       => '',
				'invoice_prefix'     => '',
				'invoice_logo'       => '',
				'invoice_style'      => '',
			)
		);

		$active_tab = sanitize_key( RequestHelper::get_key( 'billing_tab', 'general' ) );
		$tabs       = array(
			'general' => array(
				'label'    => __( 'General Settings', 'licencepress' ),
				'callback' => array( $this, 'render_general_tab' ),
			),
			'invoice' => array(
				'label'    => __( 'Invoice Settings', 'licencepress' ),
				'callback' => array( $this, 'render_invoice_tab' ),
			),
		);

		$tabs = apply_filters( 'licencepress_billing_settings_tabs', $tabs );
		do_action_ref_array( 'licencepress_register_billing_settings_tabs', array( &$tabs ) );

		if ( ! isset( $tabs[ $active_tab ] ) ) {
			$active_tab = 'general';
		}
		?>
		<div class="card shadow-sm">
			<div class="card-body">
				<div class="mb-3">
					<h5 class="h5 mb-1"><?php esc_html_e( 'Billing settings', 'licencepress' ); ?></h5>
					<p class="text-secondary mb-0"><?php esc_html_e( 'Configure the default billing profile and invoice appearance for generated customer invoices.', 'licencepress' ); ?></p>
				</div>
				<ul class="nav nav-tabs mb-3" role="tablist">
					<?php foreach ( $tabs as $slug => $tab ) : ?>
						<li class="nav-item" role="presentation">
							<a class="nav-link <?php echo esc_attr( $slug === $active_tab ? 'active' : '' ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=licencepress-settings&tab=billing&billing_tab=' . rawurlencode( $slug ) ) ); ?>" aria-selected="<?php echo esc_attr( $slug === $active_tab ? 'true' : 'false' ); ?>">
								<?php echo esc_html( $tab['label'] ?? ucfirst( str_replace( '-', ' ', $slug ) ) ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
				<div class="tab-content">
					<?php if ( ! empty( $tabs[ $active_tab ]['callback'] ) && is_callable( $tabs[ $active_tab ]['callback'] ) ) : ?>
						<?php call_user_func( $tabs[ $active_tab ]['callback'], $values ); ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the general billing configuration tab.
	 *
	 * @param array<string, mixed> $values Current values.
	 * @return void
	 */
	public function render_general_tab( array $values = array() ): void {
		$values = wp_parse_args(
			$values,
			array(
				'billing_name'      => '',
				'billing_address_1' => '',
				'billing_address_2' => '',
				'town'              => '',
				'county_state'      => '',
				'country'           => '',
				'vat_number'        => '',
				'email_address'     => '',
				'phone_number'      => '',
				'invoice_prefix'    => '',
				'invoice_logo'      => '',
			)
		);
		?>
		<form method="post" action="" class="licencepress-settings-form">
			<?php wp_nonce_field( 'licencepress_billing_general', 'licencepress_billing_general_nonce' ); ?>
			<?php echo FormFieldHelper::input( 'action', 'licencepress_save_billing_settings', array( 'type' => 'hidden' ) ); ?>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><?php echo FormFieldHelper::label( 'licencepress-billing-name', __( 'Billing Name', 'licencepress' ) ); ?></th>
						<td><?php echo FormFieldHelper::text_input( 'licencepress_billing[billing_name]', (string) ( $values['billing_name'] ?? '' ), array( 'id' => 'licencepress-billing-name', 'class' => 'w-100' ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php echo FormFieldHelper::label( 'licencepress-billing-address-1', __( 'Billing Address 1', 'licencepress' ) ); ?></th>
						<td><?php echo FormFieldHelper::text_input( 'licencepress_billing[billing_address_1]', (string) ( $values['billing_address_1'] ?? '' ), array( 'id' => 'licencepress-billing-address-1', 'class' => 'w-100', 'placeholder' => __( 'Street address', 'licencepress' ) ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php echo FormFieldHelper::label( 'licencepress-billing-address-2', __( 'Billing Address 2', 'licencepress' ) ); ?></th>
						<td><?php echo FormFieldHelper::text_input( 'licencepress_billing[billing_address_2]', (string) ( $values['billing_address_2'] ?? '' ), array( 'id' => 'licencepress-billing-address-2', 'class' => 'w-100', 'placeholder' => __( 'Apartment, suite, unit', 'licencepress' ) ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php echo FormFieldHelper::label( 'licencepress-billing-town', __( 'Town', 'licencepress' ) ); ?></th>
						<td><?php echo FormFieldHelper::text_input( 'licencepress_billing[town]', (string) ( $values['town'] ?? '' ), array( 'id' => 'licencepress-billing-town', 'class' => 'w-100' ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php echo FormFieldHelper::label( 'licencepress-billing-county-state', __( 'County/State', 'licencepress' ) ); ?></th>
						<td><?php echo FormFieldHelper::text_input( 'licencepress_billing[county_state]', (string) ( $values['county_state'] ?? '' ), array( 'id' => 'licencepress-billing-county-state', 'class' => 'w-100' ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php echo FormFieldHelper::label( 'licencepress-billing-country', __( 'Country', 'licencepress' ) ); ?></th>
						<td><?php echo FormFieldHelper::text_input( 'licencepress_billing[country]', (string) ( $values['country'] ?? '' ), array( 'id' => 'licencepress-billing-country', 'class' => 'w-100' ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php echo FormFieldHelper::label( 'licencepress-billing-vat-number', __( 'VAT Number', 'licencepress' ) ); ?></th>
						<td><?php echo FormFieldHelper::text_input( 'licencepress_billing[vat_number]', (string) ( $values['vat_number'] ?? '' ), array( 'id' => 'licencepress-billing-vat-number', 'class' => 'w-100' ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php echo FormFieldHelper::label( 'licencepress-billing-email-address', __( 'Email Address', 'licencepress' ) ); ?></th>
						<td><?php echo FormFieldHelper::text_input( 'licencepress_billing[email_address]', (string) ( $values['email_address'] ?? '' ), array( 'id' => 'licencepress-billing-email-address', 'type' => 'email', 'class' => 'w-100' ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php echo FormFieldHelper::label( 'licencepress-billing-phone-number', __( 'Phone Number', 'licencepress' ) ); ?></th>
						<td><?php echo FormFieldHelper::text_input( 'licencepress_billing[phone_number]', (string) ( $values['phone_number'] ?? '' ), array( 'id' => 'licencepress-billing-phone-number', 'type' => 'tel', 'class' => 'w-100' ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php echo FormFieldHelper::label( 'licencepress-billing-invoice-prefix', __( 'Invoice Prefix', 'licencepress' ) ); ?></th>
						<td><?php echo FormFieldHelper::text_input( 'licencepress_billing[invoice_prefix]', (string) ( $values['invoice_prefix'] ?? '' ), array( 'id' => 'licencepress-billing-invoice-prefix', 'class' => 'w-100', 'placeholder' => 'INV-' ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php echo FormFieldHelper::label( 'licencepress-billing-invoice-logo', __( 'Invoice Logo', 'licencepress' ) ); ?></th>
						<td>
							<?php echo FormFieldHelper::input( 'licencepress_billing[invoice_logo]', '', array( 'id' => 'licencepress-billing-invoice-logo', 'type' => 'file', 'class' => 'form-control', 'accept' => 'image/*' ) ); ?>
							<?php if ( ! empty( $values['invoice_logo'] ) ) : ?>
								<p class="mb-0 mt-2 text-muted"><?php echo esc_html( (string) $values['invoice_logo'] ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
				</tbody>
			</table>
			<?php submit_button( __( 'Save Billing Settings', 'licencepress' ) ); ?>
		</form>
		<?php
	}

	/**
	 * Render the invoice styling tab powered by TinyMCE.
	 *
	 * @param array<string, mixed> $values Current values.
	 * @return void
	 */
	public function render_invoice_tab( array $values = array() ): void {
		$values = wp_parse_args(
			$values,
			array(
				'invoice_style' => '',
			)
		);
		?>
		<form method="post" action="" class="licencepress-settings-form">
			<?php wp_nonce_field( 'licencepress_billing_invoice', 'licencepress_billing_invoice_nonce' ); ?>
			<?php echo FormFieldHelper::input( 'action', 'licencepress_save_billing_invoice_settings', array( 'type' => 'hidden' ) ); ?>
			<div class="mb-3">
				<?php FormFieldHelper::tinymce( 'licencepress_billing_invoice_style', 'licencepress_billing[invoice_style]', __( 'Invoice styling and terms', 'licencepress' ), (string) ( $values['invoice_style'] ?? '' ), 14, false ); ?>
				<p class="text-secondary mt-2 mb-0"><?php esc_html_e( 'Use TinyMCE to format invoice notes, payment terms, company branding, and custom invoice content.', 'licencepress' ); ?></p>
			</div>
			<?php submit_button( __( 'Save Invoice Settings', 'licencepress' ) ); ?>
		</form>
		<?php
	}
}