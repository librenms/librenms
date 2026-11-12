<?php

namespace LibreNMS\Tests\Feature\Modules;

use App\Facades\DeviceCache;
use App\Models\Device;
use App\Models\Port;
use App\Models\Vrf;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LogicException;
use LibreNMS\Data\Source\Snmp\RawSnmpResponse;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Modules\Ipv6Addresses;
use LibreNMS\OS;
use LibreNMS\Tests\DBTestCase;
use Mockery;

final class Ipv6AddressesTest extends DBTestCase
{
    use DatabaseTransactions;

    private const string TIMEOUT = 'Timeout: No Response from udp:192.0.2.9:161';

    private const array WALKS = [
        'IP-MIB::ipAddressTable' => <<<'SNMP'
            IP-MIB::ipAddressIfIndex[ipv6]["20:01:0d:b8:00:00:00:00:00:00:00:00:00:00:00:01"] = 3
            IP-MIB::ipAddressPrefix[ipv6]["20:01:0d:b8:00:00:00:00:00:00:00:00:00:00:00:01"] = IP-MIB::ipAddressPrefixOrigin[3][ipv6]["20:01:0d:b8:00:00:00:00:00:00:00:00:00:00:00:00"][64]
            IP-MIB::ipAddressOrigin[ipv6]["20:01:0d:b8:00:00:00:00:00:00:00:00:00:00:00:01"] = manual
            SNMP,
    ];

    private const array CHANGED_WALKS = [
        'IP-MIB::ipAddressTable' => <<<'SNMP'
            IP-MIB::ipAddressIfIndex[ipv6]["20:01:0d:b8:00:00:00:00:00:00:00:00:00:00:00:02"] = 3
            IP-MIB::ipAddressPrefix[ipv6]["20:01:0d:b8:00:00:00:00:00:00:00:00:00:00:00:02"] = IP-MIB::ipAddressPrefixOrigin[3][ipv6]["20:01:0d:b8:00:00:00:00:00:00:00:00:00:00:00:00"][64]
            IP-MIB::ipAddressOrigin[ipv6]["20:01:0d:b8:00:00:00:00:00:00:00:00:00:00:00:02"] = manual
            SNMP,
    ];

    private const array VRF_WALKS = [
        'IP-MIB::ipAddressTable' => <<<'SNMP'
            IP-MIB::ipAddressIfIndex[ipv6]["20:01:0d:b8:00:01:00:00:00:00:00:00:00:00:00:01"] = 4
            IP-MIB::ipAddressPrefix[ipv6]["20:01:0d:b8:00:01:00:00:00:00:00:00:00:00:00:01"] = IP-MIB::ipAddressPrefixOrigin[4][ipv6]["20:01:0d:b8:00:01:00:00:00:00:00:00:00:00:00:00"][64]
            IP-MIB::ipAddressOrigin[ipv6]["20:01:0d:b8:00:01:00:00:00:00:00:00:00:00:00:01"] = manual
            SNMP,
    ];

    private const array NO_PREFIX_POINTER_WALKS = [
        'IP-MIB::ipAddressTable' => <<<'SNMP'
            IP-MIB::ipAddressIfIndex[ipv6]["20:01:0d:b8:00:00:00:00:00:00:00:00:00:00:00:01"] = 3
            IP-MIB::ipAddressOrigin[ipv6]["20:01:0d:b8:00:00:00:00:00:00:00:00:00:00:00:01"] = manual
            SNMP,
        'IP-MIB::ipAddressPrefixOrigin' => null,
    ];

    private const array VRF_NO_PREFIX_POINTER_WALKS = [
        'IP-MIB::ipAddressTable' => <<<'SNMP'
            IP-MIB::ipAddressIfIndex[ipv6]["20:01:0d:b8:00:01:00:00:00:00:00:00:00:00:00:01"] = 4
            IP-MIB::ipAddressOrigin[ipv6]["20:01:0d:b8:00:01:00:00:00:00:00:00:00:00:00:01"] = manual
            SNMP,
        'IP-MIB::ipAddressPrefixOrigin' => null,
    ];

    private const array IPV6_MIB_WALKS = [
        'IPV6-MIB::ipv6AddrPfxLength' => 'IPV6-MIB::ipv6AddrPfxLength[3][2001:db8:0:0:0:0:0:3] = 64',
        'IPV6-MIB::ipv6AddrType' => 'IPV6-MIB::ipv6AddrType[3][2001:db8:0:0:0:0:0:3] = stateful',
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

        $this->assertSame(['2001:db8::1/64'], $this->addresses());
    }

    public function testIpv6MibAddressesAreDiscoveredWhenIpMibListsNone(): void
    {
        $this->discover(['' => self::IPV6_MIB_WALKS]);

        $this->assertSame(['2001:db8::3/64'], $this->addresses());
    }

