<?php

namespace LibreNMS\Tests\Unit;

use App\Actions\Device\CheckDeviceAvailability;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Data\Source\SnmpResponse;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\PollingMethodConfigInterface;
use LibreNMS\Interfaces\PollingMethodProbeInterface;
use LibreNMS\Polling\ConnectivityHelper;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Polling\Method\Probe\ProbeResult;
use LibreNMS\Polling\PollingMethodFactory;
use LibreNMS\Tests\TestCase;
use Mockery;
use SnmpQuery;

final class ConnectivityHelperTest extends TestCase
{
    public function testDeviceStatus(): void
    {
        $icmpMethod = new DevicePollingMethod();
        $snmpMethod = new DevicePollingMethod();

        $icmpResults = [
            ProbeResult::success(['duplicates' => false]),
            ProbeResult::failure(['duplicates' => false]),
            ProbeResult::success(['duplicates' => false]),
            ProbeResult::failure(['duplicates' => false]),
            ProbeResult::success(['duplicates' => false]),
            ProbeResult::failure(['duplicates' => false]),
            ProbeResult::success(['duplicates' => false]),
            ProbeResult::failure(['duplicates' => false]),
        ];

        $icmpProbeMock = Mockery::mock(PollingMethodProbeInterface::class);
        $icmpProbeMock->shouldReceive('check')
            ->times(8)
            ->andReturnValues($icmpResults);

        $icmpMock = Mockery::mock(PollingMethodConfigInterface::class);
        $icmpMock->shouldReceive('isEnabled')
            ->andReturnUsing(function () use (&$icmpMethod) {
                return $icmpMethod->enabled;
            });

        $snmpResults = [
            ProbeResult::success(),
            ProbeResult::success(),
            ProbeResult::failure(),
            ProbeResult::failure(),
            ProbeResult::success(),
            ProbeResult::success(),
            ProbeResult::failure(),
            ProbeResult::failure(),
        ];

        $snmpProbeMock = Mockery::mock(PollingMethodProbeInterface::class);
        $snmpProbeMock->shouldReceive('check')
            ->times(8)
            ->andReturnValues($snmpResults);

        $snmpMock = Mockery::mock(PollingMethodConfigInterface::class);
        $snmpMock->shouldReceive('isEnabled')
            ->andReturnUsing(function () use (&$snmpMethod) {
                return $snmpMethod->enabled;
            });

        $factoryMock = Mockery::mock(PollingMethodFactory::class);
        $factoryMock->shouldReceive('make')
            ->andReturnUsing(fn (DevicePollingMethod $method) => match ($method->method_type) {
                PollingMethodType::Icmp => $icmpMock,
                PollingMethodType::Snmp => $snmpMock,
                default => throw new \UnexpectedValueException('Unexpected polling method type'),
            });
        $this->instance(PollingMethodFactory::class, $factoryMock);

        $device = new Device();
        $icmpMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
            'affects_availability' => true,
        ]);
        $snmpMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Snmp,
            'enabled' => true,
            'affects_availability' => true,
        ]);
        $device->setRelation('pollingMethods', collect([$icmpMethod, $snmpMethod]));

        $icmpMethod->setRelation('device', $device);
        $snmpMethod->setRelation('device', $device);

        $this->swap(CheckDeviceAvailability::class, new CheckDeviceAvailabilityMock([
            'icmp' => $icmpProbeMock,
            'snmp' => $snmpProbeMock,
        ]));

        /** ping and snmp enabled */
        $icmpMethod->enabled = true;
        $snmpMethod->enabled = true;

        // ping up, snmp up
        $this->assertTrue(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertTrue($device->status);
        $this->assertEquals('', $device->status_reason);
        $this->assertTrue((new ConnectivityHelper($device))->isAvailable());

        // ping down, snmp up
        $this->assertFalse(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertFalse($device->status);
        $this->assertEquals('icmp', $device->status_reason);
        $this->assertFalse((new ConnectivityHelper($device))->isAvailable());

        // ping up, snmp down
        $this->assertFalse(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertFalse($device->status);
        $this->assertEquals('snmp', $device->status_reason);
        $this->assertFalse((new ConnectivityHelper($device))->isAvailable());

        // ping down, snmp down
        $this->assertFalse(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertFalse($device->status);
        $this->assertEquals('icmp,snmp', $device->status_reason);
        $this->assertFalse((new ConnectivityHelper($device))->isAvailable());

        /** ping disabled and snmp enabled */
        $device->status = true;
        $device->status_reason = '';
        $icmpMethod->enabled = false;
        $snmpMethod->enabled = true;

        // ping up, snmp up
        $this->assertTrue(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertTrue($device->status);
        $this->assertEquals('', $device->status_reason);
        $this->assertTrue((new ConnectivityHelper($device))->isAvailable());

        // ping down, snmp up
        $this->assertTrue(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertTrue($device->status);
        $this->assertEquals('', $device->status_reason);
        $this->assertTrue((new ConnectivityHelper($device))->isAvailable());

        // ping up, snmp down
        $this->assertFalse(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertFalse($device->status);
        $this->assertEquals('snmp', $device->status_reason);
        $this->assertFalse((new ConnectivityHelper($device))->isAvailable());

        // ping down, snmp down
        $this->assertFalse(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertFalse($device->status);
        $this->assertEquals('snmp', $device->status_reason);
        $this->assertFalse((new ConnectivityHelper($device))->isAvailable());

        /** ping enabled and snmp disabled */
        $device->status = true;
        $device->status_reason = '';
        $icmpMethod->enabled = true;
        $snmpMethod->enabled = false;

        // ping up, snmp up
        $this->assertTrue(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertTrue($device->status);
        $this->assertEquals('', $device->status_reason);
        $this->assertTrue((new ConnectivityHelper($device))->isAvailable());

        // ping down, snmp up
        $this->assertFalse(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertFalse($device->status);
        $this->assertEquals('icmp', $device->status_reason);
        $this->assertFalse((new ConnectivityHelper($device))->isAvailable());

        // ping up, snmp down
        $this->assertTrue(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertTrue($device->status);
        $this->assertEquals('', $device->status_reason);
        $this->assertTrue((new ConnectivityHelper($device))->isAvailable());

        // ping down, snmp down
        $this->assertFalse(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertFalse($device->status);
        $this->assertEquals('icmp', $device->status_reason);
        $this->assertFalse((new ConnectivityHelper($device))->isAvailable());

        /** ping and snmp disabled */
        $device->status = true;
        $device->status_reason = '';
        $icmpMethod->enabled = false;
        $snmpMethod->enabled = false;

        // ping up, snmp up
        $this->assertTrue(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertTrue($device->status);
        $this->assertEquals('', $device->status_reason);
        $this->assertTrue((new ConnectivityHelper($device))->isAvailable());

        // ping down, snmp up
        $this->assertTrue(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertTrue($device->status);
        $this->assertEquals('', $device->status_reason);
        $this->assertTrue((new ConnectivityHelper($device))->isAvailable());

        // ping up, snmp down
        $this->assertTrue(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertTrue($device->status);
        $this->assertEquals('', $device->status_reason);
        $this->assertTrue((new ConnectivityHelper($device))->isAvailable());

        // ping down, snmp down
        $this->assertTrue(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertTrue($device->status);
        $this->assertEquals('', $device->status_reason);
        $this->assertTrue((new ConnectivityHelper($device))->isAvailable());
    }

    public function testIpmiAndUnixAgentStatus(): void
    {
        $ipmiMethod = new DevicePollingMethod();
        $unixAgentMethod = new DevicePollingMethod();

        $ipmiProbeMock = Mockery::mock(PollingMethodProbeInterface::class);
        $ipmiProbeMock->shouldReceive('check')->andReturn(ProbeResult::success(), ProbeResult::failure());

        $unixAgentProbeMock = Mockery::mock(PollingMethodProbeInterface::class);
        $unixAgentProbeMock->shouldReceive('check')->andReturn(ProbeResult::success(), ProbeResult::failure());

        $this->swap(CheckDeviceAvailability::class, new CheckDeviceAvailabilityMock([
            'ipmi' => $ipmiProbeMock,
            'unix-agent' => $unixAgentProbeMock,
        ]));

        $device = new Device();
        $ipmiMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Ipmi,
            'enabled' => true,
            'affects_availability' => true,
        ]);
        $unixAgentMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::UnixAgent,
            'enabled' => true,
            'affects_availability' => true,
        ]);
        $device->setRelation('pollingMethods', collect([$ipmiMethod, $unixAgentMethod]));

        // ipmi up, unix agent up
        $this->assertTrue(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertTrue($device->status);
        $this->assertEquals('', $device->status_reason);
        $this->assertTrue((new ConnectivityHelper($device))->isAvailable());

        // ipmi down, unix agent down
        $this->assertFalse(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertFalse($device->status);
        $this->assertEquals('ipmi,unix-agent', $device->status_reason);
        $this->assertFalse((new ConnectivityHelper($device))->isAvailable());
    }

    public function testIsSNMPable(): void
    {
        SnmpQuery::partialMock()->shouldReceive('get')
            ->times(4)
            ->andReturn(
                new SnmpResponse('SNMPv2-MIB::sysObjectID.0 = .1', '', 0),
                new SnmpResponse('SNMPv2-MIB::sysObjectID.0 = .1', '', 1),
                new SnmpResponse('', '', 0),
                new SnmpResponse('', '', 1)
            );

        $device = new Device;
        $snmpConfig = new SnmpConfig(
            enabled: true,
            affectsAvailability: true,
            version: 'v2c',
            community: 'public',
            authname: null,
            authpass: null,
            authlevel: 'noAuthNoPriv',
            authalgo: 'SHA',
            cryptopass: null,
            cryptoalgo: 'AES',
            context: null,
            transport: 'udp',
            port: 161,
            timeout: 3,
            retries: 1,
            maxRepeaters: 0,
            maxOid: 10
        );

        $probe = PollingMethodType::Snmp->definition()->probe();

        $this->assertTrue($probe->check($device)->isSuccess());
        $this->assertTrue($probe->check($device)->isSuccess());
        $this->assertTrue($probe->check($device)->isSuccess());
        $this->assertFalse($probe->check($device)->isSuccess());
    }
}

class CheckDeviceAvailabilityMock
{
    public function __construct(private array $probeMocks)
    {
    }

    public function execute(Device $device, bool $commit = false): bool
    {
        $setDeviceAvailability = app(\App\Actions\Device\SetDeviceAvailability::class);
        $enabledPollingMethods = $device->pollingMethods->filter(fn ($m) => $m->enabled);

        foreach ($enabledPollingMethods as $method) {
            $typeKey = $method->method_type instanceof PollingMethodType ? $method->method_type->value : (string) $method->method_type;
            $probeMock = $this->probeMocks[$typeKey] ?? null;

            if ($probeMock) {
                $result = $probeMock->check($device);
                $method->last_check_successful = $result->isSuccess();
                $method->last_checked_at = now();
            }
        }

        $setDeviceAvailability->execute($device, $commit);

        return $device->status;
    }
}
