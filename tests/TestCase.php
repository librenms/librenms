<?php

namespace LibreNMS\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use SnmpsimHelpers;

    public function __construct($name = null, $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        // grab global $snmpsim from bootstrap and make it accessible
        $this->getSnmpsim();
    }

    protected static function dbRequired(): bool
    {
        $db = (bool) getenv('DBTEST');
        if (! $db) {
            static::markTestSkipped('Database tests not enabled.  Set DBTEST=1 to enable.');
        }

        return $db;
    }

    public function dbSetUp()
    {
        if (self::dbRequired()) {
            \LibreNMS\DB\Eloquent::DB()->beginTransaction();
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
}
