<?php

namespace LibreNMS\Tests\Unit\RRD;

use App\Facades\LibrenmsConfig;
use Illuminate\Support\Facades\File;
use LibreNMS\Data\Store\Rrd;
use LibreNMS\Tests\TestCase;

class RrdApplicationArraysTest extends TestCase
{
    private string $rrdDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rrdDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'librenms-rrd-test-' . uniqid();
        mkdir($this->rrdDir . DIRECTORY_SEPARATOR . 'testhost', 0777, true);

        LibrenmsConfig::set('rrd_dir', $this->rrdDir);
        LibrenmsConfig::set('rrdcached', '');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->rrdDir);

        parent::tearDown();
    }

    public function testApplicationIdIsMatchedExactly(): void
    {
        $this->createRrdFiles([
            'app-mdadm-1',
            'app-mdadm-1-md0',
            'app-mdadm-1-md1',
            'app-mdadm-12',
            'app-mdadm-12-md2',
            'app-mdadm-100-md3',
        ]);

        $rrd = new Rrd();
        $device = ['hostname' => 'testhost'];

        $this->assertSame(['md0', 'md1'], $rrd->getRrdApplicationArrays($device, 1, 'mdadm'));
        $this->assertSame(['md2'], $rrd->getRrdApplicationArrays($device, 12, 'mdadm'));
    }

    public function testApplicationIdIsMatchedExactlyWithCategory(): void
    {
        $this->createRrdFiles([
            'app-dhcp-stats-1-pools-poolA',
            'app-dhcp-stats-1-networks-netA',
            'app-dhcp-stats-12-pools-poolB',
        ]);

        $rrd = new Rrd();
        $device = ['hostname' => 'testhost'];

        $this->assertSame(['pools-poolA'], $rrd->getRrdApplicationArrays($device, 1, 'dhcp-stats', 'pools'));
        $this->assertSame(['pools-poolB'], $rrd->getRrdApplicationArrays($device, 12, 'dhcp-stats', 'pools'));
    }

    /**
     * @param  list<string>  $names
     */
    private function createRrdFiles(array $names): void
    {
        foreach ($names as $name) {
            touch($this->rrdDir . DIRECTORY_SEPARATOR . 'testhost' . DIRECTORY_SEPARATOR . $name . '.rrd');
        }
    }
}
