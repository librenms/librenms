<?php

namespace LibreNMS\Tests\Feature\Modules;

use App\Facades\DeviceCache;
use App\Models\Device;
use App\Models\Ipv6Address;
use App\Models\Port;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Modules\Ipv4Addresses;
use LibreNMS\Modules\Ipv6Addresses;
use LibreNMS\OS;
use LibreNMS\Tests\InMemoryDbTestCase;
use Mockery;

final class Ipv6AddressesTest extends InMemoryDbTestCase
{
    private Device $device;
    private Port $port;

    protected function setUp(): void
    {
        parent::setUp();

        $this->device = Device::factory()->create(['os' => 'dell-sonic']);
        $this->port = Port::factory()->create(['device_id' => $this->device->device_id, 'ifIndex' => 17]);
        DeviceCache::setPrimary($this->device->device_id);
    }

    public function testAddressTableWalkFromIpv4AddressesIsReused(): void
    {
        $walks = [];
        $this->mockSnmp([
            'IP-MIB::ipAddressIfIndex[ipv6]["20:01:0d:b8:00:00:00:00:00:00:00:00:00:00:00:01"]' => '17',
            'IP-MIB::ipAddressPrefix[ipv6]["20:01:0d:b8:00:00:00:00:00:00:00:00:00:00:00:01"]' => '.1.3.6.1.2.1.4.32.1.5.17.2.16.32.1.13.184.0.0.0.0.0.0.0.0.0.0.0.0.64',
        ], $walks);
        (new Ipv4Addresses)->discover($this->os());
        (new Ipv6Addresses)->discover($this->os());

        $ip = Ipv6Address::sole();
        $this->assertSame('2001:db8::1', $ip->ipv6_compressed);
        $this->assertEquals(64, $ip->ipv6_prefixlen);
        $this->assertEquals($this->port->port_id, $ip->port_id);
        $this->assertCount(1, array_filter($walks, fn ($walk) => $walk === ['IP-MIB::ipAddressTable', '']));
    }

    /**
     * @param  array<string, string>  $rows
     * @param  array<int, array{string, string}>  $walks
     */
    private function mockSnmp(array $rows, array &$walks): void
    {
        $backend = Mockery::mock(SnmpBackendInterface::class);
        $backend->shouldReceive('walk')->andReturnUsing(function ($target, $oid, $config, $options) use ($rows, &$walks) {
            $walks[] = [$oid, $options->context];

            return new SnmpResponse(array_filter($rows, fn ($key) => $oid === 'IP-MIB::ipAddressTable' && str_starts_with($key, 'IP-MIB::ipAddress'), ARRAY_FILTER_USE_KEY));
        });
        $this->app->instance(SnmpBackendInterface::class, $backend);
    }

    private function os(): OS
    {
        $os = Mockery::mock(OS::class);
        $os->shouldReceive('getDevice')->andReturn($this->device);

        return $os;
    }
}
