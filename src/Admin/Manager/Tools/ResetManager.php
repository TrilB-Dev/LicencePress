<?php
/**
 * ResetManager class for LicencePress plugin.
 *
 * @package LicencePress
 * @subpackage Admin\Manager\Tools
 * @since 1.0.0
 */

namespace LicencePress\Admin\Manager\Tools;

use LicencePress\Admin\Manager\Manager;
use LicencePress\Includes\Functions\Helpers\AjaxHelper;
use LicencePress\Includes\Functions\Helpers\AlertHelper;
use LicencePress\Includes\Functions\Helpers\FormFieldHelper;
use LicencePress\Includes\Functions\Helpers\LoaderHelper;
use LicencePress\Includes\Functions\Helpers\PermissionHelper;
use LicencePress\Includes\Functions\Helpers\RequestHelper;
use LicencePress\Includes\Functions\Helpers\SanitizationHelper;
use LicencePress\Includes\Plugins\PluginInterface;
use LicencePress\Includes\Plugins\Plugins;
use LicencePress\Includes\Plugins\SettingsPageProviderInterface;
use LicencePress\Includes\Settings\Settings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class ResetManager extends Manager {
    /**
     * Register hooks owned by the plugin reset tool.
     *
     * @since 1.0.0
     * @param LoaderHelper|null $loader WordPress hook loader.
     */
    public function __construct( ?LoaderHelper $loader = null ) {
        ( $loader ?? new LoaderHelper() )->register_component( $this, [
            [ 'type' => 'action', 'hook' => 'admin_post_licencepress_reset', 'callback' => 'handle_reset' ],
        ] )->run();
    }

    /**
     * Render the plugin reset tool content.
     *
    * @since 1.0.0
     * @return void
     */
    public function render_page_content(): void {
        if ( '1' === RequestHelper::get_text( 'reset_complete' ) ) {
            AlertHelper::render_admin_notice( __( 'The selected LicencePress data was reset successfully.', 'licencepress' ), 'success' );
        }
        if ( '1' === RequestHelper::get_text( 'reset_failed' ) ) {
            AlertHelper::render_admin_notice( __( 'The selected LicencePress data could not be reset.', 'licencepress' ), 'error' );
        }
        ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5"><?php esc_html_e( 'Reset LicencePress data', 'licencepress' ); ?></h2>
                <p class="text-secondary"><?php esc_html_e( 'Reset LicencePress settings and registered plugin data to their factory values. This does not delete WordPress content.', 'licencepress' ); ?></p>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php echo FormFieldHelper::input( 'action', 'licencepress_reset', [ 'type' => 'hidden' ] ); ?>
                    <?php wp_nonce_field( 'licencepress_reset', 'licencepress_reset_nonce' ); ?>
                    <?php echo FormFieldHelper::label( 'licencepress-reset-scope', __( 'Reset scope', 'licencepress' ) ); ?>
                    <?php echo FormFieldHelper::select( 'scope', $this->scope_options(), 'core', [ 'id' => 'licencepress-reset-scope' ] ); ?>
                    <fieldset class="mt-4" id="licencepress-reset-plugins" data-licencepress-reset-plugins hidden>
                        <legend><?php esc_html_e( 'Plugin data', 'licencepress' ); ?></legend>
                        <?php
        foreach ( $this->plugin_options() as $slug => $plugin ) {
                            echo FormFieldHelper::checkbox( 'plugins[]', $slug, $plugin['name'], [ 'id' => 'licencepress-reset-' . $slug ] );
        }
        ?>
                    </fieldset>
                    <div class="mt-4">
                        <?php echo FormFieldHelper::checkbox( 'confirm', '1', __( 'I understand that this action cannot be undone.', 'licencepress' ), [ 'id' => 'licencepress-reset-confirm', 'required' => true ] ); ?>
                    </div>
                    <div class="mt-4">
                        <?php echo FormFieldHelper::button( __( 'Reset selected data', 'licencepress' ), [ 'type' => 'submit', 'class' => 'btn-danger' ] ); ?>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Process a plugin reset request.
     *
     * @since 1.0.0
     * @return void
     */
    public function handle_reset(): void {
        if ( ! AjaxHelper::is_method( 'POST' ) || ! PermissionHelper::can( 'licencepress_tools_reset' ) || ! AjaxHelper::has_valid_nonce( 'licencepress_reset', 'licencepress_reset_nonce' ) ) {
            wp_die( esc_html__( 'The reset request could not be authorized.', 'licencepress' ), '', [ 'response' => 403 ] );
        }
        if ( ! RequestHelper::boolean( $_POST, 'confirm' ) ) {
            $this->redirect( false );
        }
        $scope = RequestHelper::key( $_POST, 'scope', 'core' );
        $groups = $this->groups_for_scope( $scope, RequestHelper::array( $_POST, 'plugins' ) );
        $success = 'all' === $scope ? Settings::reset_all() : ( ! empty( $groups ) && Settings::reset_groups( $groups ) );
        $this->redirect( $success );
    }

    private function redirect( bool $success ): void {
        wp_safe_redirect( admin_url( 'admin.php?page=licencepress-tools&tool=reset&' . ( $success ? 'reset_complete=1' : 'reset_failed=1' ) ) );
        exit;
    }

    private function scope_options(): array {
        return [ 'all' => __( 'All LicencePress data', 'licencepress' ), 'core' => __( 'LicencePress core only', 'licencepress' ), 'plugins' => __( 'Selected plugins', 'licencepress' ) ];
    }

    private function plugin_options(): array {
        $options = [];
        foreach ( Plugins::get_instance()->get_registered_plugins() as $plugin ) {
            if ( ! $plugin instanceof PluginInterface || ! $plugin instanceof SettingsPageProviderInterface ) {
                continue;
            }
            $page = $plugin->get_settings_page();
            $group = SanitizationHelper::key( $page['settings_group'] ?? '' );
            if ( '' !== $group ) {
                $options[ sanitize_key( $plugin->get_slug() ) ] = [ 'name' => $plugin->get_name(), 'group' => $group ];
            }
        }
        return $options;
    }

    private function groups_for_scope( string $scope, array $plugins ): array {
        if ( 'core' === $scope ) {
            return Settings::core_groups();
        }
        if ( 'plugins' !== $scope ) {
            return [];
        }
        $options = $this->plugin_options();
        $groups = [];
        foreach ( $plugins as $slug ) {
            if ( ! is_scalar( $slug ) ) {
                continue;
            }
            $slug = sanitize_key( (string) $slug );
            if ( isset( $options[ $slug ] ) ) {
                $groups[] = $options[ $slug ]['group'];
            }
        }
        return array_values( array_unique( $groups ) );
    }
}