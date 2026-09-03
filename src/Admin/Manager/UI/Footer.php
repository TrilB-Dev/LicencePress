<?php
/**
 * Footer UI component for LicencePress admin pages.
 *
 * @package LicencePress
 * @subpackage Admin\Manager\UI
 * @since 1.0.0
 */
namespace LicencePress\Admin\Manager\UI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Footer {
	public static function render(): void {
		?>
					</section>
				</div>
			</div>
		</main>
		<footer class="licencepress-footer border-top">
			<div class="container-fluid px-3 px-lg-4 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
				<span class="small text-secondary">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> LicencePress</span>
				<span class="small text-secondary"><?php esc_html_e( 'Powered by', 'licencepress' ); ?> <a class="fw-semibold text-decoration-none" href="https://github.com/TrilB-Dev/LicencePress" target="_blank" rel="noopener noreferrer">LicencePress</a></span>
			</div>
		</footer>
		<?php
	}
}
