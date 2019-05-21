<?php

namespace LibreNMS\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class LaravelTestCase extends BaseTestCase
{
    use CreatesApplication;
    use SnmpsimHelpers;

    public function dbSetUp()
    {
        if (getenv('DBTEST')) {
            \LibreNMS\DB\Eloquent::boot();
            \LibreNMS\DB\Eloquent::setStrictMode();
            \LibreNMS\DB\Eloquent::DB()->beginTransaction();
        } else {
            $this->markTestSkipped('Database tests not enabled.  Set DBTEST=1 to enable.');
        }
    }

    public function dbTearDown()
    {
        if (getenv('DBTEST')) {
            \LibreNMS\DB\Eloquent::DB()->rollBack();
        }
    }
}
