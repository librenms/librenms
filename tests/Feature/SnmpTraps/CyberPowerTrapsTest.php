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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Unit tests for CyberPower UPS SNMP trap handlers
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2020 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use App\Models\Ipv4Address;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;
use LibreNMS\Tests\DBTestCase;

class CyberPowerTrapsTest extends SnmpTrapTestCase
{
    public function testCpUpsOverload()
    {
        $device = factory(Device::class)->create();
        $ipv4 = factory(Ipv4Address::class)->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsOverload
CPS-MIB::mtrapinfoString \"The UPS has sensed an overload condition.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = "The UPS has sensed an overload condition.";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 5);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpUpsOverload trap');
    }

    public function testCpUpsDiagFailed()
    {
        $device = factory(Device::class)->create();
        $ipv4 = factory(Ipv4Address::class)->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsDiagnosticsFailed
CPS-MIB::mtrapinfoString \"The UPS battery test failed.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = "The UPS battery test failed.";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 5);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpUpsDiagFailed trap');
    }

    public function testCpUpsDischarged()
    {
        $device = factory(Device::class)->create();
        $ipv4 = factory(Ipv4Address::class)->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsDischarged
CPS-MIB::mtrapinfoString \"The UPS has started a runtime calibration process.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = "The UPS has started a runtime calibration process.";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpUpsDischarged trap');
    }

    public function testCpUpsOnBattery()
    {
        $device = factory(Device::class)->create();
        $ipv4 = factory(Ipv4Address::class)->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsOnBattery
CPS-MIB::mtrapinfoString \"Utility power failed, transfer to backup mode.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = "Utility power failed, transfer to backup mode.";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 4);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpUpsOnBattery trap');
    }

    public function testCpLowBattery()
    {
        $device = factory(Device::class)->create();
        $ipv4 = factory(Ipv4Address::class)->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::lowBattery
CPS-MIB::mtrapinfoString \"The UPS battery capacity is low than threshold, soon to be exhausted.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = "The UPS battery capacity is low than threshold, soon to be exhausted.";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 4);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpLowBattery trap');
    }

    public function testCpPowerRestored()
    {
        $device = factory(Device::class)->create();
        $ipv4 = factory(Ipv4Address::class)->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::powerRestored
CPS-MIB::mtrapinfoString \"Utility power restored, return from backup mode.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = "Utility power restored, return from backup mode.";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 1);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpPowerRestored trap');
    }

    public function testCpUpsDiagPassed()
    {
        $device = factory(Device::class)->create();
        $ipv4 = factory(Ipv4Address::class)->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsDiagnosticsPassed
CPS-MIB::mtrapinfoString \"The UPS battery test passed.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = "The UPS battery test passed.";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpUpsDiagPassed trap');
    }

    public function testCpRtnLowBattery()
    {
        $device = factory(Device::class)->create();
        $ipv4 = factory(Ipv4Address::class)->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::returnFromLowBattery
CPS-MIB::mtrapinfoString \"The UPS has returned from a low battery condition.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = "The UPS has returned from a low battery condition.";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 1);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpRtnLowBattery trap');
    }

    public function testCpUpsTurnedOff()
    {
        $device = factory(Device::class)->create();
        $ipv4 = factory(Ipv4Address::class)->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsTurnedOff
CPS-MIB::mtrapinfoString \"The UPS has been turned off.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = "The UPS has been turned off.";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 4);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpUpsTurnedOff trap');
    }

    public function testCpUpsSleeping()
    {
        $device = factory(Device::class)->create();
        $ipv4 = factory(Ipv4Address::class)->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsSleeping
CPS-MIB::mtrapinfoString \"The UPS entered sleep mode. Output power will not be provided.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = "The UPS entered sleep mode. Output power will not be provided.";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 4);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpUpsSleeping trap');
    }

    public function testCpUpsWokeUp()
    {
        $device = factory(Device::class)->create();
        $ipv4 = factory(Ipv4Address::class)->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsWokeUp
CPS-MIB::mtrapinfoString \"The UPS woke up from sleep mode. Output power is being provided.\"
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"comstring\"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps";

        $message = "The UPS woke up from sleep mode. Output power is being provided.";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 1);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle CpUpsWokeUp trap');
    }
}