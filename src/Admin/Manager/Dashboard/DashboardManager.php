<?php
/**
 * DashboardManager class for the LicencePress plugin.
 *
 * @package LicencePress
 */
namespace LicencePress\Admin\Manager\Dashboard;

use LicencePress\Admin\Manager\Manager;
use LicencePress\Assets\Assets;
use LicencePress\Includes\Licence\LicenceManager;
use LicencePress\Includes\Plugins\DashboardProviderInterface;
use LicencePress\Includes\Plugins\Plugins;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class DashboardManager extends Manager {

    protected $page;

    public function __construct() {
        $this->page = 'dashboard';
    }

    public function render(): void {
        $this->header( __( 'Licence Dashboard', 'licencepress' ) );
        $this->render_summary();
        $this->render_cards();
        $this->footer();
    }

    private function render_summary(): void {
        $summary = LicenceManager::summary();
        $cards = [
            [
                'label' => __( 'Active licences', 'licencepress' ),
                'value' => $summary['active'] ?? 0,
                'url' => admin_url( 'admin.php?page=licencepress-licences' ),
                'description' => __( 'Currently valid and live licences.', 'licencepress' ),
                'icon' => 'dashicons-yes-alt',
            ],
            [
                'label' => __( 'Expiring soon', 'licencepress' ),
                'value' => $summary['expiring_soon'] ?? 0,
                'url' => admin_url( 'admin.php?page=licencepress-tools&tool=export' ),
                'description' => __( 'Licences due for review in the next 30 days.', 'licencepress' ),
                'icon' => 'dashicons-clock',
            ],
            [
                'label' => __( 'Revoked', 'licencepress' ),
                'value' => $summary['revoked'] ?? 0,
                'url' => admin_url( 'admin.php?page=licencepress-tools&tool=debug' ),
                'description' => __( 'Licence records that have been disabled.', 'licencepress' ),
                'icon' => 'dashicons-no-alt',
            ],
            [
                'label' => __( 'Customers', 'licencepress' ),
                'value' => $summary['customers'] ?? 0,
                'url' => admin_url( 'admin.php?page=licencepress-settings&tab=access' ),
                'description' => __( 'Unique licence holders and managed customers.', 'licencepress' ),
                'icon' => 'dashicons-groups',
            ],
        ];
        ?>
        <section class="mb-4" aria-labelledby="licencepress-dashboard-summary">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 id="licencepress-dashboard-summary" class="h5 mb-0"><?php esc_html_e( 'Licence overview', 'licencepress' ); ?></h2>
                <span class="small text-secondary"><?php esc_html_e( 'Live revenue and access health', 'licencepress' ); ?></span>
            </div>
            <div class="row g-3">
                <?php foreach ( $cards as $card ) : ?>
                    <div class="col-md-6 col-xl-3">
                        <a class="licencepress-summary-card h-100 d-flex flex-column gap-1" href="<?php echo esc_url( $card['url'] ?? '' ); ?>">
                            <span class="licencepress-summary-icon dashicons <?php echo esc_attr( $card['icon'] ?? 'dashicons-admin-generic' ); ?>" aria-hidden="true"></span>
                            <span class="text-uppercase small fw-semibold text-secondary"><?php echo esc_html( $card['label'] ?? __( 'Metric', 'licencepress' ) ); ?></span>
                            <strong class="h4 mb-0"><?php echo esc_html( (string) ( $card['value'] ?? 0 ) ); ?></strong>
                            <span class="small text-secondary"><?php echo esc_html( $card['description'] ?? '' ); ?></span>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
    }

    private function render_cards(): void {
        $cards = apply_filters( 'licencepress_dashboard_cards', [
            [
                'title' => __( 'Issue a licence', 'licencepress' ),
                'description' => __( 'Create a product key for a customer, site, and feature set.', 'licencepress' ),
                'icon' => 'dashicons-plus-alt',
                'url' => admin_url( 'admin.php?page=licencepress-licences' ),
                'priority' => 10,
            ],
            [
                'title' => __( 'Security settings', 'licencepress' ),
                'description' => __( 'Review validation, key storage, and export protection.', 'licencepress' ),
                'icon' => 'dashicons-shield',
                'url' => admin_url( 'admin.php?page=licencepress-settings&tab=general' ),
                'priority' => 20,
            ],
            [
                'title' => __( 'Access control', 'licencepress' ),
                'description' => __( 'Set who can issue, revoke, export, and review licences.', 'licencepress' ),
                'icon' => 'dashicons-admin-users',
                'url' => admin_url( 'admin.php?page=licencepress-settings&tab=access' ),
                'priority' => 30,
            ],
        ] );

        if ( is_array( $cards ) ) {
            $cards = array_filter( $cards, [ $this, 'can_render' ] );
            usort( $cards, static fn( $left, $right ) => (int) ( $left['priority'] ?? 100 ) <=> (int) ( $right['priority'] ?? 100 ) );
        }

        foreach ( Plugins::get_instance()->get_registered_plugins() as $plugin ) {
            if ( $plugin instanceof DashboardProviderInterface && Plugins::get_instance()->is_plugin_enabled( $plugin->get_slug() ) ) {
                foreach ( $plugin->get_dashboard_cards() as $card ) {
                    if ( is_array( $card ) && $this->can_render( $card ) ) {
                        $cards[] = $card;
                    }
                }
            }
        }
        ?>
        <section aria-labelledby="licencepress-dashboard-cards">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 id="licencepress-dashboard-cards" class="h5 mb-0"><?php esc_html_e( 'Quick actions', 'licencepress' ); ?></h2>
                <span class="small text-secondary"><?php esc_html_e( 'Licence operations and controls', 'licencepress' ); ?></span>
            </div>
            <div class="row g-3">
                <?php foreach ( $cards as $card ) : ?>
                    <div class="col-md-6 col-xl-4">
                        <a class="licencepress-summary-card h-100 d-flex flex-column gap-1" href="<?php echo esc_url( $card['url'] ?? '' ); ?>">
                            <span class="licencepress-summary-icon dashicons <?php echo esc_attr( $card['icon'] ?? 'dashicons-admin-generic' ); ?>" aria-hidden="true"></span>
                            <span class="fw-semibold text-body"><?php echo esc_html( $card['title'] ?? __( 'Action', 'licencepress' ) ); ?></span>
                            <span class="small text-secondary"><?php echo esc_html( $card['description'] ?? '' ); ?></span>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
    }

    private function can_render( $item ): bool {
        return is_array( $item ) && ( empty( $item['capability'] ) || current_user_can( $item['capability'] ) );
    }

    public function register_assets( Assets $assets ): void {
        $this->register_page_assets( $assets, [ 'licencepress' ], 'dashboard' );
    }
}
