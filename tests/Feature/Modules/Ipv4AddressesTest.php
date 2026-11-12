<?php

namespace LibreNMS\Tests\Feature\Modules;

use App\Facades\DeviceCache;
use App\Models\Device;
use App\Models\Port;
use App\Models\Vrf;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Data\Source\Snmp\RawSnmpResponse;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
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

    private const array CHANGED_WALKS = [
        'IP-MIB::ipAdEntAddr' => 'ipAdEntAddr.192.0.2.2 = 192.0.2.2',
        'IP-MIB::ipAdEntIfIndex' => 'ipAdEntIfIndex.192.0.2.2 = 3',
        'IP-MIB::ipAdEntNetMask' => 'ipAdEntNetMask.192.0.2.2 = 255.255.255.0',
    ];

    private const array VRF_WALKS = [
        'IP-MIB::ipAdEntAddr' => 'ipAdEntAddr.198.51.100.1 = 198.51.100.1',
        'IP-MIB::ipAdEntIfIndex' => 'ipAdEntIfIndex.198.51.100.1 = 4',
        'IP-MIB::ipAdEntNetMask' => 'ipAdEntNetMask.198.51.100.1 = 255.255.255.0',
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
        $this->discover(['' => null]);

        $this->assertSame(['192.0.2.1/24'], $this->addresses());
    }

    public function testAddressesAreRemovedWhenTheDeviceListsNone(): void
    {
        $this->discover();
        $this->discover(['' => []]);

        $this->assertSame([], $this->addresses());
    }

    public function testVrfContextThatDoesNotAnswerKeepsItsAddresses(): void
    {
        Port::factory()->for($this->device)->create(['ifIndex' => 4]);
        Vrf::factory()->for($this->device)->create(['vrf_name' => 'red', 'context_name' => 'red', 'context_verified' => true, 'context_enabled' => true]);

        $this->discover(['' => self::WALKS, 'red' => self::VRF_WALKS]);
        $this->discover(['' => self::CHANGED_WALKS, 'red' => null]);

        $this->assertSame(['192.0.2.2/24', '198.51.100.1/24 red'], $this->addresses());
    }

    /**
     * @param  array<string, array<string, string>|null>  $contexts  walk output by context, null when the context does not answer
     */
    private function discover(array $contexts = ['' => self::WALKS]): void
    {
        $backend = Mockery::mock(SnmpBackendInterface::class);
        $backend->shouldReceive('walk')->andReturnUsing(function (string $target, string $oid, mixed $config, SnmpQueryOptions $options) use ($contexts) {
            if (array_key_exists($options->context, $contexts) && $contexts[$options->context] === null) {
                return new RawSnmpResponse('', self::TIMEOUT, 1);
            }

            return new RawSnmpResponse($contexts[$options->context][$oid] ?? '');
        });
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
        return $this->device->ipv4()->orderBy('ipv4_address')->get()
            ->map(fn ($ip) => trim("$ip->ipv4_address/$ip->ipv4_prefixlen $ip->context_name"))->all();
    }
}
