<?php
/**
 * General tool settings for LicencePress.
 *
 * @package LicencePress\Admin\Manager\Tools
 */
namespace LicencePress\Admin\Manager\Tools;

use LicencePress\Assets\Assets;
use LicencePress\Includes\Functions\Helpers\AlertHelper;
use LicencePress\Includes\Functions\Helpers\FormFieldHelper;
use LicencePress\Includes\Functions\Helpers\LoaderHelper;
use LicencePress\Includes\Functions\Helpers\PermissionHelper;
use LicencePress\Includes\Functions\Helpers\RequestHelper;
use LicencePress\Includes\Settings\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class General extends ToolsManager {
	/**
	 * Register the general tools settings save action.
	 *
	 * @param LoaderHelper|null $loader Optional loader instance.
	 */
	public function __construct( ?LoaderHelper $loader = null ) {
		parent::__construct( false );
		( $loader ?? new LoaderHelper() )->register_component(
			$this,
			array(
				array(
					'type'     => 'action',
					'hook'     => 'admin_post_licencepress_save_tools_general',
					'callback' => 'handle_save',
				),
			)
		)->run();
	}

	/**
	 * Render the general tools settings page.
	 *
	 * @return void
	 */
	public function render_page_content(): void {
		if ( '1' === RequestHelper::get_text( 'settings_saved' ) ) {
			AlertHelper::render_admin_notice( __( 'The general tools settings were saved.', 'licencepress' ), 'success' );
		}

		$values = array(
			'remove_all_data_on_uninstall' => Settings::get_bool( 'remove_all_data_on_uninstall', false ),
			'uninstall_3rd_party_plugins'  => Settings::get_bool( 'uninstall_3rd_party_plugins', false ),
		);
		?>
		<div class="card shadow-sm">
			<div class="card-body">
				<h2 class="h5"><?php esc_html_e( 'General tools settings', 'licencepress' ); ?></h2>
				<p class="text-secondary"><?php esc_html_e( 'Configure the default cleanup behaviour used when LicencePress is uninstalled or deactivated.', 'licencepress' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php echo FormFieldHelper::input( 'action', 'licencepress_save_tools_general', array( 'type' => 'hidden' ) ); ?>
					<?php wp_nonce_field( 'licencepress_tools_general', 'licencepress_tools_general_nonce' ); ?>
					<div class="mt-3">
						<?php echo FormFieldHelper::checkbox(
							'licencepress_tools[remove_all_data_on_uninstall]',
							'1',
							__( 'Remove all LicencePress data on uninstall', 'licencepress' ),
							array(
								'id'      => 'licencepress-remove-all-data-on-uninstall',
								'checked' => ! empty( $values['remove_all_data_on_uninstall'] ),
							)
						); ?>
					</div>
					<div class="mt-3">
						<?php echo FormFieldHelper::checkbox(
							'licencepress_tools[uninstall_3rd_party_plugins]',
							'1',
							__( 'Remove 3rd party plugins on uninstall', 'licencepress' ),
							array(
								'id'      => 'licencepress-uninstall-3rd-party-plugins',
								'checked' => ! empty( $values['uninstall_3rd_party_plugins'] ),
							)
						); ?>
					</div>
					<div class="mt-4">
						<?php echo FormFieldHelper::button(
							__( 'Save settings', 'licencepress' ),
							array(
								'type'  => 'submit',
								'class' => 'btn-primary',
							)
						); ?>
					</div>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Save the general tools settings.
	 *
	 * @return void
	 */
	public function handle_save(): void {
		if ( ! PermissionHelper::can( 'licencepress_tools_general' ) ) {
			wp_die( esc_html__( 'You are not authorized to save these LicencePress tools settings.', 'licencepress' ) );
		}

		check_admin_referer( 'licencepress_tools_general', 'licencepress_tools_general_nonce' );

		$input = isset( $_POST['licencepress_tools'] ) && is_array( $_POST['licencepress_tools'] ) ? wp_unslash( $_POST['licencepress_tools'] ) : array();
		$input = array(
			'remove_all_data_on_uninstall' => ! empty( $input['remove_all_data_on_uninstall'] ),
			'uninstall_3rd_party_plugins'  => ! empty( $input['uninstall_3rd_party_plugins'] ),
		);

		foreach ( $input as $key => $value ) {
			Settings::set( $key, $value );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=licencepress-tools&tool=general&settings_saved=1' ) );
		exit;
	}

	/**
	 * Backward-compatible render signature used by the settings UI.
	 *
	 * @param array<string, mixed> $values Current settings values.
	 * @return void
	 */
	public function render( array $values = array() ): void {
		$values = array(
			'remove_all_data_on_uninstall' => ! empty( $values['remove_all_data_on_uninstall'] ) || Settings::get_bool( 'remove_all_data_on_uninstall', false ),
			'uninstall_3rd_party_plugins'  => ! empty( $values['uninstall_3rd_party_plugins'] ) || Settings::get_bool( 'uninstall_3rd_party_plugins', false ),
		);
		?>
		<tr>
			<th scope="row"><?php echo wp_kses_post( FormFieldHelper::label( 'licencepress-remove-all-data-on-uninstall', __( 'Remove all data on uninstall', 'licencepress' ) ) ); ?></th>
			<td>
				<?php echo wp_kses_post( FormFieldHelper::checkbox(
					'licencepress_tools[remove_all_data_on_uninstall]',
					'1',
					__( 'Remove all LicencePress data when the plugin is uninstalled', 'licencepress' ),
					array(
						'id'      => 'licencepress-remove-all-data-on-uninstall',
						'checked' => ! empty( $values['remove_all_data_on_uninstall'] ),
					)
				) ); ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php echo wp_kses_post( FormFieldHelper::label( 'licencepress-uninstall-3rd-party-plugins', __( 'Third-party plugins on uninstall', 'licencepress' ) ) ); ?></th>
			<td>
				<?php echo wp_kses_post( FormFieldHelper::checkbox(
					'licencepress_tools[uninstall_3rd_party_plugins]',
					'1',
					__( 'Remove 3rd party plugins when LicencePress is uninstalled', 'licencepress' ),
					array(
						'id'      => 'licencepress-uninstall-3rd-party-plugins',
						'checked' => ! empty( $values['uninstall_3rd_party_plugins'] ),
					)
				) ); ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Register page assets.
	 *
	 * @param Assets $assets Assets manager instance.
	 * @return void
	 */
	public function register_assets( Assets $assets ): void {
		$this->register_page_assets( $assets, array( 'licencepress-tools' ), 'tools' );
	}
}