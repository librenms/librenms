<?php

namespace LibreNMS\Tests\Unit\RRD\Backend;

use App\Facades\LibrenmsConfig;
use LibreNMS\RRD\Backend\RrdBackendInterface;
use LibreNMS\RRD\Backend\Rrdtool;
use PHPUnit\Framework\Attributes\Group;

#[Group('external-dependencies')] // needs rrdtool, rrdcached and php-rrd
final class RrdtoolBackendTest extends BackendContractTestCase
{
    protected function makeBackend(?string $rrdcached): RrdBackendInterface
    {
        if (! is_executable((string) LibrenmsConfig::get('rrdtool', '/usr/bin/rrdtool'))) {
            $this->markTestSkipped('rrdtool is not installed');
        }

        return new Rrdtool($rrdcached);
    }
}
