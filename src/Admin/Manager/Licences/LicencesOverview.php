<?php
/**
 * Licence overview page for the LicencePress admin area.
 *
 * @package LicencePress
 * @subpackage Admin\Manager\Licences
 * @since 1.0.0
 */
namespace LicencePress\Admin\Manager\Licences;

use LicencePress\Includes\Functions\Helpers\FormFieldHelper;
use LicencePress\Includes\Licence\LicenceManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LicencesOverview {
	public function render(): void {
		$summary = LicenceManager::summary();
		?>
		<div class="row g-3 mb-4">
			<?php foreach ( $this->stat_cards( $summary ) as $card ) : ?>
				<div class="col-md-6 col-xl-3">
					<div class="card h-100 shadow-sm border-0">
						<div class="card-body">
							<div class="small text-uppercase text-muted"><?php echo esc_html( $card['label'] ); ?></div>
							<div class="display-6 mb-0"><?php echo esc_html( (string) $card['value'] ); ?></div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="row g-4 mb-4">
			<div class="col-xl-8">
				<div class="card shadow-sm h-100">
					<div class="card-body">
						<div class="d-flex justify-content-between align-items-center mb-3">
							<h2 class="h5 mb-0"><?php esc_html_e( 'Licence performance', 'licencepress' ); ?></h2>
							<span class="small text-secondary"><?php esc_html_e( 'Last 30 days', 'licencepress' ); ?></span>
						</div>
						<div class="d-flex align-items-end gap-2" style="min-height: 220px;">
							<?php foreach ( $this->graph_data() as $label => $value ) : ?>
								<div class="flex-fill text-center">
									<div class="bg-primary-subtle rounded-top" style="height: <?php echo esc_attr( (string) $value ); ?>%; min-height: 36px;"></div>
									<div class="small text-secondary mt-2"><?php echo esc_html( $label ); ?></div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-4">
				<div class="card shadow-sm h-100">
					<div class="card-body">
						<h2 class="h5 mb-3"><?php esc_html_e( 'Recent activity', 'licencepress' ); ?></h2>
						<ul class="list-group list-group-flush">
							<?php foreach ( $this->activity() as $entry ) : ?>
								<li class="list-group-item px-0 d-flex justify-content-between gap-3">
									<span><?php echo esc_html( $entry['label'] ); ?></span>
									<strong><?php echo esc_html( (string) $entry['value'] ); ?></strong>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>
		</div>

		<div class="card shadow-sm">
			<div class="card-body">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<h2 class="h5 mb-0"><?php esc_html_e( 'Customer overview', 'licencepress' ); ?></h2>
					<?php echo FormFieldHelper::button(
						__( 'Open customer data', 'licencepress' ),
						array(
							'href'  => admin_url( 'admin.php?page=licencepress-licence-management' ),
							'class' => 'btn-sm btn-outline-primary',
						)
					); ?>
				</div>
				<div class="row g-3">
					<?php foreach ( $this->customer_cards() as $customer ) : ?>
						<div class="col-md-6 col-xl-3">
							<div class="border rounded p-3 h-100 bg-light-subtle">
								<div class="small text-uppercase text-muted"><?php echo esc_html( $customer['label'] ); ?></div>
								<div class="h4 mb-0 mt-2"><?php echo esc_html( (string) $customer['value'] ); ?></div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}

	public function render_customer_overview(): void {
		?>
		<div class="card shadow-sm mb-4">
			<div class="card-body">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<h2 class="h5 mb-0"><?php esc_html_e( 'Customer management', 'licencepress' ); ?></h2>
					<?php echo FormFieldHelper::button( __( 'Add customer', 'licencepress' ), array( 'class' => 'btn-sm btn-primary' ) ); ?>
				</div>
				<div class="table-responsive">
					<table class="table table-striped align-middle mb-0">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Customer', 'licencepress' ); ?></th>
								<th><?php esc_html_e( 'Email', 'licencepress' ); ?></th>
								<th><?php esc_html_e( 'Licences', 'licencepress' ); ?></th>
								<th><?php esc_html_e( 'Revenue', 'licencepress' ); ?></th>
								<th class="text-end"><?php esc_html_e( 'Actions', 'licencepress' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $this->customers() as $customer ) : ?>
								<tr>
									<td><?php echo esc_html( $customer['name'] ); ?></td>
									<td><?php echo esc_html( $customer['email'] ); ?></td>
									<td><?php echo esc_html( (string) $customer['licences'] ); ?></td>
									<td><?php echo esc_html( $customer['revenue'] ); ?></td>
									<td class="text-end">
										<div class="btn-group btn-group-sm">
												<?php echo FormFieldHelper::button( __( 'View', 'licencepress' ), array( 'class' => 'btn-outline-primary' ) ); ?>
												<?php echo FormFieldHelper::button( __( 'Edit', 'licencepress' ), array( 'class' => 'btn-outline-secondary' ) ); ?>
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

	private function stat_cards( array $summary ): array {
		return array(
			array(
				'label' => __( 'Active', 'licencepress' ),
				'value' => $summary['active'] ?? 0,
			),
			array(
				'label' => __( 'Expiring soon', 'licencepress' ),
				'value' => $summary['expiring_soon'] ?? 0,
			),
			array(
				'label' => __( 'Renewed', 'licencepress' ),
				'value' => $summary['renewed'] ?? 0,
			),
			array(
				'label' => __( 'Customers', 'licencepress' ),
				'value' => $summary['customers'] ?? 0,
			),
		);
	}

	private function graph_data(): array {
		return array(
			'Jan' => 48,
			'Feb' => 62,
			'Mar' => 58,
			'Apr' => 74,
			'May' => 83,
			'Jun' => 76,
		);
	}

	private function activity(): array {
		return array(
			array( 'label' => __( 'Generated', 'licencepress' ), 'value' => '184' ),
			array( 'label' => __( 'Renewed', 'licencepress' ), 'value' => '61' ),
			array( 'label' => __( 'Upgraded', 'licencepress' ), 'value' => '29' ),
			array( 'label' => __( 'Cancelled', 'licencepress' ), 'value' => '12' ),
		);
	}

	private function customer_cards(): array {
		return array(
			array( 'label' => __( 'Registered customers', 'licencepress' ), 'value' => 148 ),
			array( 'label' => __( 'B2B accounts', 'licencepress' ), 'value' => 42 ),
			array( 'label' => __( 'Expired licences', 'licencepress' ), 'value' => 21 ),
			array( 'label' => __( 'Conversion rate', 'licencepress' ), 'value' => '68%' ),
		);
	}

	private function customers(): array {
		return array(
			array(
				'name'    => 'Northwind Studio',
				'email'   => 'hello@northwindstudio.dev',
				'licences' => 12,
				'revenue' => '$2,480',
			),
			array(
				'name'    => 'Westgate Labs',
				'email'   => 'billing@westgatelabs.com',
				'licences' => 8,
				'revenue' => '$1,910',
			),
			array(
				'name'    => 'Cloud Pine',
				'email'   => 'support@cloudpine.io',
				'licences' => 5,
				'revenue' => '$1,280',
			),
		);
	}
}
