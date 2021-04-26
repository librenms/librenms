<?php
/*
 * CyberPowerTrapsTest.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * Unit tests for CyberPower UPS SNMP trap handlers
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use App\Models\Ipv4Address;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;

class CyberPowerTrapsTest extends SnmpTrapTestCase
{
    public function testCpUpsOverload()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsOverload
CPS-MIB::mtrapinfoString \"The UPS has sensed an overload condition.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = 'The UPS has sensed an overload condition.';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 5);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpUpsOverload trap');
    }

    public function testCpUpsDiagFailed()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsDiagnosticsFailed
CPS-MIB::mtrapinfoString \"The UPS battery test failed.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = 'The UPS battery test failed.';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 5);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpUpsDiagFailed trap');
    }

    public function testCpUpsDischarged()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsDischarged
CPS-MIB::mtrapinfoString \"The UPS has started a runtime calibration process.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = 'The UPS has started a runtime calibration process.';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpUpsDischarged trap');
    }

    public function testCpUpsOnBattery()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsOnBattery
CPS-MIB::mtrapinfoString \"Utility power failed, transfer to backup mode.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = 'Utility power failed, transfer to backup mode.';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 4);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpUpsOnBattery trap');
    }

    public function testCpLowBattery()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::lowBattery
CPS-MIB::mtrapinfoString \"The UPS battery capacity is low than threshold, soon to be exhausted.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = 'The UPS battery capacity is low than threshold, soon to be exhausted.';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 4);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpLowBattery trap');
    }

    public function testCpPowerRestored()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::powerRestored
CPS-MIB::mtrapinfoString \"Utility power restored, return from backup mode.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = 'Utility power restored, return from backup mode.';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 1);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpPowerRestored trap');
    }

    public function testCpUpsDiagPassed()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsDiagnosticsPassed
CPS-MIB::mtrapinfoString \"The UPS battery test passed.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = 'The UPS battery test passed.';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpUpsDiagPassed trap');
    }

    public function testCpRtnLowBattery()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::returnFromLowBattery
CPS-MIB::mtrapinfoString \"The UPS has returned from a low battery condition.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = 'The UPS has returned from a low battery condition.';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 1);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpRtnLowBattery trap');
    }

    public function testCpUpsTurnedOff()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsTurnedOff
CPS-MIB::mtrapinfoString \"The UPS has been turned off.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = 'The UPS has been turned off.';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 4);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpUpsTurnedOff trap');
    }

    public function testCpUpsSleeping()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsSleeping
CPS-MIB::mtrapinfoString \"The UPS entered sleep mode. Output power will not be provided.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = 'The UPS entered sleep mode. Output power will not be provided.';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 4);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpUpsSleeping trap');
    }

    public function testCpUpsWokeUp()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsWokeUp
CPS-MIB::mtrapinfoString \"The UPS woke up from sleep mode. Output power is being provided.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = 'The UPS woke up from sleep mode. Output power is being provided.';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 1);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpUpsWokeUp trap');
    }

    public function testCpUpsRebootStarted()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsRebootStarted
CPS-MIB::mtrapinfoString \"The UPS started reboot sequence.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = 'The UPS started reboot sequence.';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 4);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpUpsRebootStarted trap');
    }

    public function testCpUpsOverTemp()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsOverTemp
CPS-MIB::mtrapinfoString \"The UPS inner temperature is too high.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = 'The UPS inner temperature is too high.';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 5);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpUpsOverTemp trap');
    }

    public function testCpRtnOverTemp()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::returnFromOverTemp
CPS-MIB::mtrapinfoString \"The UPS over temperature condition cleared.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = 'The UPS over temperature condition cleared.';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 1);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpRtnOverTemp trap');
    }

    public function testCpRtOverLoad()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::returnFromOverLoad
CPS-MIB::mtrapinfoString \"The UPS has returned from an overload condition.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = 'The UPS has returned from an overload condition.';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 1);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpRtOverLoad trap');
    }

    public function testCpRtnDischarged()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::returnFromDischarged
CPS-MIB::mtrapinfoString \"The UPS runtime calibration completed.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = 'The UPS runtime calibration completed.';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 1);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpRtnDischarged trap');
    }

    public function testCpUpsChargerFailure()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsChargerFailure
CPS-MIB::mtrapinfoString \"The battery charger is abnormal.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = 'The battery charger is abnormal.';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 4);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpUpsChargerFailure trap');
    }

    public function testCpRtnChargerFailure()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::returnFromChargerFailure
CPS-MIB::mtrapinfoString \"The charger returned from a failure condition.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = 'The charger returned from a failure condition.';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 1);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpRtnChargerFailure trap');
    }

    public function testCpUpsBatteryNotPresent()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsBatteryNotPresent
CPS-MIB::mtrapinfoString \"Battery is not present.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = 'Battery is not present.';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpUpsBatteryNotPresent trap');
    }
}
