<?php

namespace LibreNMS\Tests\Unit;

use App\Actions\Device\CheckDeviceAvailability;
use App\Actions\Device\DeviceSnmpIsAvailable;
use App\Models\Device;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Data\Source\Icmp\FpingResponse;
use LibreNMS\Data\Source\SnmpResponse;
use LibreNMS\Tests\TestCase;
use Mockery;
use SnmpQuery;

final class ConnectivityHelperTest extends TestCase
{
    public function testDeviceStatus(): void
    {
        // not called when ping is disabled
        $this->app->singleton(Fping::class, function () {
            $mock = Mockery::mock(Fping::class);
            $up = FpingResponse::artificialUp();
            $down = FpingResponse::artificialDown();
            $mock->shouldReceive('ping')
                ->times(8)
                ->andReturn(
                    $up,
                    $down,
                    $up,
                    $down,
                    $up,
                    $down,
                    $up,
                    $down
                );

            return $mock;
        });

        // not called when snmp is disabled or ping up
        $up = new SnmpResponse('SNMPv2-MIB::sysObjectID.0 = .1');
        $down = new SnmpResponse('', '', 1);
        SnmpQuery::partialMock()->shouldReceive('get')
            ->times(6)
            ->andReturn(
                $up,
                $down,
                $up,
                $up,
                $down,
                $down
            );

        $device = new Device();
        $icmpMethod = new \App\Models\DevicePollingMethod([
            'method_type' => \LibreNMS\Enum\PollingMethodType::Icmp,
            'enabled' => true,
            'affects_availability' => true,
        ]);
        $snmpMethod = new \App\Models\DevicePollingMethod([
            'method_type' => \LibreNMS\Enum\PollingMethodType::Snmp,
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

        // ping down, snmp up
        $this->assertFalse(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertFalse($device->status);
        $this->assertEquals('icmp,snmp', $device->status_reason);

        // ping up, snmp down
        $this->assertFalse(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertFalse($device->status);
        $this->assertEquals('snmp', $device->status_reason);

        // ping down, snmp down
        $this->assertFalse(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertFalse($device->status);
        $this->assertEquals('icmp,snmp', $device->status_reason);

        /** ping disabled and snmp enabled */
        $device->status = true;
        $device->status_reason = '';
        $icmpMethod->enabled = false;
        $snmpMethod->enabled = true;

        // ping up, snmp up
        $this->assertTrue(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertTrue($device->status);
        $this->assertEquals('', $device->status_reason);

        // ping down, snmp up
        $this->assertTrue(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertTrue($device->status);
        $this->assertEquals('', $device->status_reason);

        // ping up, snmp down
        $this->assertFalse(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertFalse($device->status);
        $this->assertEquals('snmp', $device->status_reason);

        // ping down, snmp down
        $this->assertFalse(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertFalse($device->status);
        $this->assertEquals('snmp', $device->status_reason);

        /** ping enabled and snmp disabled */
        $device->status = true;
        $device->status_reason = '';
        $icmpMethod->enabled = true;
        $snmpMethod->enabled = false;

        // ping up, snmp up
        $this->assertTrue(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertTrue($device->status);
        $this->assertEquals('', $device->status_reason);

        // ping down, snmp up
        $this->assertFalse(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertFalse($device->status);
        $this->assertEquals('icmp', $device->status_reason);

        // ping up, snmp down
        $this->assertTrue(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertTrue($device->status);
        $this->assertEquals('', $device->status_reason);

        // ping down, snmp down
        $this->assertFalse(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertFalse($device->status);
        $this->assertEquals('icmp', $device->status_reason);

        /** ping and snmp disabled */
        $device->status = true;
        $device->status_reason = '';
        $icmpMethod->enabled = false;
        $snmpMethod->enabled = false;

        // ping up, snmp up
        $this->assertTrue(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertTrue($device->status);
        $this->assertEquals('', $device->status_reason);

        // ping down, snmp up
        $this->assertTrue(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertTrue($device->status);
        $this->assertEquals('', $device->status_reason);

        // ping up, snmp down
        $this->assertTrue(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertTrue($device->status);
        $this->assertEquals('', $device->status_reason);

        // ping down, snmp down
        $this->assertTrue(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertTrue($device->status);
        $this->assertEquals('', $device->status_reason);
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

        $this->assertTrue((new DeviceSnmpIsAvailable)->execute($device));
        $this->assertTrue((new DeviceSnmpIsAvailable)->execute($device));
        $this->assertTrue((new DeviceSnmpIsAvailable)->execute($device));
        $this->assertFalse((new DeviceSnmpIsAvailable)->execute($device));
    }
}
