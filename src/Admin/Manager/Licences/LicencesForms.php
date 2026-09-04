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
				'name'    => 'WordPress Plugin 1',
				'prefix'  => 'WPP',
				'suffix'  => 'PRO',
				'length'  => 16,
				'pattern' => 'prefix-segment-segment',
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
					<div class="row g-3 align-items-end">
						<div class="col-md-4">
							<?php echo FormFieldHelper::text_input(
								'name',
								$settings['name'],
								array(
									'id'          => 'licence_type_name',
									'class'       => 'w-100',
									'placeholder' => __( 'WordPress Plugin 1', 'licencepress' ),
								)
							); ?>
						</div>
						<div class="col-md-2">
							<?php echo FormFieldHelper::text_input(
								'prefix',
								$settings['prefix'],
								array(
									'id'          => 'licence_type_prefix',
									'class'       => 'w-100',
									'placeholder' => 'WPP',
								)
							); ?>
						</div>
						<div class="col-md-2">
							<?php echo FormFieldHelper::text_input(
								'suffix',
								$settings['suffix'],
								array(
									'id'          => 'licence_type_suffix',
									'class'       => 'w-100',
									'placeholder' => 'PRO',
								)
							); ?>
						</div>
						<div class="col-md-2">
							<?php echo FormFieldHelper::text_input(
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
							); ?>
						</div>
						<div class="col-md-2">
							<?php echo FormFieldHelper::select(
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
							); ?>
						</div>
					</div>

					<div class="row g-3 mt-1">
						<div class="col-md-12">
							<?php echo FormFieldHelper::textarea(
								'description',
								'',
								array(
									'id'          => 'licence_type_description',
									'rows'        => 3,
									'class'       => 'w-100',
									'placeholder' => __( 'Describe this licence type, its features, or the intended product bundle.', 'licencepress' ),
								)
							); ?>
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
			<div class="modal-dialog modal-dialog-centered">
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
						<div class="mb-3">
							<label class="form-label" for="licence-variant-name"><?php esc_html_e( 'Variant name', 'licencepress' ); ?></label>
							<?php echo FormFieldHelper::text_input( 'licence_variant_name', '', array( 'id' => 'licence-variant-name', 'class' => 'w-100' ) ); ?>
						</div>
						<div class="mb-3">
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
