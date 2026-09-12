<?php
/**
 * Customer dashboard listing for the LicencePress admin area.
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

final class CustomerDashboard {
	/**
	 * Render the customer directory overview and table.
	 *
	 * @param int $customer_id Optional single-customer detail route.
	 * @return void
	 */
	public function render( int $customer_id = 0 ): void {
		if ( $customer_id > 0 ) {
			( new CustomerOverview() )->render( $customer_id );
			return;
		}

		$profiles = CustomerManager::customer_profiles();
		$stats    = array(
			array(
				'label' => __( 'Total customers', 'licencepress' ),
				'value' => (string) count( $profiles ),
			),
			array(
				'label' => __( 'Active licences', 'licencepress' ),
				'value' => '0',
			),
			array(
				'label' => __( 'Renewals due', 'licencepress' ),
				'value' => '0',
			),
			array(
				'label' => __( 'Internal customers', 'licencepress' ),
				'value' => '0',
			),
		);
		?>
		<div class="mb-4">
			<div class="row g-3">
				<?php foreach ( $stats as $stat ) : ?>
					<div class="col-md-6 col-xl-3">
						<div class="card h-100 shadow-sm border-0">
							<div class="card-body">
								<div class="small text-uppercase text-muted"><?php echo esc_html( $stat['label'] ); ?></div>
								<div class="display-6 mb-0"><?php echo esc_html( $stat['value'] ); ?></div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="card shadow-sm border-0">
			<div class="card-body">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<h2 class="h5 mb-0"><?php esc_html_e( 'Customer Directory', 'licencepress' ); ?></h2>
					<?php echo FormFieldHelper::button( __( 'Add customer', 'licencepress' ), array( 'class' => 'btn-sm btn-primary' ) ); ?>
				</div>
				<div class="table-responsive">
					<table class="table align-middle mb-0 table-striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Customer', 'licencepress' ); ?></th>
								<th><?php esc_html_e( 'Type', 'licencepress' ); ?></th>
								<th><?php esc_html_e( 'Primary contact', 'licencepress' ); ?></th>
								<th><?php esc_html_e( 'Accounts', 'licencepress' ); ?></th>
								<th class="text-end"><?php esc_html_e( 'Actions', 'licencepress' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $profiles as $customer ) : ?>
								<tr>
									<td><?php echo esc_html( $customer['name'] ?? __( 'Customer', 'licencepress' ) ); ?></td>
									<td><?php echo esc_html( $customer['type'] ?? __( 'Customer', 'licencepress' ) ); ?></td>
									<td><?php echo esc_html( $customer['contact'] ?? __( 'No contact', 'licencepress' ) ); ?></td>
									<td><?php echo esc_html( (string) ( $customer['accounts'] ?? 0 ) ); ?></td>
									<td class="text-end">
										<div class="btn-group btn-group-sm">
											<?php echo FormFieldHelper::button( __( 'View', 'licencepress' ), array( 'href' => admin_url( 'admin.php?page=licencepress&group=customers&customer_id=' . (int) ( $customer['id'] ?? 0 ) ), 'class' => 'btn-outline-primary' ) ); ?>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php
	}
}
