<?php

namespace LibreNMS\Tests\Unit\Scan;

use App\Actions\Device\AddFoundDeviceResult;
use App\Facades\LibrenmsConfig;
use App\Jobs\AddScannedHost;
use LibreNMS\Enum\DeviceAddOutcome;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;
use LibreNMS\Polling\Secrets\Data\SnmpSecretData;
use LibreNMS\Scan\HostProbe;
use LibreNMS\Scan\NetworkScanner;
use LibreNMS\Scan\ScanOptions;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\TestDox;

#[TestDox('NetworkScanner Unit Tests')]
final class NetworkScannerTest extends TestCase
{
    #[TestDox('ScanOptions correctly creates from default configuration')]
    public function testScanOptionsDefaults(): void
    {
        $options = ScanOptions::fromConfig(
            pollerGroup: 5,
            dnsOnly: true,
            pingFallback: true,
            pingOnly: true,
        );

        $this->assertSame(5, $options->pollerGroup);
        $this->assertTrue($options->dnsOnly);
        $this->assertTrue($options->pingFallback);
        $this->assertTrue($options->pingOnly);
        $this->assertSame([], $options->snmpConfigs, 'ping only scans do not probe snmp');
    }

    #[TestDox('NetworkScanner handles empty IP list')]
    public function testEmptyIpList(): void
    {
        $results = [];
        (new NetworkScanner)->scan([], new ScanOptions(icmpEnabled: false), 4, function (string $ip, AddFoundDeviceResult $result) use (&$results): void {
            $results[$ip] = $result;
        });

        $this->assertEmpty($results);
    }

    #[TestDox('A host without SNMP credentials or ping is not added')]
    public function testNoReply(): void
    {
        $probe = new HostProbe(new ScanOptions(pingFallback: true, icmpEnabled: false), null);

        $this->assertSame(DeviceAddOutcome::Unreachable, $probe->probe('192.0.2.1'));
    }

    #[TestDox('With ICMP disabled, ping only adds every address')]
    public function testPingOnlyWithoutIcmp(): void
    {
        $job = (new HostProbe(new ScanOptions(pollerGroup: 3, pingOnly: true, icmpEnabled: false), null))->probe('192.0.2.1');

        $this->assertInstanceOf(AddScannedHost::class, $job);
        $this->assertNull($job->snmpSecretId);
        $this->assertFalse($job->pingable, 'it was not pinged, it is pinged when added');
        $this->assertSame(3, $job->pollerGroup);
    }

    #[TestDox('The preferred credential that replies is picked, even when a later one replies first')]
    public function testSnmpCredentialPreference(): void
    {
        // a fake snmpget, the community is "<delay>-<exit code>"
        $snmpget = tempnam(sys_get_temp_dir(), 'snmpget');
        file_put_contents($snmpget, "#!/bin/sh\nwhile [ \"\$1\" != -c ]; do shift; done\nsleep \"\${2%-*}\"\nexit \"\${2#*-}\"\n");
        chmod($snmpget, 0755);
        LibrenmsConfig::set('snmpget', $snmpget);

        $configs = fn (array $communities): array => array_map(
            fn (string $community) => SnmpConfig::make((new SnmpPollingMethod)->defaults(), new SnmpSecretData(community: $community)),
            $communities,
        );
        $probe = fn (array $communities) => (new HostProbe(new ScanOptions(icmpEnabled: false, snmpConfigs: $configs($communities)), null))->probe('192.0.2.1');

        try {
            $this->assertSame(1, $probe([10 => '0-1', 1 => '0.3-0', 2 => '0-0'])->snmpSecretId, 'waits for the preferred credential');
            $this->assertSame(2, $probe([1 => '0.2-1', 2 => '0-0'])->snmpSecretId, 'skips a credential without a reply');
            $this->assertSame(DeviceAddOutcome::Unreachable, $probe([1 => '0-1', 2 => '0-1']));
        } finally {
            unlink($snmpget);
        }
    }
}
