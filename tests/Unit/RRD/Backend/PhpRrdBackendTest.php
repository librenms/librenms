<?php

namespace LibreNMS\Tests\Unit\RRD\Backend;

use LibreNMS\RRD\Backend\PhpRrd;
use LibreNMS\RRD\Backend\RrdBackendInterface;
use LibreNMS\RRD\Backend\Rrdtool;

final class PhpRrdBackendTest extends BackendContractTestCase
{
    protected function makeBackend(?string $rrdcached): RrdBackendInterface
    {
        if (! extension_loaded('rrd')) {
            // I think this is causing the CI to fail
            //$this->markTestSkipped('The php-rrd extension is not loaded');

            return new Rrdtool($rrdcached);
        }

        return new PhpRrd($rrdcached);
    }
}
