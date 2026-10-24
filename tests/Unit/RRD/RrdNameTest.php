<?php

namespace LibreNMS\Tests\Unit\RRD;

use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use App\Models\Device;
use App\Models\Eventlog;
use Illuminate\Support\Facades\File;
use LibreNMS\Data\Store\Rrd as RrdStore;
use LibreNMS\RRD\RrdPath;
use LibreNMS\Tests\TestCase;
use Mockery;

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

    public function testProxmoxPath(): void
    {
        $testpath = Rrd::proxmoxName('my cluster', '101', 'tap101i0');
        $this->assertSame('proxmox/my_cluster/101_netif_tap101i0.rrd', $testpath->relativePath());
        $this->assertSame('proxmox/.._etc', RrdPath::proxmox('../etc')->relativePath());
    }

    public function testRenameFile(): void
    {
        $rrd_dir = sys_get_temp_dir() . '/librenms-rrd-test-' . uniqid();
        LibrenmsConfig::set('rrd_dir', $rrd_dir);
        $eventlog = Mockery::mock(Eventlog::class);
        $eventlog->shouldReceive('_log');
        $this->app->instance(Eventlog::class, $eventlog);
        $device = new Device(['hostname' => 'renamehost']);

        try {
            mkdir("$rrd_dir/renamehost", 0777, true);
            touch("$rrd_dir/renamehost/bgp-2001_0db8_0000_0000_0000_0000_0000_0001.rrd");

            $this->assertTrue(Rrd::renameFile($device, 'bgp-2001_0db8_0000_0000_0000_0000_0000_0001', ['bgp', '2001:db8::1']));
            $this->assertFileDoesNotExist("$rrd_dir/renamehost/bgp-2001_0db8_0000_0000_0000_0000_0000_0001.rrd");
            $this->assertFileExists("$rrd_dir/renamehost/bgp-2001_db8__1.rrd");
        } finally {
            File::deleteDirectory($rrd_dir);
        }
    }

    public function testCheckDirExistsCreatesDirectory(): void
    {
        $this->checkDirExistsCreates('');
    }

    public function testCheckDirExistsCreatesDirectoryWithRrdcached(): void
    {
        $this->checkDirExistsCreates('unix:/var/run/rrdcached.sock');
    }

    public function testCheckDirExistsFailure(): void
    {
        $rrd_dir = sys_get_temp_dir() . '/librenms-rrd-test-' . uniqid();
        touch($rrd_dir); // a file where the directory should be prevents creation
        LibrenmsConfig::set('rrd_dir', $rrd_dir);

        try {
            LibrenmsConfig::set('rrdcached', '');
            $this->assertFalse(RrdStore::checkDirExists(RrdPath::make('newhost')));

            // failure is expected with a remote rrdcached
            LibrenmsConfig::set('rrdcached', 'rrdcached.example.com:42217');
            $this->assertTrue(RrdStore::checkDirExists(RrdPath::make('newhost')));
        } finally {
            unlink($rrd_dir);
        }
    }

    private function checkDirExistsCreates(string $rrdcached): void
    {
        $rrd_dir = sys_get_temp_dir() . '/librenms-rrd-test-' . uniqid();
        LibrenmsConfig::set('rrd_dir', $rrd_dir);
        LibrenmsConfig::set('rrdcached', $rrdcached);

        try {
            $this->assertTrue(RrdStore::checkDirExists(RrdPath::make('newhost')));
            $this->assertDirectoryExists("$rrd_dir/newhost");

            $this->assertTrue(RrdStore::checkDirExists(RrdPath::proxmox('cluster1')));
            $this->assertDirectoryExists("$rrd_dir/proxmox/cluster1");
        } finally {
            File::deleteDirectory($rrd_dir);
        }
    }
}
