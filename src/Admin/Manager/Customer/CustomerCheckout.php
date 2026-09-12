<?php
/**
 * Customer checkout flow renderer for the LicencePress admin area.
 *
 * @package LicencePress
 * @subpackage Admin\Manager\Customer
 * @since 1.0.0
 */
namespace LicencePress\Admin\Manager\Customer;

use LicencePress\Includes\Functions\Helpers\FormFieldHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class CustomerCheckout {
	/**
	 * Render the checkout screen for the selected customer.
	 *
	 * @param int $customer_id Customer ID.
	 * @return void
	 */
	public function render( int $customer_id = 0 ): void {
		$customer = CustomerManager::get_customer( $customer_id );
		if ( null === $customer ) {
			?>
			<div class="alert alert-warning"><?php esc_html_e( 'Customer not found.', 'licencepress' ); ?></div>
			<?php
			return;
		}
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
							<li class="list-group-item px-0 d-flex justify-content-between"><span><?php esc_html_e( 'Customer', 'licencepress' ); ?></span><strong><?php echo esc_html( $customer['company_name'] ); ?></strong></li>
							<li class="list-group-item px-0 d-flex justify-content-between"><span><?php esc_html_e( 'Type', 'licencepress' ); ?></span><strong><?php echo esc_html( $customer['customer_type'] ); ?></strong></li>
							<li class="list-group-item px-0 d-flex justify-content-between"><span><?php esc_html_e( 'Status', 'licencepress' ); ?></span><strong><?php echo esc_html( ucfirst( $customer['account_status'] ) ); ?></strong></li>
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
