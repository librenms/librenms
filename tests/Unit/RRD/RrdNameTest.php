<?php

namespace LibreNMS\Tests\Unit\RRD;

use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use LibreNMS\Tests\TestCase;

class RrdNameTest extends TestCase
{
    public function testPathNoCached(): void
    {
        LibrenmsConfig::set('rrdcached', '');
        $testpath = Rrd::name('pswcs07.wcs.network', ['storage', 'hrstorage', 'C:\ Label: Serial Number 7e9e7a18']);
        $this->assertSame(rtrim(LibrenmsConfig::get('rrd_dir'), '/') . '/pswcs07.wcs.network/storage-hrstorage-C___Label__Serial_Number_7e9e7a18.rrd', $testpath->defaultPath());
    }

    public function testPathCached(): void
    {
        LibrenmsConfig::set('rrdcached', 'unix:/var/run/rrdcached.sock');
        $testpath = Rrd::name('localhost', ['storage', 'hrstorage', '/boot/efi']);
        $this->assertSame('localhost/storage-hrstorage-_boot_efi.rrd', $testpath->defaultPath());
        $this->assertSame(rtrim(LibrenmsConfig::get('rrd_dir'), '/') . '/localhost/storage-hrstorage-_boot_efi.rrd', $testpath->fullPath());
    }
}
