<?php

namespace LibreNMS\Tests\Unit\Polling;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Support\Collection;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Data\Source\Icmp\FpingResponse;
use LibreNMS\Data\Source\Snmp\RawSnmpResponse;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Enum\AddressFamily;
use LibreNMS\Enum\FpingExitCode;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\Config\IcmpConfig;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Polling\Method\Config\UnixAgentConfig;
use LibreNMS\Polling\Method\Methods\IcmpPollingMethod;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;
use LibreNMS\Polling\Method\Methods\UnixAgentPollingMethod;
use LibreNMS\Polling\Secrets\Data\SnmpSecretData;
use LibreNMS\Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

final class PollingMethodProbeTest extends TestCase
{
    #[DataProvider('icmpAddressFamilyProvider')]
    public function testIcmpPingsWithConfiguredAddressFamily(string $ipVersion, string $snmpTransport, ?AddressFamily $expected): void
    {
        $device = new Device(['hostname' => '192.0.2.1']);
        $device->setRelation('pollingMethods', new Collection([
            new DevicePollingMethod(['method_type' => PollingMethodType::Snmp, 'settings' => ['transport' => $snmpTransport]]),
        ]));

        $fping = Mockery::mock(Fping::class);
        $fping->shouldReceive('ping')->with('192.0.2.1', $expected)->once()->andReturn(FpingResponse::artificialUp('192.0.2.1'));
        $this->app->instance(Fping::class, $fping);

        $result = app(IcmpPollingMethod::class)->probe($device, new IcmpConfig(ipVersion: $ipVersion));

        $this->assertTrue($result->isSuccess());
    }

    /**
     * @return array<string, array{string, string, ?AddressFamily}>
     */
    public static function icmpAddressFamilyProvider(): array
    {
        return [
            'auto' => ['default', 'udp6', null],
            'ipv4' => ['ipv4', 'udp6', AddressFamily::IPv4],
            'ipv6' => ['ipv6', 'udp', AddressFamily::IPv6],
            'match snmp ipv4' => ['match_snmp_transport', 'tcp', AddressFamily::IPv4],
            'match snmp ipv6' => ['match_snmp_transport', 'udp6', AddressFamily::IPv6],
        ];
    }

    public function testIcmpDiscoverReportsUnreachable(): void
    {
        $device = new Device(['hostname' => '192.0.2.1']);
        $device->setRelation('pollingMethods', new Collection);

        $fping = Mockery::mock(Fping::class);
        $fping->shouldReceive('ping')->once()->andReturn(FpingResponse::createError(FpingExitCode::Unreachable, '192.0.2.1'));
        $this->app->instance(Fping::class, $fping);

        $result = app(IcmpPollingMethod::class)->discover($device, new DevicePollingMethod(['method_type' => PollingMethodType::Icmp]));

        $this->assertFalse($result->isSuccess());
        $this->assertNotNull($result->errorMessage());
    }

    public function testSnmpProbesTheDeviceWithTheGivenConfig(): void
    {
        $device = new Device(['hostname' => 'snmp.test.local']);
        $config = SnmpConfig::make(['port' => 1161] + (new SnmpPollingMethod)->defaults(), new SnmpSecretData(community: 'custom-community'));

        $backend = Mockery::mock(SnmpBackendInterface::class);
        $backend->shouldReceive('get')
            ->with('snmp.test.local', ['SNMPv2-MIB::sysObjectID.0'], $config, Mockery::type(SnmpQueryOptions::class))
            ->once()
            ->andReturn(new RawSnmpResponse('SNMPv2-MIB::sysObjectID.0 = OID: SNMPv2-SMI::enterprises.9.1.1', '', 0));

        $result = (new SnmpPollingMethod($backend))->probe($device, $config);

        $this->assertTrue($result->isSuccess());
        $this->assertNull($result->errorMessage());
    }

    public function testSnmpProbeFailureHasErrorMessage(): void
    {
        $backend = Mockery::mock(SnmpBackendInterface::class);
        $backend->shouldReceive('get')->once()->andReturn(new RawSnmpResponse('', 'Timeout: No Response from snmp.test.local', 1));

        $result = (new SnmpPollingMethod($backend))->probe(new Device(['hostname' => 'snmp.test.local']), SnmpConfig::make((new SnmpPollingMethod)->defaults(), new SnmpSecretData));

        $this->assertFalse($result->isSuccess());
        $this->assertNotEmpty($result->errorMessage());
    }

    public function testUnixAgentProbeUsesConfiguredPortAndTimeout(): void
    {
        $config = new UnixAgentConfig(port: 1, timeout: 1); // nothing listens on port 1

        $result = (new UnixAgentPollingMethod)->probe(new Device(['hostname' => '127.0.0.1']), $config);

        $this->assertFalse($result->isSuccess());
        $this->assertSame(1, $result->stat('port'));
        $this->assertSame(1, $result->stat('timeout'));
    }
}
