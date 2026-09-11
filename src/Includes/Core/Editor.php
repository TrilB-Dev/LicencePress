<?php
/**
 * Editor class for handling LicencePress page creation and editing.
 * 
 * Provides methods for saving and rendering licence pages within the LicencePress plugin.
 * 
 * @package LicencePress\Includes\Core
 * @since 1.0.0
 */
namespace LicencePress\Includes\Core;

use LicencePress\Includes\Functions\Helpers\SanitizationHelper;
use LicencePress\Includes\Functions\Helpers\FormFieldHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Editor {
	/**
	 * Saves a licence page.
	 *
	 * @param int $licence_type_id The ID of the licence.
	 * @param int $licence_type_variant_id The ID of the page (optional).
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function save_licence_type_page( int $licence_type_id, int $licence_type_variant_id = 0 ): bool {
		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ?? '' ) || 'save_licence_page' !== ( $_POST['licencepress_action'] ?? '' ) || ! check_admin_referer( 'licencepress_save_licence_page', 'licencepress_save_licence_page_nonce' ) ) {
			return false;
		}
		$page = $licence_type_variant_id ? get_post( $licence_type_variant_id ) : null;
		if ( ! $licence_type_variant_id && ! current_user_can( 'licencepress_page_create' ) ) {
			return false;
		}
		if ( $licence_type_variant_id && ( ! $page || PostType::PAGE !== $page->post_type || ! current_user_can( 'licencepress_page_edit' ) || ( (int) $page->post_author !== get_current_user_id() && ! current_user_can( 'licencepress_page_edit_others' ) ) || ( 'publish' === $page->post_status && ! current_user_can( 'licencepress_page_edit_published' ) ) ) ) {
			return false;
		}

		$input = wp_unslash( $_POST['licencepress_page'] ?? array() );
		$input = is_array( $input ) ? $input : array();
		$title = SanitizationHelper::text( $input['title'] ?? '' );
		if ( '' === $title ) {
			return false;
		}

		if ( ! current_user_can( 'licencepress_page_publish' ) ) {
			return false;
		}

		$post_id = wp_insert_post(
			array(
				'ID'           => $licence_type_variant_id,
				'post_type'    => PostType::PAGE,
				'post_title'   => $title,
				'post_content' => wp_kses_post( (string) ( $input['content'] ?? '' ) ),
				'post_status'  => 'publish',
				'post_author'  => get_current_user_id(),
			),
			true
		);
		if ( is_wp_error( $post_id ) ) {
			return false;
		}

		update_post_meta( $post_id, '_licencepress_licence_type_id', $licence_type_id );
		return true;
	}

	public static function render_licence_page_form( ?\WP_Post $page = null ): void {
		?>
		<form method="post" class="card shadow-sm">
			<?php wp_nonce_field( 'licencepress_save_licence_page', 'licencepress_save_licence_page_nonce' ); ?>
			<input type="hidden" name="licencepress_action" value="save_licence_page">
			<div class="card-body">
				<div class="mb-3">
					<label class="form-label" for="licencepress-page-title">
						<?php esc_html_e( 'Page Title', 'licencepress' ); ?>
					</label>
					<input class="form-control" id="licencepress-page-title" name="licencepress_page[title]" value="<?php echo esc_attr( $page ? $page->post_title : '' ); ?>" required>
				</div>
				<?php FormFieldHelper::tinymce( 'licencepress-page-content', 'licencepress_page[content]', __( 'Page Content', 'licencepress' ), $page ? $page->post_content : '', 14, true ); ?>
			</div>
			<div class="card-footer d-flex justify-content-end gap-2">
				<a class="btn btn-outline-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=licencepress-manage' ) ); ?>">
					<?php esc_html_e( 'Cancel', 'licencepress' ); ?>
				</a>
				<button class="btn btn-primary" type="submit">
					<?php echo esc_html( $page ? __( 'Save Page', 'licencepress' ) : __( 'Create Page', 'licencepress' ) ); ?>
				</button>
			</div>
		</form>
		<?php
	}
}
