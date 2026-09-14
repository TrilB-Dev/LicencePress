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
use LicencePress\Includes\Plugins\TinyMCE\Includes\Functions\Helpers\TinyMCEHelper;
use LicencePress\Includes\Settings\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class SettingsBilling {
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
				'billing_name'        => '',
				'billing_address_1'   => '',
				'billing_address_2'   => '',
				'town'                => '',
				'country'             => '',
				'uk_county'           => '',
				'us_state'            => '',
				'other_county_state'  => '',
				'currency'            => 'GBP',
				'charge_vat'          => false,
				'vat_label'           => 'VAT',
				'vat_percentage'      => '20',
				'vat_number'          => '',
				'email_address'       => '',
				'phone_country_code'  => '',
				'phone_number'        => '',
				'prefix'              => '',
				'invoice_suffix'      => '',
				'receipt_suffix'      => '',
				'invoice_logo'        => '',
				'invoice_style'       => '',
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
				'billing_name'           => '',
				'billing_address_1'      => '',
				'billing_address_2'      => '',
				'town'                   => '',
				'country'                => '',
				'uk_county'              => '',
				'us_state'               => '',
				'other_county_state'     => '',
				'currency'               => 'GBP',
				'custom_currency_code'   => '',
				'custom_currency_symbol' => '',
				'charge_vat'             => false,
				'vat_label'              => 'VAT',
				'vat_percentage'         => '20',
				'vat_number'             => '',
				'email_address'          => '',
				'phone_country_code'     => '',
				'phone_number'           => '',
				'prefix'                 => '',
				'invoice_suffix'         => '',
				'receipt_suffix'         => '',
				'invoice_logo'           => '',
			)
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
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
                                'licencepress-billing-name', 
                                __( 'Billing Name', 'licencepress' ) 
                            ); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::text_input(
								'licencepress_billing[billing_name]',
								(string) ( $values['billing_name'] ?? '' ),
								array(
									'id' => 'licencepress-billing-name',
									'class' => 'w-100',
								)
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
                                'licencepress-billing-address-1', 
                                __( 'Billing Address 1', 'licencepress' ) 
                            ); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::text_input(
								'licencepress_billing[billing_address_1]',
								(string) ( $values['billing_address_1'] ?? '' ),
								array(
									'id' => 'licencepress-billing-address-1',
									'class' => 'w-100',
									'placeholder' => __( 'Street address', 'licencepress' ),
								)
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
                                'licencepress-billing-address-2', 
                                __( 'Billing Address 2', 'licencepress' ) 
                            ); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::text_input(
								'licencepress_billing[billing_address_2]',
								(string) ( $values['billing_address_2'] ?? '' ),
								array(
									'id' => 'licencepress-billing-address-2',
									'class' => 'w-100',
									'placeholder' => __( 'Apartment, suite, unit', 'licencepress' ),
								)
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
                                'licencepress-billing-country', 
                                __( 'Country', 'licencepress' ) 
                            ); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select(
								'licencepress_billing[country]',
								array(
									'data' => array(),
									'selected' => $values['country'] ?? '',
									'id' => 'licencepress-billing-country',
									'live_search' => true,
									'width' => '100%',
									'bscd_type' => 'country',
									'bscd_group' => true,
									'bscd_flags' => true,
								)
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
                                'licencepress-billing-town', 
                                __( 'Town', 'licencepress' ) 
                            ); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::text_input(
								'licencepress_billing[town]',
								(string) ( $values['town'] ?? '' ),
								array(
									'id' => 'licencepress-billing-town',
									'class' => 'w-100',
								)
							); ?>
						</td>
					</tr>
					<tr id="licencepress-billing-uk-county-row" hidden>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
                                'licencepress-billing-uk-county', 
                                __( 'County', 'licencepress' ) 
                            ); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select(
								'licencepress_billing[uk_county]',
								array(
									'data' => array(),
									'selected' => $values['uk_county'] ?? '',
									'id' => 'licencepress-billing-uk-county',
									'live_search' => true,
									'width' => '100%',
									'bscd_type' => 'uk-counties',
								)
							); ?>
						</td>
					</tr>
					<tr id="licencepress-billing-us-state-row" hidden>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
                                'licencepress-billing-us-state', 
                                __( 'State', 'licencepress' ) 
                            ); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select(
								'licencepress_billing[us_state]',
								array(
									'data' => array(),
									'selected' => $values['us_state'] ?? '',
									'id' => 'licencepress-billing-us-state',
									'live_search' => true,
									'width' => '100%',
									'bscd_type' => 'us-states',
								)
							); ?>
						</td>
					</tr>
					<tr id="licencepress-billing-other-county-state-row" hidden>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
                                'licencepress-billing-other-county-state', 
                                __( 'County/State', 'licencepress' ) 
                            ); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::text_input(
								'licencepress_billing[other_county_state]',
								(string) ( $values['other_county_state'] ?? '' ),
								array(
									'id' => 'licencepress-billing-other-county-state',
									'class' => 'w-100',
								)
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
                                'licencepress-billing-currency', 
                                __( 'Currency', 'licencepress' ) 
                            ); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select(
								'licencepress_billing[currency]',
								array(
									'data' => array(
										'GBP' => 'GBP - British Pound',
										'USD' => 'USD - US Dollar',
										'EUR' => 'EUR - Euro',
										'AUD' => 'AUD - Australian Dollar',
										'CAD' => 'CAD - Canadian Dollar',
										'CUSTOM' => 'CUSTOM - Custom Currency',
									),
									'selected' => $values['currency'] ?? 'GBP',
									'id' => 'licencepress-billing-currency',
									'live_search' => true,
									'width' => '100%',
								)
							); ?>
						</td>
					</tr>
					<tr id="licencepress-billing-custom-currency-code-row" hidden>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
                                'licencepress-billing-custom-currency-code', 
                                __( 'Custom Currency Code', 'licencepress' ) 
                            ); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::text_input(
								'licencepress_billing[custom_currency_code]',
								(string) ( $values['custom_currency_code'] ?? '' ),
								array(
									'id' => 'licencepress-billing-custom-currency-code',
									'class' => 'w-100',
									'placeholder' => 'AUD',
								)
							); ?>
						</td>
					</tr>
					<tr id="licencepress-billing-custom-currency-symbol-row" hidden>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
                                'licencepress-billing-custom-currency-symbol', 
                                __( 'Custom Currency Symbol', 'licencepress' ) 
                            ); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::text_input(
								'licencepress_billing[custom_currency_symbol]',
								(string) ( $values['custom_currency_symbol'] ?? '' ),
								array(
									'id' => 'licencepress-billing-custom-currency-symbol',
									'class' => 'w-100',
									'placeholder' => '$',
								)
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
                                'licencepress-billing-charge-vat', 
                                __( 'Charge VAT', 'licencepress' ) 
                            ); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::checkbox(
								'licencepress_billing[charge_vat]',
								'1',
								'',
								array(
									'checked' => ! empty( $values['charge_vat'] ),
									'id' => 'licencepress-billing-charge-vat',
								)
							); ?>
						</td>
					</tr>
					<tr id="licencepress-billing-vat-label-row" hidden>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
                                'licencepress-billing-vat-label', 
                                __( 'VAT Label', 'licencepress' ) 
                            ); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::text_input(
								'licencepress_billing[vat_label]',
								(string) ( $values['vat_label'] ?? 'VAT' ),
								array(
									'id' => 'licencepress-billing-vat-label',
									'class' => 'w-100',
								)
							); ?>
						</td>
					</tr>
					<tr id="licencepress-billing-vat-percent-row" hidden>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
                                'licencepress-billing-vat-percent', 
                                __( 'VAT %', 'licencepress' ) 
                            ); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::text_input(
								'licencepress_billing[vat_percentage]',
								(string) ( $values['vat_percentage'] ?? '20' ),
								array(
									'id' => 'licencepress-billing-vat-percent',
									'class' => 'w-100',
									'type' => 'number',
									'step' => '0.01',
									'min' => '0',
									'max' => '100',
								)
							); ?>
						</td>
					</tr>
					<tr id="licencepress-billing-vat-number-row" hidden>
						<th scope="row">
							<?php echo FormFieldHelper::label(
								'licencepress-billing-vat-number',
								__( 'VAT Number', 'licencepress' )
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::text_input(
								'licencepress_billing[vat_number]',
								(string) ( $values['vat_number'] ?? '' ),
								array(
									'id' => 'licencepress-billing-vat-number',
									'class' => 'w-100',
								)
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label(
								'licencepress-billing-email-address',
								__( 'Email Address', 'licencepress' )
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::text_input(
								'licencepress_billing[email_address]',
								(string) ( $values['email_address'] ?? '' ),
								array(
									'id' => 'licencepress-billing-email-address',
									'type' => 'email',
									'class' => 'w-100',
								)
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label(
								'licencepress-billing-phone-number',
								__( 'Phone Number', 'licencepress' )
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::input_group(
								FormFieldHelper::bootstrap_select(
									'licencepress_billing[phone_country_code]',
									array(
										'data' => array(),
										'selected' => $values['phone_country_code'] ?? '',
										'id' => 'licencepress-billing-phone-country-code',
										'bscd_type' => 'country-phone',
                                        'bscd_flags' => true,
                                        'class' => 'w-30',
									)
								) . FormFieldHelper::text_input(
									'licencepress_billing[phone_number]',
									(string) ( $values['phone_number'] ?? '' ),
									array(
										'id' => 'licencepress-billing-phone-number',
										'type' => 'tel',
										'class' => 'w-70',
									)
								),
								array( 'style' => 'width: 20%' )
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label(
								'licencepress-billing-prefix',
								__( 'Billing Prefix', 'licencepress' ),
								array(
									'description' => __( 'This prefix will be added to the beginning of both Invoice & Receipt numbers.', 'licencepress' )
								)
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::input_group(
								FormFieldHelper::text_input(
									'licencepress_billing[prefix]',
									(string) ( $values['prefix'] ?? '' ),
									array(
										'id' => 'licencepress-billing-prefix',
										'class' => 'w-70',
										'pattern' => '[A-Za-z0-9_-]{1,7}',
										'placeholder' => 'Company-'
									)
								) . '<span class="input-group-text" aria-label="Example billing prefix format">XXXXXXX</span>'
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label(
								'licencepress-billing-receipt-suffix',
								__( 'Receipt Suffix', 'licencepress' )
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::input_group(
								'<span class="input-group-text" aria-label="Example receipt suffix format">XXXXXXX</span>' .
								FormFieldHelper::text_input(
									'licencepress_billing[receipt_suffix]',
									(string) ( $values['receipt_suffix'] ?? '' ),
									array(
										'id' => 'licencepress-billing-receipt-suffix',
										'class' => 'w-70',
										'pattern' => '[A-Za-z0-9_-]{1,7}',
										'placeholder' => '-REC'
									)
								)
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label(
								'licencepress-billing-invoice-suffix',
								__( 'Invoice Suffix', 'licencepress' )
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::input_group(
								'<span class="input-group-text" aria-label="Example invoice suffix format">XXXXXXX</span>' .
								FormFieldHelper::text_input(
									'licencepress_billing[invoice_suffix]',
									(string) ( $values['invoice_suffix'] ?? '' ),
									array(
										'id' => 'licencepress-billing-invoice-suffix',
										'class' => 'w-70',
										'pattern' => '[A-Za-z0-9_-]{1,7}',
										'placeholder' => '-INV'
									)
								)
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
                                'licencepress-billing-invoice-logo', 
                                __( 'Invoice Logo', 'licencepress' ) 
                            ); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::input(
								'licencepress_billing[invoice_logo]',
								(string) ( $values['invoice_logo'] ?? '' ),
								array(
									'type' => 'hidden',
									'id'   => 'licencepress-billing-invoice-logo',
								)
							); ?>
							<?php echo FormFieldHelper::button(
								__( 'Choose image', 'licencepress' ),
								array(
									'type' => 'button',
									'class' => 'btn btn-secondary',
									'attributes' => array(
										'data-licencepress-media-select' => 'licencepress-billing-invoice-logo',
										'data-licencepress-media-title'  => __( 'Select invoice logo', 'licencepress' ),
									),
								)
							); ?>
							<?php
							$logo_value = (string) ( $values['invoice_logo'] ?? '' );
							if ( ! empty( $logo_value ) ) :
								$logo_url = is_numeric( $logo_value ) ? wp_get_attachment_url( (int) $logo_value ) : $logo_value;
								if ( ! empty( $logo_url ) ) :
									?>
									<div class="mt-2 licencepress-image-preview-wrap" data-licencepress-image-preview="licencepress-billing-invoice-logo" <?php echo ! empty( $logo_url ) ? '' : 'style="display:none;"'; ?>>
										<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php esc_attr_e( 'Invoice logo', 'licencepress' ); ?>" style="max-width:160px; max-height:80px; border:1px solid rgba(0,0,0,0.15); border-radius:4px; background:#fff;" />
									</div>
								<?php
								endif;
							endif;
							?>
						</td>
					</tr>
				</tbody>
			</table>
			<?php echo FormFieldHelper::button(
				__( 'Save', 'licencepress' ),
				array(
					'type' => 'submit',
					'class' => 'btn-primary',
				)
			); ?>
		</form>
		<?php
	}

	/**
	 * Render a template management table for invoices and receipts.
	 *
	 * @param array<string, string> $items Row values.
	 * @param string                $type Template type label.
	 * @return void
	 */
	private function render_template_table( array $items, string $type ): void {
		$title = 'invoice' === $type ? __( 'Invoice templates', 'licencepress' ) : __( 'Receipt templates', 'licencepress' );
		?>
		<div class="mb-4">
			<h3 class="h5 mb-3"><?php echo esc_html( $title ); ?></h3>
			<div class="table-responsive">
				<table class="table table-striped align-middle mb-0">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Template', 'licencepress' ); ?></th>
							<th scope="col" class="text-end"><?php esc_html_e( 'Actions', 'licencepress' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $items as $key => $label ) : ?>
							<tr>
								<td><?php echo esc_html( $label ); ?></td>
								<td class="text-end">
									<?php echo FormFieldHelper::button(
										__( 'Preview', 'licencepress' ),
										array(
											'class' => 'btn-sm btn-outline-secondary me-2',
											'type' => 'button',
											'data-bs-toggle' => 'modal',
											'data-bs-target' => '#licencepress-template-preview-modal',
											'data-licencepress-template-preview' => $type . '-' . $key,
											'data-licencepress-template-content' => esc_attr( 'invoice' === $type ? $this->render_invoice_template( $key ) : $this->render_receipt_template( $key ) ),
										)
									); ?>
									<?php echo FormFieldHelper::button(
										__( 'Edit', 'licencepress' ),
										array(
											'class' => 'btn-sm btn-primary',
											'type' => 'button',
											'data-bs-toggle' => 'modal',
											'data-bs-target' => '#licencepress-template-edit-modal',
											'data-licencepress-template-editor' => $type . '-' . $key,
											'data-licencepress-template-value' => esc_attr( 'invoice' === $type ? $this->get_invoice_template( $key ) : $this->get_receipt_template( $key ) ),
										)
									); ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
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
		$invoice_templates = array(
			'standard' => __( 'Standard Invoice', 'licencepress' ),
			'email' => __( 'Email Invoice', 'licencepress' ),
			'pdf' => __( 'PDF Invoice', 'licencepress' ),
		);
		$receipt_templates = array(
			'standard' => __( 'Standard Receipt', 'licencepress' ),
			'email' => __( 'Email Receipt', 'licencepress' ),
			'pdf' => __( 'PDF Receipt', 'licencepress' ),
		);
		?>
		<form method="post" action="" class="licencepress-settings-form">
			<?php wp_nonce_field( 'licencepress_billing_invoice', 'licencepress_billing_invoice_nonce' ); ?>
			<?php echo FormFieldHelper::input(
				'action',
				'licencepress_save_billing_invoice_settings',
				array(
					'type' => 'hidden',
				)
			); ?>
			<div class="mb-4">
				<?php TinyMCEHelper::render(
					'licencepress_billing_invoice_style',
					'licencepress_billing[invoice_style]',
					__( 'Invoice styling and terms', 'licencepress' ),
					(string) ( $values['invoice_style'] ?? '' ),
					14,
					false
				); ?>
				<p class="text-secondary mt-2 mb-0"><?php esc_html_e( 'Use TinyMCE to format invoice notes, payment terms, company branding, and custom invoice content.', 'licencepress' ); ?></p>
			</div>
			<?php $this->render_template_table( $invoice_templates, 'invoice' ); ?>
			<?php $this->render_template_table( $receipt_templates, 'receipt' ); ?>
			<?php echo FormFieldHelper::button(
				__( 'Save Invoice Settings', 'licencepress' ),
				array(
					'type' => 'submit',
					'class' => 'btn-primary',
				)
			); ?>
		</form>
		<div class="modal fade" id="licencepress-template-preview-modal" tabindex="-1" aria-labelledby="licencepress-template-preview-title" aria-hidden="true">
			<div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h2 class="modal-title h5" id="licencepress-template-preview-title"><?php esc_html_e( 'Template preview', 'licencepress' ); ?></h2>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php esc_attr_e( 'Close preview', 'licencepress' ); ?>"></button>
					</div>
					<div class="modal-body" id="licencepress-template-preview-body"></div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="licencepress-template-edit-modal" tabindex="-1" aria-labelledby="licencepress-template-edit-title" aria-hidden="true">
			<div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h2 class="modal-title h5" id="licencepress-template-edit-title"><?php esc_html_e( 'Edit template', 'licencepress' ); ?></h2>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php esc_attr_e( 'Close editor', 'licencepress' ); ?>"></button>
					</div>
					<div class="modal-body">
						<?php TinyMCEHelper::render(
							'licencepress-template-editor',
							'licencepress_template_editor',
							__( 'Template markup', 'licencepress' ),
							'',
							18,
							false
						); ?>
					</div>
					<div class="modal-footer">
						<?php echo FormFieldHelper::button(
							__( 'Close', 'licencepress' ),
							array(
								'class' => 'btn-secondary',
								'type' => 'button',
								'data-bs-dismiss' => 'modal',
							)
						); ?>
						<?php echo FormFieldHelper::button(
							__( 'Save changes', 'licencepress' ),
							array(
								'class' => 'btn-primary',
								'type' => 'button',
							)
						); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	/**
	 * Build the default variables used in invoice and receipt templates.
	 *
	 * @param array<string, mixed> $overrides Extra values to override defaults.
	 * @return array<string, string>
	 */
	public function get_default_template_variables( array $overrides = array() ): array {
		$billing = Settings::get_group( 'billing', array() ) ?? array();
		$logo    = $billing['invoice_logo'] ?? '';
		if ( is_numeric( $logo ) ) {
			$logo = wp_get_attachment_url( (int) $logo );
		}

		$vars = array(
			'{{company_logo_url}}' => ! empty( $logo ) ? (string) $logo : '',
			'{{company_name}}'     => ! empty( $billing['billing_name'] ) ? (string) $billing['billing_name'] : 'Your Company Ltd',
			'{{company_address}}'  => trim( (string) ( $billing['billing_address_1'] ?? '' ) . ' ' . (string) ( $billing['billing_address_2'] ?? '' ) ),
			'{{company_country}}'  => ! empty( $billing['country'] ) ? (string) $billing['country'] : 'United Kingdom',
			'{{company_email}}'    => ! empty( $billing['email_address'] ) ? (string) $billing['email_address'] : 'support@example.com',
			'{{company_phone}}'    => ! empty( $billing['phone_number'] ) ? (string) $billing['phone_number'] : '+44 1234 567890',
			'{{customer_name}}'    => 'John Doe',
			'{{customer_email}}'   => 'john@example.com',
			'{{invoice_number}}'   => 'INV-2026-001',
			'{{invoice_date}}'     => gmdate( 'Y-m-d' ),
			'{{receipt_number}}'   => 'RCT-2026-001',
			'{{receipt_date}}'     => gmdate( 'Y-m-d' ),
			'{{product_name}}'     => 'LicencePress Pro',
			'{{licence_key}}'      => 'LIC-ABC123-XYZ789',
			'{{currency}}'         => ! empty( $billing['currency'] ) ? (string) $billing['currency'] : 'GBP',
			'{{amount}}'           => '49.99',
			'{{vat_amount}}'       => '10.00',
			'{{total_amount}}'     => '59.99',
			'{{amount_paid}}'      => '59.99',
			'{{payment_method}}'   => 'Credit Card',
			'{{transaction_id}}'   => 'TXN-987654321',
		);

		if ( ! empty( $overrides ) ) {
			$vars = array_merge( $vars, $overrides );
		}

		return $vars;
	}

	/**
	 * Replace placeholder variables in a template string.
	 *
	 * @param string $template Template source.
	 * @param array<string, string> $vars Variables to replace.
	 * @return string Rendered template.
	 */
	public function replace_template_variables( string $template, array $vars ): string {
		if ( empty( $template ) ) {
			return '';
		}

		return str_replace( array_keys( $vars ), array_values( $vars ), $template );
	}

	/**
	 * Return the standard invoice template.
	 *
	 * @return string The invoice source template.
	 */
	public function get_invoice_template( string $type = 'standard' ): string {
		$templates = array(
			'standard' => <<<HTML
<style>
	.lp-doc {
		max-width: 800px;
		margin: 40px auto;
		padding: 24px;
		border: 1px solid #ddd;
		font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
		font-size: 14px;
		color: #222;
		background: #fff;
	}
	.lp-doc-logo {
		width: 180px;
		height: auto;
		margin-bottom: 20px;
	}
	.lp-doc-header {
		display: flex;
		justify-content: space-between;
		margin-bottom: 24px;
	}
	.lp-doc-title {
		font-size: 22px;
		font-weight: 600;
	}
	.lp-doc-meta {
		text-align: right;
		font-size: 13px;
		color: #555;
	}
	.lp-doc-section-title {
		font-weight: 600;
		margin-top: 16px;
		margin-bottom: 4px;
		font-size: 14px;
	}
	.lp-doc-block {
		margin-bottom: 12px;
		white-space: pre-line;
	}
	.lp-doc-table {
		width: 100%;
		border-collapse: collapse;
		margin-top: 12px;
		margin-bottom: 12px;
	}
	.lp-doc-table th,
	.lp-doc-table td {
		border: 1px solid #ddd;
		padding: 8px;
		font-size: 13px;
	}
	.lp-doc-table th {
		background: #f7f7f7;
		text-align: left;
	}
	.lp-doc-footer {
		margin-top: 24px;
		font-size: 12px;
		color: #666;
	}
</style>

<div class="lp-doc lp-invoice">
	<img src="{{company_logo_url}}" alt="Company Logo" class="lp-doc-logo">

	<div class="lp-doc-header">
		<div>
			<div class="lp-doc-title">Invoice</div>
			<div class="lp-doc-block">
				{{company_name}}<br>
				{{company_address}}<br>
				{{company_country}}<br>
				Email: {{company_email}}<br>
				Phone: {{company_phone}}
			</div>
		</div>
		<div class="lp-doc-meta">
			Invoice No: {{invoice_number}}<br>
			Date: {{invoice_date}}
		</div>
	</div>

	<div class="lp-doc-section">
		<div class="lp-doc-section-title">Billed To</div>
		<div class="lp-doc-block">
			{{customer_name}}<br>
			{{customer_email}}
		</div>
	</div>

	<div class="lp-doc-section">
		<div class="lp-doc-section-title">Product</div>
		<table class="lp-doc-table">
			<thead>
				<tr>
					<th>Product</th>
					<th>Licence Key</th>
					<th>Amount</th>
					<th>VAT</th>
					<th>Total</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>{{product_name}}</td>
					<td>{{licence_key}}</td>
					<td>{{currency}} {{amount}}</td>
					<td>{{currency}} {{vat_amount}}</td>
					<td>{{currency}} {{total_amount}}</td>
				</tr>
			</tbody>
		</table>
	</div>

	<div class="lp-doc-section">
		<div class="lp-doc-section-title">Payment Details</div>
		<div class="lp-doc-block">
			Payment Method: {{payment_method}}
		</div>
	</div>

	<div class="lp-doc-footer">
		Thank you for your purchase. If you have any questions regarding this invoice,
		please contact {{company_email}}.
	</div>
</div>
HTML,
			'pdf' => <<<HTML
<div style="width: 100%; font-family: Arial, sans-serif; font-size: 13px; color: #222;">
	<div style="text-align: left; margin-bottom: 20px;">
		<img src="{{company_logo_url}}" alt="Company Logo" style="width: 160px; height: auto;">
	</div>
	<table width="100%" cellpadding="4" cellspacing="0" border="0">
		<tr>
			<td valign="top" width="60%">
				<h2 style="margin: 0 0 10px 0;">Invoice</h2>
				{{company_name}}<br>
				{{company_address}}<br>
				{{company_country}}<br>
				Email: {{company_email}}<br>
				Phone: {{company_phone}}
			</td>
			<td valign="top" width="40%" align="right">
				<strong>Invoice No:</strong> {{invoice_number}}<br>
				<strong>Date:</strong> {{invoice_date}}
			</td>
		</tr>
	</table>
	<h3 style="margin-top: 20px;">Billed To</h3>
	{{customer_name}}<br>
	{{customer_email}}
	<h3 style="margin-top: 20px;">Product</h3>
	<table width="100%" cellpadding="6" cellspacing="0" border="1" style="border-collapse: collapse;">
		<tr style="background: #f0f0f0;">
			<th align="left">Product</th>
			<th align="left">Licence Key</th>
			<th align="left">Amount</th>
			<th align="left">VAT</th>
			<th align="left">Total</th>
		</tr>
		<tr>
			<td>{{product_name}}</td>
			<td>{{licence_key}}</td>
			<td>{{currency}} {{amount}}</td>
			<td>{{currency}} {{vat_amount}}</td>
			<td>{{currency}} {{total_amount}}</td>
		</tr>
	</table>
	<h3 style="margin-top: 20px;">Payment Details</h3>
	Payment Method: {{payment_method}}
	<p style="margin-top: 20px; font-size: 12px; color: #555;">
		Thank you for your purchase. For questions, contact {{company_email}}.
	</p>
</div>
HTML,
			'email' => <<<HTML
<table width="100%" cellpadding="6" cellspacing="0" style="font-family: Arial, sans-serif; font-size: 13px; color: #222;">
	<tr>
		<td>
			<img src="{{company_logo_url}}" alt="Logo" style="width: 140px; height: auto; margin-bottom: 10px;">
		</td>
	</tr>
	<tr>
		<td>
			<strong>Invoice</strong><br>
			Invoice No: {{invoice_number}}<br>
			Date: {{invoice_date}}<br><br>
			<strong>{{company_name}}</strong><br>
			{{company_address}}<br>
			{{company_country}}<br>
			Email: {{company_email}}<br>
			Phone: {{company_phone}}<br><br>
			<strong>Billed To</strong><br>
			{{customer_name}}<br>
			{{customer_email}}<br><br>
			<strong>Product</strong><br>
			{{product_name}}<br>
			Licence Key: {{licence_key}}<br>
			Amount: {{currency}} {{amount}}<br>
			VAT: {{currency}} {{vat_amount}}<br>
			Total: {{currency}} {{total_amount}}<br><br>
			<strong>Payment Method:</strong> {{payment_method}}<br><br>
			<em>Thank you for your purchase.</em>
		</td>
	</tr>
</table>
HTML,
		);

		$type = in_array( $type, array( 'standard', 'pdf', 'email' ), true ) ? $type : 'standard';
		return $templates[ $type ];
	}

	/**
	 * Render the invoice with a chosen preset.
	 *
	 * @param string $type Template type: standard, pdf, email.
	 * @param array<string, mixed> $vars Variable overrides.
	 * @return string Rendered invoice HTML.
	 */
	public function render_invoice_template( string $type = 'standard', array $vars = array() ): string {
		$template = $this->get_invoice_template( $type );
		return $this->replace_template_variables( $template, $this->get_default_template_variables( $vars ) );
	}

	/**
	 * Return the standard receipt template.
	 *
	 * @return string The receipt source template.
	 */
	public function get_receipt_template( string $type = 'standard' ): string {
		$templates = array(
			'standard' => <<<HTML
<style>
	.lp-doc {
		max-width: 800px;
		margin: 40px auto;
		padding: 24px;
		border: 1px solid #ddd;
		font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
		font-size: 14px;
		color: #222;
		background: #fff;
	}
	.lp-doc-logo {
		width: 180px;
		height: auto;
		margin-bottom: 20px;
	}
	.lp-doc-header {
		display: flex;
		justify-content: space-between;
		margin-bottom: 24px;
	}
	.lp-doc-title {
		font-size: 22px;
		font-weight: 600;
	}
	.lp-doc-meta {
		text-align: right;
		font-size: 13px;
		color: #555;
	}
	.lp-doc-section-title {
		font-weight: 600;
		margin-top: 16px;
		margin-bottom: 4px;
		font-size: 14px;
	}
	.lp-doc-block {
		margin-bottom: 12px;
		white-space: pre-line;
	}
	.lp-doc-table {
		width: 100%;
		border-collapse: collapse;
		margin-top: 12px;
		margin-bottom: 12px;
	}
	.lp-doc-table th,
	.lp-doc-table td {
		border: 1px solid #ddd;
		padding: 8px;
		font-size: 13px;
	}
	.lp-doc-table th {
		background: #f7f7f7;
		text-align: left;
	}
	.lp-doc-footer {
		margin-top: 24px;
		font-size: 12px;
		color: #666;
	}
</style>

<div class="lp-doc lp-receipt">
	<img src="{{company_logo_url}}" alt="Company Logo" class="lp-doc-logo">

	<div class="lp-doc-header">
		<div>
			<div class="lp-doc-title">Receipt</div>
			<div class="lp-doc-block">
				{{company_name}}<br>
				{{company_address}}<br>
				{{company_country}}<br>
				Email: {{company_email}}<br>
				Phone: {{company_phone}}
			</div>
		</div>
		<div class="lp-doc-meta">
			Receipt No: {{receipt_number}}<br>
			Date: {{receipt_date}}
		</div>
	</div>

	<div class="lp-doc-section">
		<div class="lp-doc-section-title">Customer</div>
		<div class="lp-doc-block">
			{{customer_name}}<br>
			{{customer_email}}
		</div>
	</div>

	<div class="lp-doc-section">
		<div class="lp-doc-section-title">Product</div>
		<table class="lp-doc-table">
			<thead>
				<tr>
					<th>Product</th>
					<th>Licence Key</th>
					<th>Amount Paid</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>{{product_name}}</td>
					<td>{{licence_key}}</td>
					<td>{{currency}} {{amount_paid}}</td>
				</tr>
			</tbody>
		</table>
	</div>

	<div class="lp-doc-section">
		<div class="lp-doc-section-title">Payment Details</div>
		<div class="lp-doc-block">
			Payment Method: {{payment_method}}<br>
			Transaction ID: {{transaction_id}}
		</div>
	</div>

	<div class="lp-doc-footer">
		This receipt confirms payment for the above product. For support or billing enquiries,
		please contact {{company_email}}.
	</div>
</div>
HTML,
			'pdf' => <<<HTML
<div style="width: 100%; font-family: Arial, sans-serif; font-size: 13px; color: #222;">
	<div style="text-align: left; margin-bottom: 20px;">
		<img src="{{company_logo_url}}" alt="Company Logo" style="width: 160px; height: auto;">
	</div>
	<table width="100%" cellpadding="4" cellspacing="0" border="0">
		<tr>
			<td valign="top" width="60%">
				<h2 style="margin: 0 0 10px 0;">Receipt</h2>
				{{company_name}}<br>
				{{company_address}}<br>
				{{company_country}}<br>
				Email: {{company_email}}<br>
				Phone: {{company_phone}}
			</td>
			<td valign="top" width="40%" align="right">
				<strong>Receipt No:</strong> {{receipt_number}}<br>
				<strong>Date:</strong> {{receipt_date}}
			</td>
		</tr>
	</table>
	<h3 style="margin-top: 20px;">Customer</h3>
	{{customer_name}}<br>
	{{customer_email}}
	<h3 style="margin-top: 20px;">Product</h3>
	<table width="100%" cellpadding="6" cellspacing="0" border="1" style="border-collapse: collapse;">
		<tr style="background: #f0f0f0;">
			<th align="left">Product</th>
			<th align="left">Licence Key</th>
			<th align="left">Amount Paid</th>
		</tr>
		<tr>
			<td>{{product_name}}</td>
			<td>{{licence_key}}</td>
			<td>{{currency}} {{amount_paid}}</td>
		</tr>
	</table>
	<h3 style="margin-top: 20px;">Payment Details</h3>
	Payment Method: {{payment_method}}<br>
	Transaction ID: {{transaction_id}}
	<p style="margin-top: 20px; font-size: 12px; color: #555;">
		Thank you for your payment. For support, contact {{company_email}}.
	</p>
</div>
HTML,
			'email' => <<<HTML
<table width="100%" cellpadding="6" cellspacing="0" style="font-family: Arial, sans-serif; font-size: 13px; color: #222;">
	<tr>
		<td>
			<img src="{{company_logo_url}}" alt="Logo" style="width: 140px; height: auto; margin-bottom: 10px;">
		</td>
	</tr>
	<tr>
		<td>
			<strong>Receipt</strong><br>
			Receipt No: {{receipt_number}}<br>
			Date: {{receipt_date}}<br><br>
			<strong>{{company_name}}</strong><br>
			{{company_address}}<br>
			{{company_country}}<br>
			Email: {{company_email}}<br>
			Phone: {{company_phone}}<br><br>
			<strong>Customer</strong><br>
			{{customer_name}}<br>
			{{customer_email}}<br><br>
			<strong>Product</strong><br>
			{{product_name}}<br>
			Licence Key: {{licence_key}}<br>
			Amount Paid: {{currency}} {{amount_paid}}<br><br>
			<strong>Payment Method:</strong> {{payment_method}}<br>
			<strong>Transaction ID:</strong> {{transaction_id}}<br><br>
			<em>Thank you for your payment.</em>
		</td>
	</tr>
</table>
HTML,
		);

		$type = in_array( $type, array( 'standard', 'pdf', 'email' ), true ) ? $type : 'standard';
		return $templates[ $type ];
	}

	/**
	 * Render the receipt with a chosen preset.
	 *
	 * @param string $type Template type: standard, pdf, email.
	 * @param array<string, mixed> $vars Variable overrides.
	 * @return string Rendered receipt HTML.
	 */
	public function render_receipt_template( string $type = 'standard', array $vars = array() ): string {
		$template = $this->get_receipt_template( $type );
		return $this->replace_template_variables( $template, $this->get_default_template_variables( $vars ) );
	}
}