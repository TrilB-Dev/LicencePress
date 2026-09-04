<?php

namespace LicencePress\Includes\Core\WP;

use LicencePress\Includes\Core\Capabilities;
use LicencePress\Includes\Plugins\Plugins;
use LicencePress\Includes\Settings\SettingsManager;
use LicencePress\Includes\Settings\Settings;
use LicencePress\Includes\Licence\LicenceRepository;
use LicencePress\Includes\Licence\KeyManager;
use LicencePress\Includes\Core\PostType;
use LicencePress\Includes\Core\Taxonomy;

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
        Settings::register_group( 'setup', [
            'first_install_complete' => false,
            'onboarding_steps_complete' => 0,
        ] );

        LicenceRepository::register_schema();
        KeyManager::ensure_configured();
        Plugins::get_instance()->init();
        Capabilities::install();
        Database::install();
        SettingsManager::install();
        ( new PostType() )->register();
        ( new Taxonomy() )->register();

        foreach ( $callbacks ?? self::$callbacks as $callback ) {
            call_user_func( $callback );
        }

        flush_rewrite_rules();
    }
}
