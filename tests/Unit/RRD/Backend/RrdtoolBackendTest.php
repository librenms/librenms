<?php

namespace LibreNMS\Tests\Unit\RRD\Backend;

use App\Facades\LibrenmsConfig;
use LibreNMS\Exceptions\RrdStoreException;
use LibreNMS\RRD\Backend\RrdBackendInterface;
use LibreNMS\RRD\Backend\Rrdtool;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\RRD\RrdPath;
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

    public function testRemoteCreateWithoutRrdDirFailsWhenDaemonIsUnreachable(): void
    {
        $rrdcached = '127.0.0.1:' . $this->freePort(); // nothing listening
        LibrenmsConfig::set('rrdcached', $rrdcached);
        LibrenmsConfig::set('rrd_dir', "$this->directory/no-local-rrd");
        $backend = $this->makeBackend($rrdcached);
        $cwd = getcwd();
        chdir("$this->directory/rrd"); // a local fallback would write host1/test.rrd here

        try {
            // connection or permission error, depending on what the local fallback hits
            $this->expectException(RrdStoreException::class);
            $backend->create(RrdPath::make('host1', 'test.rrd'), RrdDefinition::make()->addDataset('a', 'GAUGE'));
        } finally {
            chdir($cwd);
            $this->assertFileDoesNotExist("$this->directory/rrd/host1/test.rrd");
        }
    }
}
