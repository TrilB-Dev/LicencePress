<?php
/**
 * Shared customer forms for the LicencePress admin area.
 *
 * @package LicencePress
 * @subpackage Admin\Manager\Customer
 * @since 1.0.0
 */
namespace LicencePress\Admin\Manager\Customer;

use LicencePress\Includes\Functions\Helpers\FormFieldHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CustomerForms {
	/**
	 * Render the customer profile form for the selected record.
	 *
	 * @param array<string, mixed> $customer Customer record.
	 * @return void
	 */
	public function render_customer_profile_form( array $customer ): void {
		?>
		<form>
			<div class="row g-3">
				<div class="col-md-6">
					<?php echo FormFieldHelper::text_input( 'company_name', (string) $customer['company_name'], array( 'id' => 'customer_company_name', 'class' => 'w-100', 'placeholder' => __( 'Company name', 'licencepress' ) ) ); ?>
				</div>
				<div class="col-md-6">
					<?php echo FormFieldHelper::text_input( 'customer_type', (string) $customer['customer_type'], array( 'id' => 'customer_type', 'class' => 'w-100', 'placeholder' => __( 'Customer type', 'licencepress' ) ) ); ?>
				</div>
				<div class="col-md-6">
					<?php echo FormFieldHelper::text_input( 'primary_contact_name', (string) $customer['primary_contact_name'], array( 'id' => 'customer_primary_contact_name', 'class' => 'w-100', 'placeholder' => __( 'Primary contact name', 'licencepress' ) ) ); ?>
				</div>
				<div class="col-md-6">
					<?php echo FormFieldHelper::text_input( 'primary_contact_email', (string) $customer['primary_contact_email'], array( 'id' => 'customer_primary_contact_email', 'class' => 'w-100', 'placeholder' => __( 'Primary contact email', 'licencepress' ) ) ); ?>
				</div>
				<div class="col-md-6">
					<?php echo FormFieldHelper::text_input( 'phone', (string) $customer['phone'], array( 'id' => 'customer_phone', 'class' => 'w-100', 'placeholder' => __( 'Phone', 'licencepress' ) ) ); ?>
				</div>
				<div class="col-md-6">
					<?php echo FormFieldHelper::text_input( 'payment_method', (string) $customer['payment_method'], array( 'id' => 'customer_payment_method', 'class' => 'w-100', 'placeholder' => __( 'Payment method', 'licencepress' ) ) ); ?>
				</div>
				<div class="col-md-12">
					<?php echo FormFieldHelper::textarea( 'notes', (string) $customer['notes'], array( 'id' => 'customer_notes', 'rows' => 4, 'class' => 'w-100', 'placeholder' => __( 'Notes', 'licencepress' ) ) ); ?>
				</div>
			</div>
		</form>
		<?php
	}

	/**
	 * Render the customer checkout form.
	 *
	 * @param int                     $customer_id Customer ID.
	 * @param array<string, mixed> $customer    Customer record.
	 * @return void
	 */
	public function render_checkout_form( int $customer_id, array $customer = array() ): void {
		?>
		<div class="row g-4">
			<div class="col-lg-8">
				<div class="card shadow-sm border-0">
					<div class="card-body">
						<h2 class="h4 mb-3"><?php esc_html_e( 'Customer checkout', 'licencepress' ); ?></h2>
						<form>
							<div class="row g-3">
								<div class="col-md-6">
									<?php echo FormFieldHelper::text_input( 'product_id', '', array( 'id' => 'checkout_product_id', 'class' => 'w-100', 'placeholder' => __( 'Product ID', 'licencepress' ) ) ); ?>
								</div>
								<div class="col-md-6">
									<?php echo FormFieldHelper::text_input( 'days', '30', array( 'id' => 'checkout_days', 'class' => 'w-100', 'placeholder' => __( 'Duration in days', 'licencepress' ) ) ); ?>
								</div>
								<div class="col-md-12">
									<?php echo FormFieldHelper::text_input( 'site_url', '', array( 'id' => 'checkout_site_url', 'class' => 'w-100', 'placeholder' => __( 'Site URL', 'licencepress' ) ) ); ?>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
			<div class="col-lg-4">
				<div class="card shadow-sm border-0">
					<div class="card-body">
						<h3 class="h5 mb-3"><?php esc_html_e( 'Checkout summary', 'licencepress' ); ?></h3>
						<ul class="list-group list-group-flush">
							<li class="list-group-item px-0 d-flex justify-content-between"><span><?php esc_html_e( 'Customer', 'licencepress' ); ?></span><strong><?php echo esc_html( $customer['company_name'] ?? '' ); ?></strong></li>
							<li class="list-group-item px-0 d-flex justify-content-between"><span><?php esc_html_e( 'Type', 'licencepress' ); ?></span><strong><?php echo esc_html( $customer['customer_type'] ?? '' ); ?></strong></li>
							<li class="list-group-item px-0 d-flex justify-content-between"><span><?php esc_html_e( 'Status', 'licencepress' ); ?></span><strong><?php echo esc_html( ucfirst( (string) ( $customer['account_status'] ?? 'active' ) ) ); ?></strong></li>
						</ul>
						<div class="d-grid mt-3">
							<?php echo FormFieldHelper::button( __( 'Issue licence', 'licencepress' ), array( 'class' => 'btn-primary', 'type' => 'button', 'data-customer-action' => 'issue-licence', 'data-customer-id' => (string) $customer_id ) ); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}