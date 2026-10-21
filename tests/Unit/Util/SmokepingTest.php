<?php

namespace LibreNMS\Tests\Unit\Util;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use LibreNMS\Tests\TestCase;
use LibreNMS\Util\Smokeping;

final class SmokepingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        LibrenmsConfig::set('rrd_dir', '/data/rrd');
        LibrenmsConfig::set('smokeping.dir', '/data/rrd/smokeping');
        LibrenmsConfig::set('smokeping.integration', true);
    }

    public function testRrdFileNameNoCached(): void
    {
        LibrenmsConfig::set('rrdcached', '');
        $smokeping = new Smokeping(new Device(['type' => 'network']));

        $this->assertSame('/data/rrd/smokeping/network/host_example_com.rrd', $smokeping->rrdFileName('host_example_com.rrd'));
    }

    public function testRrdFileNameCached(): void
    {
        LibrenmsConfig::set('rrdcached', 'rrdcached:42217');
        $smokeping = new Smokeping(new Device(['type' => 'network']));

        $this->assertSame('smokeping/network/host_example_com.rrd', $smokeping->rrdFileName('host_example_com.rrd'));
        $this->assertSame('/data/rrd/smokeping/network/host_example_com.rrd', $smokeping->generateFileName('host_example_com.rrd'));
    }

    public function testRrdFileNameCachedOutsideRrdDir(): void
    {
        LibrenmsConfig::set('rrdcached', 'unix:/run/rrdcached.sock');
        LibrenmsConfig::set('smokeping.dir', '/var/lib/smokeping');
        $smokeping = new Smokeping(new Device(['type' => '']));

        $this->assertSame('/var/lib/smokeping/Ungrouped/host.rrd', $smokeping->rrdFileName('host.rrd'));
    }
}
