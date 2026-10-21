<?php

namespace LibreNMS\Tests\Feature\Scan;

use App\Actions\Device\AddFoundDeviceResult;
use App\Models\Device;
use App\Models\Secret;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Data\Source\Snmp\RawSnmpResponse;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Enum\DeviceAddOutcome;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Scan\NetworkScanner;
use LibreNMS\Scan\ScanOptions;
use LibreNMS\Tests\DBTestCase;
use LibreNMS\Tests\Traits\FakesNetworkTools;
use LibreNMS\Util\Dns;
use LibreNMS\Util\IP;
use Mockery;
use PHPUnit\Framework\Attributes\TestDox;

#[TestDox('NetworkScanner')]
final class NetworkScannerTest extends DBTestCase
{
    use DatabaseTransactions;
    use FakesNetworkTools;

    #[TestDox('Reports every address once, skips excluded and known addresses, pings in batches')]
    public function testScan(): void
    {
        $log = $this->fakeFping(['10.0.0.5', '10.0.1.7']);
        Device::factory()->create(['hostname' => '10.0.0.9']);
        $this->mockDns(null);

        $results = $this->scan(['10.0.0.0/23', '10.0.2.0/24'], new ScanOptions(excludedNetworks: ['10.0.2.0/25'], pingFallback: true));

        $this->assertCount(510 + 254, $results, 'every usable address is reported once');
        $this->assertSame(DeviceAddOutcome::Known, $results['10.0.0.9']);
        $this->assertSame(DeviceAddOutcome::Excluded, $results['10.0.2.1']);
        $this->assertSame(DeviceAddOutcome::Added, $results['10.0.0.5']);
        $this->assertSame(DeviceAddOutcome::Added, $results['10.0.1.7']);
        $this->assertSame(DeviceAddOutcome::Unpingable, $results['10.0.2.200']);
        $this->assertCount(764 - 127 - 1 - 2, array_keys($results, DeviceAddOutcome::Unpingable, true), 'the rest are not excluded, known or added');
        $this->assertTrue(Device::where('hostname', '10.0.1.7')->exists());

        // batches of 256 addresses, without the known one in the first and the excluded ones in the others
        $this->assertSame(['255', '254', '127'], file($log, FILE_IGNORE_NEW_LINES));
    }

    #[TestDox('Probes the addresses of overlapping networks once')]
    public function testOverlappingNetworks(): void
    {
        $log = $this->fakeFping(['10.0.0.5', '10.0.0.0']);
        $this->mockDns(null);

        // the /32 and the later /24 are in the first /24, the /31 shares only 10.0.0.0 with it
        $results = $this->scan(['10.0.0.0/24', '10.0.0.5/32', '10.0.0.0/24', '10.0.0.0/31'], new ScanOptions(pingFallback: true));

        $this->assertCount(255, $results);
        $this->assertSame(DeviceAddOutcome::Added, $results['10.0.0.5']);
        $this->assertSame(DeviceAddOutcome::Added, $results['10.0.0.0']);
        $this->assertSame(['255'], file($log, FILE_IGNORE_NEW_LINES));

        // a /31 given before a /30 with the same number of addresses
        $results = $this->scan(['10.0.1.0/31', '10.0.1.0/30'], new ScanOptions(pingFallback: true));
        $this->assertSame(['10.0.1.0', '10.0.1.1', '10.0.1.2'], array_keys($results));
    }

    #[TestDox('Adds a host with the credential it replied to, without trying the others')]
    public function testAddsWithCredential(): void
    {
        $this->fakeSnmpget();
        $this->fakeFping(['192.0.2.1']);
        $this->mockDns('scanned.example.com');
        $other = Secret::factory()->create(['secret_type' => SecretType::Snmp, 'data' => ['version' => 'v2c', 'community' => 'other']]);
        $replied = Secret::factory()->create(['secret_type' => SecretType::Snmp, 'data' => ['version' => 'v2c', 'community' => 'replied']]);

        // the add uses the SNMP backend, the probe uses snmpget
        $communities = [];
        $backend = Mockery::mock(SnmpBackendInterface::class);
        $backend->shouldReceive('get')->andReturnUsing(function ($target, $oids, $config) use (&$communities) {
            $communities[] = $config->community;

            return new RawSnmpResponse('SNMPv2-MIB::sysObjectID.0 = .1.3.6.1.4.1.8072.3.2.10', '', 0);
        });
        $backend->shouldIgnoreMissing(new RawSnmpResponse('', '', 0));
        $this->app->instance(SnmpBackendInterface::class, $backend);

        $configs = $this->fakeSnmpConfigs([$other->id => '0-1', $replied->id => '0-0']);
        $results = $this->scan(['192.0.2.1/32'], new ScanOptions(snmpConfigs: $configs));

        $this->assertSame(['192.0.2.1' => DeviceAddOutcome::Added], $results);
        $device = Device::where('hostname', 'scanned.example.com')->firstOrFail();
        $this->assertSame($replied->id, $device->pollingMethod(PollingMethodType::Snmp)?->secret_id);
        $this->assertSame(['replied'], array_values(array_unique($communities)));
    }

    /**
     * @param  string[]  $networks
     * @return array<string, DeviceAddOutcome>
     */
    private function scan(array $networks, ScanOptions $options): array
    {
        $results = [];
        app(NetworkScanner::class)->scan(array_map(IP::parse(...), $networks), $options, function (string $ip, AddFoundDeviceResult $result) use (&$results): void {
            $this->assertArrayNotHasKey($ip, $results, "$ip is reported once");
            $results[$ip] = $result->outcome;
        });

        return $results;
    }

    private function mockDns(?string $hostname): void
    {
        $dns = Mockery::mock(Dns::class);
        $dns->shouldReceive('resolveHostname')->andReturn($hostname);
        $this->app->instance(Dns::class, $dns);
    }
}
