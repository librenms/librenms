<?php
/*
 * FgTrapIpsTest.php
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
 * Unit tests for Fortigate IPS SNMP trap handlers (FgTrapIps*)
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2019 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use App\Models\Ipv4Address;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;

class FgTrapIpsTest extends SnmpTrapTestCase
{
    public function testIpsAnomaly()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 302:12:56:24.81
SNMPv2-MIB::snmpTrapOID.0 FORTINET-FORTIGATE-MIB::fgTrapIpsAnomaly
FORTINET-CORE-MIB::fnSysSerial.0 $device->serial
SNMPv2-MIB::sysName.0 $device->hostname
FORTINET-FORTIGATE-MIB::fgIpsTrapSigId.0 2
FORTINET-FORTIGATE-MIB::fgIpsTrapSrcIp.0 $ipv4->ipv4_address
FORTINET-FORTIGATE-MIB::fgIpsTrapSigMsg.0 tcp_src_session";

        $message = "DDoS prevention triggered. Source: $ipv4->ipv4_address Protocol: tcp_src_session";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 4);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle fgTrapIpsAnomaly trap');
    }

    public function testIpsPkgUdate()
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 302:12:56:24.81
SNMPv2-MIB::snmpTrapOID.0 FORTINET-FORTIGATE-MIB::fgTrapIpsPkgUpdate
FORTINET-CORE-MIB::fnSysSerial.0 $device->serial
SNMPv2-MIB::sysName.0 $device->hostname";

        $message = "IPS package updated on $device->hostname";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle fgTrapIpsPkgUpdate trap');
    }

    public function testIpsSignature()
    {
        $device = Device::factory()->create();
        $ipv4 = Ipv4Address::factory()->make();

        $trapText = "$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 302:12:56:24.81
SNMPv2-MIB::snmpTrapOID.0 FORTINET-FORTIGATE-MIB::fgTrapIpsSignature
FORTINET-CORE-MIB::fnSysSerial.0 $device->serial
SNMPv2-MIB::sysName.0 $device->hostname
FORTINET-FORTIGATE-MIB::fgIpsTrapSigId.0 47173
FORTINET-FORTIGATE-MIB::fgIpsTrapSrcIp.0 $ipv4->ipv4_address
FORTINET-FORTIGATE-MIB::fgIpsTrapSigMsg.0 UPnP.SSDP.M.Search.Anomaly";

        $message = "IPS signature UPnP.SSDP.M.Search.Anomaly detected from $ipv4->ipv4_address with Fortiguard ID 47173";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 4);

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle fgTrapIpsSignature trap');
    }
}
