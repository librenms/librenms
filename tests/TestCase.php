<?php

namespace LibreNMS\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use SnmpsimHelpers;

    /**
     * Repository root path, usable from static data providers before the app boots
     */
    protected static function basePath(string $subdir = ''): string
    {
        $dir = dirname(__DIR__);

        return $subdir
            ? $dir . '/' . $subdir
            : $dir;
    }

    public function dbSetUp()
    {
        if (getenv('DBTEST')) {
            \LibreNMS\DB\Eloquent::DB()->beginTransaction();
        } else {
            $this->markTestSkipped('Database tests not enabled.  Set DBTEST=1 to enable.');
        }
    }

    public function dbTearDown()
    {
        if (getenv('DBTEST')) {
            try {
                \LibreNMS\DB\Eloquent::DB()->rollBack();
            } catch (\Exception $e) {
                $this->fail("Exception when rolling back transaction.\n" . $e->getTraceAsString());
            }
        }
    }

    protected function tearDown(): void
    {
        $this->beforeApplicationDestroyed(function (): void {
            $this->getConnection()->disconnect();
        });

        parent::tearDown();
    }
}
