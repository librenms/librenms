<?php

namespace LibreNMS\Tests\Feature\Modules;

use App\Facades\DeviceCache;
use App\Models\Device;
use App\Models\Port;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Data\Source\Snmp\RawSnmpResponse;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Modules\Ipv4Addresses;
use LibreNMS\OS;
use LibreNMS\Tests\DBTestCase;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

final class GaiaIpv4AddressesTest extends DBTestCase
{
    use DatabaseTransactions;

    private const string TIMEOUT = 'Timeout: No Response from udp:192.0.2.9:161';

    private const array WALKS = [
        'IP-MIB::ipAdEntAddr' => 'ipAdEntAddr.192.168.196.1 = 192.168.196.1',
        'IP-MIB::ipAdEntIfIndex' => 'ipAdEntIfIndex.192.168.196.1 = 53',
        'IP-MIB::ipAdEntNetMask' => 'ipAdEntNetMask.192.168.196.1 = 255.255.255.240',
        'CHECKPOINT-MIB::svnNetIfVsid' => 'svnNetIfVsid.1 = 1',
        'CHECKPOINT-MIB::haClusterIpTable' => "haClusterIpIfName.2.0 = bond1.10\nhaClusterIpAddr.2.0 = 192.0.2.221\nhaClusterIpNetMask.2.0 = 255.255.255.252",
    ];

    private Device $device;

    protected function setUp(): void
    {
        parent::setUp();

        $this->device = Device::factory()->create(['os' => 'gaia']);
        Port::factory()->for($this->device)->create(['ifIndex' => 53, 'ifName' => 'bond1.10']);
    }

    public function testVirtualSystemGetsTheClusterAddress(): void
    {
        $this->discover();

        $this->assertSame(['192.0.2.221/30'], $this->addresses());
    }

    #[DataProvider('clusterWalks')]
    public function testClusterAddressIsKeptWhenAWalkTimesOut(string $oid): void
    {
        $this->discover();
        $this->discover($oid);

        $this->assertSame(['192.0.2.221/30'], $this->addresses());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function clusterWalks(): array
    {
        return [
            'VSID walk' => ['CHECKPOINT-MIB::svnNetIfVsid'],
            'cluster IP walk' => ['CHECKPOINT-MIB::haClusterIpTable'],
        ];
    }

    private function discover(?string $timeoutOid = null): void
    {
        $backend = Mockery::mock(SnmpBackendInterface::class);
        $backend->shouldReceive('walk')->andReturnUsing(fn (string $target, string $oid) => $oid === $timeoutOid
            ? new RawSnmpResponse('', self::TIMEOUT, 1)
            : new RawSnmpResponse(self::WALKS[$oid] ?? ''));
        $this->app->instance(SnmpBackendInterface::class, $backend);

        DeviceCache::setPrimary($this->device->device_id);
        $deviceArray = DeviceCache::refresh($this->device->device_id)->toArray();
        (new Ipv4Addresses)->discover(OS::make($deviceArray));
    }

    /**
     * @return string[]
     */
    private function addresses(): array
    {
        return $this->device->ipv4()->get()->map(fn ($ip) => "$ip->ipv4_address/$ip->ipv4_prefixlen")->all();
    }
}
