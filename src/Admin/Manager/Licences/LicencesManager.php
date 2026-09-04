<?php

namespace LicencePress\Admin\Manager\Licences;

use LicencePress\Admin\Manager\Manager;
use LicencePress\Assets\Assets;
use LicencePress\Includes\Functions\Helpers\FormFieldHelper;
use LicencePress\Includes\Licence\LicenceManager;
use LicencePress\Includes\Licence\LicenceTypeManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LicencesManager extends Manager {
	public function render(): void {
		$this->header( __( 'Licences', 'licencepress' ) );
		$this->render_summary();
		$this->render_form();
		$this->render_type_table();
		$this->footer();
	}

	public function render_add_type(): void {
		$this->header( __( 'Add Licence Type', 'licencepress' ) );
		$this->render_form();
		$this->footer();
	}

	public function render_manage_types(): void {
		$this->header( __( 'Manage Licence Types', 'licencepress' ) );
		$this->render_type_table();
		$this->footer();
	}

	public function render_manage_licences(): void {
		$this->header( __( 'Manage Licences', 'licencepress' ) );
		$this->render_licence_table();
		$this->footer();
	}

	public function register_assets( Assets $assets ): void {
		$this->register_page_assets( $assets, array( 'licencepress-licences', 'licencepress-licence-types', 'licencepress-licence-management', 'licencepress-licence-types-add' ), 'settings' );
	}

	private function render_summary(): void {
		$summary = LicenceManager::summary();
		?>
		<div class="row g-3 mb-4">
			<div class="col-md-3">
				<div class="card h-100">
					<div class="card-body">
						<div class="small text-uppercase text-muted"><?php esc_html_e( 'Active', 'licencepress' ); ?></div>
						<div class="display-6 mb-0"><?php echo esc_html( (string) ( $summary['active'] ?? 0 ) ); ?></div>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card h-100">
					<div class="card-body">
						<div class="small text-uppercase text-muted"><?php esc_html_e( 'Expiring Soon', 'licencepress' ); ?></div>
						<div class="display-6 mb-0"><?php echo esc_html( (string) ( $summary['expiring_soon'] ?? 0 ) ); ?></div>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card h-100">
					<div class="card-body">
						<div class="small text-uppercase text-muted"><?php esc_html_e( 'Revoked', 'licencepress' ); ?></div>
						<div class="display-6 mb-0"><?php echo esc_html( (string) ( $summary['revoked'] ?? 0 ) ); ?></div>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card h-100">
					<div class="card-body">
						<div class="small text-uppercase text-muted"><?php esc_html_e( 'Customers', 'licencepress' ); ?></div>
						<div class="display-6 mb-0"><?php echo esc_html( (string) ( $summary['customers'] ?? 0 ) ); ?></div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	private function render_form(): void {
		$settings = array(
			'name'    => 'WordPress Plugin 1',
			'prefix'  => 'WPP',
			'suffix'  => 'PRO',
			'length'  => 16,
			'pattern' => 'prefix-segment-segment',
		);

		$preview = LicenceTypeManager::generate_preview( $settings );
		$nonce   = wp_create_nonce( 'licencepress_licence_type_form' );
		?>
		<div class="card mb-4">
			<div class="card-body">
				<h2 class="h5 mb-3"><?php esc_html_e( 'Licence type builder', 'licencepress' ); ?></h2>
				<form id="licencepress-licence-type-form" data-nonce="<?php echo esc_attr( $nonce ); ?>" method="post">
					<input type="hidden" name="id" id="licence_type_id" value="" />
					<div class="row g-3 align-items-end">
						<div class="col-md-4">
							<?php
							echo FormFieldHelper::text_input(
								'name',
								$settings['name'],
								array(
									'id'          => 'licence_type_name',
									'class'       => 'w-100',
									'placeholder' => __( 'WordPress Plugin 1', 'licencepress' ),
								)
							);
							?>
						</div>
						<div class="col-md-2">
							<?php
							echo FormFieldHelper::text_input(
								'prefix',
								$settings['prefix'],
								array(
									'id'          => 'licence_type_prefix',
									'class'       => 'w-100',
									'placeholder' => 'WPP',
								)
							);
							?>
						</div>
						<div class="col-md-2">
							<?php
							echo FormFieldHelper::text_input(
								'suffix',
								$settings['suffix'],
								array(
									'id'          => 'licence_type_suffix',
									'class'       => 'w-100',
									'placeholder' => 'PRO',
								)
							);
							?>
						</div>
						<div class="col-md-2">
							<?php
							echo FormFieldHelper::text_input(
								'length',
								(string) $settings['length'],
								array(
									'id'    => 'licence_type_length',
									'type'  => 'number',
									'min'   => 8,
									'max'   => 32,
									'step'  => 1,
									'class' => 'w-100',
								)
							);
							?>
						</div>
						<div class="col-md-2">
							<?php
							echo FormFieldHelper::select(
								'pattern',
								array(
									'prefix-segment' => __( 'Prefix + segment', 'licencepress' ),
									'prefix-segment-segment' => __( 'Prefix + segment + segment', 'licencepress' ),
									'segment-prefix-segment' => __( 'Segment + prefix + segment', 'licencepress' ),
								),
								$settings['pattern'],
								array(
									'id'    => 'licence_type_pattern',
									'class' => 'w-100',
								)
							);
							?>
						</div>
					</div>

					<div class="row g-3 mt-1">
						<div class="col-md-12">
							<?php
							echo FormFieldHelper::textarea(
								'description',
								'',
								array(
									'id'          => 'licence_type_description',
									'rows'        => 3,
									'class'       => 'w-100',
									'placeholder' => __( 'Describe this licence type, its features, or the intended product bundle.', 'licencepress' ),
								)
							);
							?>
						</div>
					</div>

					<div class="mt-4 p-3 border rounded bg-light">
						<div class="small text-uppercase text-muted"><?php esc_html_e( 'Preview', 'licencepress' ); ?></div>
						<div id="licencepress-licence-type-preview" class="mt-2 fw-semibold fs-5"><?php echo esc_html( $preview['sample'] ?? '' ); ?></div>
						<div class="small text-secondary mt-2"><?php esc_html_e( 'As you change the prefix, suffix, length, or pattern the key preview updates with the selected rules.', 'licencepress' ); ?></div>
					</div>

					<div class="mt-3 d-flex gap-2">
						<button type="submit" class="btn btn-primary" id="licencepress-licence-type-submit"><?php esc_html_e( 'Save licence type', 'licencepress' ); ?></button>
						<button type="button" class="btn btn-outline-secondary" id="licencepress-licence-type-refresh"><?php esc_html_e( 'Refresh preview', 'licencepress' ); ?></button>
						<button type="button" class="btn btn-outline-warning d-none" id="licencepress-licence-type-cancel"><?php esc_html_e( 'Cancel edit', 'licencepress' ); ?></button>
					</div>
				</form>
			</div>
		</div>
		<script>
		(function () {
			const form = document.getElementById('licencepress-licence-type-form');
			if (!form || typeof window.ajaxurl === 'undefined') {
				return;
			}

			const previewTarget = document.getElementById('licencepress-licence-type-preview');
			const typeIdField = document.getElementById('licence_type_id');
			const submitButton = document.getElementById('licencepress-licence-type-submit');
			const cancelButton = document.getElementById('licencepress-licence-type-cancel');

			const resetForm = () => {
				form.reset();
				if (typeIdField) {
					typeIdField.value = '';
				}
				if (submitButton) {
					submitButton.textContent = '<?php esc_html_e( 'Save licence type', 'licencepress' ); ?>';
				}
				if (cancelButton) {
					cancelButton.classList.add('d-none');
				}
			};

			const refreshPreview = () => {
				const formData = new FormData(form);
				formData.append('action', 'licencepress_preview_licence_type');
				formData.append('nonce', form.dataset.nonce || '');

				fetch(window.ajaxurl, {
					method: 'POST',
					body: formData,
					credentials: 'same-origin',
				})
					.then((response) => response.json())
					.then((payload) => {
						if (payload && payload.success && payload.data && payload.data.sample) {
							previewTarget.textContent = payload.data.sample;
						}
					})
					.catch(() => undefined);
			};

			Array.from(form.querySelectorAll('input, select, textarea')).forEach((field) => {
				field.addEventListener('input', refreshPreview);
				field.addEventListener('change', refreshPreview);
			});

			form.addEventListener('submit', (event) => {
				event.preventDefault();
				const formData = new FormData(form);
				formData.append('action', 'licencepress_save_licence_type');
				formData.append('nonce', form.dataset.nonce || '');

				fetch(window.ajaxurl, {
					method: 'POST',
					body: formData,
					credentials: 'same-origin',
				})
					.then((response) => response.json())
					.then((payload) => {
						if (payload && payload.success) {
							window.location.reload();
						}
					})
					.catch(() => undefined);
			});

			const refreshButton = document.getElementById('licencepress-licence-type-refresh');
			if (refreshButton) {
				refreshButton.addEventListener('click', refreshPreview);
			}

			if (cancelButton) {
				cancelButton.addEventListener('click', resetForm);
			}

			document.querySelectorAll('[data-licence-type-edit]').forEach((button) => {
				button.addEventListener('click', function () {
					const id = this.getAttribute('data-licence-type-edit');
					if (!id) {
						return;
					}

					const formData = new FormData();
					formData.append('action', 'licencepress_load_licence_type');
					formData.append('id', id);
					formData.append('nonce', form.dataset.nonce || '');

					fetch(window.ajaxurl, {
						method: 'POST',
						body: formData,
						credentials: 'same-origin',
					})
						.then((response) => response.json())
						.then((payload) => {
							if (!payload || !payload.success || !payload.data || !payload.data.type) {
								return;
							}

							const type = payload.data.type;
							if (typeIdField) {
								typeIdField.value = String(type.id || '');
							}
							document.getElementById('licence_type_name').value = type.name || '';
							document.getElementById('licence_type_prefix').value = type.prefix || '';
							document.getElementById('licence_type_suffix').value = type.suffix || '';
							document.getElementById('licence_type_length').value = type.length || 12;
							document.getElementById('licence_type_pattern').value = type.pattern || 'prefix-segment';
							document.getElementById('licence_type_description').value = type.description || '';

							if (submitButton) {
								submitButton.textContent = '<?php esc_html_e( 'Update licence type', 'licencepress' ); ?>';
							}
							if (cancelButton) {
								cancelButton.classList.remove('d-none');
							}
							refreshPreview();
						})
						.catch(() => undefined);
				});
			});

				document.querySelectorAll('[data-licence-type-retire]').forEach((button) => {
				button.addEventListener('click', function () {
					const id = this.getAttribute('data-licence-type-retire');
					const retired = this.getAttribute('data-licence-type-retired') === '1';
					if (!id) {
						return;
					}

					const formData = new FormData();
					formData.append('action', 'licencepress_toggle_licence_type_retired');
					formData.append('id', id);
					formData.append('retired', retired ? '0' : '1');
					formData.append('nonce', form.dataset.nonce || '');

					fetch(window.ajaxurl, {
						method: 'POST',
						body: formData,
						credentials: 'same-origin',
					})
						.then((response) => response.json())
						.then((payload) => {
							if (payload && payload.success) {
								window.location.reload();
							}
						})
						.catch(() => undefined);
				});
			});

			document.querySelectorAll('[data-licence-type-delete]').forEach((button) => {
				button.addEventListener('click', function () {
					const id = this.getAttribute('data-licence-type-delete');
					if (!id || !window.confirm('<?php esc_html_e( 'Delete this licence type?', 'licencepress' ); ?>')) {
						return;
					}

					const formData = new FormData();
					formData.append('action', 'licencepress_delete_licence_type');
					formData.append('id', id);
					formData.append('nonce', form.dataset.nonce || '');

					fetch(window.ajaxurl, {
						method: 'POST',
						body: formData,
						credentials: 'same-origin',
					})
						.then((response) => response.json())
						.then((payload) => {
							if (payload && payload.success) {
								window.location.reload();
							}
						})
						.catch(() => undefined);
				});
			});
		})();
		</script>
		<?php
		$this->render_code_examples();
	}

	private function render_code_examples(): void {
		$examples = LicenceTypeManager::code_examples();
		?>
		<div class="card mt-4">
			<div class="card-body">
				<h2 class="h5 mb-3"><?php esc_html_e( 'Code examples', 'licencepress' ); ?></h2>
				<div class="row g-3">
					<?php foreach ( $examples as $example ) : ?>
						<div class="col-md-4">
							<div class="border rounded h-100 bg-light-subtle">
								<div class="px-3 pt-3 small text-uppercase text-muted"><?php echo esc_html( $example['title'] ?? __( 'Example', 'licencepress' ) ); ?></div>
								<pre class="mb-0 p-3 overflow-auto"><code><?php echo esc_html( $example['code'] ?? '' ); ?></code></pre>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}

	private function render_type_table(): void {
		$types = LicenceTypeManager::get_types();
		?>
		<div class="card mb-4">
			<div class="card-body">
				<h2 class="h5 mb-3"><?php esc_html_e( 'Licence types', 'licencepress' ); ?></h2>
				<div class="table-responsive">
					<table class="table table-striped align-middle mb-0">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Name', 'licencepress' ); ?></th>
								<th><?php esc_html_e( 'Status', 'licencepress' ); ?></th>
								<th><?php esc_html_e( 'Variant Of', 'licencepress' ); ?></th>
								<th><?php esc_html_e( 'Prefix', 'licencepress' ); ?></th>
								<th><?php esc_html_e( 'Suffix', 'licencepress' ); ?></th>
								<th><?php esc_html_e( 'Length', 'licencepress' ); ?></th>
								<th><?php esc_html_e( 'Pattern', 'licencepress' ); ?></th>
								<th><?php esc_html_e( 'Actions', 'licencepress' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( empty( $types ) ) : ?>
								<tr>
									<td colspan="7" class="text-muted"><?php esc_html_e( 'No licence types created yet.', 'licencepress' ); ?></td>
								</tr>
							<?php else : ?>
								<?php foreach ( $types as $type ) : ?>
									<tr>
										<td><?php echo esc_html( $type['name'] ?? '' ); ?></td>
										<td>
											<?php if ( ! empty( $type['is_retired'] ) ) : ?>
												<span class="badge bg-warning text-dark"><?php esc_html_e( 'Retired', 'licencepress' ); ?></span>
											<?php else : ?>
												<span class="badge bg-success"><?php esc_html_e( 'Active', 'licencepress' ); ?></span>
											<?php endif; ?>
										</td>
										<td>
											<?php
												$parent_id = $type['parent_id'] ?? 0;
											if ( ! empty( $parent_id ) ) {
												/* translators: %s is the parent licence type identifier. */
												echo esc_html( sprintf( __( 'Parent #%s', 'licencepress' ), (int) $parent_id ) );
											} else {
												echo esc_html__( 'Root type', 'licencepress' );
											}
											?>
										</td>
										<td><?php echo esc_html( $type['prefix'] ?? '' ); ?></td>
										<td><?php echo esc_html( $type['suffix'] ?? '' ); ?></td>
										<td><?php echo esc_html( (string) ( $type['length'] ?? 12 ) ); ?></td>
										<td><?php echo esc_html( $type['pattern'] ?? 'prefix-segment' ); ?></td>
										<td>
											<div class="d-flex flex-wrap gap-2">
												<button type="button" class="btn btn-sm btn-outline-secondary" data-licence-type-edit="<?php echo esc_attr( (string) ( $type['id'] ?? 0 ) ); ?>"><?php esc_html_e( 'Edit', 'licencepress' ); ?></button>
												<button type="button" class="btn btn-sm <?php echo ! empty( $type['is_retired'] ) ? 'btn-outline-success' : 'btn-outline-warning'; ?>" data-licence-type-retire="<?php echo esc_attr( (string) ( $type['id'] ?? 0 ) ); ?>" data-licence-type-retired="<?php echo esc_attr( ! empty( $type['is_retired'] ) ? '1' : '0' ); ?>"><?php echo ! empty( $type['is_retired'] ) ? esc_html__( 'Reactivate', 'licencepress' ) : esc_html__( 'Retire', 'licencepress' ); ?></button>
												<button type="button" class="btn btn-sm btn-outline-danger" data-licence-type-delete="<?php echo esc_attr( (string) ( $type['id'] ?? 0 ) ); ?>"><?php esc_html_e( 'Delete', 'licencepress' ); ?></button>
											</div>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php
	}

	private function render_licence_table(): void {
		$licences = LicenceManager::list_for_customer( 'demo-customer' );
		?>
		<div class="card">
			<div class="card-body">
				<h2 class="h5 mb-3"><?php esc_html_e( 'Issued licences', 'licencepress' ); ?></h2>
				<div class="table-responsive">
					<table class="table table-striped align-middle mb-0">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Customer', 'licencepress' ); ?></th>
								<th><?php esc_html_e( 'Product', 'licencepress' ); ?></th>
								<th><?php esc_html_e( 'Status', 'licencepress' ); ?></th>
								<th><?php esc_html_e( 'Expires', 'licencepress' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( empty( $licences ) ) : ?>
								<tr>
									<td colspan="4" class="text-muted"><?php esc_html_e( 'No licences issued yet.', 'licencepress' ); ?></td>
								</tr>
							<?php else : ?>
								<?php foreach ( $licences as $licence ) : ?>
									<tr>
										<td><?php echo esc_html( $licence['customer_id'] ?? '' ); ?></td>
										<td><?php echo esc_html( $licence['product_id'] ?? '' ); ?></td>
										<td><?php echo esc_html( $licence['status'] ?? 'active' ); ?></td>
										<td><?php echo esc_html( $licence['expires_at'] ?? __( 'No expiry', 'licencepress' ) ); ?></td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php
	}
}
