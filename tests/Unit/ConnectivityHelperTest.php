<?php

namespace LibreNMS\Tests\Unit;

use App\Actions\Device\CheckDeviceAvailability;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Data\Source\SnmpResponse;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\PollingMethod;
use LibreNMS\Polling\ConnectivityHelper;
use LibreNMS\Polling\Method\SnmpPollingMethod;
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

        $icmpMock = Mockery::mock(PollingMethod::class);
        $icmpMock->shouldReceive('isEnabled')
            ->andReturnUsing(function () use (&$icmpMethod) {
                return $icmpMethod->enabled;
            });
        $icmpMock->shouldReceive('isAvailable')
            ->times(8)
            ->andReturn(true, false, true, false, true, false, true, false);

        $snmpMock = Mockery::mock(PollingMethod::class);
        $snmpMock->shouldReceive('isEnabled')
            ->andReturnUsing(function () use (&$snmpMethod) {
                return $snmpMethod->enabled;
            });
        $snmpMock->shouldReceive('isAvailable')
            ->times(8)
            ->andReturn(true, true, false, false, true, true, false, false);

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

        $ipmiMock = Mockery::mock(PollingMethod::class);
        $ipmiMock->shouldReceive('isEnabled')
            ->andReturnUsing(function () use (&$ipmiMethod) {
                return $ipmiMethod->enabled;
            });
        $ipmiMock->shouldReceive('isAvailable')->andReturn(true, false);

        $unixAgentMock = Mockery::mock(PollingMethod::class);
        $unixAgentMock->shouldReceive('isEnabled')
            ->andReturnUsing(function () use (&$unixAgentMethod) {
                return $unixAgentMethod->enabled;
            });
        $unixAgentMock->shouldReceive('isAvailable')->andReturn(true, false);

        $factoryMock = Mockery::mock(PollingMethodFactory::class);
        $factoryMock->shouldReceive('make')
            ->andReturnUsing(fn (DevicePollingMethod $method) => match ($method->method_type) {
                PollingMethodType::Ipmi => $ipmiMock,
                PollingMethodType::UnixAgent => $unixAgentMock,
                default => throw new \UnexpectedValueException('Unexpected polling method type'),
            });
        $this->instance(PollingMethodFactory::class, $factoryMock);

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
        $snmpMethod = new SnmpPollingMethod(
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

        $this->assertTrue($snmpMethod->isAvailable($device));
        $this->assertTrue($snmpMethod->isAvailable($device));
        $this->assertTrue($snmpMethod->isAvailable($device));
        $this->assertFalse($snmpMethod->isAvailable($device));
    }
}
