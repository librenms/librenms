<?php

namespace LibreNMS\Tests\Unit\Scan;

use App\Actions\Device\AddFoundDeviceResult;
use LibreNMS\Enum\DeviceAddOutcome;
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

        $this->assertSame(DeviceAddOutcome::Unreachable, $probe->probe('192.0.2.1')->outcome);
    }
}
