<?php

namespace LibreNMS\Tests\Unit;

use App\Actions\Device\CheckDeviceAvailability;
use App\Actions\Device\DeviceIsSnmpable;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use LibreNMS\Data\Source\Fping;
use LibreNMS\Data\Source\FpingResponse;
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

        /** ping and snmp enabled */
        LibrenmsConfig::set('icmp_check', true);
        $device->snmp_disable = false;

        // ping up, snmp up
        $this->assertTrue(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertTrue($device->status);
        $this->assertEquals('', $device->status_reason);

        // ping down, snmp up
        $this->assertFalse(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertFalse($device->status);
        $this->assertEquals('icmp', $device->status_reason);

        // ping up, snmp down
        $this->assertFalse(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertFalse($device->status);
        $this->assertEquals('snmp', $device->status_reason);

        // ping down, snmp down
        $this->assertFalse(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertFalse($device->status);
        $this->assertEquals('icmp', $device->status_reason);

        /** ping disabled and snmp enabled */
        LibrenmsConfig::set('icmp_check', false);
        $device->snmp_disable = false;

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
        LibrenmsConfig::set('icmp_check', true);
        $device->snmp_disable = true;

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
        LibrenmsConfig::set('icmp_check', false);
        $device->snmp_disable = true;

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

        $this->assertTrue((new DeviceIsSnmpable)->execute($device));
        $this->assertTrue((new DeviceIsSnmpable)->execute($device));
        $this->assertTrue((new DeviceIsSnmpable)->execute($device));
        $this->assertFalse((new DeviceIsSnmpable)->execute($device));
    }
}
