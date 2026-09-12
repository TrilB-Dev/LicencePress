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

		$form = new CustomerForms();
		$form->render_checkout_form( $customer_id, $customer );
	}
}
