<?php

namespace LibreNMS\Tests\Unit\Scan;

use App\Jobs\AddScannedHost;
use LibreNMS\Enum\DeviceAddOutcome;
use LibreNMS\Scan\HostProbe;
use LibreNMS\Scan\PingBatch;
use LibreNMS\Scan\ScanOptions;
use LibreNMS\Tests\TestCase;
use LibreNMS\Tests\Traits\FakesNetworkTools;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

#[TestDox('HostProbe')]
final class HostProbeTest extends TestCase
{
    use FakesNetworkTools;

    private const IP = '192.0.2.1';

    /**
     * @param  array<string, bool>  $options
     * @param  bool|null  $replies  replies to ping, null when ICMP checks are disabled
     * @param  array{?int, bool}|DeviceAddOutcome  $expected  the job's secret id and pingable, or the outcome
     */
    #[DataProvider('probeProvider')]
    #[TestDox('$_dataName')]
    public function testProbe(array $options, ?string $snmp, ?bool $replies, array|DeviceAddOutcome $expected): void
    {
        $this->fakeSnmpget();
        $this->fakeFping($replies ? [self::IP] : []);

        $probe = new HostProbe(self::IP, new ScanOptions(
            pingFallback: $options['pingFallback'] ?? false,
            pingOnly: $options['pingOnly'] ?? false,
            icmpEnabled: $replies !== null,
            snmpConfigs: $snmp === null ? [] : $this->fakeSnmpConfigs([7 => $snmp]),
        ), $ping = $replies === null ? null : new PingBatch([self::IP]));

        while (($result = $probe->result()) === null) {
            $ping?->poll();
            usleep(1000);
        }

        if ($expected instanceof DeviceAddOutcome) {
            $this->assertSame($expected, $result);

            return;
        }

        $this->assertInstanceOf(AddScannedHost::class, $result);
        $this->assertSame($expected, [$result->snmpSecretId, $result->pingable]);
    }

    /**
     * @return array<string, array{array<string, bool>, ?string, ?bool, array{?int, bool}|DeviceAddOutcome}>
     */
    public static function probeProvider(): array
    {
        return [
            'SNMP and ping reply, added with the secret, not pinged again' => [[], '0-0', true, [7, true]],
            'only SNMP replies, added with the secret without ICMP' => [[], '0-0', false, [7, false]],
            'only ping replies, not added' => [[], '0-1', true, DeviceAddOutcome::Unreachable],
            'nothing replies' => [[], '0-1', false, DeviceAddOutcome::Unpingable],
            'without credentials, only ping replies' => [[], null, true, DeviceAddOutcome::Unreachable],
            'ping fallback, only ping replies, added as ping only' => [['pingFallback' => true], '0-1', true, [null, true]],
            'ping fallback, nothing replies' => [['pingFallback' => true], '0-1', false, DeviceAddOutcome::Unpingable],
            'ping fallback without ICMP checks, not added' => [['pingFallback' => true], '0-1', null, DeviceAddOutcome::Unreachable],
            'ping only, SNMP is not probed' => [['pingOnly' => true], '0-0', true, [null, true]],
            'ping only, no reply to ping' => [['pingOnly' => true], '0-0', false, DeviceAddOutcome::Unpingable],
            'ping only without ICMP checks, added and pinged then' => [['pingOnly' => true], null, null, [null, false]],
        ];
    }
}
