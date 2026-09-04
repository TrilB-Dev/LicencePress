<?php
/**
 * ImportManager class for LicencePress plugin.
 *
 * @package LicencePress
 * @subpackage Admin\Manager\Tools
 * @since 1.0.0
 */
namespace LicencePress\Admin\Manager\Tools;

use LicencePress\Admin\Manager\Manager;
use LicencePress\Includes\Functions\Helpers\FormFieldHelper;
use LicencePress\Includes\Functions\Helpers\UrlHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ImportManager extends Manager {
	/**
	 * Render the JSON import form below the tools settings form.
	 *
	 * @return void
	 */
	public function render(): void {
		?>
		<form
			method="post"
			action="<?php echo esc_url( UrlHelper::admin_action( 'licencepress_import' ) ); ?>"
			enctype="multipart/form-data"
			class="card licencepress-import-form shadow-sm mt-4"
		>
			<?php echo wp_kses_post( FormFieldHelper::input( 'action', 'licencepress_import', array( 'type' => 'hidden' ) ) ); ?>
			<?php wp_nonce_field( 'licencepress_import' ); ?>
			<div class="card-body">
				<?php
				echo wp_kses_post(
					FormFieldHelper::label(
						'licencepress-import-file',
						__( 'Import licence JSON', 'licencepress' ),
						array(
							'description'  => __( 'Select a JSON export containing LicencePress records, customer data, and validation metadata.', 'licencepress' ),
							'tooltip'      => __( 'Import should use a valid archive and, where required, a password-protected file to preserve security.', 'licencepress' ),
							'tooltip_icon' => 'fa-file-import',
						)
					)
				);
				echo wp_kses_post(
					FormFieldHelper::input(
						'licencepress_import_file',
						'',
						array(
							'id'       => 'licencepress-import-file',
							'type'     => 'file',
							'class'    => 'mb-3',
							'accept'   => 'application/json,.json',
							'required' => true,
						)
					)
				);
				echo wp_kses_post(
					FormFieldHelper::button(
						__( 'Import JSON', 'licencepress' ),
						array(
							'type'  => 'submit',
							'class' => 'btn-primary',
						)
					)
				);
				?>
			</div>
		</form>
		<?php
	}
}
