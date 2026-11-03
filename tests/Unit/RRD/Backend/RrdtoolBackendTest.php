<?php

namespace LibreNMS\Tests\Unit\RRD\Backend;

use App\Facades\LibrenmsConfig;
use LibreNMS\RRD\Backend\RrdBackendInterface;
use LibreNMS\RRD\Backend\Rrdtool;

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
