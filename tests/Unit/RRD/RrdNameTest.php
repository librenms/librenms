<?php

namespace LibreNMS\Tests\Unit\RRD;

use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use Illuminate\Support\Facades\File;
use LibreNMS\Data\Store\Rrd as RrdStore;
use LibreNMS\RRD\RrdPath;
use LibreNMS\Tests\TestCase;

class RrdNameTest extends TestCase
{
    public function testPath(): void
    {
        $testpath = Rrd::name('pswcs07.wcs.network', ['storage', 'hrstorage', 'C:\ Label: Serial Number 7e9e7a18']);
        $this->assertSame('pswcs07.wcs.network/storage-hrstorage-C___Label__Serial_Number_7e9e7a18.rrd', (string) $testpath);
        $this->assertSame((string) $testpath, $testpath->relativePath());
        $this->assertSame(rtrim(LibrenmsConfig::get('rrd_dir'), '/') . '/pswcs07.wcs.network/storage-hrstorage-C___Label__Serial_Number_7e9e7a18.rrd', $testpath->fullPath());
    }

    public function testProxmoxPath(): void
    {
        $testpath = Rrd::proxmoxName('my cluster', '101', 'tap101i0');
        $this->assertSame('proxmox/my_cluster/101_netif_tap101i0.rrd', $testpath->relativePath());
        $this->assertSame('proxmox/.._etc', RrdPath::proxmox('../etc')->relativePath());
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
