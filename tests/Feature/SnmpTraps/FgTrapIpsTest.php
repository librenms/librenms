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
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Enum\Severity;
use LibreNMS\Tests\Traits\RequiresDatabase;

class FgTrapIpsTest extends SnmpTrapTestCase
{
    use RequiresDatabase;
    use DatabaseTransactions;

    public function testIpsAnomaly(): void
    {
        $device = Device::factory()->create(); /** @var Device $device */
        $ipv4 = Ipv4Address::factory()->make(); /** @var Ipv4Address $ipv4 */
        $this->assertTrapLogsMessage("$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 302:12:56:24.81
SNMPv2-MIB::snmpTrapOID.0 FORTINET-FORTIGATE-MIB::fgTrapIpsAnomaly
FORTINET-CORE-MIB::fnSysSerial.0 $device->serial
SNMPv2-MIB::sysName.0 $device->hostname
FORTINET-FORTIGATE-MIB::fgIpsTrapSigId.0 2
FORTINET-FORTIGATE-MIB::fgIpsTrapSrcIp.0 $ipv4->ipv4_address
FORTINET-FORTIGATE-MIB::fgIpsTrapSigMsg.0 tcp_src_session",
            "DDoS prevention triggered. Source: $ipv4->ipv4_address Protocol: tcp_src_session",
            'Could not handle fgTrapIpsAnomaly trap',
            [Severity::Warning],
            $device,
        );
    }

    public function testIpsPkgUdate(): void
    {
        $device = Device::factory()->create(); /** @var Device $device */
        $this->assertTrapLogsMessage("$device->hostname
UDP: [$device->ip]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 302:12:56:24.81
SNMPv2-MIB::snmpTrapOID.0 FORTINET-FORTIGATE-MIB::fgTrapIpsPkgUpdate
FORTINET-CORE-MIB::fnSysSerial.0 $device->serial
SNMPv2-MIB::sysName.0 $device->hostname",
            "IPS package updated on $device->hostname",
            'Could not handle fgTrapIpsPkgUpdate trap',
            device: $device,
        );
    }

    public function testIpsSignature(): void
    {
        $device = Device::factory()->create(); /** @var Device $device */
        $ipv4 = Ipv4Address::factory()->make(); /** @var Ipv4Address $ipv4 */
        $this->assertTrapLogsMessage("{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 302:12:56:24.81
SNMPv2-MIB::snmpTrapOID.0 FORTINET-FORTIGATE-MIB::fgTrapIpsSignature
FORTINET-CORE-MIB::fnSysSerial.0 $device->serial
SNMPv2-MIB::sysName.0 $device->hostname
FORTINET-FORTIGATE-MIB::fgIpsTrapSigId.0 47173
FORTINET-FORTIGATE-MIB::fgIpsTrapSrcIp.0 $ipv4->ipv4_address
FORTINET-FORTIGATE-MIB::fgIpsTrapSigMsg.0 UPnP.SSDP.M.Search.Anomaly",
            "IPS signature UPnP.SSDP.M.Search.Anomaly detected from $ipv4->ipv4_address with Fortiguard ID 47173",
            'Could not handle fgTrapIpsSignature trap',
            [Severity::Warning],
            $device,
        );
    }
}
