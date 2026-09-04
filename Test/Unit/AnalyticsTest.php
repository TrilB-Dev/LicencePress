<?php

namespace LicencePress\Test\Unit;

use PHPUnit\Framework\TestCase;

final class AnalyticsTest extends TestCase {
    public function test_analytics_tracker_exists_and_is_callable(): void {
        $this->assertTrue( class_exists( \LicencePress\Includes\Analytics\Analytics::class ) );
        $this->assertTrue( method_exists( \LicencePress\Includes\Analytics\Analytics::class, 'track_view' ) );
        $this->assertTrue( is_callable( [ \LicencePress\Includes\Analytics\Analytics::class, 'track_view' ] ) );
    }
}
