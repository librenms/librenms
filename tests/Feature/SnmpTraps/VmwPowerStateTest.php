<?php
/**
 * VmwPowerStateTest.php
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
 * Tests vmwVmPoweredOff, vmwVmPoweredOn, and vmwVmSuspended traps from VMWare ESXi hosts.
 *
 * @link       https://www.librenms.org
 * @copyright  2019 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use App\Models\Vminfo;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;

class VmwPowerStateTest extends SnmpTrapTestCase
{
    public function testVmwVmPoweredOffTrap()
    {
        $device = Device::factory()->create();
        $guest = Vminfo::factory()->create(['device_id' => $device->device_id]);

        $trapText = "$device->hostname
UDP: [$device->ip]:28386->[10.10.10.100]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 5:18:30:26.00
SNMPv2-MIB::snmpTrapOID.0 VMWARE-VMINFO-MIB::vmwVmPoweredOff
VMWARE-VMINFO-MIB::vmwVmID.0 28 VMWARE-VMINFO-MIB::vmwVmConfigFilePath.0 /vmfs/volumes/50101bda-eaf6ac7e-7e44-d4ae5267fb9f/$guest->vmwVmDisplayName/$guest->vmwVmDisplayName.vmx
VMWARE-VMINFO-MIB::vmwVmDisplayName.28 $guest->vmwVmDisplayName
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"public\"
SNMPv2-MIB::snmpTrapEnterprise.0 VMWARE-PRODUCTS-MIB::vmwESX";

        $trap = new Trap($trapText);
        $message = "Guest $guest->vmwVmDisplayName was powered off";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle VmwVmPoweredOffTrap');
    }

    public function testVmwVmPoweredONTrap()
    {
        $device = Device::factory()->create();
        $guest = Vminfo::factory()->create(['device_id' => $device->device_id]);

        $trapText = "$device->hostname
UDP: [$device->ip]:28386->[10.10.10.100]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 5:18:30:26.00
SNMPv2-MIB::snmpTrapOID.0 VMWARE-VMINFO-MIB::vmwVmPoweredOn
VMWARE-VMINFO-MIB::vmwVmID.0 28 VMWARE-VMINFO-MIB::vmwVmConfigFilePath.0 /vmfs/volumes/50101bda-eaf6ac7e-7e44-d4ae5267fb9f/$guest->vmwVmDisplayName/$guest->vmwVmDisplayName.vmx
VMWARE-VMINFO-MIB::vmwVmDisplayName.28 $guest->vmwVmDisplayName
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"public\"
SNMPv2-MIB::snmpTrapEnterprise.0 VMWARE-PRODUCTS-MIB::vmwESX";

        $trap = new Trap($trapText);
        $message = "Guest $guest->vmwVmDisplayName was powered on";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle VmwVmPoweredOnTrap');
    }

    public function testVmwVmSuspendedTrap()
    {
        $device = Device::factory()->create();
        $guest = Vminfo::factory()->create(['device_id' => $device->device_id]);

        $trapText = "$device->hostname
UDP: [$device->ip]:28386->[10.10.10.100]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 5:18:30:26.00
SNMPv2-MIB::snmpTrapOID.0 VMWARE-VMINFO-MIB::vmwVmSuspended
VMWARE-VMINFO-MIB::vmwVmID.0 28 VMWARE-VMINFO-MIB::vmwVmConfigFilePath.0 /vmfs/volumes/50101bda-eaf6ac7e-7e44-d4ae5267fb9f/$guest->vmwVmDisplayName/$guest->vmwVmDisplayName.vmx
VMWARE-VMINFO-MIB::vmwVmDisplayName.28 $guest->vmwVmDisplayName
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 \"public\"
SNMPv2-MIB::snmpTrapEnterprise.0 VMWARE-PRODUCTS-MIB::vmwESX";

        $trap = new Trap($trapText);
        $message = "Guest $guest->vmwVmDisplayName has been suspended";
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'trap', 2);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle VmwVmSuspendedTrap');
    }
}
