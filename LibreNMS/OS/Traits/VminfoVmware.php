<?php
/*
 * VminfoVmware.php
 *
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2023 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Traits;

use App\Models\Vminfo;
use Illuminate\Support\Collection;
use LibreNMS\Enum\PowerState;

trait VminfoVmware
{
    public function discoverVmInfo(): Collection
    {
        echo 'VMware VM: ';

        /*
         * Fetch the Virtual Machine information.
         *
         *  VMWARE-VMINFO-MIB::vmwVmDisplayName.224 = STRING: My First VM
         *  VMWARE-VMINFO-MIB::vmwVmGuestOS.224 = STRING: windows7Server64Guest
         *  VMWARE-VMINFO-MIB::vmwVmMemSize.224 = INTEGER: 8192 megabytes
         *  VMWARE-VMINFO-MIB::vmwVmState.224 = STRING: poweredOn
         *  VMWARE-VMINFO-MIB::vmwVmVMID.224 = INTEGER: 224
         *  VMWARE-VMINFO-MIB::vmwVmCpus.224 = INTEGER: 2
         */

        $vm_info = \SnmpQuery::hideMib()->walk('VMWARE-VMINFO-MIB::vmwVmTable');

        return $vm_info->mapTable(function ($data, $vmwVmVMID) {
            $data['vm_type'] = 'vmware';
            $data['vmwVmVMID'] = $vmwVmVMID;
            $data['vmwVmState'] = PowerState::STATES[$data['vmwVmState']] ?? PowerState::UNKNOWN;

            /*
             * If VMware Tools is not running then don't overwrite the GuestOS with the error
             * message, but just leave it as it currently is.
             */
            if (str_contains($data['vmwVmGuestOS'], 'tools not ')) {
                unset($data['vmwVmGuestOS']);
            }

            return new Vminfo($data);
        });
    }
}
