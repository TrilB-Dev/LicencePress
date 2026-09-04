<?php
/**
 * SettingsManager class for LicencePress plugin.
 *
 * @package LicencePress
 * @subpackage Admin\Manager\Settings
 * @since 1.0.0
 */
namespace LicencePress\Admin\Manager\Settings;

use LicencePress\Admin\Manager\Manager;
use LicencePress\Assets\Assets;
use LicencePress\Admin\Manager\Settings\SettingsAccess;
use LicencePress\Admin\Manager\Settings\SettingsGeneral;
use LicencePress\Admin\Manager\Settings\SettingsPlugins;
use LicencePress\Includes\Functions\Helpers\RequestHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SettingsManager extends Manager {
	/**
	 * Plugin settings page manager instance.
	 *
	 * @var SettingsPlugins
	 */
	private SettingsPlugins $plugins_page;
	/**
	 * Current settings page slug.
	 *
	 * @var string
	 */
	protected $page;

	public function __construct() {
		$this->page         = 'settings';
		$this->plugins_page = new SettingsPlugins();
	}

	public function render(): void {
		$tab = sanitize_key( RequestHelper::get_key( 'tab', 'general' ) );
		$tab = $this->normalize_tab( $tab );

		$this->header( __( 'Settings', 'licencepress' ) );
		?>
		<div id="licencepress-settings-panel" data-current-tab="<?php echo esc_attr( $tab ); ?>">
			<?php $this->render_tab_content( $tab ); ?>
		</div>
		<?php
		$this->footer();
	}

	public function render_tab_content( string $tab ): void {
		$tab               = $this->normalize_tab( $tab );
		$view_capabilities = array(
			'general'     => array( 'licencepress_settings_general_view' ),
			'access'      => array( 'licencepress_settings_access_view' ),
			'plugins'     => array( 'licencepress_settings_plugins_view' ),
			'third-party' => array( 'licencepress_settings_plugins_view', 'licencepress_settings_plugins_ext_view' ),
		);

		if ( $this->plugins_page->has_settings_page( $tab ) && ! $this->plugins_page->can_view_settings_page( $tab ) ) {
			wp_die( esc_html__( 'You are not authorized to view these LicencePress settings.', 'licencepress' ) );
		}

		$can_view = true;
		foreach ( $view_capabilities[ $tab ] ?? array() as $capability ) {
			if ( ! current_user_can( $capability ) ) {
				$can_view = false;
				break;
			}
		}

		if ( ! $can_view ) {
			wp_die( esc_html__( 'You are not authorized to view these LicencePress settings.', 'licencepress' ) );
		}
		?>
		<div class="licencepress-settings-tab-content" role="tabpanel">
			<?php if ( 'general' === $tab ) : ?>
				<div class="card shadow-sm">
					<div class="card-body">
						<div class="mb-3">
							<h2 class="h5 mb-1"><?php esc_html_e( 'Licence configuration', 'licencepress' ); ?></h2>
							<p class="text-secondary mb-0"><?php esc_html_e( 'Set the default commercial rules for generated licences, expiry, and validation.', 'licencepress' ); ?></p>
						</div>
						<table class="form-table" role="presentation"><tbody>
							<?php ( new SettingsGeneral() )->render( array() ); ?>
						</tbody></table>
					</div>
				</div>
			<?php elseif ( 'access' === $tab ) : ?>
				<div class="card shadow-sm">
					<div class="card-body">
						<div class="mb-3">
							<h2 class="h5 mb-1"><?php esc_html_e( 'Access control', 'licencepress' ); ?></h2>
							<p class="text-secondary mb-0"><?php esc_html_e( 'Define who can issue, revoke, export, review, and manage licences.', 'licencepress' ); ?></p>
						</div>
						<table class="form-table" role="presentation"><tbody>
							<?php ( new SettingsAccess() )->render( array() ); ?>
						</tbody></table>
					</div>
				</div>
			<?php else : ?>
				<?php $this->plugins_page->render( $tab ); ?>
			<?php endif; ?>
		</div>
		<?php
	}

	private function normalize_tab( string $tab ): string {
		$allowed = array( 'general', 'access', 'plugins', 'third-party' );
		if ( in_array( $tab, $allowed, true ) || $this->plugins_page->has_settings_page( $tab ) ) {
			return $tab;
		}
		return 'general';
	}

	public function register_assets( Assets $assets ): void {
		$settings_assets              = $this->assets( 'settings' );
		$settings_assets['scripts'][] = array(
			'handle'    => 'licencepress-admin-plugins',
			'src'       => LICENCEPRESS_URL . 'src/Assets/dist/js/plugins.admin.js',
			'deps'      => array( 'licencepress-bootstrap' ),
			'in_footer' => true,
		);
		$assets->register_page( 'licencepress-settings', $settings_assets );
	}
}
