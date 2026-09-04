<?php

namespace LicencePress\Includes;

use LicencePress\Includes\Core\Core;
use LicencePress\Includes\Core\WP\WPLoader;
use LicencePress\Includes\Functions\Helpers\LoggerHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Includes {
	/** @var self|null */
	private static ?self $instance = null;
	/** @var Core */
	private Core $core;
	/** @var array<int, callable> */
	private array $extensions = array();
	/** @var bool */
	private bool $initialized = false;

	private function __construct() {
		$this->core = new Core();
		LoggerHelper::write_log( 'LicencePress core includes initialized.' );
	}

	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

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

	public function is_initialized(): bool {
		return $this->initialized;
	}
}
