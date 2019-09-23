<?php

namespace LibreNMS\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use LibreNMS\Config;

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