    public function testIpv6MibAddressTypeSetsTheOrigin(): void
    {
        $this->discover(['' => self::IPV6_MIB_WALKS]);

        $this->assertSame(['manual'], $this->device->ipv6()->pluck('ipv6_origin')->all());
    }

    public function testIpv6MibAddressesAreStoredOnceInTheDefaultContext(): void
    {
        $this->createVrf();

        $this->discover(['' => self::IPV6_MIB_WALKS]);

        $this->assertSame(['2001:db8::3/64'], $this->addresses());
    }

    public function testVrfContextThatDoesNotAnswerKeepsItsAddressesWhenIpv6MibIsWalked(): void
    {
        $this->createVrf();

        $this->discover(['' => self::IPV6_MIB_WALKS, 'red' => self::VRF_WALKS]);
        $this->discover(['' => self::IPV6_MIB_WALKS, 'red' => null]);

        $this->assertSame(['2001:db8::3/64', '2001:db8:1::1/64 red'], $this->addresses());
    }

    public function testVrfContextWhosePrefixWalkDoesNotAnswerKeepsItsAddresses(): void
    {
        $this->createVrf();

        $this->discover(['' => self::WALKS, 'red' => self::VRF_WALKS]);
        $this->discover(['' => self::CHANGED_WALKS, 'red' => self::VRF_NO_PREFIX_POINTER_WALKS]);

        $this->assertSame(['2001:db8::2/64', '2001:db8:1::1/64 red'], $this->addresses());
    }

    public function testAddressesAreKeptWhenTheDeviceDoesNotAnswer(): void
    {
        $this->discover();
        $this->discover(['' => null]);

        $this->assertSame(['2001:db8::1/64'], $this->addresses());
    }

    public function testAddressesAreKeptWhenTheIpv6MibWalkDoesNotAnswer(): void
    {
        $this->discover(['' => self::IPV6_MIB_WALKS]);
        $this->discover(['' => ['IPV6-MIB::ipv6AddrPfxLength' => null, 'IPV6-MIB::ipv6AddrType' => null]]);

        $this->assertSame(['2001:db8::3/64'], $this->addresses());
    }

    public function testAddressesAreKeptWhenThePrefixWalkDoesNotAnswer(): void
    {
        $this->discover();
        $this->discover(['' => self::NO_PREFIX_POINTER_WALKS]);

        $this->assertSame(['2001:db8::1/64'], $this->addresses());
    }

    public function testAddressesAreRemovedWhenTheDeviceListsNone(): void
    {
        $this->discover();
        $this->discover(['' => []]);

        $this->assertSame([], $this->addresses());
    }

    public function testVrfContextThatDoesNotAnswerKeepsItsAddresses(): void
    {
        $this->createVrf();

        $this->discover(['' => self::WALKS, 'red' => self::VRF_WALKS]);
        $this->discover(['' => self::CHANGED_WALKS, 'red' => null]);

        $this->assertSame(['2001:db8::2/64', '2001:db8:1::1/64 red'], $this->addresses());
    }

    private function createVrf(): void
    {
        Port::factory()->for($this->device)->create(['ifIndex' => 4]);
        Vrf::factory()->for($this->device)->create(['vrf_name' => 'red', 'context_name' => 'red', 'context_verified' => true, 'context_enabled' => true]);
    }

    /**
     * @param  array<string, array<string, string|null>|null>  $contexts  walk output by context and OID, null when it does not answer
     */
    private function discover(array $contexts = ['' => self::WALKS]): void
    {
        $backend = Mockery::mock(SnmpBackendInterface::class);
        $backend->shouldReceive('walk')->andReturnUsing(function (string $target, string $oid, mixed $config, SnmpQueryOptions $options) use ($contexts) {
            $walks = array_key_exists($options->context, $contexts) ? $contexts[$options->context] : [];
            if ($walks === null || (array_key_exists($oid, $walks) && $walks[$oid] === null)) {
                return new RawSnmpResponse('', self::TIMEOUT, 1);
            }

            $output = $walks[$oid] ?? '';
            if ($output !== '' && $options->numericEnums) {
                throw new LogicException("$oid: this test writes enum values as strings, so the walk must use enumStrings()");
            }

            return new RawSnmpResponse($output);
        });
        $this->app->instance(SnmpBackendInterface::class, $backend);

        DeviceCache::setPrimary($this->device->device_id);
        $deviceArray = DeviceCache::refresh($this->device->device_id)->toArray();
        (new Ipv6Addresses)->discover(OS::make($deviceArray));
    }

    /**
     * @return string[]
     */
    private function addresses(): array
    {
        return $this->device->ipv6()->orderBy('ipv6_address')->get()
            ->map(fn ($ip) => trim("$ip->ipv6_compressed/$ip->ipv6_prefixlen $ip->context_name"))->all();
    }
}
