<?php
/**
 * Licence manager coordinator for the LicencePress admin area.
 *
 * @package LicencePress
 * @subpackage Admin\Manager\Licences
 * @since 1.0.0
 */
namespace LicencePress\Admin\Manager\Licences;

use LicencePress\Admin\Manager\Manager;
use LicencePress\Assets\Assets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LicencesManager extends Manager {
	/**
	 * Overview page renderer.
	 *
	 * @var LicencesOverview
	 */
	private LicencesOverview $overview;

	/**
	 * Licence types page renderer.
	 *
	 * @var LicencesTypes
	 */
	private LicencesTypes $types;
	/**
	 * Constructor.
	 * 
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->overview = new LicencesOverview();
		$this->types    = new LicencesTypes();
	}
	/**
	 * Renders the overview page.
	 *
	 * @since 1.0.0
	 */
	public function render(): void {
		$this->header( __( 'Licences', 'licencepress' ) );
		$this->overview->render();
		$this->footer();
	}
	/**
	 * Renders the add licence type page.
	 *
	 * @since 1.0.0
	 */
	public function render_add_type(): void {
		$this->header( __( 'Add Licence Type', 'licencepress' ) );
		$this->types->render_editor();
		$this->footer();
	}

	/**
	 * Renders the manage licence types page.
	 *
	 * @since 1.0.0
	 */
	public function render_manage_types(): void {
		$this->header( __( 'Manage Licence Types', 'licencepress' ) );
		$this->types->render();
		$this->footer();
	}

	/**
	 * Renders the manage licences page.
	 *
	 * @since 1.0.0
	 */
	public function render_manage_licences(): void {
		$this->header( __( 'Manage Licences', 'licencepress' ) );
		$this->overview->render_customer_overview();
		$this->footer();
	}

	/**
	 * Registers the assets for the licences manager.
	 *
	 * @since 1.0.0
	 */
	public function register_assets( Assets $assets ): void {
		$this->register_page_assets(
			$assets,
			array(
				'licencepress-licences',
				'licencepress-licence-types',
				'licencepress-licence-management',
				'licencepress-licence-types-add',
			),
			'settings'
		);
	}
}
