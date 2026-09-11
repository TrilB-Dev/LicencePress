<?php
/**
 * Shared licence forms for the LicencePress admin area.
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

final class LicencesForms {
	public function render_type_form( array $settings = array(), string $nonce = '' ): void {
		$settings = wp_parse_args(
			$settings,
			array(
				'name'                               => 'WordPress Plugin 1',
				'slug'                               => 'wordpress-plugin-1',
				'prefix'                             => 'WPP',
				'suffix'                             => 'PRO',
				'length'                             => 16,
				'pattern'                            => 'prefix-segment-segment',
				'licensor_name'                      => '',
				'licensor_type'                      => 'company',
				'licensor_country'                   => '',
				'licence_pattern_type'               => 'standard',
				'custom_licence_pattern'             => '',
				'exclude_ambiguous_characters'      => array( '0', 'O', '1', 'I', 'l' ),
				'licence_pattern_format'             => 'alphanumeric',
				'licence_pattern_letter_case'        => 'uppercase',
				'licence_pattern_separator'          => '-',
				'renewal_window'                     => 30,
				'grace_period'                       => 7,
				'renewal_policy_mode'                => 'default',
				'renewal_policy_page'                => '',
				'licence_platforms'                  => array( 'website', 'windows_software' ),
				'capability_groups'                  => array(),
			)
		);

		$preview = LicenceTypeManager::generate_preview( $settings );
		$nonce   = '' !== $nonce ? $nonce : wp_create_nonce( 'licencepress_licence_type_form' );
		?>
		<div class="card shadow-sm">
			<div class="card-body">
				<h2 class="h5 mb-3"><?php esc_html_e( 'Licence type builder', 'licencepress' ); ?></h2>
				<form id="licencepress-licence-type-form" method="post" data-nonce="<?php echo esc_attr( $nonce ); ?>">
					<?php echo FormFieldHelper::input( 'id', '', array( 'type' => 'hidden', 'id' => 'licence_type_id' ) ); ?>
					<div class="row g-3">
						<div class="col-md-6">
							<?php echo FormFieldHelper::text_input( 'name', (string) $settings['name'], array( 'id' => 'licence_type_name', 'class' => 'w-100', 'placeholder' => __( 'Licence Name', 'licencepress' ) ) ); ?>
						</div>
						<div class="col-md-6">
							<?php echo FormFieldHelper::text_input( 'slug', (string) $settings['slug'], array( 'id' => 'licence_type_slug', 'class' => 'w-100', 'placeholder' => __( 'licence-slug', 'licencepress' ) ) ); ?>
						</div>
					</div>

					<div class="row g-3 mt-1">
						<div class="col-md-12">
							<?php echo FormFieldHelper::textarea( 'description', (string) ( $settings['description'] ?? '' ), array( 'id' => 'licence_type_description', 'rows' => 3, 'class' => 'w-100', 'placeholder' => __( 'Licence Description', 'licencepress' ) ) ); ?>
						</div>
					</div>

					<div class="row g-3 mt-1">
						<div class="col-md-12">
							<?php echo FormFieldHelper::textarea( 'excerpt', (string) ( $settings['excerpt'] ?? '' ), array( 'id' => 'licence_type_excerpt', 'rows' => 2, 'class' => 'w-100', 'placeholder' => __( 'Licence Excerpt', 'licencepress' ) ) ); ?>
						</div>
					</div>

					<hr class="my-4">
					<div class="row g-3 align-items-end">
						<div class="col-md-6">
							<?php echo FormFieldHelper::text_input( 'licensor_name', (string) ( $settings['licensor_name'] ?? '' ), array( 'id' => 'licence_type_licensor_name', 'class' => 'w-100', 'placeholder' => __( 'Licencor Name', 'licencepress' ) ) ); ?>
						</div>
						<div class="col-md-3">
							<?php echo FormFieldHelper::select(
								'licensor_type',
								array(
									'individual' => __( 'Individual', 'licencepress' ),
									'group' => __( 'Group', 'licencepress' ),
									'company' => __( 'Company', 'licencepress' ),
									'organization' => __( 'Organization', 'licencepress' ),
								),
								(string) ( $settings['licensor_type'] ?? 'company' ),
								array( 'id' => 'licence_type_licensor_type', 'class' => 'w-100' )
							); ?>
						</div>
						<div class="col-md-3">
							<?php echo FormFieldHelper::text_input( 'licensor_country', (string) ( $settings['licensor_country'] ?? '' ), array( 'id' => 'licence_type_licensor_country', 'class' => 'w-100', 'placeholder' => __( 'Licencor Country', 'licencepress' ) ) ); ?>
						</div>
					</div>

					<div class="row g-3 mt-1 align-items-end">
						<div class="col-md-3">
							<?php echo FormFieldHelper::text_input( 'prefix', (string) $settings['prefix'], array( 'id' => 'licence_type_prefix', 'class' => 'w-100', 'placeholder' => 'LP' ) ); ?>
						</div>
						<div class="col-md-3">
							<?php echo FormFieldHelper::select(
								'pattern',
								array(
									'prefix-segment' => __( 'Prefix + segment', 'licencepress' ),
									'prefix-segment-segment' => __( 'Prefix + segment + segment', 'licencepress' ),
									'segment-prefix-segment' => __( 'Segment + prefix + segment', 'licencepress' ),
								),
								$settings['pattern'],
								array( 'id' => 'licence_type_pattern', 'class' => 'w-100' )
							); ?>
						</div>
						<div class="col-md-3">
							<?php echo FormFieldHelper::text_input( 'length', (string) $settings['length'], array( 'id' => 'licence_type_length', 'type' => 'number', 'min' => 8, 'max' => 32, 'step' => 1, 'class' => 'w-100' ) ); ?>
						</div>
						<div class="col-md-3">
							<?php echo FormFieldHelper::select(
								'licence_pattern_type',
								array(
									'standard' => __( 'Standard', 'licencepress' ),
									'custom' => __( 'Custom', 'licencepress' ),
									'checksum' => __( 'Checksum', 'licencepress' ),
								),
								(string) ( $settings['licence_pattern_type'] ?? 'standard' ),
								array( 'id' => 'licence_type_pattern_type', 'class' => 'w-100' )
							); ?>
						</div>
					</div>

					<div class="row g-3 mt-1">
						<div class="col-md-12">
							<div class="border rounded p-3 bg-light">
								<div class="form-check form-switch mb-2">
									<?php echo FormFieldHelper::switch( 'use_default_licence_pattern', '1', __( 'Use default licence settings', 'licencepress' ), array( 'checked' => true, 'id' => 'use_default_licence_pattern' ) ); ?>
								</div>
								<div class="row g-3">
									<div class="col-md-6">
										<?php echo FormFieldHelper::select( 'licence_pattern_format', array( 'alphanumeric' => __( 'Alphanumeric', 'licencepress' ), 'numeric' => __( 'Numeric', 'licencepress' ), 'mixed' => __( 'Mixed', 'licencepress' ) ), (string) ( $settings['licence_pattern_format'] ?? 'alphanumeric' ), array( 'id' => 'licence_type_pattern_format', 'class' => 'w-100' ) ); ?>
									</div>
									<div class="col-md-6">
										<?php echo FormFieldHelper::select( 'licence_pattern_letter_case', array( 'uppercase' => __( 'Uppercase', 'licencepress' ), 'lowercase' => __( 'Lowercase', 'licencepress' ) ), (string) ( $settings['licence_pattern_letter_case'] ?? 'uppercase' ), array( 'id' => 'licence_type_letter_case', 'class' => 'w-100' ) ); ?>
									</div>
									<div class="col-md-6">
										<?php echo FormFieldHelper::text_input( 'licence_pattern_separator', (string) ( $settings['licence_pattern_separator'] ?? '-' ), array( 'id' => 'licence_type_pattern_separator', 'class' => 'w-100', 'placeholder' => '-' ) ); ?>
									</div>
									<div class="col-md-6">
										<?php echo FormFieldHelper::text_input( 'custom_licence_pattern', (string) ( $settings['custom_licence_pattern'] ?? '' ), array( 'id' => 'licence_type_custom_pattern', 'class' => 'w-100', 'placeholder' => 'WPP-XXXX-XXXX' ) ); ?>
									</div>
								</div>
								<div class="mt-3">
									<label class="form-label"><?php esc_html_e( 'Exclude Ambiguous characters', 'licencepress' ); ?></label>
									<div class="d-flex flex-wrap gap-2">
										<?php foreach ( array( '0', 'O', '1', 'I', 'l', '5', 'S' ) as $char ) : ?>
											<div class="form-check form-check-inline">
												<input class="form-check-input" type="checkbox" name="exclude_ambiguous_characters[]" value="<?php echo esc_attr( $char ); ?>" <?php checked( in_array( $char, (array) ( $settings['exclude_ambiguous_characters'] ?? array() ), true ), true ); ?>>
												<label class="form-check-label"><?php echo esc_html( $char ); ?></label>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="row g-3 mt-1 align-items-end">
						<div class="col-md-4">
							<?php echo FormFieldHelper::text_input( 'renewal_window', (string) ( $settings['renewal_window'] ?? 30 ), array( 'id' => 'licence_type_renewal_window', 'type' => 'number', 'min' => 0, 'class' => 'w-100' ) ); ?>
						</div>
						<div class="col-md-4">
							<?php echo FormFieldHelper::text_input( 'grace_period', (string) ( $settings['grace_period'] ?? 7 ), array( 'id' => 'licence_type_grace_period', 'type' => 'number', 'min' => 0, 'class' => 'w-100' ) ); ?>
						</div>
						<div class="col-md-4">
							<?php echo FormFieldHelper::select(
								'renewal_policy_mode',
								array(
									'default' => __( 'Use Default', 'licencepress' ),
									'dedicated' => __( 'Use Dedicated Renewal Template', 'licencepress' ),
									'custom' => __( 'Use Custom', 'licencepress' ),
								),
								(string) ( $settings['renewal_policy_mode'] ?? 'default' ),
								array( 'id' => 'licence_type_renewal_policy_mode', 'class' => 'w-100' )
							); ?>
						</div>
					</div>

					<div class="row g-3 mt-1">
						<div class="col-md-12">
							<?php echo FormFieldHelper::select(
								'renewal_policy_page',
								array( '' => __( 'Select a page', 'licencepress' ) ) + wp_list_pluck( get_pages(), 'post_title', 'ID' ),
								(string) ( $settings['renewal_policy_page'] ?? '' ),
								array( 'id' => 'licence_type_renewal_policy_page', 'class' => 'w-100' )
							); ?>
						</div>
					</div>

					<div class="mt-4">
						<h3 class="h6 mb-3"><?php esc_html_e( 'Licence Platforms', 'licencepress' ); ?></h3>
						<div class="d-flex flex-wrap gap-2">
							<?php foreach ( array( 'website' => __( 'Website', 'licencepress' ), 'windows_software' => __( 'Windows Software', 'licencepress' ), 'linux_software' => __( 'Linux Software', 'licencepress' ), 'macos_software' => __( 'MacOS Software', 'licencepress' ), 'android_devices' => __( 'Android Devices', 'licencepress' ), 'ios_devices' => __( 'iOS Devices', 'licencepress' ) ) as $value => $label ) : ?>
								<div class="form-check form-check-inline">
									<input class="form-check-input" type="checkbox" name="licence_platforms[]" value="<?php echo esc_attr( $value ); ?>" <?php checked( in_array( $value, (array) ( $settings['licence_platforms'] ?? array() ), true ), true ); ?>>
									<label class="form-check-label"><?php echo esc_html( $label ); ?></label>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="mt-4">
						<h3 class="h6 mb-3"><?php esc_html_e( 'Licence Capabilities', 'licencepress' ); ?></h3>
						<div class="accordion" id="licence-type-capability-accordion">
							<?php foreach ( array( 'Product Access', 'Support', 'Security', 'Management' ) as $index => $category_name ) : ?>
								<div class="accordion-item">
									<h4 class="accordion-header" id="capability-category-<?php echo esc_attr( $index ); ?>-heading">
										<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#capability-category-<?php echo esc_attr( $index ); ?>" aria-expanded="false">
											<?php echo esc_html( $category_name ); ?>
										</button>
									</h4>
									<div id="capability-category-<?php echo esc_attr( $index ); ?>" class="accordion-collapse collapse" data-bs-parent="#licence-type-capability-accordion">
										<div class="accordion-body">
											<div class="mb-3">
												<label class="form-label"><?php esc_html_e( 'Category Name', 'licencepress' ); ?></label>
												<?php echo FormFieldHelper::text_input( 'capability_category_name[]', $category_name, array( 'class' => 'w-100' ) ); ?>
											</div>
											<div class="mb-3">
												<label class="form-label"><?php esc_html_e( 'Category Slug', 'licencepress' ); ?></label>
												<?php echo FormFieldHelper::text_input( 'capability_category_slug[]', sanitize_title( $category_name ), array( 'class' => 'w-100' ) ); ?>
											</div>
											<div class="border rounded p-3 bg-white">
												<?php foreach ( array( 'feature_access' => __( 'Feature Access', 'licencepress' ), 'reporting' => __( 'Reporting', 'licencepress' ), 'updates' => __( 'Updates', 'licencepress' ) ) as $capability_value => $capability_label ) : ?>
													<div class="row g-2 align-items-center mb-2">
														<div class="col-md-4">
															<?php echo FormFieldHelper::text_input( 'capability_name[]', $capability_label, array( 'class' => 'w-100' ) ); ?>
														</div>
														<div class="col-md-5">
															<?php echo FormFieldHelper::text_input( 'capability_description[]', '', array( 'class' => 'w-100', 'placeholder' => __( 'Capability description', 'licencepress' ) ) ); ?>
														</div>
														<div class="col-md-2">
															<?php echo FormFieldHelper::text_input( 'capability_slug[]', $capability_value, array( 'class' => 'w-100' ) ); ?>
														</div>
														<div class="col-md-1 text-end">
															<input class="form-check-input" type="checkbox" name="capability_enabled[]" value="1" checked>
														</div>
													</div>
												<?php endforeach; ?>
											</div>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="mt-4 p-3 border rounded bg-light">
						<div class="small text-uppercase text-muted"><?php esc_html_e( 'Preview', 'licencepress' ); ?></div>
						<div id="licencepress-licence-type-preview" class="mt-2 fw-semibold fs-5"><?php echo esc_html( $preview['sample'] ?? '' ); ?></div>
						<div class="small text-secondary mt-2"><?php esc_html_e( 'As you change the prefix, suffix, length, or pattern the key preview updates with the selected rules.', 'licencepress' ); ?></div>
					</div>

					<div class="mt-3 d-flex gap-2">
						<?php echo FormFieldHelper::button( __( 'Save licence type', 'licencepress' ), array( 'type' => 'submit' ) ); ?>
						<?php echo FormFieldHelper::button( __( 'Refresh preview', 'licencepress' ), array( 'class' => 'btn-outline-secondary', 'type' => 'button' ) ); ?>
					</div>
				</form>
			</div>
		</div>
		<?php
	}

	public function render_variant_modal(): void {
		?>
		<div class="modal fade" id="licence-variant-modal" tabindex="-1" aria-labelledby="licence-variant-modal-label" aria-hidden="true">
			<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h2 class="modal-title h5" id="licence-variant-modal-label"><?php esc_html_e( 'Licence variant', 'licencepress' ); ?></h2>
						<?php echo FormFieldHelper::button(
							'',
							array(
								'class' => 'btn-close',
								'type' => 'button',
								'data-bs-dismiss' => 'modal',
								'aria-label' => __( 'Close', 'licencepress' ),
							)
						); ?>
					</div>
					<div class="modal-body">
						<div class="row g-3">
							<div class="col-md-6">
								<label class="form-label" for="licence-variant-name"><?php esc_html_e( 'Variant name', 'licencepress' ); ?></label>
								<?php echo FormFieldHelper::text_input( 'licence_variant_name', '', array( 'id' => 'licence-variant-name', 'class' => 'w-100', 'placeholder' => __( 'Starter', 'licencepress' ) ) ); ?>
							</div>
							<div class="col-md-6">
								<label class="form-label" for="licence-variant-slug"><?php esc_html_e( 'Variant slug', 'licencepress' ); ?></label>
								<?php echo FormFieldHelper::text_input( 'licence_variant_slug', '', array( 'id' => 'licence-variant-slug', 'class' => 'w-100', 'placeholder' => __( 'starter', 'licencepress' ) ) ); ?>
							</div>
						</div>

						<div class="row g-3 mt-1">
							<div class="col-md-12">
								<label class="form-label" for="licence-variant-excerpt"><?php esc_html_e( 'Variant excerpt', 'licencepress' ); ?></label>
								<?php echo FormFieldHelper::textarea( 'licence_variant_excerpt', '', array( 'id' => 'licence-variant-excerpt', 'rows' => 2, 'class' => 'w-100', 'placeholder' => __( 'Short variant summary', 'licencepress' ) ) ); ?>
							</div>
						</div>

						<div class="row g-3 mt-1">
							<div class="col-md-12">
								<label class="form-label" for="licence-variant-description"><?php esc_html_e( 'Variant description', 'licencepress' ); ?></label>
								<?php echo FormFieldHelper::textarea( 'licence_variant_description', '', array( 'id' => 'licence-variant-description', 'rows' => 4, 'class' => 'w-100', 'placeholder' => __( 'Detailed variant information', 'licencepress' ) ) ); ?>
							</div>
						</div>

						<div class="row g-3 mt-3">
							<div class="col-md-6">
								<label class="form-label"><?php esc_html_e( 'Licence term', 'licencepress' ); ?></label>
								<div class="btn-group w-100 flex-wrap" role="group" aria-label="Licence term selector">
									<?php foreach ( array( '1 Month' => '1m', '3 Months' => '3m', '6 Months' => '6m', '9 Months' => '9m', '12 Months' => '12m', 'Lifetime' => 'lifetime' ) as $label => $value ) : ?>
										<?php echo FormFieldHelper::button( $label, array( 'class' => 'btn-outline-primary', 'type' => 'button', 'data-value' => $value ) ); ?>
									<?php endforeach; ?>
								</div>
							</div>
							<div class="col-md-6">
								<label class="form-label" for="licence-variant-price"><?php esc_html_e( 'Variant price', 'licencepress' ); ?></label>
								<?php echo FormFieldHelper::text_input( 'licence_variant_price', '', array( 'id' => 'licence-variant-price', 'type' => 'number', 'min' => 0, 'step' => '0.01', 'class' => 'w-100' ) ); ?>
							</div>
						</div>

						<div class="row g-3 mt-1">
							<div class="col-md-6">
								<label class="form-label" for="licence-variant-installations"><?php esc_html_e( 'Installations', 'licencepress' ); ?></label>
								<?php echo FormFieldHelper::text_input( 'licence_variant_installations', '0', array( 'id' => 'licence-variant-installations', 'type' => 'number', 'min' => 0, 'max' => 9999, 'class' => 'w-100' ) ); ?>
							</div>
							<div class="col-md-6">
								<label class="form-label" for="licence-variant-type"><?php esc_html_e( 'Licence type', 'licencepress' ); ?></label>
								<?php echo FormFieldHelper::select(
									'licence_variant_type',
									array(
										'starter' => __( 'Starter', 'licencepress' ),
										'business' => __( 'Business', 'licencepress' ),
										'agency' => __( 'Agency', 'licencepress' ),
									),
									'',
									array( 'id' => 'licence-variant-type', 'class' => 'w-100' )
								); ?>
							</div>
						</div>

						<div class="mt-4">
							<h3 class="h6 mb-3"><?php esc_html_e( 'Variant capabilities', 'licencepress' ); ?></h3>
							<div class="accordion" id="licence-variant-capabilities-accordion">
								<?php foreach ( array( 'Access', 'Usage', 'Support' ) as $index => $category ) : ?>
									<div class="accordion-item">
										<h4 class="accordion-header" id="variant-capability-<?php echo esc_attr( $index ); ?>-heading">
											<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#variant-capability-<?php echo esc_attr( $index ); ?>" aria-expanded="false">
												<?php echo esc_html( $category ); ?>
											</button>
										</h4>
										<div id="variant-capability-<?php echo esc_attr( $index ); ?>" class="accordion-collapse collapse" data-bs-parent="#licence-variant-capabilities-accordion">
											<div class="accordion-body">
												<?php foreach ( array( 'feature_access', 'support_priority', 'export_access' ) as $capability ) : ?>
													<div class="d-flex justify-content-between align-items-center border-bottom py-2">
														<div>
															<div class="fw-semibold"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $capability ) ) ); ?></div>
															<div class="small text-muted"><?php esc_html_e( 'Toggle capability access for this variant.', 'licencepress' ); ?></div>
														</div>
														<div class="form-check form-switch">
															<input class="form-check-input" type="checkbox" name="variant_capabilities[]" value="<?php echo esc_attr( $capability ); ?>" checked>
														</div>
													</div>
												<?php endforeach; ?>
											</div>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<?php echo FormFieldHelper::button( __( 'Cancel', 'licencepress' ), array( 'class' => 'btn-outline-secondary', 'type' => 'button', 'data-bs-dismiss' => 'modal' ) ); ?>
						<?php echo FormFieldHelper::button( __( 'Save variant', 'licencepress' ), array( 'type' => 'button' ) ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public function render_type_modal(): void {
		?>
		<div class="modal fade" id="licence-type-edit-modal" tabindex="-1" aria-labelledby="licence-type-edit-modal-label" aria-hidden="true">
			<div class="modal-dialog modal-lg modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h2 class="modal-title h5" id="licence-type-edit-modal-label"><?php esc_html_e( 'Edit licence type', 'licencepress' ); ?></h2>
						<?php echo FormFieldHelper::button(
							'',
							array(
								'class' => 'btn-close',
								'type' => 'button',
								'data-bs-dismiss' => 'modal',
								'aria-label' => __( 'Close', 'licencepress' ),
							)
						); ?>
					</div>
					<div class="modal-body">
						<?php $this->render_type_form(); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
