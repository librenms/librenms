<?php

namespace LibreNMS\Tests\Feature\Modules;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Port;
use App\Models\Vrf;
use Illuminate\Support\Facades\Cache;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Modules\Ipv4Addresses;
use LibreNMS\Modules\Ipv6Addresses;
use LibreNMS\OS;
use LibreNMS\Tests\InMemoryDbTestCase;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

final class Ipv4AddressesTest extends InMemoryDbTestCase
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

    public function testLegacyTable(): void
    {
        $this->discover($this->legacy());
        $this->assertAddress('192.0.2.1', 31);
    }

    #[DataProvider('prefixProvider')]
    public function testNewTable(string $prefix, int $length): void
    {
        $this->discover($this->modern('192.0.2.1', $prefix));
        $this->assertAddress('192.0.2.1', $length);
    }

    /** @return array<string, array{string, int}> */
    public static function prefixProvider(): array
    {
        return [
            'numeric pointer' => ['.1.3.6.1.2.1.4.32.1.5.17.1.4.192.0.2.0.31', 31],
            'symbolic pointer' => ['IP-MIB::ipAddressPrefixOrigin.17.ipv4."192.0.2.0".31', 31],
            'table pointer' => ['IP-MIB::ipAddressPrefixOrigin[17][ipv4][192.0.2.0][31]', 31],
            'host address' => ['.1.3.6.1.2.1.4.32.1.5.17.1.4.192.0.2.1.32', 32],
            'null pointer falls back' => ['.0.0', 24],
            'invalid prefix falls back' => ['.1.3.6.1.2.1.4.32.1.5.17.1.4.192.0.2.0.64', 24],
        ];
    }

    public function testLegacyTableAddsAddressesMissingFromModernTable(): void
    {
        $this->discover(array_merge($this->legacy(), $this->legacy('198.51.100.1', '255.255.255.0'), $this->modern(), $this->broadcast()));

        $this->assertSame(['192.0.2.1', '198.51.100.1'], Ipv4Address::orderBy('ipv4_address')->pluck('ipv4_address')->all());
        $this->assertEquals([31, 24], Ipv4Address::orderBy('ipv4_address')->pluck('ipv4_prefixlen')->all());
    }

    public function testLegacyTableIsSkippedWhenModernTableIsComplete(): void
    {
        $walks = [];
        $this->discover(array_merge($this->legacy('198.51.100.1', '255.255.255.0'), $this->modern()), $walks);

        $this->assertAddress('192.0.2.1', 31);
        $this->assertNotContains(['IP-MIB::ipAdEntAddr', ''], $walks);
    }

    public function testLegacyTableIsSkippedWhenAnInterfaceHasSeveralAddresses(): void
    {
        $walks = [];
        $this->discover(array_merge($this->legacy('198.51.100.1', '255.255.255.0'), $this->modern(), $this->modern('192.0.2.3'), $this->broadcast()), $walks);

        $this->assertSame(['192.0.2.1', '192.0.2.3'], Ipv4Address::orderBy('ipv4_address')->pluck('ipv4_address')->all());
        $this->assertNotContains(['IP-MIB::ipAdEntAddr', ''], $walks);
    }

    public function testLegacyTableIsWalkedWhenEnabledForTheOs(): void
    {
        LibrenmsConfig::set('os.dell-sonic.ipv4_legacy_table', true);
        $this->discover(array_merge($this->legacy('198.51.100.1', '255.255.255.0'), $this->modern(), $this->modern('192.0.2.3')));

        $this->assertSame(['192.0.2.1', '192.0.2.3', '198.51.100.1'], Ipv4Address::orderBy('ipv4_address')->pluck('ipv4_address')->all());
    }

    public function testInvalidAddressesAndUnknownPortsAreSkipped(): void
    {
        $rows = $this->modern('192.0.2.1', ifIndex: 999);
        $rows['IP-MIB::ipAddressIfIndex[ipv6][2001:db8::1]'] = '17';
        $rows['IP-MIB::ipAddressIfIndex[ipv4][999.0.2.1]'] = '17';
        $this->discover($rows);

        $this->assertSame(0, Ipv4Address::count());
    }

    #[DataProvider('addressTypeProvider')]
    public function testModernAddressTypes(?string $type, bool $expected): void
    {
        $rows = $this->modern();
        if ($type !== null) {
            $rows['IP-MIB::ipAddressType[ipv4]["192.0.2.1"]'] = $type;
        }
        $this->discover($rows);

        if ($expected) {
            $this->assertAddress('192.0.2.1', 31);
        } else {
            $this->assertSame(0, Ipv4Address::count());
        }
    }

    /** @return array<string, array{?string, bool}> */
    public static function addressTypeProvider(): array
    {
        return [
            'unicast' => ['unicast', true],
            'broadcast' => ['broadcast', false],
            'anycast' => ['anycast', false],
            'missing type' => [null, true],
        ];
    }

    public function testModernPrefixTakesPrecedence(): void
    {
        $this->discover(array_merge($this->legacy(), $this->modern(prefix: '.1.3.6.1.2.1.4.32.1.5.17.1.4.192.0.2.0.30'), $this->broadcast()));
        $this->assertAddress('192.0.2.1', 30);
    }

    public function testModernInterfaceTakesPrecedence(): void
    {
        Port::factory()->create(['device_id' => $this->device->device_id, 'ifIndex' => 18]);
        $this->discover(array_merge($this->legacy(ifIndex: 18), $this->modern(), $this->broadcast()));

        $this->assertAddress('192.0.2.1', 31);
    }

    public function testLegacyIsKeptWhenModernInterfaceIsUnknown(): void
    {
        $this->discover(array_merge($this->legacy(), $this->modern(ifIndex: 999), $this->broadcast()));
        $this->assertAddress('192.0.2.1', 31);
    }

    #[DataProvider('missingPrefixProvider')]
    public function testLegacyNetmaskFillsMissingModernPrefix(?string $pointer): void
    {
        $rows = array_merge($this->legacy(), $this->modern(prefix: $pointer ?? ''));
        if ($pointer === null) {
            unset($rows['IP-MIB::ipAddressPrefix[ipv4]["192.0.2.1"]']);
        }
        $this->discover($rows);

        $this->assertAddress('192.0.2.1', 31);
    }

    public function testPrefixTableTakesPrecedenceOverLegacyNetmask(): void
    {
        $this->discover(array_merge($this->legacy(), $this->modern(prefix: '.0.0'), $this->broadcast(), [
            '.1.3.6.1.2.1.4.32.1.5.17.1.4.192.0.2.0.30' => 'manual',
        ]));

        $this->assertAddress('192.0.2.1', 30);
    }

    public function testMissingPrefixUsesExistingClassfulFallback(): void
    {
        $this->discover($this->modern(prefix: ''));
        $this->assertAddress('192.0.2.1', 24);
    }

    #[DataProvider('missingPrefixProvider')]
    public function testPrefixTableFallback(?string $pointer): void
    {
        // Synthetic documentation addresses only, not a device capture.
        $rows = array_merge($this->modern(prefix: $pointer ?? ''), [
            '.1.3.6.1.2.1.4.32.1.5.17.1.4.192.0.2.0.30' => 'manual',
            '.1.3.6.1.2.1.4.32.1.5.17.1.4.192.0.2.0.24' => 'manual',
            '.1.3.6.1.2.1.4.32.1.5.17.1.4.198.51.100.0.28' => 'manual',
            '.1.3.6.1.2.1.4.32.1.5.42.1.4.192.0.2.0.31' => 'manual',
            '.1.3.6.1.2.1.4.32.1.5.17.1.4.192.0.2.0.64' => 'manual',
            '.1.3.6.1.2.1.4.32.1.5.17.1.4.999.0.2.0.31' => 'manual',
            '.1.3.6.1.2.1.4.32.1.5.17.2.16.32.1.13.184.0.0.0.0.0.0.0.0.0.0.0.0.64' => 'manual',
        ]);
        if ($pointer === null) {
            unset($rows['IP-MIB::ipAddressPrefix[ipv4]["192.0.2.1"]']);
        }
        $walks = [];
        $this->discover($rows, $walks);

        $this->assertAddress('192.0.2.1', 30);
        $this->assertContains(['IP-MIB::ipAddressPrefixOrigin', ''], $walks);
    }

    /** @return array<string, array{?string}> */
    public static function missingPrefixProvider(): array
    {
        return [
            'missing column' => [null],
            'empty pointer' => [''],
            'null pointer' => ['.0.0'],
            'invalid length' => ['.1.3.6.1.2.1.4.32.1.5.17.1.4.192.0.2.0.64'],
        ];
    }

    public function testPrefixTableIsWalkedOncePerContext(): void
    {
        $this->device->setRelation('vrfs', collect([
            (new Vrf)->forceFill(['vrf_name' => 'blue', 'context_name' => 'blue', 'context_verified' => true, 'context_enabled' => true]),
            (new Vrf)->forceFill(['vrf_name' => 'red', 'context_name' => 'red', 'context_verified' => true, 'context_enabled' => true]),
        ]));
        $walks = [];
        $this->discover(array_merge($this->modern(prefix: ''), $this->modern('192.0.2.2', prefix: ''), [
            '.1.3.6.1.2.1.4.32.1.5.17.1.4.192.0.2.0.30' => 'manual',
        ]), $walks);

        $this->assertSame(6, Ipv4Address::count());
        $this->assertEquals([30], Ipv4Address::distinct()->pluck('ipv4_prefixlen')->all());
        foreach (['', 'blue', 'red'] as $context) {
            $this->assertCount(1, array_filter($walks, fn ($walk) => $walk === ['IP-MIB::ipAddressPrefixOrigin', $context]));
        }
    }

    public function testPrefixPointerDoesNotRequirePrefixTableWalk(): void
    {
        $walks = [];
        $this->discover($this->modern(), $walks);
        $this->assertAddress('192.0.2.1', 31);
        $this->assertNotContains(['IP-MIB::ipAddressPrefixOrigin', ''], $walks);
    }

    public function testBothTablesAreWalkedInEachVrfContext(): void
    {
        $this->device->setRelation('vrfs', collect([
            (new Vrf)->forceFill(['vrf_name' => 'blue', 'context_name' => 'blue', 'context_verified' => true, 'context_enabled' => true]),
            (new Vrf)->forceFill(['vrf_name' => 'red', 'context_name' => 'red', 'context_verified' => true, 'context_enabled' => true]),
        ]));
        $walks = [];
        $this->discover(array_merge($this->legacy(), $this->modern('198.51.100.1'), $this->broadcast()), $walks);

        $this->assertSame(6, Ipv4Address::count());
        $this->assertSame(['', 'blue', 'red'], Ipv4Address::distinct()->orderBy('context_name')->pluck('context_name')->all());
        foreach (['', 'blue', 'red'] as $context) {
            foreach (['IP-MIB::ipAdEntAddr', 'IP-MIB::ipAdEntIfIndex', 'IP-MIB::ipAdEntNetMask', 'IP-MIB::ipAddressTable'] as $oid) {
                $this->assertContains([$oid, $context], $walks);
            }
        }
    }

    public function testAddressTableWalkFromIpv6AddressesIsReused(): void
    {
        $walks = [];
        $this->mockSnmp($this->modern(), $walks);
        (new Ipv6Addresses)->discover($this->os());
        (new Ipv4Addresses)->discover($this->os());

        $this->assertAddress('192.0.2.1', 31);
        $this->assertCount(1, array_filter($walks, fn ($walk) => $walk === ['IP-MIB::ipAddressTable', '']));
    }

    public function testEmptyTablesRemoveStaleAddresses(): void
    {
        $this->port->ipv4()->create(['ipv4_address' => '192.0.2.1', 'ipv4_prefixlen' => 31, 'ipv4_network_id' => 0, 'context_name' => '']);
        $this->discover([]);

        $this->assertSame(0, Ipv4Address::count());
    }

    public function testSwitchingTablesPreservesTheExistingAddress(): void
    {
        $this->discover($this->modern());
        $id = Ipv4Address::sole()->ipv4_address_id;

        $this->device->unsetRelation('ipv4');
        $this->discover($this->legacy());

        $this->assertAddress('192.0.2.1', 31);
        $this->assertSame($id, Ipv4Address::sole()->ipv4_address_id);
    }

    /** @return array<string, string> */
    private function legacy(string $address = '192.0.2.1', string $mask = '255.255.255.254', int $ifIndex = 17): array
    {
        return [
            "ipAdEntAddr[$address]" => $address,
            "ipAdEntIfIndex[$address]" => (string) $ifIndex,
            "ipAdEntNetMask[$address]" => $mask,
        ];
    }

    /** @return array<string, string> */
    private function modern(string $address = '192.0.2.1', string $prefix = '.1.3.6.1.2.1.4.32.1.5.17.1.4.192.0.2.0.31', int $ifIndex = 17): array
    {
        return [
            "IP-MIB::ipAddressIfIndex[ipv4][\"$address\"]" => (string) $ifIndex,
            "IP-MIB::ipAddressPrefix[ipv4][\"$address\"]" => $prefix,
        ];
    }

    /**
     * net-snmp lists the broadcast address of each IPv4 address.
     *
     * @return array<string, string>
     */
    private function broadcast(string $address = '192.0.2.255', int $ifIndex = 17): array
    {
        return [
            "IP-MIB::ipAddressIfIndex[ipv4][\"$address\"]" => (string) $ifIndex,
            "IP-MIB::ipAddressType[ipv4][\"$address\"]" => 'broadcast',
        ];
    }

    /**
     * @param  array<string, string>  $rows
     * @param  array<int, array{string, string}>  $walks
     */
    private function discover(array $rows, array &$walks = []): void
    {
        // discovery starts with an empty device cache
        Cache::driver('device')->flush();
        $this->mockSnmp($rows, $walks);
        (new Ipv4Addresses)->discover($this->os());
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

            return new SnmpResponse(array_filter($rows, fn ($key) => str_starts_with($key, $oid . '[')
                || str_starts_with($key, str_replace('IP-MIB::', '', $oid) . '[')
                || ($oid === 'IP-MIB::ipAddressTable' && str_starts_with($key, 'IP-MIB::ipAddress'))
                || ($oid === 'IP-MIB::ipAddressPrefixOrigin' && str_starts_with($key, '.1.3.6.1.2.1.4.32.1.5.')), ARRAY_FILTER_USE_KEY));
        });
        $this->app->instance(SnmpBackendInterface::class, $backend);
    }

    private function os(): OS
    {
        $os = Mockery::mock(OS::class);
        $os->shouldReceive('getDevice')->andReturn($this->device);

        return $os;
    }

    private function assertAddress(string $address, int $length): void
    {
        $this->assertSame(1, Ipv4Address::count());
        $ip = Ipv4Address::sole();
        $this->assertSame($address, $ip->ipv4_address);
        $this->assertEquals($length, $ip->ipv4_prefixlen);
        $this->assertEquals($this->port->port_id, $ip->port_id);
        $this->assertSame('', $ip->context_name);
    }
}
