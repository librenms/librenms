<?php

namespace LibreNMS\Tests\Unit\RRD\Backend;

use LibreNMS\RRD\Backend\PhpRrd;
use LibreNMS\RRD\Backend\RrdBackendInterface;

abstract class PhpRrdBackendTest extends BackendContractTestCase
{
    protected function makeBackend(?string $rrdcached): RrdBackendInterface
    {
        if (! extension_loaded('rrd')) {
            $this->markTestSkipped('The php-rrd extension is not loaded');
        }

        return new PhpRrd($rrdcached);
    }
}
