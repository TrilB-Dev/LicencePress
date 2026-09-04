<?php
/**
 * Sidebar UI component for LicencePress admin pages.
 *
 * @package LicencePress
 * @subpackage Admin\Manager\UI
 * @since 1.0.0
 */
namespace LicencePress\Admin\Manager\UI;

use LicencePress\Includes\Functions\Admin\FunctionsSidebar;
use LicencePress\Includes\Functions\Helpers\RequestHelper;
use LicencePress\Includes\Functions\Helpers\SanitizationHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the sidebar from the centralized FunctionsSidebar menu model.
 */
final class Sidebar {
	/**
	 * Render the admin sidebar.
	 *
	 * @return void
	 */
	public static function render(): void {
		$current = RequestHelper::get_key( 'page', 'licencepress' );
		$groups  = FunctionsSidebar::get_sidebar_groups();
		?>
		<aside class="col-12 col-lg-auto licencepress-sidebar-column">
			<div class="licencepress-sidebar position-sticky" style="top: 32px;">
				<div class="d-flex align-items-center justify-content-between mb-3 px-2">
					<span class="small text-uppercase fw-semibold text-secondary"><?php esc_html_e( 'Navigate', 'licencepress' ); ?></span>
					<span class="badge rounded-pill text-bg-light">WP</span>
				</div>
				<nav aria-label="<?php esc_attr_e( 'LicencePress admin navigation', 'licencepress' ); ?>">
					<a class="licencepress-sidebar-link <?php echo 'licencepress' === $current ? 'active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=licencepress' ) ); ?>">
						<?php echo self::render_icon_markup( 'fa-solid fa-house' ); ?><?php esc_html_e( 'Dashboard', 'licencepress' ); ?>
					</a>
					<div id="licencepress-sidebar-groups">
						<?php foreach ( $groups as $key => $group ) : ?>
							<?php $expanded = self::group_is_expanded( $key, $group, $current ); ?>
							<div class="licencepress-sidebar-group">
								<h3 class="licencepress-sidebar-group-heading">
									<button class="licencepress-sidebar-link licencepress-sidebar-group-link border-0 bg-transparent w-100 text-start <?php echo $expanded ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#licencepress-group-<?php echo esc_attr( $key ); ?>" aria-expanded="<?php echo $expanded ? 'true' : 'false'; ?>" aria-controls="licencepress-group-<?php echo esc_attr( $key ); ?>">
										<?php echo self::render_icon_markup( (string) ( $group['icon'] ?? '' ) ); ?><?php echo esc_html( $group['label'] ); ?><span class="ms-auto text-secondary"><?php echo count( $group['items'] ); ?></span>
									</button>
								</h3>
								<div id="licencepress-group-<?php echo esc_attr( $key ); ?>" class="collapse <?php echo $expanded ? 'show' : ''; ?>">
									<div class="nav flex-column licencepress-sidebar-group-items">
										<?php foreach ( $group['items'] as $slug => $item ) : ?>
											<?php $page = self::item_page( $slug ); $query = self::item_query( $slug ); $active = self::item_is_active( $page, $query, $current ); ?>
											<a class="nav-link <?php echo $active ? 'active' : ''; ?>" <?php echo $active ? 'aria-current="page"' : ''; ?> href="<?php echo esc_url( self::item_url( $page, $query ) ); ?>"><?php echo self::render_icon_markup( (string) ( $item['icon'] ?? '' ), true ); ?><?php echo esc_html( $item['label'] ); ?></a>
										<?php endforeach; ?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</nav>
			</div>
		</aside>
		<?php
	}

	private static function render_icon_markup( string $icon, bool $with_spacing = false ): string {
		$icon = trim( $icon );
		if ( '' === $icon ) {
			return '';
		}

		if ( preg_match( '/^(https?:)?\/\//i', $icon ) || preg_match( '/\.(svg|png|jpg|jpeg|webp)(\?.*)?$/i', $icon ) ) {
			return sprintf(
				'<span class="licencepress-sidebar-icon licencepress-sidebar-icon-image" aria-hidden="true"><img src="%1$s" alt="" loading="lazy"%2$s /></span>',
				esc_url( $icon ),
				$with_spacing ? ' class="me-2"' : ''
			);
		}

		return sprintf(
			'<span class="licencepress-sidebar-icon" aria-hidden="true"><i class="%1$s%2$s"></i></span>',
			esc_attr( $icon ),
			$with_spacing ? ' me-2' : ''
		);
	}

	/** @param array<string, mixed> $group */
	private static function group_is_expanded( string $key, array $group, string $current ): bool {
		if ( 'settings' === $key ) {
			return 'licencepress-settings' === $current;
		}
		if ( 'tools' === $key ) {
			return 'licencepress-tools' === $current;
		}

		foreach ( $group['items'] as $slug => $item ) {
			if ( self::item_is_active( self::item_page( $slug ), self::item_query( $slug ), $current ) ) {
				return true;
			}
		}

		return false;
	}

	private static function item_page( string $slug ): string {
		return strtok( $slug, '&' );
	}

	/** @return array<string, string> */
	private static function item_query( string $slug ): array {
		$query = [];
		parse_str( (string) strstr( $slug, '&' ), $query );
		return $query;
	}

	/** @param array<string, string> $query */
	private static function item_url( string $page, array $query ): string {
		$query_string = empty( $query ) ? '' : '?' . http_build_query( $query, '', '&', PHP_QUERY_RFC3986 );
		if ( in_array( $page, [ 'edit.php', 'post-new.php' ], true ) ) {
			return admin_url( $page . $query_string );
		}

		return admin_url( 'admin.php?page=' . $page . ( empty( $query ) ? '' : '&' . ltrim( $query_string, '?' ) ) );
	}

	/** @param array<string, string> $query */
	private static function item_is_active( string $page, array $query, string $current ): bool {
		if ( $page !== $current ) {
			return false;
		}

		foreach ( $query as $key => $value ) {
			if ( (string) RequestHelper::value( $_GET, $key, '' ) !== (string) $value ) {
				return false;
			}
		}

		return true;
	}
}
