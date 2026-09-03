<?php

namespace LicencePress\Includes\Core\WP;

use LicencePress\Includes\Core\Capabilities;
use LicencePress\Includes\Plugins\Plugins;
use LicencePress\Includes\Settings\SettingsManager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Activator {
    /** @var array<int, callable> */
    private static array $callbacks = array();

    /**
     * Register extension activation callbacks.
     *
     * @param callable $callback Callback invoked during activation.
     * @return void
     */
    public static function register( callable $callback ): void {
        self::$callbacks[] = $callback;
    }

    /**
     * Run LicencePress and extension activation tasks.
     *
     * @param array<int, callable>|null $callbacks Optional callbacks for this run.
     * @return void
     */
    public static function activate( ?array $callbacks = null ): void {
        Plugins::get_instance()->init();
        Capabilities::install();
        Database::install();
        SettingsManager::install();
        ( new \LicencePress\Includes\Core\PostType() )->register();
        ( new \LicencePress\Includes\Core\Taxonomy() )->register();

        foreach ( $callbacks ?? self::$callbacks as $callback ) {
            call_user_func( $callback );
        }

        flush_rewrite_rules();
    }
}
