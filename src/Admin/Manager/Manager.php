<?php
/**
 * Manager class for LicencePress plugin.
 *
 * @package LicencePress
 * @subpackage Admin\Manager
 * @since 1.0.0
 */
namespace LicencePress\Admin\Manager;

use LicencePress\Assets\Assets;
use LicencePress\Admin\Manager\UI\Footer;
use LicencePress\Admin\Manager\UI\Header;
use LicencePress\Admin\Manager\UI\Sidebar;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Manager {
	/**
	 * Register one asset bundle for a group of admin pages.
	 *
	 * @param Assets             $assets Asset registry.
	 * @param array<int, string> $pages Admin page slugs.
	 * @param string             $bundle Compiled bundle name.
	 * @return void
	 */
	protected function register_page_assets( Assets $assets, array $pages, string $bundle ): void {
		foreach ( $pages as $page ) {
			$assets->register_page( $page, $this->assets( $bundle ) );
		}
	}

	/**
	 * Build the asset definition for an admin bundle.
	 *
	 * @param string $bundle Compiled bundle name.
	 * @return array<string, array<int, array<string, mixed>>> Asset definition.
	 */
	protected function assets( string $bundle ): array {
		$bundle_name = $this->resolve_bundle_name( $bundle );

		return array(
			'styles'  => array(
				array(
					'handle' => 'licencepress-admin-' . $bundle,
					'src'    => LICENCEPRESS_URL . 'src/Assets/dist/css/' . $bundle_name . '.css',
					'deps'   => array( 'licencepress-bootstrap', 'licencepress-admin-ui' ),
				),
			),
			'scripts' => array(
				array(
					'handle'    => 'licencepress-admin-' . $bundle,
					'src'       => LICENCEPRESS_URL . 'src/Assets/dist/js/' . $bundle_name . '.js',
					'deps'      => array( 'licencepress-bootstrap', 'licencepress-admin-ui' ),
					'in_footer' => true,
				),
			),
		);
	}

	/**
	 * Map the logical bundle name to the compiled asset file name.
	 *
	 * @param string $bundle Logical bundle name.
	 * @return string Compiled bundle file name.
	 */
	protected function resolve_bundle_name( string $bundle ): string {
		$mapping = array(
			'dashboard' => 'dashboard.admin',
			'debug'     => 'debug.admin',
			'settings'  => 'admin.settings',
			'plugins'   => 'plugins.admin',
		);

		return $mapping[ $bundle ] ?? ( $bundle . '.admin' );
	}

	/**
	 * Render the shared admin page header.
	 *
	 * @param string $title Page title.
	 * @return void
	 */
	protected function header( string $title ): void {
		echo '<div class="wrap licencepress-admin">';
		Header::render();
		?>
		<main class="licencepress-admin-main">
			<div class="container-fluid licencepress-shell px-3 px-lg-4 py-4">
				<div class="row g-4">
					<?php Sidebar::render(); ?>
					<section class="col-12 col-lg flex-grow-1" aria-labelledby="licencepress-page-title">
						<div class="licencepress-page-heading d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
							<h1 class="h1 mb-0" id="licencepress-page-title"><?php echo esc_html( $title ); ?></h1>
						</div>
		<?php
	}

	/**
	 * Render the shared admin page footer.
	 *
	 * @return void
	 */
	protected function footer(): void {
		Footer::render();
		echo '</div>';
	}

	/**
	 * Render a dashboard statistic card.
	 *
	 * @param string $label Card label.
	 * @param mixed  $value Card value.
	 * @param string $slug Destination admin page slug.
	 * @return void
	 */
	protected function card( string $label, $value, string $slug ): void {
		printf(
			'<div class="col-md-6 col-xl-3 mb-4"><div class="card licencepress-dashboard-card h-100 shadow-sm"><div class="card-body"><h2 class="h6 text-muted">%s</h2><p class="display-6 mb-0"><a class="text-decoration-none" href="%s">%s</a></p></div></div></div>',
			esc_html( $label ),
			esc_url( admin_url( 'admin.php?page=' . $slug ) ),
			esc_html( (string) $value )
		);
	}
}
