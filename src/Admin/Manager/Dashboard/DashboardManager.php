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
use LicencePress\Includes\Settings\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DashboardManager extends Manager {

	/** @var string */
	protected $page;

	public function __construct() {
		$this->page = 'dashboard';
	}

	public function render(): void {
		$this->header( __( 'Licence Dashboard', 'licencepress' ) );

		if ( ! Settings::get_bool( 'first_install_complete', false ) ) {
			$this->render_onboarding_modal();
		}

		$this->render_summary();
		$this->render_cards();
		$this->footer();
	}

	private function render_onboarding_modal(): void {
		Settings::register_group(
			'setup',
			array(
				'first_install_complete'    => false,
				'onboarding_steps_complete' => 0,
			)
		);
		?>
		<div class="modal fade licencepress-onboarding-modal" id="licencepress-onboarding-modal" tabindex="-1" aria-labelledby="licencepress-onboarding-title" aria-hidden="true">
			<div class="modal-dialog modal-lg modal-dialog-centered">
				<div class="modal-content border-0 shadow">
					<div class="modal-header border-0 pb-0">
						<div>
							<span class="badge text-bg-primary-subtle text-primary mb-2"><?php esc_html_e( 'First-time setup', 'licencepress' ); ?></span>
							<h2 class="h3 mb-0" id="licencepress-onboarding-title"><?php esc_html_e( 'Welcome to LicencePress', 'licencepress' ); ?></h2>
						</div>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php esc_attr_e( 'Close onboarding', 'licencepress' ); ?>"></button>
					</div>
					<div class="modal-body py-4">
						<div class="licencepress-onboarding-step" data-step="1">
							<p class="text-secondary mb-3"><?php esc_html_e( 'Let’s configure the essentials for your first licence workflow.', 'licencepress' ); ?></p>
							<div class="row g-3">
								<div class="col-md-4">
									<div class="card h-100 border-0 bg-light">
										<div class="card-body d-flex flex-column">
											<div class="text-primary mb-2"><span class="dashicons dashicons-admin-generic"></span></div>
											<h3 class="h6"><?php esc_html_e( 'General settings', 'licencepress' ); ?></h3>
											<p class="small text-secondary flex-grow-1 mb-3"><?php esc_html_e( 'Review product defaults, validation rules, and licence behaviour.', 'licencepress' ); ?></p>
											<a class="btn btn-sm btn-outline-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=licencepress-settings&tab=general' ) ); ?>"><?php esc_html_e( 'Open settings', 'licencepress' ); ?></a>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="card h-100 border-0 bg-light">
										<div class="card-body d-flex flex-column">
											<div class="text-primary mb-2"><span class="dashicons dashicons-cart"></span></div>
											<h3 class="h6"><?php esc_html_e( 'Payments', 'licencepress' ); ?></h3>
											<p class="small text-secondary flex-grow-1 mb-3"><?php esc_html_e( 'Connect PayPal to unlock checkout, subscriptions, and payment flows.', 'licencepress' ); ?></p>
											<a class="btn btn-sm btn-outline-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=licencepress-paypal' ) ); ?>"><?php esc_html_e( 'Connect PayPal', 'licencepress' ); ?></a>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="card h-100 border-0 bg-light">
										<div class="card-body d-flex flex-column">
											<div class="text-primary mb-2"><span class="dashicons dashicons-shield"></span></div>
											<h3 class="h6"><?php esc_html_e( 'Access control', 'licencepress' ); ?></h3>
											<p class="small text-secondary flex-grow-1 mb-3"><?php esc_html_e( 'Define who can issue, manage, and review licences and customer permissions.', 'licencepress' ); ?></p>
											<a class="btn btn-sm btn-outline-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=licencepress-settings&tab=access' ) ); ?>"><?php esc_html_e( 'Manage access', 'licencepress' ); ?></a>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="licencepress-onboarding-step d-none" data-step="2">
							<h3 class="h5 mb-3"><?php esc_html_e( 'Recommended setup checklist', 'licencepress' ); ?></h3>
							<ul class="list-group list-group-flush">
								<li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-3">
									<span><?php esc_html_e( 'Set the default licence and validation settings for your product catalog.', 'licencepress' ); ?></span>
									<a class="btn btn-sm btn-outline-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=licencepress-settings&tab=general' ) ); ?>"><?php esc_html_e( 'Open', 'licencepress' ); ?></a>
								</li>
								<li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-3">
									<span><?php esc_html_e( 'Review access roles and who can manage licences, tools, and settings.', 'licencepress' ); ?></span>
									<a class="btn btn-sm btn-outline-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=licencepress-settings&tab=access' ) ); ?>"><?php esc_html_e( 'Open', 'licencepress' ); ?></a>
								</li>
								<li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-3">
									<span><?php esc_html_e( 'Connect your PayPal app to enable checkout and subscription purchase flows.', 'licencepress' ); ?></span>
									<a class="btn btn-sm btn-outline-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=licencepress-paypal' ) ); ?>"><?php esc_html_e( 'Connect', 'licencepress' ); ?></a>
								</li>
							</ul>
						</div>
						<div class="licencepress-onboarding-step d-none" data-step="3">
							<h3 class="h5 mb-3"><?php esc_html_e( 'You are ready to launch', 'licencepress' ); ?></h3>
							<div class="alert alert-success border-0 bg-success-subtle text-success-emphasis mb-3">
								<?php esc_html_e( 'Your licence platform is now configured for first issue, customer validation, and secure product access.', 'licencepress' ); ?>
							</div>
							<div class="d-flex flex-wrap gap-2">
								<a class="btn btn-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=licencepress-licences' ) ); ?>"><?php esc_html_e( 'Issue a licence', 'licencepress' ); ?></a>
								<a class="btn btn-outline-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=licencepress-settings&tab=general' ) ); ?>"><?php esc_html_e( 'Review settings', 'licencepress' ); ?></a>
							</div>
						</div>
					</div>
					<div class="modal-footer border-0 pt-0">
						<button type="button" class="btn btn-link text-secondary" data-role="skip"><?php esc_html_e( 'Skip for now', 'licencepress' ); ?></button>
						<button type="button" class="btn btn-outline-secondary" data-role="prev"><?php esc_html_e( 'Back', 'licencepress' ); ?></button>
						<button type="button" class="btn btn-primary" data-role="next"><?php esc_html_e( 'Next', 'licencepress' ); ?></button>
						<button type="button" class="btn btn-success d-none" data-role="finish"><?php esc_html_e( 'Finish setup', 'licencepress' ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	private function render_summary(): void {
		$summary = LicenceManager::summary();
		$cards   = array(
			array(
				'label'       => __( 'Active licences', 'licencepress' ),
				'value'       => $summary['active'] ?? 0,
				'url'         => admin_url( 'admin.php?page=licencepress-licences' ),
				'description' => __( 'Currently valid and live licences.', 'licencepress' ),
				'icon'        => 'dashicons-yes-alt',
			),
			array(
				'label'       => __( 'Expiring soon', 'licencepress' ),
				'value'       => $summary['expiring_soon'] ?? 0,
				'url'         => admin_url( 'admin.php?page=licencepress-tools&tool=export' ),
				'description' => __( 'Licences due for review in the next 30 days.', 'licencepress' ),
				'icon'        => 'dashicons-clock',
			),
			array(
				'label'       => __( 'Revoked', 'licencepress' ),
				'value'       => $summary['revoked'] ?? 0,
				'url'         => admin_url( 'admin.php?page=licencepress-tools&tool=debug' ),
				'description' => __( 'Licence records that have been disabled.', 'licencepress' ),
				'icon'        => 'dashicons-no-alt',
			),
			array(
				'label'       => __( 'Customers', 'licencepress' ),
				'value'       => $summary['customers'] ?? 0,
				'url'         => admin_url( 'admin.php?page=licencepress-settings&tab=access' ),
				'description' => __( 'Unique licence holders and managed customers.', 'licencepress' ),
				'icon'        => 'dashicons-groups',
			),
		);
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
		$cards = apply_filters(
			'licencepress_dashboard_cards',
			array(
				array(
					'title'       => __( 'Issue a licence', 'licencepress' ),
					'description' => __( 'Create a product key for a customer, site, and feature set.', 'licencepress' ),
					'icon'        => 'dashicons-plus-alt',
					'url'         => admin_url( 'admin.php?page=licencepress-licences' ),
					'priority'    => 10,
				),
				array(
					'title'       => __( 'Security settings', 'licencepress' ),
					'description' => __( 'Review validation, key storage, and export protection.', 'licencepress' ),
					'icon'        => 'dashicons-shield',
					'url'         => admin_url( 'admin.php?page=licencepress-settings&tab=general' ),
					'priority'    => 20,
				),
				array(
					'title'       => __( 'Access control', 'licencepress' ),
					'description' => __( 'Set who can issue, revoke, export, and review licences.', 'licencepress' ),
					'icon'        => 'dashicons-admin-users',
					'url'         => admin_url( 'admin.php?page=licencepress-settings&tab=access' ),
					'priority'    => 30,
				),
			)
		);

		if ( is_array( $cards ) ) {
			$cards = array_filter( $cards, array( $this, 'can_render' ) );
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
		$this->register_page_assets( $assets, array( 'licencepress' ), 'dashboard' );
	}
}
