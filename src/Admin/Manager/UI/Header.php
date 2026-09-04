<?php
/**
 * Header UI component for LicencePress admin pages.
 *
 * @package LicencePress
 * @subpackage Admin\Manager\UI
 * @since 1.0.0
 */
namespace LicencePress\Admin\Manager\UI;

use LicencePress\Assets\Assets;
use LicencePress\Includes\Functions\Helpers\FormFieldHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Header {
	/**
	 * Renders the header for LicencePress admin pages.
	 *
	 * @return void
	 */
	public static function render(): void {
		$links = array(
			array(
				'label' => __( 'Documentation', 'licencepress' ),
				'url'   => 'https://github.com/TrilB-Dev/LicencePress',
			),
			array(
				'label' => __( 'Community', 'licencepress' ),
				'url'   => 'https://github.com/TrilB-Dev/LicencePress/discussions',
			),
			array(
				'label' => __( 'Extensions', 'licencepress' ),
				'url'   => 'https://github.com/TrilB-Dev/LicencePress',
			),
			array(
				'label' => __( 'Support', 'licencepress' ),
				'url'   => 'https://github.com/TrilB-Dev/LicencePress/issues',
			),
			array(
				'label' => __( 'Roadmap', 'licencepress' ),
				'url'   => 'https://github.com/TrilB-Dev/LicencePress/issues',
			),
			array(
				'label' => __( 'Account', 'licencepress' ),
				'url'   => 'https://github.com/TrilB-Dev/LicencePress',
			),
		);
		?>
		<header class="licencepress-header border-bottom">
			<nav class="navbar navbar-expand-lg" aria-label="<?php esc_attr_e( 'LicencePress header navigation', 'licencepress' ); ?>"> 
				<div class="container-fluid licencepress-shell px-3 px-lg-4">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=licencepress' ) ); ?>">
						<img class="navbar-brand d-flex align-items-center gap-2" src="<?php echo esc_url( Assets::get_image( 'logo/LicencePress-Logo.svg' ) ); ?>" alt="" />
					</a>
					<?php echo FormFieldHelper::button(
						'<span class="navbar-toggler-icon" aria-hidden="true"></span>',
						array(
							'class'          => 'navbar-toggler',
							'type'           => 'button',
							'data-bs-toggle' => 'collapse',
							'data-bs-target' => '#licencepress-header-menu',
							'aria-controls'  => 'licencepress-header-menu',
							'aria-expanded'  => 'false',
							'aria-label'     => __( 'Toggle header navigation', 'licencepress' ),
							'raw'            => true,
						)
					); ?>
					<div class="collapse navbar-collapse" id="licencepress-header-menu">
						<ul class="navbar-nav ms-auto align-items-lg-start gap-lg-1">
							<?php foreach ( $links as $link ) : ?>
								<li class="nav-item"><a class="nav-link" href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $link['label'] ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</nav>
		</header>
		<?php
	}
}
