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

final class Ipv4AddressesTest extends DBTestCase
{
    use DatabaseTransactions;

    private const string TIMEOUT = 'Timeout: No Response from udp:192.0.2.9:161';

    private const array WALKS = [
        'IP-MIB::ipAdEntAddr' => 'ipAdEntAddr.192.0.2.1 = 192.0.2.1',
        'IP-MIB::ipAdEntIfIndex' => 'ipAdEntIfIndex.192.0.2.1 = 3',
        'IP-MIB::ipAdEntNetMask' => 'ipAdEntNetMask.192.0.2.1 = 255.255.255.0',
    ];

    private Device $device;

    protected function setUp(): void
    {
        parent::setUp();

        $this->device = Device::factory()->create(['os' => 'generic']);
        Port::factory()->for($this->device)->create(['ifIndex' => 3]);
    }

    public function testIpMibAddressesAreDiscovered(): void
    {
        $this->discover();

        $this->assertSame(['192.0.2.1/24'], $this->addresses());
    }

    public function testAddressesAreKeptWhenTheDeviceDoesNotAnswer(): void
    {
        $this->discover();
        $this->discover(timeout: true);

        $this->assertSame(['192.0.2.1/24'], $this->addresses());
    }

    private function discover(bool $timeout = false): void
    {
        $backend = Mockery::mock(SnmpBackendInterface::class);
        $backend->shouldReceive('walk')->andReturnUsing(fn (string $target, string $oid) => $timeout
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
