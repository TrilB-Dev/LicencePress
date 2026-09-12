<?php
/**
 * Includes class.
 *
 * @package LicencePress\Includes
 */
namespace LicencePress\Includes;

use LicencePress\Includes\Core\Core;
use LicencePress\Includes\Core\WP\WPLoader;
use LicencePress\Includes\Functions\Helpers\LoggerHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Includes {
	/**
	 * Singleton instance of the Includes class.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;
	/**
	 * Core instance managed by the Includes class.
	 *
	 * @var Core
	 */
	private Core $core;
	/**
	 * List of registered extension initializers.
	 *
	 * @var array<int, callable>
	 */
	private array $extensions = array();
	/**
	 * Indicates whether the Includes class has been initialized.
	 *
	 * @var bool
	 */
	private bool $initialized = false;
	/**
	 * Private constructor to enforce singleton pattern.
	 */
	private function __construct() {
		$this->core = new Core();
		LoggerHelper::write_log( 'LicencePress core includes initialized.' );
	}
	/**
	 * Get the singleton instance of the Includes class.
	 *
	 * @return self The singleton instance.
	 */
	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	/**
	 * Initialize the Includes class and its extensions.
	 *
	 * @return void
	 */
	public function init(): void {
		if ( $this->initialized ) {
			return;
		}

		$this->core->register();
		foreach ( $this->extensions as $extension ) {
			call_user_func( $extension, $this );
		}
		$this->initialized = true;
	}

	/**
	 * Get the Core instance managed by the Includes class.
	 *
	 * @return Core The Core instance.
	 */
	public function core(): Core {
		return $this->core;
	}

	/**
	 * Queue an extension initializer for the shared Includes lifecycle.
	 *
	 * Extensions registered after initialization are invoked immediately.
	 *
	 * @param callable $extension Callback receiving this Includes instance.
	 * @return self
	 */
	public function register_extension( callable $extension ): self {
		if ( $this->initialized ) {
			call_user_func( $extension, $this );
		} else {
			$this->extensions[] = $extension;
		}

		return $this;
	}

	/**
	 * Attach Core registration to an external LicencePress loader.
	 *
	 * @param WPLoader $loader Loader owned by the main runtime or an extension.
	 * @param string   $hook WordPress action name.
	 * @param int      $priority Hook priority.
	 * @return self
	 */
	public function register_hooks( WPLoader $loader, string $hook = 'init', int $priority = 10 ): self {
		$this->core->register_hooks( $loader, $hook, $priority );
		return $this;
	}

	/**
	 * Check if the Includes class has been initialized.
	 *
	 * @return bool True if initialized, false otherwise.
	 */
	public function is_initialized(): bool {
		return $this->initialized;
	}
}
