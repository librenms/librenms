<?php

namespace LibreNMS\Tests\Unit\RRD;

use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use App\Models\Device;
use App\Models\Eventlog;
use Illuminate\Support\Facades\File;
use LibreNMS\Data\Store\Rrd as RrdStore;
use LibreNMS\Exceptions\RrdNotFoundException;
use LibreNMS\RRD\RrdPath;
use LibreNMS\RRD\RrdProcess;
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

    public function testRenameFileRemoteRrdcachedNotifiesOnce(): void
    {
        $remote_files = ['remotehost/toner-Black.rrd'];
        $events = $this->fakeRemoteRrdcached($remote_files);
        $device = new Device(['hostname' => 'remotehost']);
        $rrd = new RrdStore();

        $this->assertTrue($rrd->renameFile($device, ['toner', 'Black'], ['toner', 'black', '1']));
        $remote_files[] = 'remotehost/toner-black-1.rrd'; // created by the first write
        $this->assertTrue($rrd->renameFile($device, ['toner', 'Black'], ['toner', 'black', '1']));

        $this->assertSame(['Unable to rename remotehost/toner-Black.rrd to remotehost/toner-black-1.rrd on remote rrdcached server, rename manually to keep history'], $events->getArrayCopy());
    }

    public function testRenameFileRemoteRrdcachedNoOldFile(): void
    {
        $remote_files = [];
        $events = $this->fakeRemoteRrdcached($remote_files);

        $this->assertTrue((new RrdStore())->renameFile(new Device(['hostname' => 'remotehost']), ['toner', 'Black'], ['toner', 'black', '1']));

        $this->assertCount(0, $events);
    }

    public function testRenameFileNoRrdcachedDoesNotQueryRrdtool(): void
    {
        $remote_files = ['localhost/toner-Black.rrd'];
        $events = $this->fakeRemoteRrdcached($remote_files);
        LibrenmsConfig::set('rrdcached', '');

        $this->assertTrue((new RrdStore())->renameFile(new Device(['hostname' => 'localhost']), ['toner', 'Black'], ['toner', 'black', '1']));

        $this->assertCount(0, $events);
    }

    /**
     * Fake a remote rrdcached server holding $remote_files (relative paths) and collect eventlog messages
     *
     * @param  string[]  $remote_files
     * @return \ArrayObject<int, string>
     */
    private function fakeRemoteRrdcached(array &$remote_files): \ArrayObject
    {
        LibrenmsConfig::set('rrd_dir', sys_get_temp_dir() . '/librenms-rrd-test-' . uniqid());
        LibrenmsConfig::set('rrdcached', 'rrdcached.example.com:42217');
        LibrenmsConfig::set('rrdtool_version', '1.7');

        $process = Mockery::mock(RrdProcess::class);
        $process->shouldReceive('run')->andReturnUsing(function (string $command) use (&$remote_files): string {
            $this->assertNotSame('', LibrenmsConfig::get('rrdcached'), 'rrdtool should not be queried without rrdcached');
            $file = explode(' ', $command)[1];
            if (! in_array($file, $remote_files)) {
                throw new RrdNotFoundException("opening '$file': No such file or directory");
            }

            return '1798761600';
        });
        $process->shouldReceive('stop');
        $this->app->bind(RrdProcess::class, fn () => $process);

        /** @var \ArrayObject<int, string> $events */
        $events = new \ArrayObject();
        $eventlog = Mockery::mock(Eventlog::class);
        $eventlog->shouldReceive('_log')->andReturnUsing(function (string $text) use ($events): void {
            $events[] = $text;
        });
        $this->app->instance(Eventlog::class, $eventlog);

        return $events;
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
