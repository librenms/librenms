<?php

namespace LibreNMS\Tests\Feature\Discovery;

use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Ipv6Address;
use App\Models\Port;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Discovery\Neighbors\Neighbor;
use LibreNMS\Discovery\Neighbors\NeighborFinder;
use LibreNMS\Tests\DBTestCase;
use LibreNMS\Util\IP;
use PHPUnit\Framework\Attributes\DataProvider;

final class NeighborFinderAmbiguityTest extends DBTestCase
{
    use DatabaseTransactions;

    /** @return list<array{string}> */
    public static function addresses(): array
    {
        return [['198.51.100.209'], ['2001:db8::209']];
    }

    /** @return array<string, array{string, string, string}> */
    public static function addressNames(): array
    {
        $cases = [];
        foreach (['portMac', 'chassisMac'] as $identifier) {
            $cases["IPv4 $identifier"] = ['192.168.1.11', '192.168.1.11', $identifier];
            $cases["IPv6 $identifier"] = ['2001:db8::11', '2001:0db8:0000:0000:0000:0000:0000:0011', $identifier];
        }

        return $cases;
    }

    #[DataProvider('addressNames')]
    public function testAmbiguousAddressNameDoesNotOverrideMac(string $ip, string $name, string $identifier): void
    {
        $remote = $this->owner($ip, device: Device::factory()->create(['hostname' => '172.19.7.11', 'ip' => '172.19.7.11', 'sysName' => 'site1-access']));
        $this->owner($ip, device: Device::factory()->create(['hostname' => $ip, 'ip' => $ip, 'sysName' => $name]));
        $finder = new NeighborFinder;

        $neighbor = new Neighbor('lldp', null, ...[
            'sysName' => $name,
            'managementIp' => $ip,
            $identifier => str_replace(':', '', (string) $remote->ifPhysAddress),
        ]);
        $actual = $finder->findDevice($neighbor);
        $this->assertSame($remote->device_id, $actual?->device_id, "Expected NAT device B (172.19.7.11), got {$actual?->hostname}");
        $this->assertNull($finder->findDevice(new Neighbor('lldp', null, sysName: $name, managementIp: $ip)));
        $this->assertSame($remote->device_id, $finder->findDevice($neighbor)?->device_id);
    }

    #[DataProvider('addresses')]
    public function testAmbiguousAddressNameIsNotIndependentNameEvidence(string $ip): void
    {
        $remote = $this->owner($ip);
        $direct = Device::factory()->create(['hostname' => $ip, 'ip' => $ip, 'sysName' => $ip]);
        $this->owner($ip, device: $direct);
        $finder = new NeighborFinder;

        foreach (['lldp' => null, 'cdp' => $direct->device_id, 'fdp' => $direct->device_id, 'lldp-again' => null] as $protocol => $expected) {
            $neighbor = new Neighbor($protocol === 'lldp-again' ? 'lldp' : $protocol, null, sysName: $ip, managementIp: $ip);
            $this->assertSame($expected, $finder->findDevice($neighbor)?->device_id);
        }

        $withMac = new Neighbor('lldp', null, sysName: $ip, managementIp: $ip, portMac: str_replace(':', '', (string) $remote->ifPhysAddress));
        $this->assertSame($remote->device_id, $finder->findDevice($withMac)?->device_id);
    }

    #[DataProvider('addresses')]
    public function testProtocolCompatibilityAndCacheIsolation(string $ip): void
    {
        $direct = Device::factory()->create(['hostname' => $ip, 'ip' => $ip]);
        $this->owner($ip);
        $finder = new NeighborFinder;

        // CDP/FDP supply no MAC identifiers; preserve the unique direct management match.
        foreach (['lldp' => null, 'cdp' => $direct->device_id, 'fdp' => $direct->device_id, 'lldp-again' => null] as $protocol => $expected) {
            $neighbor = new Neighbor($protocol === 'lldp-again' ? 'lldp' : $protocol, null, sysName: 'serial-id.invalid', managementIp: $ip);
            $this->assertSame($expected, $finder->findDevice($neighbor)?->device_id);
        }

        $this->assertNull($finder->findDevice(new Neighbor('cdp', null, managementIp: $ip, portMac: 'feedffffffff')));
    }

    #[DataProvider('addresses')]
    public function testDeletedPortsCannotCreateAmbiguityOrSupplyMacEvidence(string $ip): void
    {
        $direct = Device::factory()->create(['hostname' => $ip, 'ip' => $ip]);
        $stale = $this->owner($ip, deleted: true);
        $finder = new NeighborFinder;
        $this->assertSame($direct->device_id, $finder->findDevice(new Neighbor('lldp', null, managementIp: $ip))?->device_id);
        $this->assertNull($finder->findDevice(new Neighbor('lldp', null, chassisMac: str_replace(':', '', (string) $stale->ifPhysAddress))));

        $direct->delete();
        $this->assertNull((new NeighborFinder)->findDevice(new Neighbor('lldp', null, managementIp: $ip)));
    }

    #[DataProvider('addresses')]
    public function testAllCandidatesAreConsideredAndGlobalMatchesAreExcluded(string $ip): void
    {
        Device::factory()->create(['hostname' => $ip, 'ip' => $ip]);
        $this->owner($ip);
        $third = $this->owner($ip);
        $mac = str_replace(':', '', (string) $third->ifPhysAddress);
        $outside = Device::factory()->create(['sysName' => 'outside-serial']);
        Port::factory()->create(['device_id' => $outside->device_id, 'ifPhysAddress' => $mac]);
        $finder = new NeighborFinder;

        // The third IP owner remains eligible even when its MAC is reused elsewhere.
        $this->assertSame($third->device_id, $finder->findDevice(new Neighbor('lldp', null, managementIp: $ip, portMac: $mac))?->device_id);
        $this->assertNull($finder->findDevice(new Neighbor('lldp', null, sysName: 'outside-serial', managementIp: $ip)));

        $third->device->update(['sysName' => 'candidate-serial']);
        $outside->update(['sysName' => 'candidate-serial']);
        $this->assertSame($third->device_id, $finder->findDevice(new Neighbor('lldp', null, sysName: 'candidate-serial', managementIp: $ip))?->device_id);
    }

    #[DataProvider('addresses')]
    public function testDisabledAndIgnoredDevicesStillOwnTheirAddresses(string $ip): void
    {
        foreach (['disabled', 'ignore'] as $flag) {
            $port = $this->owner($ip);
            $port->device->update([$flag => true]);
        }

        $this->assertNull((new NeighborFinder)->findDevice(new Neighbor('lldp', null, managementIp: $ip)));
    }

    private function owner(string $ip, bool $deleted = false, ?Device $device = null): Port
    {
        $port = Port::factory()->create(['device_id' => $device ?? Device::factory(), 'deleted' => $deleted]);
        $port->ifPhysAddress = sprintf('feed%08x', $port->port_id);
        $port->save();
        $address = IP::parse($ip);
        if ($address->getFamily() === 'ipv4') {
            Ipv4Address::factory()->create(['port_id' => $port->port_id, 'ipv4_address' => $address->uncompressed()]);
        } else {
            Ipv6Address::factory()->create(['port_id' => $port->port_id, 'ipv6_address' => $address->uncompressed(), 'ipv6_compressed' => $address->compressed()]);
        }

        return $port;
    }
}
