<?php

namespace LibreNMS\Tests\Unit\RRD\Backend;

use LibreNMS\RRD\Backend\PhpRrd;
use LibreNMS\RRD\Backend\RrdBackendInterface;
use PHPUnit\Framework\Attributes\Group;

#[Group('external-dependencies')] // needs rrdtool, rrdcached and php-rrd
final class PhpRrdBackendTest extends BackendContractTestCase
{
    protected function makeBackend(?string $rrdcached): RrdBackendInterface
    {
        if (! extension_loaded('rrd')) {
            $this->markTestSkipped('The php-rrd extension is not loaded');
        }

        return new PhpRrd($rrdcached);
    }
}
