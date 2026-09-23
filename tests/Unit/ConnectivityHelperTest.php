<?php

namespace LibreNMS\Tests\Unit;

use App\Actions\Device\CheckDeviceAvailability;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\ConnectivityHelper;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Polling\Method\PollingMethodRegistry;
use LibreNMS\Polling\Method\ProbeResult;
use LibreNMS\Tests\TestCase;
use Mockery;
use SnmpQuery;

final class ConnectivityHelperTest extends TestCase
{
    public function testDeviceStatus(): void
    {
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

        $icmpMethodMock = Mockery::mock(\LibreNMS\Polling\Method\Methods\PollingMethod::class);
        $icmpMethodMock->shouldReceive('probe')->andReturn(...$icmpResults);

        $snmpMethodMock = Mockery::mock(\LibreNMS\Polling\Method\Methods\PollingMethod::class);
        $snmpMethodMock->shouldReceive('probe')->andReturn(...$snmpResults);

        $device = new Device();
        $icmpMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
            'affects_availability' => true,
        ]);
        $icmpMethod->setRelation('device', $device);

        $snmpMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Snmp,
            'enabled' => true,
            'affects_availability' => true,
        ]);
        $snmpMethod->setRelation('device', $device);

        $device->setRelation('pollingMethods', collect([$icmpMethod, $snmpMethod]));

        $this->swap(CheckDeviceAvailability::class, new CheckDeviceAvailabilityMock([
            'icmp' => $icmpMethodMock,
            'snmp' => $snmpMethodMock,
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

        $ipmiMethodMock = Mockery::mock(\LibreNMS\Polling\Method\Methods\PollingMethod::class);
        $ipmiMethodMock->shouldReceive('probe')->andReturn(ProbeResult::success(), ProbeResult::failure());

        $unixAgentMethodMock = Mockery::mock(\LibreNMS\Polling\Method\Methods\PollingMethod::class);
        $unixAgentMethodMock->shouldReceive('probe')->andReturn(ProbeResult::success(), ProbeResult::failure());

        $this->swap(CheckDeviceAvailability::class, new CheckDeviceAvailabilityMock([
            'ipmi' => $ipmiMethodMock,
            'unix-agent' => $unixAgentMethodMock,
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
                new SnmpResponse(['SNMPv2-MIB::sysObjectID.0' => '.1'], '', 0),
                new SnmpResponse(['SNMPv2-MIB::sysObjectID.0' => '.1'], '', 1),
                new SnmpResponse([], '', 0),
                new SnmpResponse([], '', 1)
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
            transport: 'udp',
            port: 161,
            context: null,
            timeout: 3,
            retries: 1,
            maxRepeaters: 0,
            maxOid: 10
        );

        $snmpMethod = app(PollingMethodRegistry::class)->require(PollingMethodType::Snmp);

        $this->assertTrue($snmpMethod->probe($device)->isSuccess());
        $this->assertTrue($snmpMethod->probe($device)->isSuccess());
        $this->assertTrue($snmpMethod->probe($device)->isSuccess());
        $this->assertFalse($snmpMethod->probe($device)->isSuccess());
    }
}

class CheckDeviceAvailabilityMock
{
    public function __construct(private array $methodMocks)
    {
    }

    public function execute(Device $device, bool $commit = false): bool
    {
        $setDeviceAvailability = app(\App\Actions\Device\SetDeviceAvailability::class);
        $enabledPollingMethods = $device->pollingMethods->filter(fn ($m) => $m->enabled);

        foreach ($enabledPollingMethods as $deviceMethod) {
            $typeKey = $deviceMethod->method_type instanceof PollingMethodType ? $deviceMethod->method_type->value : (string) $deviceMethod->method_type;
            $methodMock = $this->methodMocks[$typeKey] ?? null;

            if ($methodMock) {
                $result = $methodMock->probe($device);
                $deviceMethod->last_check_successful = $result->isSuccess();
                $deviceMethod->last_checked_at = now();
            }
        }

        $setDeviceAvailability->execute($device, $commit);

        return $device->status;
    }
}
