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
use LicencePress\Admin\Manager\Settings\SettingsBilling;
use LicencePress\Admin\Manager\Settings\SettingsPolicies;
use LicencePress\Includes\Functions\Helpers\LoaderHelper;
use LicencePress\Includes\Functions\Helpers\RequestHelper;
use LicencePress\Includes\Settings\Settings;
use LicencePress\Includes\Functions\Admin\FunctionsPlugins;
use LicencePress\Includes\Functions\Admin\FunctionsSettings;

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
	 * The instance of the FunctionsSettings class that handles the plugin's settings functionality.
	 *
	 * @var FunctionsSettings
	 * @since 1.0.0
	 * @access protected
	 */
	protected FunctionsSettings $settings_functions;
	/**
	 * Current settings page slug.
	 *
	 * @var string
	 */
	protected $page;
	/**
	 * Constructor for the settings manager.
	 */
	public function __construct() {
		$this->page               = 'settings';
		$this->plugins_page       = new SettingsPlugins();
		$this->settings_functions = new FunctionsSettings( new FunctionsPlugins() );
		$this->settings_functions->register_admin_post_hooks( new LoaderHelper() );
	}
	/**
	 * Renders the settings page.
	 *
	 * @return void
	 */
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
	/**
	 * Renders the content for a specific settings tab.
	 *
	 * @param string $tab The tab to render.
	 * @return void
	 */
	public function render_tab_content( string $tab ): void {
		$tab               = $this->normalize_tab( $tab );
		$view_capabilities = array(
			'general'     => array( 'licencepress_settings_general_view' ),
			'billing'     => array( 'licencepress_settings_general_view' ),
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
							<h5 class="h5 mb-1"><?php esc_html_e( 'Licence configuration', 'licencepress' ); ?></h5>
							<p class="text-secondary mb-0"><?php esc_html_e( 'Set the default commercial rules for generated licences, expiry, and validation.', 'licencepress' ); ?></p>
						</div>
						<table class="form-table" role="presentation"><tbody>
							<?php ( new SettingsGeneral() )->render( Settings::get_group( 'general', array() ) ?? array() ); ?>
						</tbody></table>
					</div>
				</div>
			<?php elseif ( 'billing' === $tab ) : ?>
				<div class="card shadow-sm">
					<div class="card-body">
						<div class="mb-3">
							<h5 class="h5 mb-1"><?php esc_html_e( 'Billing settings', 'licencepress' ); ?></h5>
							<p class="text-secondary mb-0"><?php esc_html_e( 'Configure the default billing profile and invoice appearance for generated customer invoices.', 'licencepress' ); ?></p>
						</div>
							<?php ( new SettingsBilling() )->render( Settings::get_group( 'billing', array() ) ?? array() ); ?>
					</div>
				</div>
			<?php elseif ( 'access' === $tab ) : ?>
				<div class="card shadow-sm">
					<div class="card-body">
						<div class="mb-3">
							<h5 class="h5 mb-1"><?php esc_html_e( 'Access control', 'licencepress' ); ?></h5>
							<p class="text-secondary mb-0"><?php esc_html_e( 'Define who can issue, revoke, export, review, and manage licences.', 'licencepress' ); ?></p>
						</div>
						<table class="form-table" role="presentation"><tbody>
							<?php ( new SettingsAccess() )->render( Settings::get_group( 'access', array() ) ?? array() ); ?>
						</tbody></table>
					</div>
				</div>
			<?php elseif ( 'policies' === $tab ) : ?>
				<div class="card shadow-sm">
					<div class="card-body">
						<div class="mb-3">
							<h5 class="h5 mb-1"><?php esc_html_e( 'Policies', 'licencepress' ); ?></h5>
							<p class="text-secondary mb-0"><?php esc_html_e( 'Define the policies for managing licences.', 'licencepress' ); ?></p>
						</div>
						<table class="form-table" role="presentation"><tbody>
							<?php ( new SettingsPolicies() )->render( Settings::get_group( 'policies', array() ) ?? array() ); ?>
						</tbody></table>
					</div>
				</div>
			<?php else : ?>
				<?php $this->plugins_page->render( $tab ); ?>
			<?php endif; ?>
		</div>
		<?php
	}
	/**
	 * Normalizes the tab value to ensure it is valid.
	 *
	 * @param string $tab The tab to normalize.
	 * @return string The normalized tab.
	 */
	private function normalize_tab( string $tab ): string {
		$allowed = array( 'general', 'billing', 'access', 'policies', 'plugins', 'third-party' );
		if ( in_array( $tab, $allowed, true ) || $this->plugins_page->has_settings_page( $tab ) ) {
			return $tab;
		}
		return 'general';
	}
	/**
	 * Registers the assets for the settings page.
	 *
	 * @param Assets $assets The assets manager instance.
	 * @return void
	 */
	public function register_assets( Assets $assets ): void {
		$settings_assets              = $this->assets( 'settings' );
		$settings_assets['scripts'][] = array(
			'handle'    => 'licencepress-admin-plugins',
			'src'       => LICENCEPRESS_URL . 'src/Assets/dist/js/admin.plugins.js',
			'deps'      => array( 'licencepress-bootstrap' ),
			'in_footer' => true,
		);
		$assets->register_page( 'licencepress-settings', $settings_assets );
	}
}
