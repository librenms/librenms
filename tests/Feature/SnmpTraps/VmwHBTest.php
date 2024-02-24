<?php
/**
 * VmwHBTest.php
 * -Description-
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
 *
 * Tests vmwVmHBLost and vmwVmHBDetected traps from VMWare ESXi hosts.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2019 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use LibreNMS\Enum\Severity;

class VmwHBTest extends SnmpTrapTestCase
{
    public function testVmwVmHBLostTrap(): void
    {
        $guest = Device::factory()->make(); /** @var Device $guest */
        $this->assertTrapLogsMessage("{{ hostname }}
UDP: [{{ ip }}]:28386->[10.10.10.100]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 5:18:30:26.00
SNMPv2-MIB::snmpTrapOID.0 VMWARE-VMINFO-MIB::vmwVmHBLost
VMWARE-VMINFO-MIB::vmwVmID.0 28 VMWARE-VMINFO-MIB::vmwVmConfigFilePath.0 /vmfs/volumes/50101bda-eaf6ac7e-7e44-d4ae5267fb9f/$guest->hostname/$guest->hostname.vmx
VMWARE-VMINFO-MIB::vmwVmDisplayName.28 $guest->hostname
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $guest->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"public\"
SNMPv2-MIB::snmpTrapEnterprise.0 VMWARE-PRODUCTS-MIB::vmwESX",
            "Heartbeat from guest $guest->hostname lost",
            'Could not handle VmwVmHBLostTrap',
            [Severity::Warning],
        );
    }

    public function testVmwVmHBDetectedTrap(): void
    {
        $guest = Device::factory()->make(); /** @var Device $guest */
        $this->assertTrapLogsMessage("{{ hostname }}
UDP: [{{ ip }}]:28386->[10.10.10.100]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 5:18:30:26.00
SNMPv2-MIB::snmpTrapOID.0 VMWARE-VMINFO-MIB::vmwVmHBDetected
VMWARE-VMINFO-MIB::vmwVmID.0 28 VMWARE-VMINFO-MIB::vmwVmConfigFilePath.0 /vmfs/volumes/50101bda-eaf6ac7e-7e44-d4ae5267fb9f/$guest->hostname/$guest->hostname.vmx
VMWARE-VMINFO-MIB::vmwVmDisplayName.28 $guest->hostname
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $guest->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"public\"
SNMPv2-MIB::snmpTrapEnterprise.0 VMWARE-PRODUCTS-MIB::vmwESX",
            "Heartbeat from guest $guest->hostname detected",
            'Could not handle VmwVmHBDetectedTrap',
            [Severity::Ok],
        );
    }
}
