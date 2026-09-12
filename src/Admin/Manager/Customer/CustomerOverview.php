<?php
/**
 * CustomerOverview class for LicencePress plugin.
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

final class CustomerOverview {
	public function render( int $customer_id = 0 ): void {
		if ( $customer_id > 0 ) {
			$this->render_detail( $customer_id );
			return;
		}

		$tabs = array(
			'overview' => __( 'Overview', 'licencepress' ),
			'profiles' => __( 'Profiles', 'licencepress' ),
			'licences' => __( 'Licences', 'licencepress' ),
		);
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'overview';
		$active_tab = array_key_exists( $active_tab, $tabs ) ? $active_tab : 'overview';
		?>
		<div class="mb-4">
			<ul class="nav nav-tabs" role="tablist">
				<?php foreach ( $tabs as $key => $label ) : ?>
					<li class="nav-item" role="presentation">
						<a class="nav-link <?php echo esc_attr( $key === $active_tab ? 'active' : '' ); ?>"
							href="<?php echo esc_url( admin_url( 'admin.php?page=licencepress&group=customers&tab=' . $key ) ); ?>"
							aria-selected="<?php echo esc_attr( $key === $active_tab ? 'true' : 'false' ); ?>">
							<?php echo esc_html( $label ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<?php if ( 'overview' === $active_tab ) : ?>
			<div class="row g-3 mb-4">
				<?php foreach ( $this->stats() as $stat ) : ?>
					<div class="col-md-6 col-xl-3">
						<div class="card h-100 shadow-sm border-0">
							<div class="card-body">
								<div class="small text-uppercase text-muted"><?php echo esc_html( $stat['label'] ); ?></div>
								<div class="display-6 mb-0"><?php echo esc_html( (string) $stat['value'] ); ?></div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php elseif ( 'profiles' === $active_tab ) : ?>
			<div class="card shadow-sm border-0">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center mb-3">
						<h2 class="h5 mb-0"><?php esc_html_e( 'Customer profiles', 'licencepress' ); ?></h2>
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
								<?php foreach ( $this->profiles() as $customer ) : ?>
									<tr>
										<td><?php echo esc_html( $customer['name'] ); ?></td>
										<td><?php echo esc_html( $customer['type'] ); ?></td>
										<td><?php echo esc_html( $customer['contact'] ); ?></td>
										<td><?php echo esc_html( (string) $customer['accounts'] ); ?></td>
										<td class="text-end">
											<div class="btn-group btn-group-sm">
												<?php echo FormFieldHelper::button( __( 'View', 'licencepress' ), array( 'href' => admin_url( 'admin.php?page=licencepress&group=customers&customer_id=' . (int) $customer['id'] ), 'class' => 'btn-outline-primary' ) ); ?>
												<?php echo FormFieldHelper::button( __( 'Edit', 'licencepress' ), array( 'href' => admin_url( 'admin.php?page=licencepress&group=customers&customer_id=' . (int) $customer['id'] . '&tab=profiles' ), 'class' => 'btn-outline-secondary' ) ); ?>
											</div>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		<?php else : ?>
			<div class="card shadow-sm border-0">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center mb-3">
						<h2 class="h5 mb-0"><?php esc_html_e( 'Licence activity', 'licencepress' ); ?></h2>
						<?php echo FormFieldHelper::button( __( 'Create licence', 'licencepress' ), array( 'class' => 'btn-sm btn-primary' ) ); ?>
					</div>
					<div class="accordion" id="customer-licence-accordion">
						<?php foreach ( $this->licence_groups() as $index => $group ) : ?>
							<div class="accordion-item">
								<h3 class="accordion-header" id="heading-<?php echo esc_attr( (string) $index ); ?>">
									<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo esc_attr( (string) $index ); ?>" aria-expanded="false" aria-controls="collapse-<?php echo esc_attr( (string) $index ); ?>">
										<?php echo esc_html( $group['label'] ); ?>
									</button>
								</h3>
								<div id="collapse-<?php echo esc_attr( (string) $index ); ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo esc_attr( (string) $index ); ?>" data-bs-parent="#customer-licence-accordion">
									<div class="accordion-body">
										<ul class="list-group list-group-flush">
											<?php foreach ( $group['items'] as $item ) : ?>
												<li class="list-group-item px-0 d-flex justify-content-between gap-3">
													<span><?php echo esc_html( $item['name'] ); ?></span>
													<strong><?php echo esc_html( $item['status'] ); ?></strong>
												</li>
											<?php endforeach; ?>
										</ul>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	<?php
	}

	private function render_detail( int $customer_id ): void {
		$customer = CustomerManager::get_customer( $customer_id );
		if ( null === $customer ) {
			?>
			<div class="alert alert-warning"><?php esc_html_e( 'Customer not found.', 'licencepress' ); ?></div>
			<?php
			return;
		}

		$licences = CustomerManager::customer_licences( $customer_id );
		$licence_count = count( $licences );
		$next_renewal = '—';
		if ( ! empty( $licences ) ) {
			$dates = array_map(
				static fn( $record ) => isset( $record['expires_at'] ) ? strtotime( (string) $record['expires_at'] ) : 0,
				$licences
			);
			$valid_dates = array_filter( $dates, static fn( $date ) => $date > 0 );
			if ( ! empty( $valid_dates ) ) {
				$next_renewal = gmdate( 'Y-m-d', min( $valid_dates ) );
			}
		}
		?>
		<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
			<div>
				<h2 class="h3 mb-1"><?php echo esc_html( $customer['company_name'] ); ?></h2>
				<p class="text-muted mb-0"><?php echo esc_html( $customer['customer_type'] ); ?> · <?php echo esc_html( $customer['user_email'] ); ?></p>
			</div>
			<div class="btn-group">
				<?php echo FormFieldHelper::button( __( 'Back to customers', 'licencepress' ), array( 'href' => admin_url( 'admin.php?page=licencepress&group=customers' ), 'class' => 'btn-outline-secondary' ) ); ?>
				<?php echo FormFieldHelper::button( __( 'Create licence', 'licencepress' ), array( 'class' => 'btn-primary', 'type' => 'button', 'data-customer-action' => 'issue-licence', 'data-customer-id' => (string) $customer_id ) ); ?>
			</div>
		</div>

		<div class="row g-4">
			<div class="col-lg-8">
				<div class="card shadow-sm border-0">
					<div class="card-body">
						<h3 class="h5 mb-3"><?php esc_html_e( 'Customer profile', 'licencepress' ); ?></h3>
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
					</div>
				</div>
			</div>

			<div class="col-lg-4">
				<div class="card shadow-sm border-0 mb-4">
					<div class="card-body">
						<h3 class="h5 mb-3"><?php esc_html_e( 'Account status', 'licencepress' ); ?></h3>
						<ul class="list-group list-group-flush">
							<li class="list-group-item px-0 d-flex justify-content-between"><span><?php esc_html_e( 'Status', 'licencepress' ); ?></span><strong><?php echo esc_html( ucfirst( (string) $customer['account_status'] ) ); ?></strong></li>
							<li class="list-group-item px-0 d-flex justify-content-between"><span><?php esc_html_e( 'Licences', 'licencepress' ); ?></span><strong><?php echo esc_html( (string) $licence_count ); ?></strong></li>
							<li class="list-group-item px-0 d-flex justify-content-between"><span><?php esc_html_e( 'Next renewal', 'licencepress' ); ?></span><strong><?php echo esc_html( $next_renewal ); ?></strong></li>
						</ul>
					</div>
				</div>
				<div class="card shadow-sm border-0">
					<div class="card-body">
						<h3 class="h5 mb-3"><?php esc_html_e( 'Recent licences', 'licencepress' ); ?></h3>
						<ul class="list-group list-group-flush">
							<?php foreach ( $licences as $licence ) : ?>
								<li class="list-group-item px-0 d-flex justify-content-between gap-3 align-items-center">
									<span><?php echo esc_html( (string) ( $licence['product_id'] ?? __( 'Licence', 'licencepress' ) ) ); ?></span>
									<div class="d-flex gap-2 align-items-center">
										<strong><?php echo esc_html( ucfirst( (string) ( $licence['status'] ?? 'active' ) ) ); ?></strong>
										<?php echo FormFieldHelper::button( __( 'Revoke', 'licencepress' ), array( 'type' => 'button', 'class' => 'btn-outline-danger btn-sm', 'data-customer-action' => 'revoke-licence', 'data-customer-id' => (string) $customer_id, 'data-licence-token' => (string) ( $licence['token'] ?? '' ) ) ); ?>
									</div>
								</li>
							<?php endforeach; ?>
							<?php if ( empty( $licences ) ) : ?>
								<li class="list-group-item px-0"><?php esc_html_e( 'No licences issued yet.', 'licencepress' ); ?></li>
							<?php endif; ?>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	private function stats(): array {
		return array(
			array( 'label' => __( 'Total customers', 'licencepress' ), 'value' => 148 ),
			array( 'label' => __( 'Active licences', 'licencepress' ), 'value' => 536 ),
			array( 'label' => __( 'Renewals due', 'licencepress' ), 'value' => 34 ),
			array( 'label' => __( 'Internal customers', 'licencepress' ), 'value' => 17 ),
		);
	}

	private function profiles(): array {
		return CustomerManager::customer_profiles();
	}

	private function licence_groups(): array {
		return array(
			array(
				'label' => __( 'Active', 'licencepress' ),
				'items' => array(
					array( 'name' => 'Northwind Studio — Pro Licence', 'status' => 'Active' ),
					array( 'name' => 'Westgate Labs — Team Licence', 'status' => 'Active' ),
				),
			),
			array(
				'label' => __( 'Expiring soon', 'licencepress' ),
				'items' => array(
					array( 'name' => 'Cloud Pine — Business Licence', 'status' => 'Due in 12 days' ),
				),
			),
		);
	}
}