<?php
/**
 * Licence type management page for the LicencePress admin area.
 *
 * @package LicencePress
 * @subpackage Admin\Manager\Licences
 * @since 1.0.0
 */
namespace LicencePress\Admin\Manager\Licences;

use LicencePress\Includes\Functions\Helpers\FormFieldHelper;
use LicencePress\Includes\Licence\LicenceTypeManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LicencesTypes {
	private LicencesForms $forms;

	public function __construct() {
		$this->forms = new LicencesForms();
	}

	public function render(): void {
		$types = LicenceTypeManager::get_types();
		?>
		<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
			<h2 class="h5 mb-0"><?php esc_html_e( 'Licence types', 'licencepress' ); ?></h2>
			<div class="btn-group btn-group-sm" role="group" aria-label="Licence type layout toggle">
				<?php echo FormFieldHelper::button( __( 'Table view', 'licencepress' ), array( 'class' => 'btn-primary', 'data-type-layout' => 'table' ) ); ?>
				<?php echo FormFieldHelper::button( __( 'Grid view', 'licencepress' ), array( 'class' => 'btn-outline-primary', 'data-type-layout' => 'grid' ) ); ?>
			</div>
		</div>

		<div class="row g-4" data-licence-type-grid>
			<?php foreach ( $types as $type ) : ?>
				<div class="col-md-6 col-xl-4">
					<div class="card h-100 shadow-sm border-0">
						<div class="card-body d-flex flex-column">
							<div class="d-flex justify-content-between align-items-start gap-2">
								<h3 class="h6 mb-0"><?php echo esc_html( $type['name'] ?? __( 'Untitled licence type', 'licencepress' ) ); ?></h3>
								<span class="badge text-bg-light text-secondary"><?php echo esc_html( $type['status'] ?? __( 'Standard', 'licencepress' ) ); ?></span>
							</div>
							<p class="text-secondary mt-3 mb-3"><?php echo esc_html( $type['description'] ?? __( 'No description available for this licence type.', 'licencepress' ) ); ?></p>
							<div class="mt-auto d-flex justify-content-between align-items-center">
								<span class="small text-secondary"><?php echo esc_html( $type['pattern'] ?? '-' ); ?></span>
								<?php echo FormFieldHelper::button(
									__( 'Edit', 'licencepress' ),
									array(
										'class'          => 'btn-sm btn-outline-primary',
										'data-bs-toggle' => 'modal',
										'data-bs-target' => '#licence-type-edit-modal',
									)
								); ?>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="card shadow-sm mt-4" data-licence-type-table>
			<div class="card-body">
				<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
					<h3 class="h5 mb-0"><?php esc_html_e( 'Licence variants', 'licencepress' ); ?></h3>
					<?php echo FormFieldHelper::button(
						__( 'Add variant', 'licencepress' ),
						array(
							'class'          => 'btn-sm btn-primary',
							'data-bs-toggle' => 'modal',
							'data-bs-target' => '#licence-variant-modal',
						)
					); ?>
				</div>
				<div class="table-responsive">
					<table class="table table-striped align-middle mb-0">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Variant name', 'licencepress' ); ?></th>
								<th><?php esc_html_e( 'Date created', 'licencepress' ); ?></th>
								<th class="text-end"><?php esc_html_e( 'Actions', 'licencepress' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $this->variants() as $variant ) : ?>
								<tr>
									<td><?php echo esc_html( $variant['name'] ); ?></td>
									<td><?php echo esc_html( $variant['created'] ); ?></td>
									<td class="text-end">
										<div class="btn-group btn-group-sm" role="group">
											<?php echo FormFieldHelper::button(
												__( 'Edit', 'licencepress' ),
												array(
													'class'          => 'btn-outline-primary',
													'data-bs-toggle' => 'modal',
													'data-bs-target' => '#licence-variant-modal',
												)
											); ?>
											<?php echo FormFieldHelper::button( __( 'Delete', 'licencepress' ), array( 'class' => 'btn-outline-danger' ) ); ?>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<?php $this->forms->render_variant_modal(); ?>
		<?php $this->forms->render_type_modal(); ?>
		<?php
	}

	public function render_editor(): void {
		$this->forms->render_type_form();
	}

	private function variants(): array {
		return array(
			array(
				'name'    => 'Starter',
				'created' => '2026-08-11',
			),
			array(
				'name'    => 'Business',
				'created' => '2026-08-18',
			),
			array(
				'name'    => 'Agency',
				'created' => '2026-09-02',
			),
		);
	}
}
