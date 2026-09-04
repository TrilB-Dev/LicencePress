<?php
/**
 * ExportManager class for LicencePress plugin.
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

final class ExportManager extends Manager {
	/**
	 * Render the JSON export form below the tools settings form.
	 *
	 * @return void
	 */
	public function render_page_content(): void {
		?>
		<div class="card shadow-sm">
			<div class="card-body">
				<h2 class="h5"><?php esc_html_e( 'Export licence data', 'licencepress' ); ?></h2>
				<p class="text-secondary"><?php esc_html_e( 'Download a protected JSON export of licence records, customer meta, and validation data.', 'licencepress' ); ?></p>
				<?php echo wp_kses_post(
					FormFieldHelper::button(
						esc_html__( 'Export licence JSON', 'licencepress' ),
						array(
							'href'  => UrlHelper::admin_action_nonce( 'licencepress_export', 'licencepress_export' ),
							'class' => 'btn-outline-primary',
						)
					)
				); ?>
			</div>
		</div>
		<?php
	}

	public function render(): void {
		?>
		<tr>
			<th scope="row"><?php echo wp_kses_post(
				FormFieldHelper::label(
					'licencepress-export',
					esc_html__( 'Import and export', 'licencepress' ),
					array(
						'description' => __( 'Export or import LicencePress data as a password-protected JSON archive.', 'licencepress' ),
						'tooltip'     => __( 'Exports are protected with a WordPress nonce and should use a password whenever shared with partners.', 'licencepress' ),
					)
				)
			); ?></th>
			<td><?php echo wp_kses_post(
				FormFieldHelper::button(
					esc_html__( 'Export licence JSON', 'licencepress' ),
					array(
						'href'  => UrlHelper::admin_action_nonce( 'licencepress_export', 'licencepress_export' ),
						'class' => 'btn-outline-primary',
					)
				)
			); ?></td>
		</tr>
		<tr>
			<th scope="row"><?php echo FormFieldHelper::label(
				'licencepress-database-manager',
				esc_html__( 'Database manager', 'licencepress' ),
				array(
					'description' => __( 'Licence records are kept in the plugin database tables and managed through the core lifecycle.', 'licencepress' ),
					'tooltip'     => __( 'Manual database changes are not required for normal licence operations.', 'licencepress' ),
				)
			); ?></th>
			<td><?php esc_html_e( 'Managed automatically', 'licencepress' ); ?></td>
		</tr>
		<?php
	}
}
